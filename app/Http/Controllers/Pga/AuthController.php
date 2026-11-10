<?php

namespace App\Http\Controllers\Pga;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const ALLOWED_ROLES = ['pga'];

    public function showLogin(): View|RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        /** @var User|null $user */
        if ($user && $user->hasAnyRole(self::ALLOWED_ROLES)) {
            return redirect()->route('pga.index');
        }

        return view('pga.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        $credentials['employee_code'] = strtoupper(trim($credentials['employee_code']));

        if (! Auth::guard('tally')->attempt($credentials)) {
            return back()
                ->withErrors(['employee_code' => 'ID atau Password Salah!'])
                ->onlyInput('employee_code');
        }

        $user = Auth::guard('tally')->user();
        /** @var User|null $user */
        if (! $user->hasAnyRole(self::ALLOWED_ROLES)) {
            ActivityLogger::log(
                'pga',
                'login_rejected',
                "{$user->employee_code} ({$user->name}) mencoba login ke PGA tapi role tidak diizinkan",
                $user
            );

            Auth::guard('tally')->logout();

            return back()
                ->withErrors(['employee_code' => 'Akun ini tidak memiliki akses ke modul PGA.'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        ActivityLogger::log(
            'pga',
            'login',
            "{$user->employee_code} ({$user->name}) login ke PGA",
            $user
        );

        return redirect()->intended(route('pga.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user) {
            ActivityLogger::log(
                'pga',
                'logout',
                "{$user->employee_code} ({$user->name}) logout dari PGA",
                $user
            );
        }

        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pga.login');
    }
}
