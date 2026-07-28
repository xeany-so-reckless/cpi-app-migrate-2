<?php

namespace App\Http\Controllers\SerahTerima;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SerahTerimaBatch;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SerahTerimaController extends Controller
{
    /**
     * Menampilkan halaman utama. Menggantikan #mainPage di Index.html lama.
     */
    public function index(): View
    {
        $products = Product::query()
            ->active()
            ->orderBy('code')
            ->get(['code', 'name'])
            ->map(fn (Product $p) => ['code' => $p->code, 'name' => $p->name])
            ->values();

        return view('serah-terima.index', [
            'products' => $products,
        ]);
    }

    /**
     * Menggantikan getDataByDate() di code.gs.
     * Filter tanggal pakai prefix kode_produksi, bukan kolom tanggal asli
     * (tetap konsisten dengan cara kerja aslinya).
     */
    public function data(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');

        $query = SerahTerimaBatch::with(['produk', 'tallyProduksi', 'tallyGudang'])
            ->orderBy('id');

        if ($tanggal) {
            $prefix = SerahTerimaBatch::getKodeDatePrefix($tanggal);
            $query->where('kode_produksi', 'like', $prefix.'%');
        }

        $batches = $query->get()->map(fn (SerahTerimaBatch $b) => $this->formatBatch($b));

        return response()->json($batches);
    }

    /**
     * Menggantikan saveDataTallyProduksi() di code.gs.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeRole($request, ['tally_produksi'], 'create');

        $data = $this->validateBatchInput($request);

        $produk = Product::where('code', $data['kode_item'])->first();
        if (! $produk) {
            return response()->json(['message' => 'Kode item tidak ditemukan.'], 422);
        }

        if ($this->isDuplicateTrolly($data['tanggal_produksi'], $data['no_trolly'])) {
            return response()->json([
                'message' => 'Nomor Trolly sudah pernah dimasukkan pada kode produksi tanggal ini!',
            ], 422);
        }

        $kodeProduksi = SerahTerimaBatch::generateKodeProduksi($data['tanggal_produksi'], $data['kode_item']);
        $user = $request->user('tally');

        $qrText = "Kode Prod: {$kodeProduksi}\nOleh Tally Prod: {$user->name}\nStatus: Recorded";
        $qrProdUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.urlencode($qrText);

        $batch = new SerahTerimaBatch([
            'kode_produksi'    => $kodeProduksi,
            'tanggal_produksi' => $data['tanggal_produksi'],
            'no_trolly'        => $data['no_trolly'],
            'produk_id'        => $produk->id,
            'jumlah_bag'       => $data['jumlah_bag'],
            'status_approval'  => 'BELUM APPROVED',
            'qr_prod_url'      => $qrProdUrl,
        ]);

        $this->applyBagSlots($batch, $data['jumlah_bag'], $data['kg_bags']);
        $batch->tally_produksi_user_id = $user->id;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'create',
            "{$user->employee_code} ({$user->name}) membuat data produksi baru dengan Kode Produksi: {$kodeProduksi} (Trolly {$data['no_trolly']}, {$data['jumlah_bag']} bag)",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil direkam dengan Kode Produksi: '.$kodeProduksi,
        ]);
    }

    /**
     * Menggantikan updateKoreksiProduksi() di code.gs.
     * Tally Produksi tidak boleh mengedit lagi kalau Tally Gudang sudah
     * mulai memproses batch ini (ada bag yang sudah diverifikasi, atau
     * kode cell sudah disahkan). Supervisor tetap boleh override kapan saja.
     */
    public function update(Request $request, SerahTerimaBatch $batch): JsonResponse
    {
        $this->authorizeRole($request, ['tally_produksi', 'supervisor'], 'update');

        $user = $request->user('tally');
        $kodeProduksiLama = $batch->kode_produksi;

        if ($user->hasRole('tally_produksi') && $this->isLockedByGudang($batch)) {
            ActivityLogger::log(
                'serah_terima',
                'update',
                "DITOLAK: {$user->employee_code} ({$user->name}) mencoba mengoreksi {$kodeProduksiLama} tapi sudah diproses Tally Gudang",
                $user
            );

            return response()->json([
                'message' => 'Data tidak bisa diedit karena Tally Gudang sudah memproses batch ini. Hubungi Supervisor jika perlu koreksi.',
            ], 422);
        }

        $data = $this->validateBatchInput($request);

        $produk = Product::where('code', $data['kode_item'])->first();
        if (! $produk) {
            return response()->json(['message' => 'Kode item tidak ditemukan.'], 422);
        }

        $kodeProduksi = SerahTerimaBatch::generateKodeProduksi($data['tanggal_produksi'], $data['kode_item']);

        $batch->kode_produksi = $kodeProduksi;
        $batch->tanggal_produksi = $data['tanggal_produksi'];
        $batch->no_trolly = $data['no_trolly'];
        $batch->produk_id = $produk->id;
        $batch->jumlah_bag = $data['jumlah_bag'];

        $this->applyBagSlots($batch, $data['jumlah_bag'], $data['kg_bags']);

        $batch->status_approval = 'BELUM APPROVED';
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'update',
            "{$user->employee_code} ({$user->name}) mengoreksi data {$kodeProduksiLama} menjadi Kode Produksi: {$kodeProduksi}",
            $user
        );

