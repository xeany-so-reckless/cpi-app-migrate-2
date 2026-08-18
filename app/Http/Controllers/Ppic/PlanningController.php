<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\PpicPlan;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanningController extends Controller
{
    public function index(): View
    {
        return view('ppic.planning');
    }

    /**
     * Data Planning vs Aktual, urut tanggal terbaru duluan. Accessor
     * (selisih_ekor, persen_selisih_ekor, dst) otomatis ikut ke JSON
     * karena sudah didefinisikan di Model.
     */
    public function data(Request $request): JsonResponse
    {
        $query = PpicPlan::with('user')->orderByDesc('tanggal');

        if ($request->filled('bulan')) {
            // format bulan: yyyy-MM
            $query->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$request->query('bulan')]);
        }

        $plans = $query->get()->map(fn (PpicPlan $p) => [
            'id'               => $p->id,
            'tanggal'          => $p->tanggal->format('Y-m-d'),
            'tanggalLabel'     => $p->tanggal->format('d/m/Y'),
            'planEkor'         => $p->plan_ekor,
            'aktualEkor'       => $p->aktual_ekor,
            'selisihEkor'      => $p->selisih_ekor,
            'persenSelisihEkor'=> $p->persen_selisih_ekor,
            'planKg'           => (float) $p->plan_kg,
            'aktualKg'         => (float) $p->aktual_kg,
            'selisihKg'        => $p->selisih_kg,
            'persenSelisihKg'  => $p->persen_selisih_kg,
            'keterangan'       => $p->keterangan,
            'namaUser'         => $p->user->name ?? '-',
        ]);

        return response()->json($plans);
    }

    /**
     * Simpan/update data Plan 1 tanggal. Karena tanggal UNIQUE di tabel,
     * pakai updateOrCreate - kalau tanggal itu sudah ada datanya,
     * otomatis di-update (bukan bikin baris baru/duplikat).
     *
     * DIUBAH: aktual_ekor & aktual_kg TIDAK LAGI diinput manual oleh
     * PPIC - dihapus dari validasi. Kolom itu sekarang murni hasil
     * sinkronisasi otomatis dari Report Harian Bahan Baku LB (lihat
     * PpicPlan::recalculateAktual() & LbReportController::storeSetelah()).
     *
     * Kalau baris tanggal ini SUDAH ADA, Aktual yang sudah tersimpan
     * (hasil sinkron dari LB) TIDAK disentuh sama sekali di sini - PPIC
     * cuma boleh ubah Plan & Keterangan lewat form ini.
     *
     * Kalau baris tanggal ini BELUM ADA (baru pertama kali PPIC input
     * Plan untuk tanggal itu), Aktual dihitung dulu dari data LB yang
     * mungkin sudah lebih dulu ada untuk tanggal tersebut - supaya tidak
     * start dari 0 kalau ternyata datanya sudah lengkap di sisi LB.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal'    => ['required', 'date'],
            'plan_ekor'  => ['required', 'integer', 'min:0'],
            'plan_kg'    => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user('tally');
        $sudahAda = PpicPlan::where('tanggal', $data['tanggal'])->exists();

        $plan = PpicPlan::updateOrCreate(
            ['tanggal' => $data['tanggal']],
            array_merge(
                [
                    'plan_ekor'  => $data['plan_ekor'],
                    'plan_kg'    => $data['plan_kg'],
                    'keterangan' => $data['keterangan'] ?? null,
                    'user_id'    => $user->id,
                ],
                // Baris baru -> tarik Aktual dari data LB yang mungkin
                // sudah ada duluan. Baris sudah ada -> Aktual tersimpan
                // tidak diubah sama sekali di sini.
                $sudahAda ? [] : PpicPlan::recalculateAktual($data['tanggal'])
            )
        );

        ActivityLogger::log(
            'ppic',
            $plan->wasRecentlyCreated ? 'create' : 'update',
            "{$user->employee_code} ({$user->name}) ".($plan->wasRecentlyCreated ? 'menambah' : 'memperbarui')." data Planning tanggal {$data['tanggal']}",
            $user
        );

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan.']);
    }

    public function destroy(Request $request, PpicPlan $plan): JsonResponse
    {
        $user = $request->user('tally');
        $tanggal = $plan->tanggal->format('Y-m-d');

        $plan->delete();

        ActivityLogger::log(
            'ppic',
            'delete',
            "{$user->employee_code} ({$user->name}) menghapus data Planning vs Aktual tanggal {$tanggal}",
            $user
        );

        return response()->json(['success' => true]);
    }
}