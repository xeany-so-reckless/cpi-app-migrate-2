<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class RedirectIfItAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('tally')->user();


        /** @var User|null $user */
        if ($user && $user->hasRole('it')) {
            return redirect()->route('it.index');
        }

        return $next($request);
    }
}
