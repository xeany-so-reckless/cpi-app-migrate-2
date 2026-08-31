<?php

namespace App\Http\Controllers\Warehouse\Outbound;

use App\Http\Controllers\Controller;
use App\Models\Cell;
use App\Models\CellStockAdjustment;
use App\Models\OutboundShipment;
use App\Models\OutboundShipmentCell;
use App\Models\OutboundShipmentCellBag;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutboundController extends Controller
{
    public function index(): View
    {
        return view('warehouse.outbound.workspace');
    }

    /**
     * Daftar Cell untuk dropdown "Kode Cell" di form Outbound. Cuma
     * menampilkan Cell yang benar-benar ada stock-nya (stockBag() > 0) -
     * Cell kosong tidak ada gunanya dipilih untuk dikeluarkan barangnya.
     */
    public function listCellsWithStock(): JsonResponse
    {
        $cells = Cell::where('is_active', true)
            ->orderBy('kode_cell')
            ->get()
            ->filter(fn (Cell $cell) => $cell->stockBag() > 0)
            ->map(fn (Cell $cell) => [
                'id'          => $cell->id,
                'kodeCell'    => $cell->kode_cell,
                'coldStorage' => $cell->cold_storage,
                'lantai'      => $cell->lantai,
                'stockBag'    => $cell->stockBag(),
                'stockKg'     => round($cell->stockKg(), 2),
            ])
            ->values();

        return response()->json($cells);
    }

    /**
     * Isi 1 Cell (dipanggil saat Checker klik salah satu Kode Cell di
     * dropdown) - daftar bag yang bisa dicentang, dari Cell::availableBags().
     */
    public function getCellContents(Cell $cell): JsonResponse
    {
        return response()->json([
            'cell' => [
                'id'       => $cell->id,
                'kodeCell' => $cell->kode_cell,
                'stockBag' => $cell->stockBag(),
                'stockKg'  => round($cell->stockKg(), 2),
            ],
            'bags' => $cell->availableBags(),
        ]);
    }

    /**
     * Simpan 1 DO. Payload:
     * {
     *   tanggal, no_do, nama_customer, jam_muat, no_pol, nama_driver,
     *   cells: [
     *     {
     *       cell_id: 1,
     *       bags: [
     *         { type: 'batch', batch_id: 5, nomor_bag: 3 },
     *         { type: 'generic' }   // ambil SEMUA sisa stock generik cell ini
     *       ]
     *     },
     *     ...
     *   ]
     * }
     *
     * PENTING: kg & kode_produksi TIDAK dipercaya dari input client -
     * semua di-lookup ulang dari data server (batch asli / Cell) supaya
     * tidak bisa dimanipulasi dan supaya konsisten dengan availableBags().
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal'                    => ['required', 'date'],
            'no_do'                      => ['required', 'string', 'max:100'],
            'nama_customer'              => ['required', 'string', 'max:150'],
            'jam_muat'                   => ['required'],
            'no_pol'                     => ['required', 'string', 'max:20'],
            'nama_driver'                => ['required', 'string', 'max:100'],
            'cells'                      => ['required', 'array', 'min:1'],
            'cells.*.cell_id'            => ['required', 'integer', 'exists:cells,id'],
            'cells.*.bags'               => ['required', 'array', 'min:1'],
            'cells.*.bags.*.type'        => ['required', 'in:batch,generic'],
            'cells.*.bags.*.batch_id'    => ['required_if:cells.*.bags.*.type,batch', 'nullable', 'integer'],
            'cells.*.bags.*.nomor_bag'   => ['required_if:cells.*.bags.*.type,batch', 'nullable', 'integer', 'between:1,10'],
        ]);

        $shipment = DB::transaction(function () use ($data, $request) {
            $shipment = OutboundShipment::create([
                'tanggal'         => $data['tanggal'],
                'no_do'           => strtoupper(trim($data['no_do'])),
                'nama_customer'   => $data['nama_customer'],
                'jam_muat'        => $data['jam_muat'],
                'no_pol'          => strtoupper(trim($data['no_pol'])),
                'nama_driver'     => $data['nama_driver'],
                'checker_user_id' => $request->user('tally')->id,
                'status'          => 'SELESAI',
            ]);

            foreach ($data['cells'] as $cellInput) {
                $this->processCellOutbound($shipment, $cellInput);
            }

            return $shipment;
        });

        ActivityLogger::log(
            'warehouse_outbound',
            'create',
            "{$request->user('tally')->employee_code} ({$request->user('tally')->name}) input Outbound No DO {$shipment->no_do}",
            $request->user('tally')
        );

        return response()->json([
            'status'  => 'success',
            'message' => "Outbound No DO {$shipment->no_do} berhasil disimpan!",
        ]);
    }

    /**
     * Proses 1 Cell dalam 1 DO: validasi ulang bag yang dicentang masih
     * benar-benar tersedia (anti race-condition/manipulasi), lalu buat
     * 1 baris cell_stock_adjustment (sumber='outbound') + detail bag-nya.
     */
    private function processCellOutbound(OutboundShipment $shipment, array $cellInput): void
    {
        $cell = Cell::findOrFail($cellInput['cell_id']);

        // Ambil ulang daftar bag yang MASIH tersedia sekarang (bukan
        // dari input client) - supaya kalau ada bag yang sudah kepakai
        // shipment lain sejak halaman dibuka, sistem tidak salah kurang.
        $availableBatchBags = collect($cell->availableBags())
            ->filter(fn ($b) => $b['type'] === 'batch')
            ->keyBy(fn ($b) => $b['batch_id'].'-'.$b['nomor_bag']);

        $totalBag = 0;
        $totalKg = 0.0;
        $warnaBag = ['merah' => 0, 'biru' => 0, 'hijau' => 0, 'kuning' => 0];
        $warnaKg = ['merah' => 0.0, 'biru' => 0.0, 'hijau' => 0.0, 'kuning' => 0.0];
        $bagRowsToInsert = [];
        $pakaiGeneric = false;

        foreach ($cellInput['bags'] as $bagInput) {
            if ($bagInput['type'] === 'generic') {
                $pakaiGeneric = true;
                continue;
            }

            $key = $bagInput['batch_id'].'-'.$bagInput['nomor_bag'];
            $bag = $availableBatchBags->get($key);

            if (! $bag) {
                // Bag ini sudah tidak tersedia lagi (mungkin baru saja
                // keluar lewat DO lain) - lewati saja daripada gagal
                // total, tapi baiknya sistem kasih tahu di response FE
                // kalau ada bag yang di-skip (silakan sesuaikan sesuai
                // kebutuhan UX, sementara di-skip diam-diam).
                continue;
            }

            $totalBag++;
            $totalKg += $bag['kg'];

            if ($bag['tanggal_produksi']) {
                $warna = $this->warnaDariTanggal($bag['tanggal_produksi']);
                $warnaBag[$warna]++;
                $warnaKg[$warna] += $bag['kg'];
            }

            $bagRowsToInsert[] = [
                'batch_id'      => $bag['batch_id'],
                'nomor_bag'     => $bag['nomor_bag'],
                'kg'            => $bag['kg'],
                'kode_produksi' => $bag['kode_produksi'],
                'keterangan'    => null,
            ];
        }

        $genericBagCount = 0;
        $genericKg = 0.0;
        if ($pakaiGeneric) {
            $genericBagCount = $cell->genericAdjustmentBag();
            $genericKg = $cell->genericAdjustmentKg();

            if ($genericBagCount > 0) {
                $totalBag += $genericBagCount;
                $totalKg += $genericKg;

                $bagRowsToInsert[] = [
                    'batch_id'      => null,
                    'nomor_bag'     => null,
                    'kg'            => $genericKg,
                    'kode_produksi' => null,
                    'keterangan'    => 'Stock Adjustment',
                ];
            }
        }

        if ($totalBag === 0) {
            // Tidak ada bag valid yang benar-benar keluar untuk cell ini
            // (semua sudah terpakai DO lain) - lewati cell ini tanpa
            // membuat adjustment kosong.
            return;
        }

        $jumlahSistemSebelumBag = $cell->stockBag();
        $jumlahSistemSebelumKg = $cell->stockKg();

        $adjustment = CellStockAdjustment::create([
            'cell_id'               => $cell->id,
            'jumlah_sistem_sebelum' => $jumlahSistemSebelumBag,
            'jumlah_aktual'         => $jumlahSistemSebelumBag - $totalBag,
            'selisih'               => -$totalBag,
            'kg_sistem_sebelum'     => $jumlahSistemSebelumKg,
            'kg_aktual'             => $jumlahSistemSebelumKg - $totalKg,
            'selisih_kg'            => -$totalKg,
            'bag_merah'             => -$warnaBag['merah'],
            'bag_biru'              => -$warnaBag['biru'],
            'bag_hijau'             => -$warnaBag['hijau'],
            'bag_kuning'            => -$warnaBag['kuning'],
            'kg_merah'              => -$warnaKg['merah'],
            'kg_biru'               => -$warnaKg['biru'],
            'kg_hijau'              => -$warnaKg['hijau'],
            'kg_kuning'             => -$warnaKg['kuning'],
            'sumber'                => 'outbound',
            'nama_file'             => null,
            'user_id'               => $shipment->checker_user_id,
        ]);

        $shipmentCell = OutboundShipmentCell::create([
            'outbound_shipment_id'     => $shipment->id,
            'cell_id'                  => $cell->id,
            'total_bag'                => $totalBag,
            'total_kg'                 => $totalKg,
            'cell_stock_adjustment_id' => $adjustment->id,
        ]);

        foreach ($bagRowsToInsert as $row) {
            OutboundShipmentCellBag::create(array_merge($row, [
                'outbound_shipment_cell_id' => $shipmentCell->id,
            ]));
        }
    }

    /**
     * Merah = Jan-Mar, Biru = Apr-Jun, Hijau = Jul-Sep, Kuning = Okt-Des.
     * Sama seperti definisi di migration cell_stock_adjustments.
     */
    private function warnaDariTanggal(string $tanggalProduksi): string
    {
        $bulan = (int) date('n', strtotime($tanggalProduksi));

        return match (true) {
            $bulan <= 3 => 'merah',
            $bulan <= 6 => 'biru',
            $bulan <= 9 => 'hijau',
            default     => 'kuning',
        };
    }
}
