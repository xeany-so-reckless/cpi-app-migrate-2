<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

class ActivityLogger
{
    /**
     * Catat 1 baris riwayat aktivitas.
     *
     * @param string $module Modul asal, misal: 'tally_pro', 'serah_terima',
     *                        'uniformity', 'report_lb', 'produksi_dashboard', 'auth'
     * @param string $action Jenis aksi, misal: 'login', 'logout', 'create',
     *                        'update', 'delete', 'approve', 'verify', 'sign'
     * @param string $description Teks manusiawi, misal:
     *                        "TLY01 (Eka) generate rekap tanggal 2026-07-13"
     * @param User|null $user Kalau null, otomatis pakai user yang sedang
     *                        login lewat guard "tally". Untuk modul stateless
     *                        (Uniformity, Produksi Dashboard) yang tidak
     *                        pakai sesi login, WAJIB kirim $user manual
     *                        (hasil dari validasi credentials).
     */
    public static function log(string $module, string $action, string $description, ?User $user = null): void
    {
        $user = $user ?? Auth::guard('tally')->user();

        ActivityLog::create([
            'user_id'       => $user?->id,
            'employee_code' => $user?->employee_code,
            'user_name'     => $user?->name,
            'module'        => $module,
            'action'        => $action,
            'description'   => $description,
            'ip_address'    => RequestFacade::ip(),
            'created_at'    => now(),
        ]);
    }
}
