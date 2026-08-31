<?php

namespace App\Http\Controllers\Warehouse\Outbound;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    // Cuma role 'checker' yang boleh masuk halaman Outbound.
    private const ALLOWED_ROLES = ['checker'];

    public function showLogin(): View|RedirectResponse
    {
        // Kalau sudah login & rolenya sesuai, langsung lempar ke halaman
        // Outbound - tidak perlu login ulang.
        $user = Auth::guard('tally')->user();
        if ($user && $user->hasAnyRole(self::ALLOWED_ROLES)) {
            return redirect()->route('warehouse.outbound.index');
        }

        return view('warehouse.outbound.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        // Case-insensitive, sama seperti modul lain.
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
                'warehouse_outbound',
                'login_rejected',
                "{$user->employee_code} ({$user->name}) mencoba login ke Outbound tapi role tidak diizinkan",
                $user
            );

            Auth::guard('tally')->logout();

            return back()
                ->withErrors(['employee_code' => 'Akun ini tidak memiliki akses ke Outbound.'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        ActivityLogger::log(
            'warehouse_outbound',
            'login',
            "{$user->employee_code} ({$user->name}) login ke Outbound",
            $user
        );

        return redirect()->intended(route('warehouse.outbound.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user) {
            ActivityLogger::log(
                'warehouse_outbound',
                'logout',
                "{$user->employee_code} ({$user->name}) logout dari Outbound",
                $user
            );
        }

        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('warehouse.outbound.login');
    }
}
