<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect ke halaman utama Tally Pro HANYA kalau user yang sedang login
 * (guard "tally") memang punya role Tally Pro (tally/foreman).
 *
 * Karena guard "tally" dipakai bareng dengan modul Serah Terima, user
 * dengan role Serah Terima (tally_produksi/tally_gudang/supervisor) yang
 * kebetulan masih login TETAP bisa melihat form login Tally Pro ini
 * (misal untuk ganti akun), bukan asal di-redirect ke halaman yang
 * tidak bisa mereka akses.
 */
class RedirectIfTallyAuthenticated
{
    private const TALLY_PRO_ROLES = ['tally', 'foreman'];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::guard('tally')->user();

        if ($user && $user->hasAnyRole(self::TALLY_PRO_ROLES)) {
            return redirect()->route('tally.input');
        }

        return $next($request);
    }
}