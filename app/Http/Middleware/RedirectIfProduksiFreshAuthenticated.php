<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfProduksiFreshAuthenticated
{
    private const ALLOWED_ROLES = ['foreman', 'tally_by_product', 'supervisor'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('tally')->user();

        if ($user && $user->hasAnyRole(self::ALLOWED_ROLES)) {
            return redirect()->route('produksifresh.workspace');
        }

        return $next($request);
    }
}
