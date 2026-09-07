<?php

namespace App\Http\Controllers\SerahTerima;

use App\Http\Controllers\Controller;
use App\Models\Cell;
use App\Models\CellReservation;
use App\Models\Product;
use App\Models\SerahTerimaBatch;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $query = SerahTerimaBatch::with(['produk', 'tallyProduksi', 'tallyGudang', 'adminGudang', 'supervisor', 'cellReservation'])
            ->orderBy('id');

        if ($tanggal) {
            $prefix = SerahTerimaBatch::getKodeDatePrefix($tanggal);
            $query->where('kode_produksi', 'like', $prefix.'%');
        }

        $batches = $query->get()->map(fn (SerahTerimaBatch $b) => $this->formatBatch($b));

        return response()->json($batches);
    }

    /**
     * BARU - Menampilkan halaman Dashboard Rekap.
     * Halaman ini murni shell HTML, semua data diambil lewat fetch()
     * ke endpoint dashboardData() di bawah (konsisten dengan pola
     * halaman index yang sudah ada: render dulu, data nyusul via JS).
     * Bisa diakses semua role (tidak ada authorizeRole di sini).
     */
    public function dashboard(): View
    {
        return view('serah-terima.dashboard');
    }

    /**
     * BARU - Endpoint API agregasi untuk Dashboard Rekap.
     * Menerima parameter query: dari=YYYY-MM-DD & sampai=YYYY-MM-DD
     * Default: 7 hari terakhir kalau parameter tidak dikirim.
     *
     * Return 4 blok data:
     * - summary   : total bag, total kg, total batch untuk rentang terpilih
     * - trend     : total bag & kg per tanggal (buat chart trend harian)
     * - per_produk: breakdown per produk, diurutkan dari yang paling banyak
     * - per_cell  : breakdown per cell, diurutkan dari yang paling banyak
     */
    public function dashboardData(Request $request): JsonResponse
    {
        $dari = $request->query('dari') ?: now()->subDays(6)->format('Y-m-d');
        $sampai = $request->query('sampai') ?: now()->format('Y-m-d');

        // Ekspresi SQL untuk menjumlahkan kg_bag_1 s.d kg_bag_10 jadi total kg
        // per baris, dilakukan di level SQL (bukan tarik semua row lalu
        // diolah di PHP) supaya tetap ringan meskipun data sudah banyak.
        $kgSumExpr = collect(range(1, 10))
            ->map(fn ($i) => "COALESCE(kg_bag_{$i}, 0)")
            ->implode(' + ');

        $baseQuery = DB::table('serah_terima_batches')
    ->whereBetween('tanggal_produksi', [$dari, $sampai]);

        // 1. Ringkasan total untuk rentang terpilih
        $summaryRow = (clone $baseQuery)
            ->selectRaw("SUM(jumlah_bag) as total_bag, SUM({$kgSumExpr}) as total_kg, COUNT(*) as total_batch")
            ->first();

        // 2. Trend per tanggal (buat grafik)
        $trend = (clone $baseQuery)
            ->selectRaw("tanggal_produksi, SUM(jumlah_bag) as total_bag, SUM({$kgSumExpr}) as total_kg")
            ->groupBy('tanggal_produksi')
            ->orderBy('tanggal_produksi')
            ->get();

        // 3. Breakdown per produk
        $perProduk = (clone $baseQuery)
            ->join('products', 'products.id', '=', 'serah_terima_batches.produk_id')
            ->selectRaw("products.code as kode_produk, products.name as nama_produk, SUM(serah_terima_batches.jumlah_bag) as total_bag, SUM({$kgSumExpr}) as total_kg")
            ->groupBy('products.id', 'products.code', 'products.name')
            ->orderByDesc('total_bag')
            ->get();

        // 4. Breakdown per cell
        $perCell = (clone $baseQuery)
            ->whereNotNull('kode_cell')
            ->selectRaw("kode_cell, SUM(jumlah_bag) as total_bag, SUM({$kgSumExpr}) as total_kg, COUNT(*) as total_batch")
            ->groupBy('kode_cell')
            ->orderByDesc('total_bag')
            ->get();

        return response()->json([
            'summary' => [
                'dari'        => $dari,
                'sampai'      => $sampai,
                'total_bag'   => (int) ($summaryRow->total_bag ?? 0),
                'total_kg'    => round((float) ($summaryRow->total_kg ?? 0), 1),
                'total_batch' => (int) ($summaryRow->total_batch ?? 0),
            ],
            'trend' => $trend->map(fn ($t) => [
                'tanggal'   => $t->tanggal_produksi,
                'total_bag' => (int) $t->total_bag,
                'total_kg'  => round((float) $t->total_kg, 1),
            ]),
            'per_produk' => $perProduk->map(fn ($p) => [
                'kode_produk' => $p->kode_produk,
                'nama_produk' => $p->nama_produk,
                'total_bag'   => (int) $p->total_bag,
                'total_kg'    => round((float) $p->total_kg, 1),
            ]),
            'per_cell' => $perCell->map(fn ($c) => [
                'kode_cell'   => $c->kode_cell,
                'total_bag'   => (int) $c->total_bag,
                'total_kg'    => round((float) $c->total_kg, 1),
                'total_batch' => (int) $c->total_batch,
            ]),
        ]);
    }

    /**
     * BARU - Daftar Cell aktif beserta sisa kapasitas live saat ini.
     * Dipakai untuk dropdown TWH saat mau membuat reservasi baru, supaya
     * bisa lihat dulu Cell mana yang masih ada sisa sebelum pilih.
     */
    public function listCells(Request $request): JsonResponse
    {
        $cells = Cell::query()
            ->where('is_active', true)
            ->orderBy('kode_cell')
            ->get()
            ->map(fn (Cell $c) => [
                'id'            => $c->id,
                'kode_cell'     => $c->kode_cell,
                'kapasitas_max' => $c->kapasitas_max,
                'sisa'          => $c->sisaKapasitas(),
            ]);

        return response()->json($cells);
    }

    /**
     * BARU - TWH pilih Cell duluan (sebelum TPR input batch), sistem
     * hitung sisa kapasitas cell tsb dan buat reservasi.
     * max_bag_allowed = MIN(10, sisa kapasitas saat itu).
     */
    public function storeCellReservation(Request $request): JsonResponse
    {
        $this->authorizeRole($request, ['tally_gudang'], 'create');

        $data = $request->validate([
            'cell_id' => ['required', 'exists:cells,id'],
        ]);

        $cell = Cell::findOrFail($data['cell_id']);
        $user = $request->user('tally');

        if (! $cell->is_active) {
            return response()->json(['message' => 'Cell ini sedang tidak aktif.'], 422);
        }

        $sisa = $cell->sisaKapasitas();

        if ($sisa <= 0) {
            return response()->json([
                'message' => "Cell {$cell->kode_cell} sudah penuh, sisa kapasitas 0 bag.",
            ], 422);
        }

        $maxBagAllowed = min(10, $sisa);

        $reservation = CellReservation::create([
            'cell_id'            => $cell->id,
            'max_bag_allowed'    => $maxBagAllowed,
            'status'             => 'PENDING',
            'created_by_user_id' => $user->id,
        ]);

        ActivityLogger::log(
            'serah_terima',
            'create',
            "{$user->employee_code} ({$user->name}) membuat reservasi Cell {$cell->kode_cell} (maks {$maxBagAllowed} bag, sisa kapasitas saat itu: {$sisa} bag)",
            $user
        );

        return response()->json([
            'success' => true,
            'reservation' => [
                'id'              => $reservation->id,
                'kode_cell'       => $cell->kode_cell,
                'max_bag_allowed' => $reservation->max_bag_allowed,
            ],
        ]);
    }

    /**
     * BARU - Daftar reservasi Cell yang masih PENDING (belum dipakai TPR),
     * dipakai untuk dropdown di form input Tally Produksi.
     */
    public function listCellReservations(Request $request): JsonResponse
    {
        $reservations = CellReservation::with(['cell.products'])
            ->where('status', 'PENDING')
            ->orderBy('created_at')
            ->get()
            ->map(fn (CellReservation $r) => [
                'id'              => $r->id,
                'kode_cell'       => $r->cell->kode_cell,
                'max_bag_allowed' => $r->max_bag_allowed,
                'dibuat_oleh'     => $r->createdBy->name ?? '-',
                'dibuat_pada'     => $r->created_at->format('d/m/Y H:i'),
                // Kode produk yang sah untuk Cell reservasi ini (Master
                // Produk-Cell), dipakai frontend untuk validasi real-time
                // saat TPR ketik Kode Item - tanpa perlu submit dulu.
                'produk_codes'    => $r->cell->products->pluck('code')->values(),
            ]);

        return response()->json($reservations);
    }

    /**
     * Menggantikan saveDataTallyProduksi() di code.gs.
     * REVISI: sekarang wajib pilih reservation_id (dibuat TWH duluan).
     * jumlah_bag dibatasi oleh max_bag_allowed reservasi, dan produk yang
     * dipilih harus terdaftar untuk Cell reservasi tsb (Master Produk-Cell).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeRole($request, ['tally_produksi'], 'create');

        $data = $this->validateBatchInput($request);

        $reservation = CellReservation::with('cell')->find($data['reservation_id']);

        if (! $reservation || $reservation->status !== 'PENDING') {
            return response()->json(['message' => 'Reservasi Cell tidak valid atau sudah dipakai. Minta Tally Gudang buat reservasi baru.'], 422);
        }

        $produk = Product::where('code', $data['kode_item'])->first();
        if (! $produk) {
            return response()->json(['message' => 'Kode item tidak ditemukan.'], 422);
        }

        $produkBolehDiCell = $produk->cells()->where('cells.id', $reservation->cell_id)->exists();
        if (! $produkBolehDiCell) {
            return response()->json([
                'message' => "Produk \"{$produk->name}\" tidak terdaftar untuk Cell {$reservation->cell->kode_cell}. Pilih reservasi Cell lain yang sesuai.",
            ], 422);
        }

        if ($data['jumlah_bag'] > $reservation->max_bag_allowed) {
            return response()->json([
                'message' => "Jumlah bag ({$data['jumlah_bag']}) melebihi sisa kapasitas reservasi Cell {$reservation->cell->kode_cell} (maks {$reservation->max_bag_allowed} bag).",
            ], 422);
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

        $batch = DB::transaction(function () use ($data, $produk, $kodeProduksi, $qrProdUrl, $user, $reservation) {
            $batch = new SerahTerimaBatch([
                'kode_produksi'    => $kodeProduksi,
                'tanggal_produksi' => $data['tanggal_produksi'],
                'no_trolly'        => $data['no_trolly'],
                'produk_id'        => $produk->id,
                'jumlah_bag'       => $data['jumlah_bag'],
                'status_approval'  => 'BELUM APPROVED',
                'qr_prod_url'      => $qrProdUrl,
                'kode_cell'        => $reservation->cell->kode_cell,
            ]);

            $this->applyBagSlots($batch, $data['jumlah_bag'], $data['kg_bags']);
            $batch->tally_produksi_user_id = $user->id;
            $batch->save();

            $reservation->update([
                'status'   => 'USED',
                'batch_id' => $batch->id,
            ]);

            return $batch;
        });

        ActivityLogger::log(
            'serah_terima',
            'create',
            "{$user->employee_code} ({$user->name}) membuat data produksi baru dengan Kode Produksi: {$kodeProduksi} (Trolly {$data['no_trolly']}, {$data['jumlah_bag']} bag, Cell {$reservation->cell->kode_cell})",
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
     *
     * CATATAN: koreksi TIDAK mengubah reservasi Cell (Cell & jumlah_bag
     * maksimalnya tetap terikat ke reservasi awal). Kalau jumlah_bag mau
     * dinaikkan melebihi max_bag_allowed reservasi awal, harus ditolak.
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

        $data = $this->validateBatchInput($request, forUpdate: true);

        $produk = Product::where('code', $data['kode_item'])->first();
        if (! $produk) {
            return response()->json(['message' => 'Kode item tidak ditemukan.'], 422);
        }

        $reservation = $batch->cellReservation;
        if ($reservation) {
            $produkBolehDiCell = $produk->cells()->where('cells.id', $reservation->cell_id)->exists();
            if (! $produkBolehDiCell) {
                return response()->json([
                    'message' => "Produk \"{$produk->name}\" tidak terdaftar untuk Cell {$reservation->cell->kode_cell} (Cell reservasi batch ini). Hapus & buat ulang dengan reservasi baru kalau perlu ganti produk.",
                ], 422);
            }

            if ($data['jumlah_bag'] > $reservation->max_bag_allowed) {
                return response()->json([
                    'message' => "Jumlah bag melebihi kapasitas reservasi Cell {$reservation->cell->kode_cell} (maks {$reservation->max_bag_allowed} bag).",
                ], 422);
            }
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
     * BARU - Approval oleh Admin Gudang / Supervisor Gudang (QC kedua,
     * sejajar dengan SPV Produksi, bukan berurutan). Generate QR sendiri.
     * Barcode final baru terbit kalau SPV Produksi JUGA sudah approve.
     */
    public function approveAdminGudang(Request $request, SerahTerimaBatch $batch): JsonResponse
    {
        $this->authorizeRole($request, ['admin_gudang', 'supervisor_gudang'], 'approve');

        $user = $request->user('tally');

        $summary = "Kode Prod: {$batch->kode_produksi}\nWH Cell: {$batch->kode_cell}\nApproved By Gudang: {$user->name}";
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.urlencode($summary);

        $batch->status_approval_admin_gudang = 'VERIFIED & APPROVED';
        $batch->admin_gudang_user_id = $user->id;
        $batch->qr_admin_gudang_url = $qrUrl;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'approve',
            "{$user->employee_code} ({$user->name}) approve sisi Gudang untuk {$batch->kode_produksi} (Cell: {$batch->kode_cell})",
            $user
        );

        $this->maybeFinalizeBarcode($batch);

        return response()->json(['success' => true]);
    }

    /**
     * REVISI dari approveDokumen() di code.gs - sekarang jadi approval
     * SPV Produksi (independen, sejajar Admin/Supervisor Gudang, bukan
     * approval tunggal). Barcode final baru terbit kalau sisi Gudang
     * JUGA sudah approve.
     */
    public function approveSpv(Request $request, SerahTerimaBatch $batch): JsonResponse
    {
        $this->authorizeRole($request, ['supervisor'], 'approve');

        $user = $request->user('tally');

        $summary = "Kode Prod: {$batch->kode_produksi}\nApproved By SPV Produksi: {$user->name}";
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.urlencode($summary);

        $batch->status_approval_spv = 'VERIFIED & APPROVED';
        $batch->supervisor_user_id = $user->id;
        $batch->qr_spv_url = $qrUrl;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'approve',
            "{$user->employee_code} ({$user->name}) approve sisi SPV Produksi untuk {$batch->kode_produksi}",
            $user
        );

        $this->maybeFinalizeBarcode($batch);

        return response()->json(['success' => true]);
    }

    /**
     * BARU - kalau KEDUA approval (Admin/Supervisor Gudang & SPV Produksi)
     * sudah VERIFIED & APPROVED, generate barcode final gabungan.
     */
    private function maybeFinalizeBarcode(SerahTerimaBatch $batch): void
    {
        $adminGudangOk = $batch->status_approval_admin_gudang === 'VERIFIED & APPROVED';
        $spvOk = $batch->status_approval_spv === 'VERIFIED & APPROVED';

        if (! ($adminGudangOk && $spvOk) || $batch->barcode_url) {
            return; // salah satu belum approve, atau barcode final sudah pernah dibuat
        }

        $namaGudang = $batch->adminGudang->name ?? '-';
        $namaSpv = $batch->supervisor->name ?? '-';

        $summary = "Kode Prod: {$batch->kode_produksi}\nWH Cell: {$batch->kode_cell}\nApproved Gudang: {$namaGudang}\nApproved SPV Produksi: {$namaSpv}";
        $barcodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.urlencode($summary);

        $batch->status_approval = 'VERIFIED & APPROVED';
        $batch->barcode_url = $barcodeUrl;
        $batch->save();

        ActivityLogger::log(
            'serah_terima',
            'approve',
            "Barcode final terbit untuk {$batch->kode_produksi} (Gudang: {$namaGudang}, SPV Produksi: {$namaSpv})",
            null
        );
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

    private function validateBatchInput(Request $request, bool $forUpdate = false): array
    {
        $rules = [
            'tanggal_produksi'  => ['required', 'date'],
            'kode_item'         => ['required', 'string'],
            'no_trolly'         => ['required', 'string'],
            'jumlah_bag'        => ['required', 'integer', 'min:1', 'max:10'],
            'kg_bags'           => ['required', 'array'],
            'kg_bags.*'         => ['nullable', 'numeric'],
        ];

        // reservation_id cuma wajib saat create, bukan saat update
        // (koreksi tetap terikat ke reservasi/Cell yang sudah ada).
        if (! $forUpdate) {
            $rules['reservation_id'] = ['required', 'integer', 'exists:cell_reservations,id'];
        }

        return $request->validate($rules);
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
     * ada minimal 1 bag yang statusnya bukan PENDING/-.
     *
     * CATATAN: sejak revisi reservasi Cell, kode_cell SELALU terisi sejak
     * batch dibuat (dari reservasi TWH), jadi tidak bisa lagi dipakai
     * sebagai penanda "sudah diproses gudang" seperti sebelumnya - cukup
     * andalkan status verifikasi bag saja.
     */
    private function isLockedByGudang(SerahTerimaBatch $batch): bool
    {
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
            'maxBagAllowed'     => $b->cellReservation->max_bag_allowed ?? null,
            'statusApprove'     => $b->status_approval,
            'statusApproveAdminGudang' => $b->status_approval_admin_gudang,
            'statusApproveSpv'  => $b->status_approval_spv,
            'qrProdUrl'         => $b->qr_prod_url,
            'qrAdminGudangUrl'  => $b->qr_admin_gudang_url,
            'qrSpvUrl'          => $b->qr_spv_url,
            'barcodeUrl'        => $b->barcode_url,
            'namaTallyProd'     => $b->tallyProduksi->name ?? '',
            'namaTallyWh'       => $b->tallyGudang->name ?? '',
            'namaAdminGudang'   => $b->adminGudang->name ?? '',
            'namaSpv'           => $b->supervisor->name ?? '',
            'isLockedByGudang'  => $this->isLockedByGudang($b),
        ];
    }
}