<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Cell;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Halaman utama Stock Warehouse - list per Cell + filter.
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

        $data = $cells->map(function (Cell $c) use ($usedAgg, $pendingAgg) {
            $used = (int) ($usedAgg[$c->id] ?? 0);
            $pending = (int) ($pendingAgg[$c->id] ?? 0);
            $terpakai = $used + $pending;
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
}