        return response()->json(['success' => true, 'message' => 'Koreksi Berhasil Diperbarui!']);
    }

    /**
     * Menggantikan updateBagStatusWH() di code.gs.
     * $bagIndex dari JS = 0-based, dikonversi ke kolom 1-based di sini.
     */
    public function updateBagStatus(Request $request, SerahTerimaBatch $batch, int $bagIndex): JsonResponse
    {
        $this->authorizeRole($request, ['tally_gudang'], 'verify');

        $data = $request->validate([
            'status' => ['required', 'in:OK VERIFIED,TOLAK (REJECT)'],
        ]);

        $col = $bagIndex + 1;
        if ($col < 1 || $col > 10) {
            abort(422, 'Index bag tidak valid.');
        }

        $batch->{"status_bag_{$col}"} = $data['status'];
        $user = $request->user('tally');
        $batch->tally_gudang_user_id = $user->id;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'verify',
            "{$user->employee_code} ({$user->name}) mengubah status bag #{$col} pada {$batch->kode_produksi} menjadi {$data['status']}",
            $user
        );

        return response()->json(['success' => true]);
    }

    /**
     * Menggantikan pemanggilan updateBagStatusWH() berulang di
     * verifikasiSemuaBag() (JS) - di sini digabung jadi 1 request.
     */
    public function verifyAllBags(Request $request, SerahTerimaBatch $batch): JsonResponse
    {
        $this->authorizeRole($request, ['tally_gudang'], 'verify');

        $data = $request->validate([
            'status' => ['required', 'in:OK VERIFIED,TOLAK (REJECT)'],
        ]);

        for ($i = 1; $i <= $batch->jumlah_bag; $i++) {
            $batch->{"status_bag_{$i}"} = $data['status'];
        }

        $user = $request->user('tally');
        $batch->tally_gudang_user_id = $user->id;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'verify',
            "{$user->employee_code} ({$user->name}) memverifikasi semua bag ({$batch->jumlah_bag} bag) pada {$batch->kode_produksi} menjadi {$data['status']}",
            $user
        );

        return response()->json(['success' => true]);
    }

    /**
     * Menggantikan finalizeTallyGudang() di code.gs.
     */
    public function finalize(Request $request, SerahTerimaBatch $batch): JsonResponse
    {
        $this->authorizeRole($request, ['tally_gudang'], 'update');

        $data = $request->validate([
            'kode_cell' => ['required', 'string', 'max:50'],
        ]);

        $batch->kode_cell = $data['kode_cell'];
        $user = $request->user('tally');
        $batch->tally_gudang_user_id = $user->id;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'update',
            "{$user->employee_code} ({$user->name}) menyelesaikan Tally Gudang untuk {$batch->kode_produksi} dengan Kode Cell: {$data['kode_cell']}",
            $user
        );

        return response()->json(['success' => true]);
    }

    /**
     * Menggantikan approveDokumen() di code.gs.
     */
    public function approve(Request $request, SerahTerimaBatch $batch): JsonResponse
    {
        $this->authorizeRole($request, ['supervisor'], 'approve');

        $user = $request->user('tally');
        $namaGudang = $batch->tallyGudang->name ?? '-';

        $summary = "Kode Prod: {$batch->kode_produksi}\nWH Cell: {$batch->kode_cell}\nWH Verifikator: {$namaGudang}\nApproved By SPV: {$user->name}";
        $barcodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.urlencode($summary);

        $batch->status_approval = 'VERIFIED & APPROVED';
        $batch->barcode_url = $barcodeUrl;
        $batch->supervisor_user_id = $user->id;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'approve',
            "{$user->employee_code} ({$user->name}) menyetujui dokumen {$batch->kode_produksi} (WH Cell: {$batch->kode_cell}, Verifikator: {$namaGudang})",
            $user
        );

        return response()->json(['success' => true]);
    }

    /**
     * Menggantikan hapusDataTrolly() di code.gs.
     */
    public function destroy(Request $request, SerahTerimaBatch $batch): JsonResponse
    {
        $this->authorizeRole($request, ['supervisor'], 'delete');

        $user = $request->user('tally');
        $kodeProduksi = $batch->kode_produksi;
        $noTrolly = $batch->no_trolly;

        $batch->delete();

        ActivityLogger::log(
            'serah_terima',
            'delete',
            "{$user->employee_code} ({$user->name}) menghapus data Kode Produksi: {$kodeProduksi} (Trolly {$noTrolly})",
            $user
        );

        return response()->json(['success' => true]);
    }

    /**
     * $logAction wajib salah satu dari 8 action baku (login, logout,
     * create, update, delete, approve, verify, sign) supaya konsisten
     * dengan badge warna di Panel IT. Kalau ditolak, tetap dicatat
     * dengan action yang sama seperti yang seharusnya terjadi, tapi
     * deskripsi diberi prefix "DITOLAK:" supaya tetap jelas dibedakan.
     */
    private function authorizeRole(Request $request, array $roles, string $logAction): void
    {
        $user = $request->user('tally');

        if (! $user->hasAnyRole($roles)) {
            ActivityLogger::log(
                'serah_terima',
                $logAction,
                "DITOLAK: {$user->employee_code} ({$user->name}) mencoba aksi ini tapi role tidak diizinkan (butuh: ".implode(', ', $roles).')',
                $user
            );

            abort(403, 'Anda tidak memiliki akses untuk aksi ini.');
        }
    }

    private function validateBatchInput(Request $request): array
    {
        return $request->validate([
            'tanggal_produksi'  => ['required', 'date'],
            'kode_item'         => ['required', 'string'],
            'no_trolly'         => ['required', 'string'],
            'jumlah_bag'        => ['required', 'integer', 'min:1', 'max:10'],
            'kg_bags'           => ['required', 'array'],
            'kg_bags.*'         => ['nullable', 'numeric'],
        ]);
    }

    private function isDuplicateTrolly(string $tanggalProduksi, string $noTrolly, ?int $exceptId = null): bool
    {
        $prefix = SerahTerimaBatch::getKodeDatePrefix($tanggalProduksi);

        return SerahTerimaBatch::where('kode_produksi', 'like', $prefix.'%')
            ->whereRaw('UPPER(no_trolly) = ?', [strtoupper(trim($noTrolly))])
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    private function applyBagSlots(SerahTerimaBatch $batch, int $jumlahBag, array $kgBags): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $kg = $kgBags[$i - 1] ?? 0;
            $batch->{"kg_bag_{$i}"} = $kg;
            $batch->{"status_bag_{$i}"} = $i <= $jumlahBag ? 'PENDING' : '-';
        }
    }

    /**
     * True kalau Tally Gudang sudah mulai memproses batch ini:
     * ada minimal 1 bag yang statusnya bukan PENDING/-, atau kode cell
     * sudah disahkan. Dipakai untuk mengunci edit oleh Tally Produksi.
     */
    private function isLockedByGudang(SerahTerimaBatch $batch): bool
    {
        if (! empty($batch->kode_cell)) {
            return true;
        }

        for ($i = 1; $i <= $batch->jumlah_bag; $i++) {
            $status = $batch->{"status_bag_{$i}"};
            if ($status && ! in_array($status, ['PENDING', '-'], true)) {
                return true;
            }
        }

        return false;
    }

    private function formatBatch(SerahTerimaBatch $b): array
    {
        return [
            'colIndex'          => $b->id,
            'id'                => $b->kode_produksi,
            'timestamp'         => $b->created_at->format('d/m/Y H:i:s'),
            'noTrolly'          => $b->no_trolly,
            'namaItem'          => $b->produk->name ?? '-',
            'kodeItem'          => $b->produk->code ?? '',
            'jumlahBag'         => $b->jumlah_bag,
            'kgBags'            => $b->kg_bags_array,
            'statusBags'        => $b->status_bags_array,
            'totalKg'           => $b->total_kg,
            'kodeCell'          => $b->kode_cell,
            'statusApprove'     => $b->status_approval,
            'qrProdUrl'         => $b->qr_prod_url,
            'barcodeUrl'        => $b->barcode_url,
            'namaTallyProd'     => $b->tallyProduksi->name ?? '',
            'namaTallyWh'       => $b->tallyGudang->name ?? '',
            'isLockedByGudang'  => $this->isLockedByGudang($b),
        ];
    }
}