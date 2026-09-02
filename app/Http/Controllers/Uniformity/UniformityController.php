<?php

namespace App\Http\Controllers\Uniformity;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UniformityRit;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\LbPenerimaan;
use App\Models\PurchaseOrder;

class UniformityController extends Controller
{
    /**
     * Halaman Dashboard + Input (tab). Menggantikan Index.html.
     */
    public function index(): View
    {
        return view('uniformity.index');
    }

    /**
     * Halaman Export & Rekap. Menggantikan Export.html.
     */
    public function exportPage(): View
    {
        return view('uniformity.export');
    }

    /**
     * Menggantikan getDashboardData() di code.gs.
     */
    public function data(): JsonResponse
    {
        $rits = UniformityRit::orderBy('tanggal')->orderBy('no_rit')->get();

        return response()->json($rits->map(fn (UniformityRit $r) => $this->formatRit($r)));
    }

    /**
     * Daftar No PO untuk dropdown pertama di form input Uniformity.
     * Sumbernya sama dengan dropdown "Nomor PO" di form Sebelum Bongkar
     * (LB Report): dari PurchaseOrder milik PPIC - BUKAN dari
     * LbPenerimaan, karena LbPenerimaan cuma berisi PO yang sudah ada
     * rit/bongkarnya. PO yang sudah TECO disembunyikan, konsisten
     * dengan perilaku dropdown LB Report.
     */
    public function poList(): JsonResponse
    {
        $data = PurchaseOrder::whereNull('teco_at')
            ->orderByDesc('tanggal')
            ->pluck('nomor_po');

        return response()->json($data);
    }

    /**
     * Menggantikan lookup DTA di code.gs.
     * Sekarang wajib menyertakan no_po (dipilih via dropdown di form)
     * supaya pencarian No Rit lebih presisi kalau ada No Rit yang
     * kebetulan sama di PO yang berbeda.
     */
    public function dtaByRit(Request $request): JsonResponse
    {
        $noPo  = $request->query('no_po');
        $noRit = $request->query('no_rit');

        if (!$noPo) {
            return response()->json(['message' => 'No PO wajib dipilih terlebih dahulu.'], 422);
        }
        if (!$noRit) {
            return response()->json(['message' => 'Nomor Rit wajib diisi.'], 422);
        }

        $data = LbPenerimaan::where('no_po', $noPo)
            ->where('no_rit', $noRit)
            ->latest('tanggal')
            ->first();

        if (!$data) {
            return response()->json([
                'message' => "Rit '{$noRit}' tidak ditemukan pada PO '{$noPo}'."
            ], 404);
        }

        return response()->json([
            'tanggal'  => \Carbon\Carbon::parse($data->tanggal)->format('Y-m-d'),
            'no_po'    => $data->no_po,
            'farm'     => $data->farm,
            'size'     => $data->size,
            'kg_dta'   => $data->kg_dta,
            'ekor_dta' => $data->ekor_dta,
        ]);
    }

    /**
     * Menggantikan getRekapData(jenis) di code.gs.
     * jenis: 'harian' atau 'bulanan'.
     */
    public function rekap(Request $request): JsonResponse
    {
        $jenis = $request->query('jenis', 'harian');
        $sqlFormat = $jenis === 'bulanan' ? '%Y-%m' : '%Y-%m-%d';

        $rows = DB::table('uniformity_rits')
            ->selectRaw("DATE_FORMAT(tanggal, '{$sqlFormat}') as periode, AVG(undersize_percent) as undersize, AVG(size_masuk_percent) as size_masuk, AVG(oversize_percent) as oversize")
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        return response()->json($rows->map(fn ($row) => [
            'periode'   => $row->periode,
            'undersize' => round((float) $row->undersize, 1),
            'sizeMasuk' => round((float) $row->size_masuk, 1),
            'oversize'  => round((float) $row->oversize, 1),
        ]));
    }

