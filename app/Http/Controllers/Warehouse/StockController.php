<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Cell;
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

        $data = $cells->map(function (Cell $c) use ($usedAgg, $pendingAgg, $adjustmentAgg) {
            $used = (int) ($usedAgg[$c->id] ?? 0);
            $pending = (int) ($pendingAgg[$c->id] ?? 0);
            $adjustment = (int) ($adjustmentAgg[$c->id] ?? 0);
            $terpakai = max(0, $used + $pending + $adjustment);
            $sisa = max(0, $c->kapasitas_max - $terpakai);
            $persenTerisi = $c->kapasitas_max > 0
                ? round(($terpakai / $c->kapasitas_max) * 100, 1)
                : 0;

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
     * Upload Excel data real dari lapangan (SPV/Admin Gudang).
     * Format kolom wajib (header baris pertama, urutan bebas):
     * KODE CELL, COLD STORAGE, LANTAI, KODE PRODUK, NAMA PRODUK,
     * KAPASITAS, JUMLAH (BAG)
     *
     * Cuma kolom "KODE CELL" dan "JUMLAH (BAG)" yang benar-benar dipakai
     * untuk hitung penyesuaian - kolom lain cuma informasi/referensi dari
     * sisi Excel, tidak menimpa data master di sistem.
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
        foreach ($headerRow as $colLetter => $headerText) {
            $normalized = strtoupper(trim((string) $headerText));
            if ($normalized === 'KODE CELL') {
                $colKodeCell = $colLetter;
            }
            if ($normalized === 'JUMLAH (BAG)' || $normalized === 'JUMLAH') {
                $colJumlah = $colLetter;
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

            CellStockAdjustment::create([
                'cell_id'               => $cell->id,
                'jumlah_sistem_sebelum' => $terpakaiSebelum,
                'jumlah_aktual'         => $jumlahAktual,
                'selisih'               => $selisih,
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
}