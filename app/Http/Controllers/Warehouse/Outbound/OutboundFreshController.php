<?php

namespace App\Http\Controllers\Warehouse\Outbound;

use App\Http\Controllers\Controller;
use App\Models\OutboundFreshShipment;
use App\Models\OutboundFreshShipmentItem;
use App\Models\ProduksiFresh;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * BARU - Outbound Fresh: versi lebih sederhana dari OutboundController
 * (Frozen). TIDAK ada konsep Cell/Tir sama sekali karena produk fresh
 * tidak masuk cell stock, cuma "lewat" saja di warehouse.
 *
 * Alur: Checker pilih No PO (yang sudah punya data histori di
 * Produksi Fresh & masih ada sisa belum ter-outbound) -> sistem
 * tampilkan checklist baris produksi_fresh terkait -> Checker centang
 * yang benar-benar keluar -> simpan sebagai 1 dokumen Outbound Fresh.
 *
 * Sesuai revisi manager: HANYA produk tipe 'main' yang ikut Outbound
 * Fresh (by product tidak ikut) - lihat ProduksiFresh::scopeAvailableForOutbound().
 *
 * view workspace & history tetap 1 halaman gabungan dengan Frozen
 * (toggle tab di sisi frontend) - lihat OutboundController::index()
 * dan ::history() untuk view-nya; controller ini murni data endpoint.
 */
class OutboundFreshController extends Controller
{
    /**
     * Daftar No PO untuk dropdown - hanya No PO yang MASIH punya minimal
     * 1 baris produksi_fresh tersedia (tipe main, belum ter-outbound).
     * Dikelompokkan supaya dropdown bisa menampilkan ringkasan jumlah
     * item & total qty, mirip stockBag/stockKg di dropdown Cell Frozen.
     */
    public function listPurchaseOrdersWithFreshStock(): JsonResponse
    {
        $rows = ProduksiFresh::availableForOutbound()
            ->select('no_po')
            ->selectRaw('COUNT(*) as jumlah_item')
            ->selectRaw('SUM(qty) as total_qty')
            ->groupBy('no_po')
            ->orderBy('no_po')
            ->get()
            ->map(fn ($r) => [
                'noPo'       => $r->no_po,
                'jumlahItem' => (int) $r->jumlah_item,
                'totalQty'   => round((float) $r->total_qty, 2),
            ]);

        return response()->json($rows);
    }

    /**
     * Checklist item untuk 1 No PO (dipanggil saat Checker pilih No PO
     * di dropdown) - daftar baris produksi_fresh yang masih tersedia
     * (analog OutboundController::getCellContents()).
     */
    public function getPoItems(string $noPo): JsonResponse
    {
        $noPo = strtoupper(trim($noPo));

        $items = ProduksiFresh::with('product:id,code,name')
            ->availableForOutbound()
            ->where('no_po', $noPo)
            ->orderBy('created_at')
            ->get()
            ->map(fn (ProduksiFresh $p) => [
                'id'           => $p->id,
                'kodeProduk'   => $p->product->code ?? '-',
                'namaProduk'   => $p->product->name ?? '-',
                'kodeProduksi' => $p->kode_produksi,
                'qty'          => (float) $p->qty,
            ]);

        if ($items->isEmpty()) {
            abort(422, "Tidak ada item yang tersedia untuk No PO '{$noPo}' (mungkin sudah ter-outbound semua).");
        }

        return response()->json([
            'noPo'  => $noPo,
            'items' => $items,
        ]);
    }

