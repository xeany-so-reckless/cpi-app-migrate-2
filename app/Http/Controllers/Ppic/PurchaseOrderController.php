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
     * DIUBAH: jumlah_rit sekarang cuma WAJIB untuk jenis_po = FEHM.
     * Untuk jenis PO lain, jumlah rit tidak diketahui di depan oleh PPIC
     * - nomor rit ditentukan mandiri oleh tim LB Report saat truk datang
     * (lihat LbReportController::storeSebelum()), jadi PPIC tidak perlu
     * dan tidak bisa menentukannya di sini.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis_po'   => ['required', 'string', 'max:100'],
            'nomor_po'   => ['required', 'string', 'max:100', 'unique:purchase_orders,nomor_po'],
            'tanggal'    => ['required', 'date'],
            'jumlah_rit' => ['required_if:jenis_po,FEHM', 'nullable', 'integer', 'min:1'],
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

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
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
}