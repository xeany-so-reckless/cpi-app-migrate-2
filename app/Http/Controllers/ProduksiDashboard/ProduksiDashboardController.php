<?php

namespace App\Http\Controllers\ProduksiDashboard;

use App\Http\Controllers\Controller;
use App\Models\ProduksiHarian;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProduksiDashboardController extends Controller
{
    /**
     * Halaman dashboard - terbuka tanpa login (sesuai keputusan bisnis).
     */
    public function index(): View
    {
        return view('produksi-dashboard.index');
    }

    /**
     * Menggantikan getProductionData() di code.gs.
     * Semua kalkulasi (ABW, Yield, dll) dilakukan di model ProduksiHarian::toDashboardArray().
     */
    public function data(): JsonResponse
    {
        $rows = ProduksiHarian::orderBy('tanggal')->get();

        return response()->json($rows->map(fn (ProduksiHarian $r) => $r->toDashboardArray()));
    }

    /**
     * Menggantikan getLatestUpdateInfo() di code.gs.
     * Dipakai untuk badge notifikasi "Data Baru Tersedia" (<24 jam).
     */
    public function latestUpdateInfo(): JsonResponse
    {
        $latest = ProduksiHarian::orderByDesc('tanggal')->first();

        if (! $latest) {
            return response()->json(['lastTimestamp' => null]);
        }

        return response()->json([
            'lastTimestamp' => $latest->tanggal->valueOf(),
        ]);
    }

    /**
     * Menggantikan pengecekan akun admin/spv hardcode (USERS object) di JS
     * lama. Dipakai untuk membuka gerbang tab Input DAN tombol Edit -
     * modul ini tidak punya sesi login penuh (tidak ada halaman login
     * sendiri), jadi ini murni pengecekan kredensial sesaat (stateless).
     */
    public function verifySignature(Request $request): JsonResponse
    {
        $user = $this->validateCredentials($request);

        if (! $user) {
            return response()->json([
                'valid'   => false,
                'message' => 'ID atau Password salah, atau tidak memiliki wewenang Supervisor!',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'name'  => $user->name,
        ]);
    }

    /**
     * Menggantikan saveProductionData() di code.gs. Role: supervisor.
     *
     * Karena modul ini stateless (tidak ada sesi login), employee_code +
     * password WAJIB dikirim bareng setiap request simpan/edit - bukan
     * cuma sekali di gerbang awal - supaya request ini benar-benar
     * tervalidasi di server, bukan cuma dikunci di tampilan browser.
     *
     * Ditambah validasi duplikat tanggal (tidak ada di kode asli, sesuai
     * keputusan bisnis - dulu bisa dobel kalau tanggal sama diinput 2x).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->validateCredentials($request);
        if (! $user) {
            abort(403, 'Kredensial tidak valid atau bukan Supervisor.');
        }

        $data = $this->validatedPayload($request);

        $duplikat = ProduksiHarian::where('tanggal', $data['tanggal'])->exists();
        if ($duplikat) {
            return response()->json([
                'success' => false,
                'message' => "Data untuk tanggal {$data['tanggal']} sudah pernah diinput! Gunakan fitur Edit kalau ingin mengoreksi.",
            ], 422);
        }

        ProduksiHarian::create($data);

        ActivityLogger::log(
            'produksi_dashboard',
            'create',
            "{$user->employee_code} ({$user->name}) input data produksi tanggal {$data['tanggal']}",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Data laporan produksi berhasil disimpan!',
        ]);
    }

    /**
     * Menggantikan updateFullProductionData() di code.gs. Role: supervisor.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $this->validateCredentials($request);
        if (! $user) {
            abort(403, 'Kredensial tidak valid atau bukan Supervisor.');
        }

        $data = $this->validatedPayload($request);

        $row = ProduksiHarian::where('tanggal', $data['tanggal'])->first();

        if (! $row) {
            return response()->json([
                'success' => false,
                'message' => "Gagal update: Data dengan tanggal {$data['tanggal']} tidak ditemukan.",
            ], 404);
        }

        $row->update($data);

        ActivityLogger::log(
            'produksi_dashboard',
            'update',
            "{$user->employee_code} ({$user->name}) edit data produksi tanggal {$data['tanggal']}",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => "Data produksi tanggal {$data['tanggal']} berhasil diperbarui secara menyeluruh!",
        ]);
    }

    /**
     * Validasi employee_code + password dari request, WAJIB role supervisor.
     * Return User kalau valid, null kalau tidak.
     */
    private function validateCredentials(Request $request): ?User
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        $employeeCode = strtoupper(trim($credentials['employee_code']));
        $user = User::where('employee_code', $employeeCode)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->hasRole('supervisor')) {
            return null;
        }

        return $user;
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'tanggal'       => ['required', 'date'],
            'kg_dta'        => ['required', 'numeric', 'min:0'],
            'ekor_dta'      => ['required', 'integer', 'min:0'],
            'kg_netto'      => ['required', 'numeric', 'min:0'],
            'ayam_mati'     => ['required', 'integer', 'min:0'],
            'kg_titik_nol'  => ['required', 'numeric', 'min:0'],
            'kg_fg_bp'      => ['required', 'numeric', 'min:0'],
            'kg_by_product' => ['required', 'numeric', 'min:0'],
            'pct_kw2'       => ['required', 'numeric', 'min:0'],
            'pct_defect'    => ['required', 'numeric', 'min:0'],
            'prod_griller'  => ['required', 'numeric', 'min:0'],
            'prod_parting'  => ['required', 'numeric', 'min:0'],
            'prod_marinasi' => ['required', 'numeric', 'min:0'],
            'total_hasil'   => ['required', 'numeric', 'min:0'],
        ]);
    }
}