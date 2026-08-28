<?php

namespace App\Http\Controllers\ProduksiFresh;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProduksiFresh;
use App\Models\PurchaseOrder;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProduksiFreshController extends Controller
{
    public function workspace(Request $request): View
    {
        return view('produksi-fresh.workspace', [
            'tipeInput' => $request->session()->get('produksi_fresh_tipe'),
        ]);
    }

    /**
     * Daftar semua PO dari PPIC untuk dropdown Nomor PO.
     *
     * DIUBAH: PO yang sudah TECO (Technically Complete, ditandai PPIC)
     * dikeluarkan dari daftar - sama seperti LbReportController, PO yang
     * sudah ditutup PPIC tidak boleh lagi dipilih untuk input baru.
     * Dashboard Produksi Bulanan SENGAJA TIDAK ikut difilter (keputusan
     * bisnis: TECO cuma mempengaruhi LB Report & Produksi Fresh).
     */
    public function listPurchaseOrders(): JsonResponse
    {
        $list = PurchaseOrder::whereNull('teco_at')
            ->orderByDesc('tanggal')
            ->get(['nomor_po', 'jenis_po', 'tanggal'])
            ->map(fn (PurchaseOrder $po) => [
                'nomorPo' => $po->nomor_po,
                'jenisPo' => $po->jenis_po,
                'tanggal' => $po->tanggal->format('d/m/Y'),
            ]);

        return response()->json($list);
    }

    /**
     * Daftar produk sesuai tipe yang dipilih SAAT LOGIN (dibaca dari
     * session, BUKAN dari query string) - mencegah user mengakali
     * filter dengan mengirim tipe lain lewat request langsung.
     */
    public function listProducts(Request $request): JsonResponse
    {
        $tipe = $request->session()->get('produksi_fresh_tipe');

        $query = Product::active();
        $query = $tipe === 'main' ? $query->main() : $query->byProduct();

        $list = $query->orderBy('display_order')
            ->get(['id', 'code', 'name', 'category_code'])
            ->map(fn (Product $p) => [
                'id'           => $p->id,
                'code'         => $p->code,
                'name'         => $p->name,
                'categoryCode' => $p->category_code,
            ]);

        return response()->json($list);
    }

    /**
     * Terima banyak baris draft sekaligus (pola "tambah ke draft, submit
     * semua" dari Apps Script lama), insert dalam SATU transaksi - kalau
     * ada 1 baris gagal validasi, semua baris dibatalkan (tidak ada yang
     * tersimpan sebagian).
     *
     * Kode Produksi DIHITUNG ULANG DI SINI (server), tidak dipercaya
     * dari nilai yang mungkin ditampilkan di form client.
     */
    public function store(Request $request): JsonResponse
    {
        $tipe = $request->session()->get('produksi_fresh_tipe');
        if (! $tipe) {
            abort(403, 'Sesi tidak valid, silakan login ulang.');
        }

        $data = $request->validate([
            'rows'               => ['required', 'array', 'min:1'],
            'rows.*.no_po'       => ['required', 'string', 'exists:purchase_orders,nomor_po'],
            'rows.*.kode_produk' => ['required', 'string'],
            'rows.*.qty'         => ['required', 'numeric', 'min:0.01'],
        ]);

        $user = $request->user('tally');

        $inserted = DB::transaction(function () use ($data, $tipe, $user) {
            $rows = [];

            foreach ($data['rows'] as $row) {
                $product = Product::byCode($row['kode_produk'])->active()->first();

                if (! $product) {
                    abort(422, "Kode produk '{$row['kode_produk']}' tidak ditemukan atau tidak aktif.");
                }

                if ($product->type !== $tipe) {
                    $labelTipe = $tipe === 'main' ? 'Main Product' : 'By Product';
                    abort(422, "Kode produk '{$row['kode_produk']}' bukan produk tipe {$labelTipe}.");
                }

                $kodeProduksi = ProduksiFresh::generateKodeProduksi($product->category_code ?? '00');

                $rows[] = ProduksiFresh::create([
                    'no_po'         => strtoupper(trim($row['no_po'])),
                    'user_id'       => $user->id,
                    'tipe_input'    => $tipe,
                    'produk_id'     => $product->id,
                    'kode_produksi' => $kodeProduksi,
                    'qty'           => $row['qty'],
                ]);
            }

            return $rows;
        });

        $noPoList = collect($data['rows'])->pluck('no_po')->unique()->implode(', ');

        ActivityLogger::log(
            'produksi_fresh',
            'create',
            "{$user->employee_code} ({$user->name}) input ".count($inserted)." data produksi Fresh (tipe: {$tipe}, PO: {$noPoList})",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => count($inserted).' data berhasil disimpan ke sistem.',
        ]);
    }
}