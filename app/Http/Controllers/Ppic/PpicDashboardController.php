<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\PpicPlan;
use App\Models\ProduksiFresh;
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
            'bulan'         => $bulan,
            'trend'         => $trend,
            'summary'       => $summary,
            'totalPo'       => $poBulanIni->count(),
            'poByJenis'     => $poByJenis,
            'produksiFresh' => $this->produksiFreshRekap($poBulanIni),
        ]);
    }

    /**
     * BARU - Rekap total Qty Produksi Fresh per PO, untuk PO-PO di bulan
     * yang sedang difilter. Cuma PO yang SUDAH ADA input Fresh-nya yang
     * ditampilkan (PO tanpa input Fresh sama sekali di-skip, supaya
     * tabel tidak penuh baris kosong).
     *
     * Ini murni tampilan READ-ONLY - tidak ada data yang ditulis balik
     * dari sini, PPIC cuma melihat rekap hasil input tim Produksi Fresh.
     */
    private function produksiFreshRekap($poBulanIni)
    {
        $nomorPoList = $poBulanIni->pluck('nomor_po');

        $freshRows = ProduksiFresh::whereIn('no_po', $nomorPoList)
            ->selectRaw('no_po, tipe_input, SUM(qty) as total_qty, COUNT(*) as jumlah_entri')
            ->groupBy('no_po', 'tipe_input')
            ->get()
            ->groupBy('no_po');

        return $poBulanIni->map(function (PurchaseOrder $po) use ($freshRows) {
            $rows = $freshRows->get($po->nomor_po, collect());

            $qtyMain = (float) ($rows->firstWhere('tipe_input', 'main')->total_qty ?? 0);
            $qtyByProduct = (float) ($rows->firstWhere('tipe_input', 'byproduct')->total_qty ?? 0);
            $jumlahEntri = (int) $rows->sum('jumlah_entri');

            return [
                'nomorPo'      => $po->nomor_po,
                'jenisPo'      => $po->jenis_po,
                'tanggalLabel' => $po->tanggal->format('d/m/Y'),
                'qtyMain'      => $qtyMain,
                'qtyByProduct' => $qtyByProduct,
                'qtyTotal'     => $qtyMain + $qtyByProduct,
                'jumlahEntri'  => $jumlahEntri,
            ];
        })->filter(fn ($row) => $row['jumlahEntri'] > 0)->values();
    }
}