<?php

namespace App\Http\Controllers\Pga;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PgaController extends Controller
{
    /**
     * Dashboard PGA - ringkasan inspeksi APAR per bulan (default bulan berjalan, WIB).
     */
    public function dashboard(Request $request): View
    {
        $bulan = (string) $request->query('bulan', '');
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = now('Asia/Jakarta')->format('Y-m');
        }

        $awal  = Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();

        $base = AparInspection::query()
            ->whereBetween('tanggal_inspeksi', [$awal->toDateString(), $akhir->toDateString()]);

        $total  = (clone $base)->count();
        $temuan = (clone $base)->where('has_findings', true)->count();
        $ok     = $total - $temuan;
        $persenTemuan = $total > 0 ? round($temuan / $total * 100, 1) : 0;

        $hariIni = AparInspection::query()
            ->whereDate('tanggal_inspeksi', now('Asia/Jakarta')->toDateString())
            ->count();

        // Jumlah "Rusak" per komponen (satu query)
        $selects = [];
        foreach (array_keys(AparInspection::KOMPONEN) as $field) {
            $selects[] = "SUM(CASE WHEN {$field} = 'rusak' THEN 1 ELSE 0 END) AS {$field}";
        }
        $row = (clone $base)->selectRaw(implode(', ', $selects))->first();

        $perKomponen = [];
        foreach (AparInspection::KOMPONEN as $field => $label) {
            $perKomponen[$field] = [
                'label' => $label,
                'jumlah' => (int) ($row->{$field} ?? 0),
            ];
        }
        $maxKomponen = max(1, collect($perKomponen)->max('jumlah'));

        // Lokasi dengan temuan terbanyak
        $topLokasi = (clone $base)
            ->where('has_findings', true)
            ->select('lokasi_apar', DB::raw('COUNT(*) AS jumlah'))
            ->groupBy('lokasi_apar')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get();

        // Inspeksi terbaru (semua bulan)
        $terbaru = AparInspection::query()
            ->orderByDesc('tanggal_inspeksi')
            ->orderByDesc('jam_inspeksi')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('pga.dashboard', [
            'bulan'        => $bulan,
            'labelBulan'   => $awal->copy()->locale('id')->translatedFormat('F Y'),
            'total'        => $total,
            'temuan'       => $temuan,
            'ok'           => $ok,
            'persenTemuan' => $persenTemuan,
            'hariIni'      => $hariIni,
            'perKomponen'  => $perKomponen,
            'maxKomponen'  => $maxKomponen,
            'topLokasi'    => $topLokasi,
            'terbaru'      => $terbaru,
        ]);
    }

    /**
     * Menu Buku Tamu (placeholder - Coming Soon).
     */
    public function bukuTamu(): View
    {
        return view('pga.buku-tamu');
    }
}