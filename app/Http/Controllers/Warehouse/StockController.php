<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Cell;
use App\Models\CellStockAdjustment;
use App\Models\Product;
use App\Models\SerahTerimaBatch;
use App\Models\StockTransferBag;
use App\Support\ActivityLogger;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StockController extends Controller
{
    /**
     * Batas kuartal produksi untuk breakdown warna.
     * Merah=Jan-Mar, Biru=Apr-Jun, Hijau=Jul-Sep, Kuning=Okt-Des.
     */
    private const KUARTAL_WARNA = [
        'merah'  => [1, 3],
        'biru'   => [4, 6],
        'hijau'  => [7, 9],
        'kuning' => [10, 12],
    ];

    /**
     * Halaman utama Stock Warehouse - list per Cell + filter.
     * Akses dibatasi lewat middleware route (auth:tally + role:admin_gudang,
     * supervisor_gudang), bukan dicek manual disini - konsisten dengan
     * pola SerahTerimaController.
     */
    public function index(): View
    {
        return view('warehouse.stock');
    }

    /**
     * Data stock per Cell (terpakai/sisa dihitung sama seperti logic
     * Cell::sisaKapasitas(), tapi di-agregasi sekali jalan disini biar
     * tidak N+1 query kalau cell-nya 636 baris (576 utama + 60 RK).
     *
     * Terpakai = SUM jumlah_bag dari reservasi USED (batch nyata) +
     * SUM max_bag_allowed dari reservasi PENDING (masih dipesan, belum
     * dipakai TPR, tapi tetap dihitung supaya tidak dobel-alokasi).
     *
     * REVISI (Pemindahan): tabel stock_transfer_bags adalah riwayat. Total
     * bag yang pernah dipindah KELUAR dari sebuah cell dikurangi, dan total
     * yang pernah dipindah MASUK ditambahkan - termasuk kg dan breakdown
     * warna. Hitungan ini tetap benar walau satu bag dipindah berkali-kali
     * (simpanan -> cell biasa -> simpanan, dst).
     * Daftar produk cell simpanan = produk dari bag yang SAAT INI masih
     * berada di sana (pemindahan terakhir bag itu menuju cell simpanan itu).
     */
    public function data(Request $request): JsonResponse
    {
        $query = Cell::with(['currentProduct:id,code,name,category'])
            ->where('is_active', true);

        if ($request->filled('cold_storage')) {
            $query->where('cold_storage', $request->query('cold_storage'));
        }

        if ($request->filled('lantai')) {
            $query->where('lantai', $request->query('lantai'));
        }

        if ($request->filled('kategori')) {
            $kategori = $request->query('kategori');
            $query->where(function ($q) use ($kategori) {
                $q->whereHas('currentProduct', fn ($qq) => $qq->where('category', $kategori))
                    // Cell simpanan tidak punya currentProduct - cocokkan
                    // lewat produk dari bag yang sedang ada di sana.
                    ->orWhere(function ($qq) use ($kategori) {
                        $qq->where('is_simpanan', true)
                            ->whereIn('id', $this->simpananCellIdsDenganProduk(
                                fn ($p) => $p->where('p.category', $kategori)
                            ));
                    });
            });
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode_cell', 'like', "%{$search}%")
                    ->orWhereHas('currentProduct', fn ($qq) => $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"))
                    ->orWhere(function ($qq) use ($search) {
                        $qq->where('is_simpanan', true)
                            ->whereIn('id', $this->simpananCellIdsDenganProduk(
                                fn ($p) => $p->where(function ($pp) use ($search) {
                                    $pp->where('p.name', 'like', "%{$search}%")
                                        ->orWhere('p.code', 'like', "%{$search}%");
                                })
                            ));
                    });
            });
        }

        $cells = $query->orderBy('kode_cell')->get();

        $usedAgg = DB::table('cell_reservations')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->where('cell_reservations.status', 'USED')
            ->groupBy('cell_reservations.cell_id')
            ->select('cell_reservations.cell_id', DB::raw('SUM(serah_terima_batches.jumlah_bag) as total'))
            ->pluck('total', 'cell_id');

        $pendingAgg = DB::table('cell_reservations')
            ->where('status', 'PENDING')
            ->groupBy('cell_id')
            ->select('cell_id', DB::raw('SUM(max_bag_allowed) as total'))
            ->pluck('total', 'cell_id');

        $adjustmentAgg = DB::table('cell_stock_adjustments')
            ->groupBy('cell_id')
            ->select('cell_id', DB::raw('SUM(selisih) as total'))
            ->pluck('total', 'cell_id');

        // --- KG: dihitung terpisah dari bag. PENDING reservation TIDAK
        // ikut dihitung disini (belum ada berat pasti sebelum TPR input
        // kg per bag), beda dengan versi bag yang ikut hitung PENDING. ---
        $usedKgAgg = DB::table('cell_reservations')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->where('cell_reservations.status', 'USED')
            ->groupBy('cell_reservations.cell_id')
            ->select('cell_reservations.cell_id', DB::raw('SUM(
                serah_terima_batches.kg_bag_1 + serah_terima_batches.kg_bag_2 + serah_terima_batches.kg_bag_3 +
                serah_terima_batches.kg_bag_4 + serah_terima_batches.kg_bag_5 + serah_terima_batches.kg_bag_6 +
                serah_terima_batches.kg_bag_7 + serah_terima_batches.kg_bag_8 + serah_terima_batches.kg_bag_9 +
                serah_terima_batches.kg_bag_10
            ) as total'))
            ->pluck('total', 'cell_id');

        $adjustmentKgAgg = DB::table('cell_stock_adjustments')
            ->groupBy('cell_id')
            ->select('cell_id', DB::raw('SUM(selisih_kg) as total'))
            ->pluck('total', 'cell_id');

        // --- Breakdown warna (kuartal produksi), gabungan dari semua
        // penyesuaian yang pernah ada untuk tiap cell. ---
        $warnaAgg = DB::table('cell_stock_adjustments')
            ->groupBy('cell_id')
            ->select(
                'cell_id',
                DB::raw('SUM(bag_merah) as bag_merah'),
                DB::raw('SUM(bag_biru) as bag_biru'),
                DB::raw('SUM(bag_hijau) as bag_hijau'),
                DB::raw('SUM(bag_kuning) as bag_kuning'),
                DB::raw('SUM(kg_merah) as kg_merah'),
                DB::raw('SUM(kg_biru) as kg_biru'),
                DB::raw('SUM(kg_hijau) as kg_hijau'),
                DB::raw('SUM(kg_kuning) as kg_kuning'),
            )
            ->get()
            ->keyBy('cell_id');

        // --- Breakdown warna dari batch ASLI Inbound - dihitung otomatis
        // dari tanggal_produksi tiap batch (Merah=Jan-Mar, Biru=Apr-Jun,
        // Hijau=Jul-Sep, Kuning=Okt-Des), lalu DIGABUNG dengan hasil
        // upload Excel di atas. Jadi cell yang isinya murni dari Inbound
        // (belum pernah di-upload-Excel-kan) tetap punya breakdown warna. ---
        $kgExpr = 'serah_terima_batches.kg_bag_1 + serah_terima_batches.kg_bag_2 + serah_terima_batches.kg_bag_3 + '.
            'serah_terima_batches.kg_bag_4 + serah_terima_batches.kg_bag_5 + serah_terima_batches.kg_bag_6 + '.
            'serah_terima_batches.kg_bag_7 + serah_terima_batches.kg_bag_8 + serah_terima_batches.kg_bag_9 + '.
            'serah_terima_batches.kg_bag_10';

        $batchWarnaAgg = DB::table('cell_reservations')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->where('cell_reservations.status', 'USED')
            ->whereNotNull('serah_terima_batches.tanggal_produksi')
            ->groupBy('cell_reservations.cell_id')
            ->select(
                'cell_reservations.cell_id',
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 1 AND 3 THEN serah_terima_batches.jumlah_bag ELSE 0 END) as bag_merah"),
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 4 AND 6 THEN serah_terima_batches.jumlah_bag ELSE 0 END) as bag_biru"),
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 7 AND 9 THEN serah_terima_batches.jumlah_bag ELSE 0 END) as bag_hijau"),
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 10 AND 12 THEN serah_terima_batches.jumlah_bag ELSE 0 END) as bag_kuning"),
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 1 AND 3 THEN ({$kgExpr}) ELSE 0 END) as kg_merah"),
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 4 AND 6 THEN ({$kgExpr}) ELSE 0 END) as kg_biru"),
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 7 AND 9 THEN ({$kgExpr}) ELSE 0 END) as kg_hijau"),
                DB::raw("SUM(CASE WHEN MONTH(serah_terima_batches.tanggal_produksi) BETWEEN 10 AND 12 THEN ({$kgExpr}) ELSE 0 END) as kg_kuning"),
            )
            ->get()
            ->keyBy('cell_id');

        // --- Pemindahan: total bag yang pernah KELUAR dari tiap cell dan
        // yang pernah MASUK ke tiap cell (riwayat), lengkap dengan kg &
        // breakdown warna. ---
        $transferKeluarAgg = $this->transferAgg('from_cell_id');
        $transferMasukAgg = $this->transferAgg('to_cell_id');

        // --- Daftar produk unik di tiap cell simpanan, dari bag yang
        // SAAT INI masih ada di sana (cell simpanan tidak punya
        // currentProduct). ---
        $produkSimpanan = $this->bagDiSimpananQuery()
            ->select('t.to_cell_id', 'p.id', 'p.code', 'p.name', 'p.category')
            ->distinct()
            ->orderBy('p.code')
            ->get()
            ->groupBy('to_cell_id');

        $data = $cells->map(function (Cell $c) use (
            $usedAgg, $pendingAgg, $adjustmentAgg, $usedKgAgg, $adjustmentKgAgg,
            $warnaAgg, $batchWarnaAgg, $transferKeluarAgg, $transferMasukAgg, $produkSimpanan
        ) {
            $used = (int) ($usedAgg[$c->id] ?? 0);
            $pending = (int) ($pendingAgg[$c->id] ?? 0);
            $adjustment = (int) ($adjustmentAgg[$c->id] ?? 0);

            $tKeluar = $transferKeluarAgg[$c->id] ?? null;
            $tMasuk = $transferMasukAgg[$c->id] ?? null;
            $keluarBag = (int) ($tKeluar->total_bag ?? 0);
            $masukBag = (int) ($tMasuk->total_bag ?? 0);
            $keluarKg = (float) ($tKeluar->total_kg ?? 0);
            $masukKg = (float) ($tMasuk->total_kg ?? 0);

            $terpakai = max(0, $used + $pending + $adjustment - $keluarBag + $masukBag);

            // REVISI STEP 8: kapasitas acuan sekarang dinamis mengikuti
            // produk yang sedang mengunci Cell ini (fallback ke
            // kapasitas_max statis kalau Cell kosong / kombinasi belum
            // ada di standard_capacities) - lihat Cell::kapasitasBagUntukProduk().
            // Untuk cell simpanan selalu kapasitas_max statis.
            $kapasitasBag = $c->kapasitasBagUntukProduk($c->currentProduct) ?? $c->kapasitas_max;

            $sisa = max(0, $kapasitasBag - $terpakai);
            $persenTerisi = $kapasitasBag > 0
                ? round(($terpakai / $kapasitasBag) * 100, 1)
                : 0;

            $usedKg = (float) ($usedKgAgg[$c->id] ?? 0);
            $adjustmentKg = (float) ($adjustmentKgAgg[$c->id] ?? 0);
            $terpakaiKg = max(0, round($usedKg + $adjustmentKg - $keluarKg + $masukKg, 2));
            $sisaKg = $c->kapasitas_max_kg !== null ? max(0, round((float) $c->kapasitas_max_kg - $terpakaiKg, 2)) : null;
            $persenTerisiKg = ($c->kapasitas_max_kg !== null && $c->kapasitas_max_kg > 0)
                ? round(($terpakaiKg / $c->kapasitas_max_kg) * 100, 1)
                : 0;

            $w = $warnaAgg[$c->id] ?? null;
            $bw = $batchWarnaAgg[$c->id] ?? null;

            $breakdownWarna = [];
            foreach (array_keys(self::KUARTAL_WARNA) as $warna) {
                $bagKey = "bag_{$warna}";
                $kgKey = "kg_{$warna}";

                $breakdownWarna[$warna] = [
                    'bag' => (int) ($w->$bagKey ?? 0)
                        + (int) ($bw->$bagKey ?? 0)
                        - (int) ($tKeluar->$bagKey ?? 0)
                        + (int) ($tMasuk->$bagKey ?? 0),
                    'kg'  => round(
                        (float) ($w->$kgKey ?? 0)
                        + (float) ($bw->$kgKey ?? 0)
                        - (float) ($tKeluar->$kgKey ?? 0)
                        + (float) ($tMasuk->$kgKey ?? 0),
                        2
                    ),
                ];
            }

            // Produk: cell biasa = occupant tunggal (currentProduct),
            // cell simpanan = semua produk unik dari bag yang ada di sana.
            if ($c->isSimpanan()) {
                $produk = ($produkSimpanan[$c->id] ?? collect())
                    ->map(fn ($p) => [
                        'code'     => $p->code,
                        'name'     => $p->name,
                        'category' => $p->category,
                    ])
                    ->values()
                    ->all();
            } else {
                $produk = $c->currentProduct ? [[
                    'code'     => $c->currentProduct->code,
                    'name'     => $c->currentProduct->name,
                    'category' => $c->currentProduct->category,
                ]] : [];
            }

            return [
                'id'             => $c->id,
                'kodeCell'       => $c->kode_cell,
                'coldStorage'    => $c->cold_storage,
                'lantai'         => $c->lantai,
                'isSimpanan'     => $c->isSimpanan(),
                'isKosong'       => $c->isSimpanan() ? $terpakai <= 0 : $c->isKosong(),
                'kapasitasMax'   => $kapasitasBag,
                'terpakai'       => $terpakai,
                'terpakaiUsed'   => $used,
                'terpakaiPending'=> $pending,
                'terpakaiAdjustment' => $adjustment,
                'terpakaiTransferKeluar' => $keluarBag,
                'terpakaiTransferMasuk'  => $masukBag,
                'sisa'           => $sisa,
                'persenTerisi'   => $persenTerisi,
                'kapasitasMaxKg' => $c->kapasitas_max_kg,
                'terpakaiKg'     => $terpakaiKg,
                'sisaKg'         => $sisaKg,
                'persenTerisiKg' => $persenTerisiKg,
                'breakdownWarna' => $breakdownWarna,
                'produk'         => $produk,
            ];
        });

        return response()->json($data->values());
    }

    /**
     * Daftar opsi filter (Cold Storage, Lantai, Kategori) untuk dropdown
     * di frontend - diambil dari data yang benar-benar ada, bukan hardcode.
     */
    public function filterOptions(): JsonResponse
    {
        return response()->json([
            'coldStorage' => Cell::query()
                ->whereNotNull('cold_storage')
                ->distinct()
                ->orderBy('cold_storage')
                ->pluck('cold_storage'),
            'lantai' => Cell::query()
                ->whereNotNull('lantai')
                ->distinct()
                ->orderBy('lantai')
                ->pluck('lantai'),
            'kategori' => Product::query()
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ]);
    }

    /**
     * Daftar bag yang SAAT INI ada di Cell ini, dikelompokkan per batch
     * asli (Inbound/Serah Terima). Dipanggil on-demand (bukan sekaligus di
     * data()) - baru diambil pas user klik expand cell tertentu, biar tidak
     * berat kalau 636 cell sekaligus dimuat semua.
     *
     * REVISI (Pemindahan): lokasi bag ditentukan oleh Cell::daftarBagFisik()
     * - bag yang pemindahan terakhirnya menuju cell ini (atau belum pernah
     * dipindah dan memang reservasi cell ini), dan belum keluar lewat
     * Outbound. Jadi:
     * - bag yang sedang di cell simpanan tidak tampil di cell asalnya;
     * - bag yang dikembalikan dari simpanan tampil lagi di cell biasa;
     * - bag reject TIDAK dikecualikan, supaya konsisten dengan angka
     *   "Terpakai" di tabel utama (yang menghitung jumlah_bag penuh).
     *
     * Tiap batch membawa 'batchId', 'produkId' dan 'bags' (nomor + kg per
     * bag + 'asalCell' kalau bag itu hasil pemindahan) supaya frontend bisa
     * menampilkan checkbox per bag. 'kgBags' tetap ada untuk kompatibilitas.
     */
    public function batches(Cell $cell): JsonResponse
    {
        $fisik = collect($cell->daftarBagFisik(true));

        if ($fisik->isEmpty()) {
            return response()->json([]);
        }

        $kodeCellAsal = Cell::whereIn(
            'id',
            $fisik->pluck('asal_cell_id')->filter()->unique()->values()->all()
        )->pluck('kode_cell', 'id');

        $batches = $fisik
            ->groupBy('batch_id')
            ->map(function ($items) use ($kodeCellAsal) {
                $batch = $items->first()['batch'];

                $bags = $items->map(fn ($item) => [
                    'nomorBag' => $item['nomor_bag'],
                    'kg'       => $item['kg'],
                    'asalCell' => $item['asal_cell_id']
                        ? $kodeCellAsal->get($item['asal_cell_id'], '-')
                        : null,
                ])->values()->all();

                return $this->formatBatchRow($batch, $bags);
            })
            ->values();

        return response()->json($batches);
    }

    /**
     * Daftar 6 cell simpanan beserta sisa kapasitas live (bag & kg) -
     * tujuan pemindahan dari cell BIASA.
     */
    public function tujuanSimpanan(): JsonResponse
    {
        $cells = Cell::where('is_simpanan', true)
            ->where('is_active', true)
            ->orderBy('kode_cell')
            ->get()
            ->map(fn (Cell $c) => $this->formatTujuan($c))
            ->values();

        return response()->json($cells);
    }

    /**
     * BARU - Daftar cell BIASA yang boleh menerima bag dari cell simpanan:
     * hanya cell aktif yang SUDAH terkunci ke produk yang sama dengan bag
     * yang dipindah (?produk_id=...), lengkap dengan sisa kapasitas.
     */
    public function tujuanProduk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'produk_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $cells = Cell::with('currentProduct')
            ->where('is_simpanan', false)
            ->where('is_active', true)
            ->where('current_product_id', $data['produk_id'])
            ->orderBy('kode_cell')
            ->get()
            ->map(fn (Cell $c) => $this->formatTujuan($c))
            ->values();

        return response()->json($cells);
    }

    /**
     * BARU - Pindahkan bag terpilih dari 1 cell asal ke 1 cell tujuan.
     * Payload:
     * {
     *   from_cell_id: 12,
     *   to_cell_id: 3,
     *   bags: [ { batch_id: 5, nomor_bag: 3 }, ... ]
     * }
     *
     * Dua arah yang diizinkan:
     * 1. Cell BIASA -> cell SIMPANAN (bebas produk apa saja).
     * 2. Cell SIMPANAN -> cell BIASA, dengan syarat: semua bag yang dipilih
     *    satu produk yang sama, dan cell tujuan SUDAH terkunci ke produk itu.
     * Simpanan -> simpanan dan biasa -> biasa DITOLAK.
     *
     * PENTING: kg TIDAK dipercaya dari input client - di-lookup ulang
     * dari batch asli di server. Lokasi bag divalidasi ulang lewat
     * Cell::daftarBagFisik() (bag harus benar-benar sedang di cell asal
     * dan belum keluar via Outbound). Semua pengecekan dilakukan di dalam
     * transaksi dengan lock pada kedua cell, supaya aman dari klik ganda
     * dan request bersamaan.
     *
     * Kapasitas tujuan (bag dan kg) wajib dipatuhi.
     */
    public function transfer(Request $request): JsonResponse
    {
        $user = $request->user('tally');

        // Pengecekan role di server (selain middleware route).
        if (! $user->hasAnyRole(['admin_gudang', 'supervisor_gudang'])) {
            ActivityLogger::log(
                'warehouse_stock',
                'update',
                "DITOLAK: {$user->employee_code} ({$user->name}) mencoba memindahkan stock tapi role tidak diizinkan",
                $user
            );

            abort(403, 'Anda tidak memiliki akses untuk aksi ini.');
        }

        $data = $request->validate([
            'from_cell_id'       => ['required', 'integer', 'exists:cells,id'],
            'to_cell_id'         => ['required', 'integer', 'exists:cells,id'],
            'bags'               => ['required', 'array', 'min:1', 'max:200'],
            'bags.*.batch_id'    => ['required', 'integer'],
            'bags.*.nomor_bag'   => ['required', 'integer', 'between:1,10'],
        ]);

        if ((int) $data['from_cell_id'] === (int) $data['to_cell_id']) {
            return response()->json(['message' => 'Cell asal dan cell tujuan tidak boleh sama.'], 422);
        }

        // Buang duplikat dari input (batch_id + nomor_bag yang sama).
        $bagInputs = collect($data['bags'])
            ->unique(fn ($b) => $b['batch_id'].'-'.$b['nomor_bag'])
            ->values();

        $hasil = DB::transaction(function () use ($data, $bagInputs, $user) {
            // Lock kedua cell, urut by id supaya tidak deadlock. Ini harus
            // jadi query pertama di dalam transaksi supaya pembacaan
            // berikutnya melihat data terbaru dari transaksi lain.
            $cells = Cell::whereIn('id', [$data['from_cell_id'], $data['to_cell_id']])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $from = $cells->get((int) $data['from_cell_id']);
            $to = $cells->get((int) $data['to_cell_id']);

            if (! $from || ! $to) {
                abort(422, 'Cell asal atau tujuan tidak ditemukan.');
            }

            if (! $to->is_active) {
                abort(422, "Cell {$to->kode_cell} sedang tidak aktif.");
            }

            $dariSimpanan = $from->isSimpanan();
            $keSimpanan = $to->isSimpanan();

            if ($dariSimpanan && $keSimpanan) {
                abort(422, 'Pemindahan antar cell simpanan tidak diperbolehkan.');
            }

            if (! $dariSimpanan && ! $keSimpanan) {
                abort(422, "Cell {$to->kode_cell} bukan cell simpanan. Dari cell biasa, bag hanya boleh dipindah ke cell simpanan.");
            }

            // Bag yang SAAT INI benar-benar ada di cell asal.
            $fisik = collect($from->daftarBagFisik(true))
                ->keyBy(fn ($item) => $item['batch_id'].'-'.$item['nomor_bag']);

            $rows = [];
            $totalKg = 0.0;
            $tidakValid = 0;

            foreach ($bagInputs as $bagInput) {
                $key = ((int) $bagInput['batch_id']).'-'.((int) $bagInput['nomor_bag']);
                $item = $fisik->get($key);

                if (! $item) {
                    $tidakValid++;
                    continue;
                }

                $totalKg += $item['kg'];

                $rows[] = [
                    'batch_id'  => $item['batch_id'],
                    'nomor_bag' => $item['nomor_bag'],
                    'kg'        => $item['kg'],
                    'produk_id' => (int) $item['batch']->produk_id,
                ];
            }

            if ($tidakValid > 0) {
                abort(422, "{$tidakValid} bag yang dicentang sudah tidak tersedia di Cell {$from->kode_cell} (mungkin sudah keluar atau sudah dipindah). Muat ulang halaman lalu coba lagi.");
            }

            // Dari simpanan ke cell biasa: satu produk saja, dan tujuan
            // harus sudah terkunci ke produk yang sama.
            if ($dariSimpanan) {
                $produkIds = collect($rows)->pluck('produk_id')->unique()->values();

                if ($produkIds->count() > 1) {
                    abort(422, 'Bag yang dipilih berasal dari lebih dari satu produk. Pindahkan satu produk per proses.');
                }

                $produkId = (int) $produkIds->first();

                if (! $to->terkunciKeProdukId($produkId)) {
                    $namaProduk = Product::find($produkId)->name ?? '-';
                    abort(422, "Cell {$to->kode_cell} tidak terkunci untuk produk \"{$namaProduk}\". Bag dari cell simpanan hanya boleh dipindah ke cell yang sudah terkunci produk yang sama.");
                }
            }

            $jumlahBag = count($rows);
            $totalKg = round($totalKg, 2);

            // Cek kapasitas tujuan - bag dan kg sama-sama wajib dipatuhi.
            $sisaBag = $to->sisaKapasitas();
            if ($jumlahBag > $sisaBag) {
                abort(422, "Kapasitas Cell {$to->kode_cell} tidak cukup. Sisa {$sisaBag} bag, sedangkan yang dipindah {$jumlahBag} bag.");
            }

            if ($to->kapasitas_max_kg !== null) {
                $sisaKg = round($to->sisaKapasitasKg(), 2);
                if ($totalKg > $sisaKg + 0.001) {
                    abort(422, "Kapasitas kg Cell {$to->kode_cell} tidak cukup. Sisa {$sisaKg} kg, sedangkan yang dipindah {$totalKg} kg.");
                }
            }

            foreach ($rows as $row) {
                StockTransferBag::create([
                    'batch_id'       => $row['batch_id'],
                    'nomor_bag'      => $row['nomor_bag'],
                    'from_cell_id'   => $from->id,
                    'to_cell_id'     => $to->id,
                    'kg'             => $row['kg'],
                    'user_id'        => $user->id,
                    'transferred_at' => now(),
                ]);
            }

            // Stock cell asal baru saja berkurang - kalau cell biasa jadi 0,
            // lepas kunci produknya (untuk cell simpanan tidak berlaku).
            $from->releaseJikaKosong();

            return [
                'from'        => $from->kode_cell,
                'to'          => $to->kode_cell,
                'arah'        => $dariSimpanan ? 'dari_simpanan' : 'ke_simpanan',
                'jumlahBag'   => $jumlahBag,
                'totalKg'     => $totalKg,
                'jumlahBatch' => collect($rows)->pluck('batch_id')->unique()->count(),
            ];
        });

        $deskripsiArah = $hasil['arah'] === 'dari_simpanan'
            ? "Cell simpanan {$hasil['from']} -> Cell {$hasil['to']}"
            : "Cell {$hasil['from']} -> Cell simpanan {$hasil['to']}";

        ActivityLogger::log(
            'warehouse_stock',
            'update',
            "{$user->employee_code} ({$user->name}) memindahkan {$hasil['jumlahBag']} bag ({$hasil['totalKg']} kg) dari {$hasil['jumlahBatch']} batch, {$deskripsiArah}",
            $user
        );

        return response()->json([
            'success'   => true,
            'message'   => "{$hasil['jumlahBag']} bag berhasil dipindahkan ke Cell {$hasil['to']}.",
            'jumlahBag' => $hasil['jumlahBag'],
            'totalKg'   => $hasil['totalKg'],
        ]);
    }

    /**
     * Upload Excel data real dari lapangan (SPV/Admin Gudang).
     * Format kolom wajib (header baris pertama, urutan bebas):
     * KODE CELL, JUMLAH (BAG)
     * Kolom opsional (kalau ada, ikut diproses):
     * JUMLAH (KG) - total kg
     * BAG MERAH, BAG BIRU, BAG HIJAU, BAG KUNING - breakdown bag per kuartal
     * KG MERAH, KG BIRU, KG HIJAU, KG KUNING - breakdown kg per kuartal
     * (Merah=Jan-Mar, Biru=Apr-Jun, Hijau=Jul-Sep, Kuning=Okt-Des)
     *
     * Kolom lain (COLD STORAGE, LANTAI, KODE PRODUK, NAMA PRODUK,
     * KAPASITAS, KAPASITAS (KG)) cuma informasi/referensi dari sisi
     * Excel, tidak menimpa data master di sistem.
     *
     * Baris dengan kode cell yang tidak ditemukan di sistem di-skip
     * (bukan gagal total), dan dilaporkan balik ke frontend.
     *
     * CATATAN PENTING: fitur upload Excel ini TIDAK diubah logikanya
     * sama sekali di Step 8 - ini fitur penyesuaian stok fisik rutin
     * yang sudah ada, terpisah total dari mekanisme StandardCapacity
     * (Step 4) yang cuma dipakai sekali untuk isi data master. Satu-
     * satunya tambahan di method ini adalah panggilan
     * $cell->releaseJikaKosong() di akhir setiap baris yang berhasil
     * diproses.
     *
     * REVISI (Cell Simpanan): baris dengan cell simpanan (1C01-1C06)
     * DITOLAK dan masuk daftar "dilewati" - isi cell simpanan hanya
     * boleh berubah lewat fitur Pindahkan.
     */
    public function uploadExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $file = $request->file('file');
        $user = $request->user('tally');

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // keyed A, B, C, ...

        $rowKeys = array_keys($rows);
        $headerRowKey = array_shift($rowKeys);
        $headerRow = $rows[$headerRowKey];

        // Cari kolom berdasarkan nama header (bukan posisi tetap), supaya
        // tetap jalan walau urutan kolom di Excel-nya beda-beda.
        $colKodeCell = null;
        $colJumlah = null;
        $colJumlahKg = null;
        $colBagMerah = null;
        $colBagBiru = null;
        $colBagHijau = null;
        $colBagKuning = null;
        $colKgMerah = null;
        $colKgBiru = null;
        $colKgHijau = null;
        $colKgKuning = null;
        foreach ($headerRow as $colLetter => $headerText) {
            $normalized = strtoupper(trim((string) $headerText));
            if ($normalized === 'KODE CELL') {
                $colKodeCell = $colLetter;
            }
            if ($normalized === 'JUMLAH (BAG)' || $normalized === 'JUMLAH') {
                $colJumlah = $colLetter;
            }
            if ($normalized === 'JUMLAH (KG)' || $normalized === 'KG') {
                $colJumlahKg = $colLetter;
            }
            if ($normalized === 'BAG MERAH') {
                $colBagMerah = $colLetter;
            }
            if ($normalized === 'BAG BIRU') {
                $colBagBiru = $colLetter;
            }
            if ($normalized === 'BAG HIJAU') {
                $colBagHijau = $colLetter;
            }
            if ($normalized === 'BAG KUNING') {
                $colBagKuning = $colLetter;
            }
            if ($normalized === 'KG MERAH') {
                $colKgMerah = $colLetter;
            }
            if ($normalized === 'KG BIRU') {
                $colKgBiru = $colLetter;
            }
            if ($normalized === 'KG HIJAU') {
                $colKgHijau = $colLetter;
            }
            if ($normalized === 'KG KUNING') {
                $colKgKuning = $colLetter;
            }
        }

        if (! $colKodeCell || ! $colJumlah) {
            return response()->json([
                'message' => 'Format Excel tidak sesuai. Pastikan ada kolom header "KODE CELL" dan "JUMLAH (BAG)".',
            ], 422);
        }

        $berhasil = 0;
        $dilewati = [];

        foreach ($rowKeys as $rowKey) {
            $row = $rows[$rowKey];
            $kodeCell = trim((string) ($row[$colKodeCell] ?? ''));
            $jumlahRaw = $row[$colJumlah] ?? null;
            $jumlahKgRaw = $colJumlahKg ? ($row[$colJumlahKg] ?? null) : null;

            if ($kodeCell === '') {
                continue; // baris kosong, lewati diam-diam
            }

            if ($jumlahRaw === null || $jumlahRaw === '') {
                $dilewati[] = ['baris' => $rowKey, 'kode_cell' => $kodeCell, 'alasan' => 'Kolom JUMLAH (BAG) kosong'];
                continue;
            }

            $cell = Cell::where('kode_cell', $kodeCell)->first();
            if (! $cell) {
                $dilewati[] = ['baris' => $rowKey, 'kode_cell' => $kodeCell, 'alasan' => 'Kode Cell tidak ditemukan di sistem'];
                continue;
            }

            // Cell simpanan tidak boleh disesuaikan lewat Excel.
            if ($cell->isSimpanan()) {
                $dilewati[] = ['baris' => $rowKey, 'kode_cell' => $kodeCell, 'alasan' => 'Cell simpanan tidak bisa diubah lewat upload Excel'];
                continue;
            }

            $jumlahAktual = (int) $jumlahRaw;
            $terpakaiSebelum = $this->hitungTerpakaiSistem($cell);
            $selisih = $jumlahAktual - $terpakaiSebelum;

            // KG opsional: kalau kolomnya tidak ada di Excel (atau kosong
            // di baris ini), anggap tidak ada perubahan kg (selisih_kg=0).
            $kgSebelum = $this->hitungKgTerpakaiSistem($cell);
            $kgAktual = ($jumlahKgRaw !== null && $jumlahKgRaw !== '') ? (float) $jumlahKgRaw : $kgSebelum;
            $selisihKg = round($kgAktual - $kgSebelum, 2);

            // Breakdown warna - semua opsional, default 0 kalau kolomnya
            // tidak ada di Excel atau kosong di baris ini.
            $readInt = fn ($colLetter) => ($colLetter && isset($row[$colLetter]) && $row[$colLetter] !== '')
                ? (int) $row[$colLetter] : 0;
            $readFloat = fn ($colLetter) => ($colLetter && isset($row[$colLetter]) && $row[$colLetter] !== '')
                ? (float) $row[$colLetter] : 0;

            CellStockAdjustment::create([
                'cell_id'               => $cell->id,
                'jumlah_sistem_sebelum' => $terpakaiSebelum,
                'jumlah_aktual'         => $jumlahAktual,
                'selisih'               => $selisih,
                'kg_sistem_sebelum'     => $kgSebelum,
                'kg_aktual'             => $kgAktual,
                'selisih_kg'            => $selisihKg,
                'bag_merah'             => $readInt($colBagMerah),
                'bag_biru'              => $readInt($colBagBiru),
                'bag_hijau'             => $readInt($colBagHijau),
                'bag_kuning'            => $readInt($colBagKuning),
                'kg_merah'              => $readFloat($colKgMerah),
                'kg_biru'               => $readFloat($colKgBiru),
                'kg_hijau'              => $readFloat($colKgHijau),
                'kg_kuning'             => $readFloat($colKgKuning),
                'sumber'                => 'upload_excel',
                'nama_file'             => $file->getClientOriginalName(),
                'user_id'               => $user->id,
            ]);

            // STEP 8 - hasil penyesuaian fisik baris ini bisa saja membuat
            // stock Cell jadi 0 (misal audit gudang menemukan Cell ini
            // ternyata sudah kosong). Cek & lepas kunci produk kalau iya.
            $cell->releaseJikaKosong();

            $berhasil++;
        }

        ActivityLogger::log(
            'warehouse_stock',
            'update',
            "{$user->employee_code} ({$user->name}) upload Excel penyesuaian stock: {$file->getClientOriginalName()} - {$berhasil} cell berhasil, ".count($dilewati).' dilewati',
            $user
        );

        return response()->json([
            'success'  => true,
            'berhasil' => $berhasil,
            'dilewati' => $dilewati,
        ]);
    }

    /**
     * Hitung terpakai versi sistem saat ini untuk 1 cell (dipakai sebagai
     * baseline "jumlah_sistem_sebelum" saat upload Excel). Sengaja query
     * per-cell (bukan agregasi massal seperti di data()) karena upload
     * Excel adalah aksi jarang/manual, bukan endpoint yang sering dipanggil.
     *
     * REVISI (Pemindahan): ikut memperhitungkan bag yang dipindah keluar
     * dan masuk, supaya selisih penyesuaian tetap benar.
     */
    private function hitungTerpakaiSistem(Cell $cell): int
    {
        $used = (int) DB::table('cell_reservations')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->where('cell_reservations.cell_id', $cell->id)
            ->where('cell_reservations.status', 'USED')
            ->sum('serah_terima_batches.jumlah_bag');

        $pending = (int) DB::table('cell_reservations')
            ->where('cell_id', $cell->id)
            ->where('status', 'PENDING')
            ->sum('max_bag_allowed');

        $adjustment = $cell->totalAdjustment();

        return $used + $pending + $adjustment
            - $cell->transferKeluarBag()
            + $cell->transferMasukBag();
    }

    /**
     * Versi KG dari hitungTerpakaiSistem() - dipakai sebagai baseline
     * "kg_sistem_sebelum" saat upload Excel. PENDING reservation TIDAK
     * ikut dihitung (belum ada berat pasti).
     *
     * REVISI (Pemindahan): ikut memperhitungkan kg bag yang dipindah
     * keluar dan masuk.
     */
    private function hitungKgTerpakaiSistem(Cell $cell): float
    {
        $usedKg = (float) DB::table('cell_reservations')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->where('cell_reservations.cell_id', $cell->id)
            ->where('cell_reservations.status', 'USED')
            ->selectRaw('COALESCE(SUM(
                kg_bag_1 + kg_bag_2 + kg_bag_3 + kg_bag_4 + kg_bag_5 +
                kg_bag_6 + kg_bag_7 + kg_bag_8 + kg_bag_9 + kg_bag_10
            ), 0) as total')
            ->value('total');

        $adjustmentKg = $cell->totalAdjustmentKg();

        return round(
            $usedKg + $adjustmentKg - $cell->transferKeluarKg() + $cell->transferMasukKg(),
            2
        );
    }

    /**
     * Agregat riwayat pemindahan, dikelompokkan per cell. $kolomCell hanya
     * boleh 'from_cell_id' (hitung yang KELUAR dari cell) atau 'to_cell_id'
     * (hitung yang MASUK ke cell). Hasil di-key by cell_id, tiap baris
     * berisi total_bag, total_kg, plus bag_/kg_ per warna (kuartal produksi
     * batch asal bag).
     *
     * Karena yang dihitung adalah riwayat (masuk dikurangi keluar), angkanya
     * tetap benar walau satu bag dipindah berkali-kali.
     */
    private function transferAgg(string $kolomCell)
    {
        $select = [
            DB::raw("stock_transfer_bags.{$kolomCell} as cell_id"),
            DB::raw('COUNT(*) as total_bag'),
            DB::raw('SUM(stock_transfer_bags.kg) as total_kg'),
        ];

        foreach (self::KUARTAL_WARNA as $warna => [$awal, $akhir]) {
            $cond = "MONTH(serah_terima_batches.tanggal_produksi) BETWEEN {$awal} AND {$akhir}";
            $select[] = DB::raw("SUM(CASE WHEN {$cond} THEN 1 ELSE 0 END) as bag_{$warna}");
            $select[] = DB::raw("SUM(CASE WHEN {$cond} THEN stock_transfer_bags.kg ELSE 0 END) as kg_{$warna}");
        }

        return DB::table('stock_transfer_bags')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'stock_transfer_bags.batch_id')
            ->groupBy("stock_transfer_bags.{$kolomCell}")
            ->select($select)
            ->get()
            ->keyBy('cell_id');
    }

    /**
     * Query dasar: bag yang SAAT INI berada di cell simpanan, yaitu baris
     * riwayat TERAKHIR (id terbesar) tiap bag yang tujuannya cell simpanan.
     * Sudah di-join ke cells (c), serah_terima_batches (b), dan products (p);
     * tabel riwayat memakai alias t.
     */
    private function bagDiSimpananQuery(): QueryBuilder
    {
        return DB::table('stock_transfer_bags as t')
            ->join('cells as c', 'c.id', '=', 't.to_cell_id')
            ->join('serah_terima_batches as b', 'b.id', '=', 't.batch_id')
            ->join('products as p', 'p.id', '=', 'b.produk_id')
            ->where('c.is_simpanan', true)
            ->whereRaw('t.id = (
                SELECT MAX(t2.id) FROM stock_transfer_bags t2
                WHERE t2.batch_id = t.batch_id AND t2.nomor_bag = t.nomor_bag
            )');
    }

    /**
     * Subquery: id cell simpanan yang SAAT INI berisi bag dari produk yang
     * cocok dengan filter. $filterProduk menerima query builder yang sudah
     * join ke products (alias p).
     */
    private function simpananCellIdsDenganProduk(\Closure $filterProduk): QueryBuilder
    {
        $q = $this->bagDiSimpananQuery()->select('t.to_cell_id');

        $filterProduk($q);

        return $q;
    }

    /**
     * Bentuk 1 cell tujuan untuk modal pemindahan (bag & kg).
     */
    private function formatTujuan(Cell $c): array
    {
        return [
            'id'             => $c->id,
            'kodeCell'       => $c->kode_cell,
            'lantai'         => $c->lantai,
            'kapasitasMax'   => $c->kapasitasBagUntukProduk($c->currentProduct),
            'terpakai'       => $c->stockBag(),
            'sisa'           => $c->sisaKapasitas(),
            'kapasitasMaxKg' => $c->kapasitas_max_kg !== null ? (float) $c->kapasitas_max_kg : null,
            'terpakaiKg'     => round($c->terpakaiKg(), 2),
            'sisaKg'         => $c->kapasitas_max_kg !== null ? round($c->sisaKapasitasKg(), 2) : null,
        ];
    }

    /**
     * Bentuk 1 baris batch untuk response batches().
     * $bags = [['nomorBag' => int, 'kg' => float, 'asalCell' => ?string], ...]
     */
    private function formatBatchRow(SerahTerimaBatch $batch, array $bags): array
    {
        $kgBags = array_map(fn ($b) => (float) $b['kg'], $bags);

        return [
            'batchId'         => $batch->id,
            'kodeProduksi'    => $batch->kode_produksi,
            'noTrolly'        => $batch->no_trolly,
            'tanggalProduksi' => optional($batch->tanggal_produksi)->format('d/m/Y'),
            'produkId'        => $batch->produk_id,
            'namaProduk'      => $batch->produk->name ?? null,
            'jumlahBag'       => count($bags),
            'kgBags'          => $kgBags,
            'bags'            => array_values($bags),
            'totalKg'         => round(array_sum($kgBags), 2),
        ];
    }
}