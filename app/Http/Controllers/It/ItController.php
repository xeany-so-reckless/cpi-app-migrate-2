<?php

namespace App\Http\Controllers\It;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItController extends Controller
{
    /**
     * Halaman utama Riwayat Log Aktivitas. Butuh login (guard tally,
     * role 'it') - middleware diatur di routes/web.php.
     */
    public function index(): View
    {
        return view('it.index');
    }

    /**
     * Ambil data log dengan filter opsional: modul, aksi, employee_code,
     * dan rentang tanggal. Dipakai tabel di halaman utama.
     */
    public function data(Request $request): JsonResponse
    {
        $query = ActivityLog::query()->orderByDesc('created_at');

        if ($module = $request->query('module')) {
            $query->where('module', $module);
        }

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        if ($employeeCode = $request->query('employee_code')) {
            $query->where('employee_code', strtoupper(trim($employeeCode)));
        }

        if ($tanggalDari = $request->query('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $tanggalDari);
        }

        if ($tanggalSampai = $request->query('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $tanggalSampai);
        }

        $logs = $query->limit(500)->get();

        return response()->json($logs->map(fn (ActivityLog $log) => [
            'id'           => $log->id,
            'waktu'        => $log->created_at->format('d/m/Y H:i:s'),
            'employeeCode' => $log->employee_code ?: '-',
            'userName'     => $log->user_name ?: '-',
            'module'       => $log->module,
            'action'       => $log->action,
            'description'  => $log->description,
            'ipAddress'    => $log->ip_address ?: '-',
        ]));
    }

    /**
     * Daftar modul & aksi unik yang pernah tercatat, dipakai untuk
     * mengisi dropdown filter secara dinamis (bukan hardcode).
     */
    public function filterOptions(): JsonResponse
    {
        return response()->json([
            'modules' => ActivityLog::query()->distinct()->orderBy('module')->pluck('module'),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
