<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfLbReportAuthenticated
{
    private const ALLOWED_ROLES = ['lb_penerimaan_awal', 'lb_penerimaan_akhir', 'lb_hanging', 'supervisor'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('tally')->user();

        if ($user && $user->hasAnyRole(self::ALLOWED_ROLES)) {
            return redirect()->route('lbreport.workspace');
        }

        return $next($request);
    }
}
