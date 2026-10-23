<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    /**
     * Semua jenis PO yang diterima. FEHT = transformasi stok (tanpa rit,
     * tanpa produk), perilakunya mengikuti FEH1 (nomor PO tetap unik).
     */
    private const JENIS_PO = ['FEH0', 'FEH1', 'FEH2', 'FEHM', 'FEHT'];

    /**
     * Jenis PO yang boleh punya nomor PO sama sampai maksimal 2 baris
     * (produksi lintas hari: hari ke-1 pengiriman, hari ke-2 serah terima).
     * Jenis lain tetap 1 nomor PO = 1 baris.
     */
    private const JENIS_BOLEH_DOBEL = ['FEH2', 'FEHM'];

    private const MAKS_BARIS_DOBEL = 2;

    public function index(): View
    {
        return view('ppic.purchase-order');
    }

    public function data(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['user', 'product'])->orderByDesc('tanggal');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                    ->orWhere('jenis_po', 'like', "%{$search}%");
            });
        }

        // Filter bulan (format: yyyy-MM), bisa dipakai bareng search.
        // Konsisten dengan pola filter bulan di PlanningController::data().
        if ($request->filled('bulan')) {
            $query->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$request->query('bulan')]);
        }

        $orders = $query->get()->map(fn (PurchaseOrder $po) => [
            'id'           => $po->id,
            'jenisPo'      => $po->jenis_po,
            'nomorPo'      => $po->nomor_po,
            'namaProduk'   => $po->product->name ?? '-',
            'jumlahRit'    => $po->jumlah_rit,
            'tanggal'      => $po->tanggal->format('Y-m-d'),
            'tanggalLabel' => $po->tanggal->format('d/m/Y'),
            'namaUser'     => $po->user->name ?? '-',
            'isTeco'       => $po->isTeco(),
            'tecoAtLabel'  => $po->teco_at?->format('d/m/Y H:i'),
        ]);

        return response()->json($orders);
    }

    /**
     * DIUBAH: aturan nomor PO.
     *
     * - FEH0, FEH1, FEHT : nomor PO tetap unik (1 nomor = 1 baris).
     * - FEH2, FEHM       : 1 nomor PO boleh maksimal 2 baris AKTIF, dengan
     *                      syarat jenis PO sama, tanggal beda, dan untuk
     *                      FEHM produknya sama. Validasi ada di
     *                      cekAturanNomorPo() dan dijalankan dalam
     *                      transaksi supaya input bersamaan tidak lolos.
     *
     * Validasi 'unique:purchase_orders,nomor_po' DIHAPUS karena unique di
     * DB sudah diganti unique komposit (nomor_po, tanggal).
     *
     * jumlah_rit hanya WAJIB (dan hanya disimpan) untuk FEH0. produk_id
     * hanya WAJIB (dan hanya disimpan) untuk FEHM. Untuk jenis lain nomor
     * rit ditentukan mandiri oleh tim LB Report saat truk datang (lihat
     * LbReportController::storeSebelum()).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis_po'   => ['required', 'string', Rule::in(self::JENIS_PO)],
            'nomor_po'   => ['required', 'string', 'max:100'],
            'tanggal'    => ['required', 'date'],
            'jumlah_rit' => ['required_if:jenis_po,FEH0', 'nullable', 'integer', 'min:1'],
            'produk_id'  => ['required_if:jenis_po,FEHM', 'nullable', 'exists:products,id'],
        ]);

        $user = $request->user('tally');

        $jenisPo = $data['jenis_po'];
        $nomorPo = trim($data['nomor_po']);
        $jumlahRit = $jenisPo === 'FEH0' ? (int) $data['jumlah_rit'] : 0;
        $produkId = $jenisPo === 'FEHM' ? (int) $data['produk_id'] : null;

        $error = DB::transaction(function () use ($data, $jenisPo, $nomorPo, $jumlahRit, $produkId, $user) {
            $error = $this->cekAturanNomorPo($nomorPo, $jenisPo, $produkId, $data['tanggal']);
            if ($error !== null) {
                return $error;
            }

            PurchaseOrder::create([
                'jenis_po'   => $jenisPo,
                'nomor_po'   => $nomorPo,
                'tanggal'    => $data['tanggal'],
                // Kolom jumlah_rit di DB default 0 (unsignedInteger, tidak
                // nullable) - jenis selain FEH0 disimpan 0.
                'jumlah_rit' => $jumlahRit,
                'produk_id'  => $produkId,
                'user_id'    => $user->id,
            ]);

            return null;
        }, 3);

        if ($error !== null) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        ActivityLogger::log(
            'ppic',
            'create',
            "{$user->employee_code} ({$user->name}) menambah PO baru: {$nomorPo} ({$jenisPo})",
            $user
        );

        return response()->json(['success' => true, 'message' => 'PO berhasil disimpan.']);
    }

    /**
     * Koreksi jumlah_rit dan/atau tanggal PO. Dipakai saat aktual di
     * lapangan beda dari input awal PPIC (misal input 14 rit, ternyata
     * ada kendala jadi cuma 13 rit di lapangan).
     *
     * Sengaja TIDAK block PO yang sudah TECO - beda dengan destroy().
     * Koreksi rit sering baru ketahuan setelah truk selesai jalan, dan PO
     * bisa saja sudah keburu ditandai TECO duluan.
     *
     * jumlah_rit hanya boleh dikoreksi untuk jenis_po = FEH0. Untuk jenis
     * lain field ini di-ignore diam-diam (bukan error, karena frontend
     * memang menyembunyikan field ini untuk jenis PO selain FEH0).
     *
     * DIUBAH: tanggal baru dicek supaya tidak bentrok dengan baris lain
     * yang nomor POnya sama (termasuk yang ada di Riwayat Terhapus,
     * karena unique DB (nomor_po, tanggal) tetap menghitungnya).
     *
     * Hanya nomor_po, jenis_po, dan produk_id yang TIDAK bisa diubah lewat
     * endpoint ini - kalau itu yang salah input, harus dihapus lalu input
     * ulang lewat store().
     */
    public function updateRit(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $data = $request->validate([
            'jumlah_rit' => ['nullable', 'integer', 'min:1'],
            'tanggal'    => ['required', 'date'],
        ]);

        $isFeh0 = $purchaseOrder->jenis_po === 'FEH0';

        if ($isFeh0 && (! isset($data['jumlah_rit']) || $data['jumlah_rit'] < 1)) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah Rit wajib diisi, minimal 1, untuk jenis PO FEH0.',
            ], 422);
        }

        $bentrok = PurchaseOrder::withTrashed()
            ->where('nomor_po', $purchaseOrder->nomor_po)
            ->where('id', '!=', $purchaseOrder->id)
            ->where('tanggal', $data['tanggal'])
            ->first();

        if ($bentrok) {
            $tanggalLabel = $bentrok->tanggal->format('d/m/Y');
            $pesan = $bentrok->trashed()
                ? "Nomor PO {$purchaseOrder->nomor_po} tanggal {$tanggalLabel} ada di Riwayat Terhapus. Restore atau hapus permanen terlebih dahulu, atau pilih tanggal lain."
                : "Nomor PO {$purchaseOrder->nomor_po} sudah ada di tanggal {$tanggalLabel}. Pilih tanggal lain.";

            return response()->json(['success' => false, 'message' => $pesan], 422);
        }

        $user = $request->user('tally');
        $ritLama = $purchaseOrder->jumlah_rit;
        $tanggalLama = $purchaseOrder->tanggal->format('d/m/Y');

        $purchaseOrder->tanggal = $data['tanggal'];
        if ($isFeh0) {
            $purchaseOrder->jumlah_rit = $data['jumlah_rit'];
        }
        $purchaseOrder->save();

        $tanggalBaru = $purchaseOrder->tanggal->format('d/m/Y');

        $perubahan = [];
        if ($isFeh0 && $ritLama !== $purchaseOrder->jumlah_rit) {
            $perubahan[] = "jumlah rit dari {$ritLama} menjadi {$purchaseOrder->jumlah_rit}";
        }
        if ($tanggalLama !== $tanggalBaru) {
            $perubahan[] = "tanggal dari {$tanggalLama} menjadi {$tanggalBaru}";
        }
        $infoPerubahan = $perubahan ? ' ('.implode(', ', $perubahan).')' : ' (tidak ada perubahan nilai)';

        ActivityLogger::log(
            'ppic',
            'update',
            "{$user->employee_code} ({$user->name}) mengoreksi PO {$purchaseOrder->nomor_po}{$infoPerubahan}",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => "PO {$purchaseOrder->nomor_po} berhasil diperbarui.",
        ]);
    }

    /**
     * PO yang sudah TECO tidak boleh dihapus - harus dibuka (unTECO) dulu
     * lewat toggleTeco() kalau memang perlu dihapus. Proteksi ini di
     * server, bukan cuma sembunyikan tombol di frontend.
     *
     * Hapus di sini adalah SOFT DELETE (Model pakai trait SoftDeletes) -
     * PO tidak benar-benar hilang dari database, bisa di-restore lewat
     * restore() kalau ternyata salah hapus.
     */
    public function destroy(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->isTeco()) {
            return response()->json([
                'success' => false,
                'message' => "PO {$purchaseOrder->nomor_po} sudah TECO dan tidak bisa dihapus. Buka status TECO-nya terlebih dahulu jika memang perlu dihapus.",
            ], 422);
        }

        $user = $request->user('tally');
        $nomorPo = $purchaseOrder->nomor_po;

        $purchaseOrder->delete();

        ActivityLogger::log(
            'ppic',
            'delete',
            "{$user->employee_code} ({$user->name}) menghapus PO: {$nomorPo}",
            $user
        );

        return response()->json(['success' => true]);
    }

    /**
     * Toggle status TECO (Technically Complete, istilah SAP) PO.
     * Kalau belum TECO -> ditandai TECO (teco_at diisi waktu sekarang).
     * Kalau sudah TECO -> dibuka lagi / unTECO (teco_at dikosongkan).
     *
     * TECO tetap PER BARIS (per tanggal). Untuk PO ganda FEH2/FEHM, PO
     * hari ke-2 hanya dipakai produksi (serah terima), tidak dipakai di
     * LB Report, jadi tidak perlu disinkronkan antar baris.
     *
     * Efek TECO: PO hilang dari dropdown "Nomor PO" di form Sebelum
     * Bongkar (LB Report) - lihat LbReportController::listPurchaseOrders().
     * Tidak ada proteksi tambahan di level server submit LB Report -
     * murni penyaringan tampilan dropdown sesuai keputusan bisnis.
     * Tidak berpengaruh ke Dashboard Produksi Bulanan.
     */
    public function toggleTeco(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $user = $request->user('tally');
        $akanTeco = ! $purchaseOrder->isTeco();

        $purchaseOrder->forceFill([
            'teco_at' => $akanTeco ? now() : null,
        ])->save();

        ActivityLogger::log(
            'ppic',
            'update',
            "{$user->employee_code} ({$user->name}) ".($akanTeco ? 'menandai' : 'membuka kembali')." PO {$purchaseOrder->nomor_po} ".($akanTeco ? 'sebagai TECO' : 'dari status TECO'),
            $user
        );

        return response()->json([
            'success' => true,
            'isTeco'  => $purchaseOrder->isTeco(),
            'message' => $akanTeco
                ? "PO {$purchaseOrder->nomor_po} berhasil ditandai TECO."
                : "PO {$purchaseOrder->nomor_po} berhasil dibuka kembali dari status TECO.",
        ]);
    }

    /**
     * Daftar PO yang sudah dihapus (soft-deleted), dipakai untuk section
     * "Riwayat Terhapus" di halaman Input PO supaya PPIC bisa restore
     * kalau ternyata salah hapus.
     */
    public function trashed(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::onlyTrashed()
            ->with(['user', 'product'])
            ->orderByDesc('deleted_at')
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'id'             => $po->id,
                'jenisPo'        => $po->jenis_po,
                'nomorPo'        => $po->nomor_po,
                'namaProduk'     => $po->product->name ?? '-',
                'tanggalLabel'   => $po->tanggal->format('d/m/Y'),
                'namaUser'       => $po->user->name ?? '-',
                'deletedAtLabel' => $po->deleted_at?->format('d/m/Y H:i'),
            ]);

        return response()->json($orders);
    }

    /**
     * Restore PO yang sudah dihapus (soft-deleted). Pakai $id mentah
     * (bukan route-model-binding {purchaseOrder} biasa), karena binding
     * normal otomatis skip baris yang soft-deleted.
     *
     * DIUBAH: restore dicek dengan aturan nomor PO yang sama seperti
     * store(), supaya hasil restore tidak membuat nomor PO jadi 3 baris,
     * jenis campur, produk FEHM beda, atau melanggar keunikan jenis lain.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $user = $request->user('tally');

        $hasil = DB::transaction(function () use ($id) {
            $purchaseOrder = PurchaseOrder::onlyTrashed()->lockForUpdate()->findOrFail($id);

            $error = $this->cekAturanNomorPo(
                $purchaseOrder->nomor_po,
                $purchaseOrder->jenis_po,
                $purchaseOrder->produk_id,
                $purchaseOrder->tanggal->format('Y-m-d'),
                $purchaseOrder->id
            );

            if ($error !== null) {
                return ['error' => $error, 'po' => $purchaseOrder];
            }

            $purchaseOrder->restore();

            return ['error' => null, 'po' => $purchaseOrder];
        }, 3);

        $purchaseOrder = $hasil['po'];

        if ($hasil['error'] !== null) {
            return response()->json([
                'success' => false,
                'message' => 'PO tidak bisa dipulihkan. '.$hasil['error'],
            ], 422);
        }

        ActivityLogger::log(
            'ppic',
            'update',
            "{$user->employee_code} ({$user->name}) me-restore PO yang terhapus: {$purchaseOrder->nomor_po}",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => "PO {$purchaseOrder->nomor_po} berhasil dipulihkan.",
        ]);
    }

    /**
     * Hapus PO secara PERMANEN (beneran hilang dari database, bukan soft
     * delete). Hanya bisa dilakukan terhadap PO yang sudah di-soft-delete
     * lebih dulu (harus lewat Riwayat Terhapus) - jaga-jaga supaya tidak
     * sengaja menghapus permanen PO yang masih aktif.
     *
     * Setelah dihapus permanen, kombinasi nomor_po + tanggal otomatis
     * lepas dan bisa dipakai ulang.
     */
    public function forceDeletePermanent(Request $request, int $id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::onlyTrashed()->findOrFail($id);
        $user = $request->user('tally');
        $nomorPo = $purchaseOrder->nomor_po;

        $purchaseOrder->forceDelete();

        ActivityLogger::log(
            'ppic',
            'delete',
            "{$user->employee_code} ({$user->name}) menghapus PERMANEN PO: {$nomorPo}",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => "PO {$nomorPo} berhasil dihapus permanen.",
        ]);
    }

    /**
     * BARU - Cek apakah sebuah baris PO (nomor, jenis, produk, tanggal)
     * boleh ada, terhadap baris lain yang nomor POnya sama. Dipakai oleh
     * store() dan restore(). Harus dipanggil di dalam DB::transaction()
     * karena memakai lockForUpdate().
     *
     * @param  int|null  $kecualiId  id baris yang diabaikan (baris itu sendiri saat restore)
     * @return string|null  pesan error, atau null kalau lolos
     */
    private function cekAturanNomorPo(
        string $nomorPo,
        string $jenisPo,
        ?int $produkId,
        string $tanggal,
        ?int $kecualiId = null
    ): ?string {
        // withTrashed(): baris soft-deleted tetap ikut dicek karena unique
        // DB (nomor_po, tanggal) tetap menghitungnya.
        $rows = PurchaseOrder::withTrashed()
            ->where('nomor_po', $nomorPo)
            ->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))
            ->lockForUpdate()
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        // 1. Nomor PO + tanggal yang sama sudah ada (aktif / terhapus).
        $bentrok = $rows->first(fn (PurchaseOrder $r) => $r->tanggal->format('Y-m-d') === $tanggal);
        if ($bentrok) {
            $tanggalLabel = $bentrok->tanggal->format('d/m/Y');

            return $bentrok->trashed()
                ? "Nomor PO {$nomorPo} tanggal {$tanggalLabel} ada di Riwayat Terhapus. Restore atau hapus permanen terlebih dahulu."
                : "Nomor PO {$nomorPo} sudah ada di tanggal {$tanggalLabel}.";
        }

        // 2. Jenis yang tidak boleh dobel: nomor PO harus unik.
        if (! in_array($jenisPo, self::JENIS_BOLEH_DOBEL, true)) {
            $semuaTerhapus = $rows->every(fn (PurchaseOrder $r) => $r->trashed());

            return $semuaTerhapus
                ? "Nomor PO {$nomorPo} ada di Riwayat Terhapus. Restore atau hapus permanen terlebih dahulu jika ingin memakainya lagi."
                : "Nomor PO {$nomorPo} sudah terdaftar. Jenis PO {$jenisPo} tidak boleh memakai nomor PO yang sama lebih dari sekali.";
        }

        // 3. FEH2 / FEHM: hanya baris AKTIF yang dihitung.
        $aktif = $rows->reject(fn (PurchaseOrder $r) => $r->trashed())->values();

        if ($aktif->isEmpty()) {
            return null;
        }

        $pertama = $aktif->first();

        if ($pertama->jenis_po !== $jenisPo) {
            return "Nomor PO {$nomorPo} sudah terdaftar dengan jenis {$pertama->jenis_po}. Jenis PO harus sama ({$pertama->jenis_po}).";
        }

        if ($aktif->count() >= self::MAKS_BARIS_DOBEL) {
            return "Nomor PO {$nomorPo} sudah punya ".self::MAKS_BARIS_DOBEL.' tanggal (maksimal '.self::MAKS_BARIS_DOBEL.').';
        }

        if ($jenisPo === 'FEHM' && (int) $pertama->produk_id !== (int) $produkId) {
            $namaProduk = $pertama->product->name ?? '-';

            return "Nomor PO {$nomorPo} sudah terdaftar dengan produk {$namaProduk}. Produk harus sama.";
        }

        return null;
    }
}