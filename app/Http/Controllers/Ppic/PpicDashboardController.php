<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\PpicPlan;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PpicDashboardController extends Controller
{
    public function index(): View
    {
        return view('ppic.dashboard');
    }

    /**
     * Data ringkasan & tren buat grafik Dashboard PPIC.
     * Default bulan berjalan kalau tidak dikasih parameter ?bulan=yyyy-MM.
     */
    public function data(Request $request): JsonResponse
    {
        $bulan = $request->query('bulan', now()->format('Y-m'));

        $plans = PpicPlan::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->orderBy('tanggal')
            ->get();

        $trend = $plans->map(fn (PpicPlan $p) => [
            'tanggal'          => $p->tanggal->format('d/m'),
            'planEkor'         => $p->plan_ekor,
            'aktualEkor'       => $p->aktual_ekor,
            'persenSelisihEkor'=> $p->persen_selisih_ekor,
            'planKg'           => (float) $p->plan_kg,
            'aktualKg'         => (float) $p->aktual_kg,
            'persenSelisihKg'  => $p->persen_selisih_kg,
        ]);

        $summary = [
            'totalPlanEkor'   => $plans->sum('plan_ekor'),
            'totalAktualEkor' => $plans->sum('aktual_ekor'),
            'totalPlanKg'     => round($plans->sum('plan_kg'), 2),
            'totalAktualKg'   => round($plans->sum('aktual_kg'), 2),
            'jumlahHariTercatat' => $plans->count(),
        ];

        $poBulanIni = PurchaseOrder::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])->get();
        $poByJenis = $poBulanIni->groupBy('jenis_po')->map->count();

        return response()->json([
            'bulan'     => $bulan,
            'trend'     => $trend,
            'summary'   => $summary,
            'totalPo'   => $poBulanIni->count(),
            'poByJenis' => $poByJenis,
        ]);
    }
}
