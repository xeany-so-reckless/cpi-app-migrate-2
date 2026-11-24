<?php

namespace App\Http\Controllers\Pga;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Models\TrainingEvaluation;
use App\Models\TrainingEvaluationAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PgaController extends Controller
{
    /**
     * Dashboard PGA - ringkasan inspeksi APAR & evaluasi training per bulan (default bulan berjalan, WIB).
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
            'training'     => $this->ringkasanTraining($awal, $akhir),
        ]);
    }

    /**
     * Ringkasan Evaluasi Training untuk bulan yang dipilih.
     * Rata-rata dihitung dari nilai 1-4 di database, tapi yang dikirim ke tampilan hanya label teks.
     */
    private function ringkasanTraining(Carbon $awal, Carbon $akhir): array
    {
        $dari   = $awal->toDateString();
        $sampai = $akhir->toDateString();

        $base = TrainingEvaluation::query()->whereBetween('tanggal_pengisian', [$dari, $sampai]);

        $total     = (clone $base)->count();
        $perhatian = (clone $base)->where('has_findings', true)->count();
        $peserta   = (clone $base)->distinct()->count('nik');
        $hariIni   = TrainingEvaluation::query()
            ->whereDate('tanggal_pengisian', now('Asia/Jakarta')->toDateString())
            ->count();

        // Rata-rata per materi
        $rawMateri = TrainingEvaluationAnswer::query()
            ->join('training_evaluations as te', 'te.id', '=', 'training_evaluation_answers.training_evaluation_id')
            ->whereBetween('te.tanggal_pengisian', [$dari, $sampai])
            ->selectRaw('training_evaluation_answers.materi AS grup, AVG(training_evaluation_answers.nilai) AS rata, COUNT(DISTINCT te.id) AS pengisi')
            ->groupBy('training_evaluation_answers.materi')
            ->get()
            ->keyBy('grup');

        $perMateri = [];
        foreach (TrainingEvaluation::materi() as $kode => $m) {
            $perMateri[$kode] = $this->barisRingkas(
                $rawMateri->get($kode),
                ['kode' => $m['kode'], 'nama' => $m['judul'], 'wajib' => (bool) ($m['wajib'] ?? false)]
            );
        }

        // Rata-rata per bagian
        $rawBagian = TrainingEvaluationAnswer::query()
            ->join('training_evaluations as te', 'te.id', '=', 'training_evaluation_answers.training_evaluation_id')
            ->whereBetween('te.tanggal_pengisian', [$dari, $sampai])
            ->selectRaw('te.bagian AS grup, AVG(training_evaluation_answers.nilai) AS rata, COUNT(DISTINCT te.id) AS pengisi')
            ->groupBy('te.bagian')
            ->get()
            ->keyBy('grup');

        $perBagian = [];
        foreach (TrainingEvaluation::BAGIAN as $kode => $label) {
            $baris = $this->barisRingkas($rawBagian->get($kode), ['kode' => null, 'nama' => $label, 'wajib' => false]);
            if ($baris['pengisi'] > 0) {
                $perBagian[$kode] = $baris;
            }
        }
        // Bagian yang paling lemah di atas
        uasort($perBagian, fn ($a, $b) => $a['urut'] <=> $b['urut']);

        $terbaru = TrainingEvaluation::query()
            ->withAvg('answers as rata_nilai', 'nilai')
            ->orderByDesc('tanggal_pengisian')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return [
            'total'     => $total,
            'perhatian' => $perhatian,
            'peserta'   => $peserta,
            'hariIni'   => $hariIni,
            'perMateri' => $perMateri,
            'perBagian' => $perBagian,
            'terbaru'   => $terbaru,
        ];
    }

    /** Satu baris ringkasan (materi / bagian): label teks, lebar bar, dan penanda perlu perhatian. */
    private function barisRingkas(?object $raw, array $info): array
    {
        $rata = $raw ? (float) $raw->rata : null;

        return $info + [
            'pengisi'   => $raw ? (int) $raw->pengisi : 0,
            'label'     => $rata !== null ? TrainingEvaluation::labelRataRata($rata) : null,
            'persen'    => $rata !== null ? max(5, (int) round(($rata - 1) / 3 * 100)) : 0,
            'perhatian' => $rata !== null && TrainingEvaluation::rataRataPerluPerhatian($rata),
            'urut'      => $rata ?? 99, // hanya untuk pengurutan, tidak ditampilkan
        ];
    }

    /**
     * Menu Buku Tamu (placeholder - Coming Soon).
     */
    public function bukuTamu(): View
    {
        return view('pga.buku-tamu');
    }
}