    /**
     * Menggantikan pengecekan PIN_OTORISASI di JS lama.
     * Sekarang divalidasi server-side, PIN tidak lagi kelihatan di kode
     * yang dikirim ke browser.
     *
     * Catatan: modul ini pakai PIN bersama (bukan login per-orang), jadi
     * log-nya tidak bisa mencatat siapa persisnya - cuma catat kejadian
     * "PIN berhasil diverifikasi" + IP address, sesuai batasan desainnya.
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string'],
        ]);

        $valid = hash_equals((string) config('uniformity.input_pin'), $data['pin']);

        if ($valid) {
            ActivityLogger::log('uniformity', 'verify', 'PIN akses form Input Uniformity berhasil diverifikasi');
        }

        return response()->json(['valid' => $valid]);
    }

    /**
     * Menggantikan simpanDataAplikasi() di code.gs.
     * Client mengirim 1 rit per request (mengikuti pola "antrean sementara"
     * yang dikirim satu-satu secara berurutan di kode lama).
     *
     * Catatan: sama seperti verifyPin(), modul ini tidak punya login
     * per-orang, jadi log tidak menyertakan identitas user - cuma catat
     * No Rit yang disimpan.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal'      => ['required', 'date'],
            'no_rit'       => ['required', 'string', 'max:50'],
            'asal_kandang' => ['required', 'string'],
            'size_min'     => ['required', 'numeric', 'min:0'],
            'size_max'     => ['required', 'numeric', 'min:0'],
            'kg_dta'       => ['required', 'numeric', 'min:0'],
            'ekor_dta'     => ['required', 'integer', 'min:0'],
            'samples'      => ['required', 'array', 'min:1', 'max:200'],
            'samples.*'    => ['numeric', 'min:0'],
        ]);

        $noRit = strtoupper(trim($data['no_rit']));

        $duplikat = UniformityRit::where('tanggal', $data['tanggal'])
            ->where('no_rit', $noRit)
            ->exists();

        if ($duplikat) {
            return response()->json([
                'status'  => 'error',
                'message' => "No Rit '{$noRit}' sudah pernah didaftarkan pada tanggal {$data['tanggal']}!",
            ], 422);
        }

        $rit = UniformityRit::create([
            'tanggal'      => $data['tanggal'],
            'no_rit'       => $noRit,
            'asal_kandang' => strtoupper(trim($data['asal_kandang'])),
            'size_min'     => $data['size_min'],
            'size_max'     => $data['size_max'],
            'kg_dta'       => $data['kg_dta'],
            'ekor_dta'     => $data['ekor_dta'],
        ]);

        foreach (array_values($data['samples']) as $index => $berat) {
            $rit->samples()->create([
                'sample_index' => $index + 1,
                'berat'        => $berat,
            ]);
        }

        $rit->recalculateFromSamples();
        $rit->save();

        ActivityLogger::log('uniformity', 'create', "Data Rit {$rit->no_rit} (tanggal {$data['tanggal']}) berhasil disimpan di Uniformity");

        return response()->json([
            'status'  => 'success',
            'message' => "Data Rit {$rit->no_rit} berhasil disimpan!",
        ]);
    }

    /**
     * Menggantikan pengecekan daftarOtorisasi (array APP01-05 hardcode)
     * di Export.html. Reuse user role 'foreman' yang sudah ada di Tally Pro.
     */
    public function verifySignature(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        $employeeCode = strtoupper(trim($credentials['employee_code']));
        $user = User::where('employee_code', $employeeCode)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->hasAnyRole(['foreman'])) {
            return response()->json([
                'valid'   => false,
                'message' => 'ID atau Password salah, atau tidak memiliki wewenang approve!',
            ], 422);
        }

        ActivityLogger::log(
            'uniformity',
            'sign',
            "{$user->employee_code} ({$user->name}) tanda tangan laporan Export Uniformity",
            $user
        );

        return response()->json([
            'valid' => true,
            'name'  => $user->name,
        ]);
    }

    private function formatRit(UniformityRit $r): array
    {
        return [
            'tanggal'      => $r->tanggal->format('Y-m-d'),
            'noRit'        => $r->no_rit,
            'asalKandang'  => $r->asal_kandang,
            'sizeMin'      => (float) $r->size_min,
            'sizeMax'      => (float) $r->size_max,
            'kgDta'        => (float) $r->kg_dta,
            'ekorDta'      => (int) $r->ekor_dta,
            'rerataAbw'    => (float) $r->rerata_abw,
            'jumlahSample' => $r->jumlah_sample,
            'undersize'    => (float) $r->undersize_percent,
            'sizeMasuk'    => (float) $r->size_masuk_percent,
            'oversize'     => (float) $r->oversize_percent,
        ];
    }
}