    /**
     * Simpan 1 dokumen Outbound Fresh. Payload:
     * {
     *   tanggal, no_po, nama_customer, jam_muat, no_pol, nama_driver,
     *   produksi_fresh_ids: [12, 15, 20, ...]   // id baris produksi_fresh
     *                                           // yang dicentang Checker
     * }
     *
     * PENTING (anti race-condition/manipulasi): id yang dikirim client
     * DIVALIDASI ULANG di server lewat availableForOutbound() - kalau
     * ada id yang ternyata sudah terpakai shipment lain sejak halaman
     * dibuka (atau bukan tipe main), id itu di-skip diam-diam, sama
     * pola dengan processCellOutbound() di OutboundController. Snapshot
     * kode_produk/nama_produk/kode_produksi/qty diambil dari data
     * SERVER saat ini, bukan dari client.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal'              => ['required', 'date'],
            'no_po'                => ['required', 'string', 'max:100'],
            'nama_customer'        => ['required', 'string', 'max:150'],
            'jam_muat'             => ['required'],
            'no_pol'               => ['required', 'string', 'max:20'],
            'nama_driver'          => ['required', 'string', 'max:100'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.produksi_fresh_id' => ['required', 'integer', 'exists:produksi_fresh,id'],
            'items.*.keterangan'        => ['nullable', 'string', 'max:255'],
        ]);

        $noPo = strtoupper(trim($data['no_po']));

        $shipment = DB::transaction(function () use ($data, $noPo, $request) {

            // BARU - ambil daftar id yang dicentang + map keterangan per id
            $ids = collect($data['items'])->pluck('produksi_fresh_id')->all();
            $keteranganMap = collect($data['items'])->pluck('keterangan', 'produksi_fresh_id');
            // Ambil ulang dari server - hanya baris yang MASIH tersedia
            // sekarang (tipe main + belum ter-outbound) untuk No PO ini.
            // Baris yang dikirim client tapi ternyata sudah tidak
            // available lagi (misal kepakai shipment lain, atau bukan
            // No PO ini) otomatis ter-filter keluar di sini.
            $rows = ProduksiFresh::with('product:id,code,name')
                ->availableForOutbound()
                ->where('no_po', $noPo)
                ->whereIn('id', $ids)
                ->get();

            if ($rows->isEmpty()) {
                abort(422, 'Semua item yang dipilih sudah tidak tersedia (mungkin sudah ter-outbound lebih dulu). Silakan muat ulang halaman.');
            }

            $shipment = OutboundFreshShipment::create([
                'tanggal'         => $data['tanggal'],
                'no_po'           => $noPo,
                'nama_customer'   => $data['nama_customer'],
                'jam_muat'        => $data['jam_muat'],
                'no_pol'          => strtoupper(trim($data['no_pol'])),
                'nama_driver'     => $data['nama_driver'],
                'checker_user_id' => $request->user('tally')->id,
                'status'          => 'SELESAI',
            ]);

            foreach ($rows as $row) {
                OutboundFreshShipmentItem::create([
                    'outbound_fresh_shipment_id' => $shipment->id,
                    'produksi_fresh_id'          => $row->id,
                    'kode_produk'                => $row->product->code ?? null,
                    'nama_produk'                => $row->product->name ?? null,
                    'kode_produksi'              => $row->kode_produksi,
                    'qty'                        => $row->qty,
                    'keterangan'                 => $keteranganMap->get($row->id), // BARU
                ]);
            }

            return $shipment;
        });

        ActivityLogger::log(
            'warehouse_outbound_fresh',
            'create',
            "{$request->user('tally')->employee_code} ({$request->user('tally')->name}) input Outbound Fresh No PO {$shipment->no_po}",
            $request->user('tally')
        );

        return response()->json([
            'status'  => 'success',
            'message' => "Outbound Fresh No PO {$shipment->no_po} berhasil disimpan!",
        ]);
    }

    /**
     * BARU - List ringkasan untuk tabel Riwayat tab Fresh, dengan filter
     * opsional: tanggal, checker_user_id, dan pencarian No PO. Pola
     * sama persis dengan OutboundController::historyData() versi Frozen.
     */
    public function historyData(Request $request): JsonResponse
    {
        $query = OutboundFreshShipment::with('checker')->withCount('items');

        if ($tanggal = $request->query('tanggal')) {
            $query->whereDate('tanggal', $tanggal);
        }

        if ($checkerId = $request->query('checker_user_id')) {
            $query->where('checker_user_id', $checkerId);
        }

        if ($search = $request->query('search')) {
            $query->where('no_po', 'like', '%'.strtoupper(trim($search)).'%');
        }

        $shipments = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $result = $shipments->map(fn (OutboundFreshShipment $s) => [
            'id'           => $s->id,
            'tanggal'      => $s->tanggal->format('Y-m-d'),
            'noPo'         => $s->no_po,
            'namaCustomer' => $s->nama_customer,
            'jamMuat'      => $s->jam_muat,
            'noPol'        => $s->no_pol,
            'namaDriver'   => $s->nama_driver,
            'checkerNama'  => $s->checker->name ?? '-',
            'jumlahItem'   => $s->items_count,
            'totalQty'     => round((float) $s->items->sum('qty'), 2),
        ]);

        return response()->json($result);
    }

    /**
     * BARU - Detail 1 dokumen Outbound Fresh (dipanggil saat baris
     * di-expand di tabel Riwayat).
     */
    public function historyDetail(OutboundFreshShipment $outboundFreshShipment): JsonResponse
    {
        $outboundFreshShipment->load('checker', 'items');

        return response()->json([
            'id'           => $outboundFreshShipment->id,
            'tanggal'      => $outboundFreshShipment->tanggal->format('Y-m-d'),
            'noPo'         => $outboundFreshShipment->no_po,
            'namaCustomer' => $outboundFreshShipment->nama_customer,
            'jamMuat'      => $outboundFreshShipment->jam_muat,
            'noPol'        => $outboundFreshShipment->no_pol,
            'namaDriver'   => $outboundFreshShipment->nama_driver,
            'checkerNama'  => $outboundFreshShipment->checker->name ?? '-',
            'items'        => $outboundFreshShipment->items->map(fn (OutboundFreshShipmentItem $item) => [
                'kodeProduk'   => $item->kode_produk,
                'namaProduk'   => $item->nama_produk,
                'kodeProduksi' => $item->kode_produksi,
                'qty'          => (float) $item->qty,
                'keterangan'   => $item->keterangan,
            ]),
        ]);
    }
}
