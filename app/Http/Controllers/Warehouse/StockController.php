<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Cell;
use App\Models\CellReservation;
use App\Models\CellStockAdjustment;
use App\Models\Product;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StockController extends Controller
{
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
     */
    public function data(Request $request): JsonResponse
    {
        $query = Cell::with(['products:id,code,name,category'])
            ->where('is_active', true);

        if ($request->filled('cold_storage')) {
            $query->where('cold_storage', $request->query('cold_storage'));
        }

        if ($request->filled('lantai')) {
            $query->where('lantai', $request->query('lantai'));
        }

        if ($request->filled('kategori')) {
            $kategori = $request->query('kategori');
            $query->whereHas('products', fn ($q) => $q->where('category', $kategori));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode_cell', 'like', "%{$search}%")
                    ->orWhereHas('products', fn ($qq) => $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
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

        $data = $cells->map(function (Cell $c) use ($usedAgg, $pendingAgg, $adjustmentAgg, $usedKgAgg, $adjustmentKgAgg, $warnaAgg, $batchWarnaAgg) {
            $used = (int) ($usedAgg[$c->id] ?? 0);
            $pending = (int) ($pendingAgg[$c->id] ?? 0);
            $adjustment = (int) ($adjustmentAgg[$c->id] ?? 0);
            $terpakai = max(0, $used + $pending + $adjustment);
            $sisa = max(0, $c->kapasitas_max - $terpakai);
            $persenTerisi = $c->kapasitas_max > 0
                ? round(($terpakai / $c->kapasitas_max) * 100, 1)
                : 0;

            $usedKg = (float) ($usedKgAgg[$c->id] ?? 0);
            $adjustmentKg = (float) ($adjustmentKgAgg[$c->id] ?? 0);
            $terpakaiKg = max(0, round($usedKg + $adjustmentKg, 2));
            $sisaKg = $c->kapasitas_max_kg !== null ? max(0, round((float) $c->kapasitas_max_kg - $terpakaiKg, 2)) : null;
            $persenTerisiKg = ($c->kapasitas_max_kg !== null && $c->kapasitas_max_kg > 0)
                ? round(($terpakaiKg / $c->kapasitas_max_kg) * 100, 1)
                : 0;

            $w = $warnaAgg[$c->id] ?? null;
            $bw = $batchWarnaAgg[$c->id] ?? null;
            $breakdownWarna = [
                'merah'  => [
                    'bag' => (int) ($w->bag_merah ?? 0) + (int) ($bw->bag_merah ?? 0),
                    'kg'  => round((float) ($w->kg_merah ?? 0) + (float) ($bw->kg_merah ?? 0), 2),
                ],
                'biru'   => [
                    'bag' => (int) ($w->bag_biru ?? 0) + (int) ($bw->bag_biru ?? 0),
                    'kg'  => round((float) ($w->kg_biru ?? 0) + (float) ($bw->kg_biru ?? 0), 2),
                ],
                'hijau'  => [
                    'bag' => (int) ($w->bag_hijau ?? 0) + (int) ($bw->bag_hijau ?? 0),
                    'kg'  => round((float) ($w->kg_hijau ?? 0) + (float) ($bw->kg_hijau ?? 0), 2),
                ],
                'kuning' => [
                    'bag' => (int) ($w->bag_kuning ?? 0) + (int) ($bw->bag_kuning ?? 0),
                    'kg'  => round((float) ($w->kg_kuning ?? 0) + (float) ($bw->kg_kuning ?? 0), 2),
                ],
            ];

            return [
                'id'             => $c->id,
                'kodeCell'       => $c->kode_cell,
                'coldStorage'    => $c->cold_storage,
                'lantai'         => $c->lantai,
                'kapasitasMax'   => $c->kapasitas_max,
                'terpakai'       => $terpakai,
                'terpakaiUsed'   => $used,
                'terpakaiPending'=> $pending,
                'terpakaiAdjustment' => $adjustment,
                'sisa'           => $sisa,
                'persenTerisi'   => $persenTerisi,
                'kapasitasMaxKg' => $c->kapasitas_max_kg,
                'terpakaiKg'     => $terpakaiKg,
                'sisaKg'         => $sisaKg,
                'persenTerisiKg' => $persenTerisiKg,
                'breakdownWarna' => $breakdownWarna,
                'produk'         => $c->products->map(fn (Product $p) => [
                    'code'     => $p->code,
                    'name'     => $p->name,
                    'category' => $p->category,
                ])->values(),
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
     * BARU - Daftar batch ASLI (dari Inbound/Serah Terima) yang pernah
     * ditempatkan di Cell ini, lewat reservasi yang statusnya USED.
     * Dipanggil on-demand (bukan sekaligus di data()) - baru diambil pas
     * user klik expand cell tertentu, biar tidak berat kalau 636 cell
     * sekaligus dimuat semua.
     */
    public function batches(Cell $cell): JsonResponse
    {
        $batches = $cell->reservations()
            ->where('status', 'USED')
            ->with('batch')
            ->get()
            ->filter(fn (CellReservation $r) => $r->batch !== null)
            ->map(function (CellReservation $r) {
                $batch = $r->batch;

                return [
                    'kodeProduksi'    => $batch->kode_produksi,
                    'noTrolly'        => $batch->no_trolly,
                    'tanggalProduksi' => optional($batch->tanggal_produksi)->format('d/m/Y'),
                    'jumlahBag'       => $batch->jumlah_bag,
                    'kgBags'          => array_slice($batch->kg_bags_array, 0, $batch->jumlah_bag),
                    'totalKg'         => $batch->total_kg,
                ];
            })
            ->values();

        return response()->json($batches);
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

        return $used + $pending + $adjustment;
    }

    /**
     * Versi KG dari hitungTerpakaiSistem() - dipakai sebagai baseline
     * "kg_sistem_sebelum" saat upload Excel. PENDING reservation TIDAK
     * ikut dihitung (belum ada berat pasti).
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

        return round($usedKg + $adjustmentKg, 2);
    }
}