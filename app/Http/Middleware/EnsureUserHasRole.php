<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Karena guard "tally" dipakai bareng oleh modul Tally Pro DAN Serah Terima
 * Produksi (satu tabel users untuk semua), guard saja tidak cukup untuk
 * membatasi akses. Middleware ini mengecek role user yang login lewat
 * relasi roles (many-to-many), dipasang di atas middleware `auth:tally`.
 *
 * Pemakaian di route: ->middleware('role:tally,foreman')
 *                      ->middleware('role:tally_produksi,tally_gudang,supervisor')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('tally');

        if (! $user || ! $user->hasAnyRole($roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}