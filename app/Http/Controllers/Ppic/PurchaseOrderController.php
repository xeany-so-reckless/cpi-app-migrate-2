<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
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
     * DIUBAH: jumlah_rit sekarang cuma WAJIB untuk jenis_po = FEH0.
     * produk_id tetap wajib khusus jenis_po = FEHM. Untuk jenis PO lain
     * (selain FEH0), jumlah rit tidak diketahui di depan oleh PPIC -
     * nomor rit ditentukan mandiri oleh tim LB Report saat truk datang
     * (lihat LbReportController::storeSebelum()), jadi PPIC tidak perlu
     * dan tidak bisa menentukannya di sini.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis_po'   => ['required', 'string', 'max:100'],
            'nomor_po'   => ['required', 'string', 'max:100', 'unique:purchase_orders,nomor_po'],
            'tanggal'    => ['required', 'date'],
            'jumlah_rit' => ['required_if:jenis_po,FEH0', 'nullable', 'integer', 'min:1'],
            'produk_id'  => ['required_if:jenis_po,FEHM', 'nullable', 'exists:products,id'],
        ]);

        $user = $request->user('tally');

        PurchaseOrder::create([
            'jenis_po'   => $data['jenis_po'],
            'nomor_po'   => $data['nomor_po'],
            'tanggal'    => $data['tanggal'],
            // Kolom jumlah_rit di DB default 0 (unsignedInteger, tidak
            // nullable) - untuk non-FEHM yang tidak mengisi, jatuhkan ke
            // 0 secara eksplisit supaya tidak "Undefined array key".
            'jumlah_rit' => $data['jumlah_rit'] ?? 0,
            'produk_id'  => $data['produk_id'] ?? null,
            'user_id'    => $user->id,
        ]);

        ActivityLogger::log(
            'ppic',
            'create',
            "{$user->employee_code} ({$user->name}) menambah PO baru: {$data['nomor_po']} ({$data['jenis_po']})",
            $user
        );

        return response()->json(['success' => true, 'message' => 'PO berhasil disimpan.']);
    }

    /**
     * DIUBAH: PO yang sudah TECO tidak boleh dihapus - harus dibuka
     * (unTECO) dulu lewat toggleTeco() kalau memang perlu dihapus.
     * Proteksi ini di server, bukan cuma sembunyikan tombol di frontend.
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
     * BARU - Toggle status TECO (Technically Complete, istilah SAP) PO.
     * Kalau belum TECO -> ditandai TECO (teco_at diisi waktu sekarang).
     * Kalau sudah TECO -> dibuka lagi / unTECO (teco_at dikosongkan).
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
     * BARU - Daftar PO yang sudah dihapus (soft-deleted), dipakai untuk
     * section "Riwayat Terhapus" di halaman Input PO supaya PPIC bisa
     * restore kalau ternyata salah hapus.
     */
    public function trashed(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::onlyTrashed()
            ->with(['user', 'product'])
            ->orderByDesc('deleted_at')
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'id'            => $po->id,
                'jenisPo'       => $po->jenis_po,
                'nomorPo'       => $po->nomor_po,
                'namaProduk'    => $po->product->name ?? '-',
                'tanggalLabel'  => $po->tanggal->format('d/m/Y'),
                'namaUser'      => $po->user->name ?? '-',
                'deletedAtLabel' => $po->deleted_at?->format('d/m/Y H:i'),
            ]);

        return response()->json($orders);
    }

    /**
     * BARU - Restore PO yang sudah dihapus (soft-deleted). Pakai $id
     * mentah (bukan route-model-binding {purchaseOrder} biasa), karena
     * binding normal otomatis skip baris yang soft-deleted - jadi PO
     * yang mau di-restore justru tidak akan ketemu lewat binding biasa.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::onlyTrashed()->findOrFail($id);
        $user = $request->user('tally');

        $purchaseOrder->restore();

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
}