<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sama seperti RedirectIfTallyAuthenticated, tapi khusus untuk modul
 * Serah Terima Produksi. Cuma redirect ke halaman utama modul ini kalau
 * user yang login rolenya memang salah satu role Serah Terima -
 * supaya user Tally Pro (role tally/foreman) yang kebetulan masih
 * punya sesi aktif tetap bisa melihat form login modul ini
 * (untuk ganti login ke akun Serah Terima).
 */
class RedirectIfSerahTerimaAuthenticated
{
    private const SERAH_TERIMA_ROLES = ['tally_produksi', 'tally_gudang', 'supervisor'];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::guard('tally')->user();

        if ($user && $user->hasAnyRole(self::SERAH_TERIMA_ROLES)) {
            return redirect()->route('serahterima.index');
        }

        return $next($request);
    }
}