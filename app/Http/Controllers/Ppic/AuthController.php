<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const ALLOWED_ROLES = ['ppic'];

    public function showLogin(): View|RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user && $user->hasAnyRole(self::ALLOWED_ROLES)) {
            return redirect()->route('ppic.index');
        }

        return view('ppic.auth.login');
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
                'ppic',
                'login_rejected',
                "{$user->employee_code} ({$user->name}) mencoba login ke PPIC tapi role tidak diizinkan",
                $user
            );

            Auth::guard('tally')->logout();

            return back()
                ->withErrors(['employee_code' => 'Akun ini tidak memiliki akses ke modul PPIC.'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        ActivityLogger::log(
            'ppic',
            'login',
            "{$user->employee_code} ({$user->name}) login ke PPIC",
            $user
        );

        return redirect()->intended(route('ppic.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user) {
            ActivityLogger::log(
                'ppic',
                'logout',
                "{$user->employee_code} ({$user->name}) logout dari PPIC",
                $user
            );
        }

        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ppic.login');
    }
}
