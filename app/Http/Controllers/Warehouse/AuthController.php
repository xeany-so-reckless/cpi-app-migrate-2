<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    // Cuma 2 akun ini yang boleh masuk Stock Warehouse: SPVG (supervisor_gudang)
    // dan ADMG01 (admin_gudang). Beda dari login Serah Terima yang lebih luas.
    private const ALLOWED_ROLES = ['admin_gudang', 'supervisor_gudang'];

    public function showLogin(): View|RedirectResponse
    {
        // Kalau sudah login & rolenya sesuai, langsung lempar ke halaman
        // Stock Warehouse - tidak perlu login ulang.
        $user = Auth::guard('tally')->user();
        if ($user && $user->hasAnyRole(self::ALLOWED_ROLES)) {
            return redirect()->route('warehouse.stock.index');
        }

        return view('warehouse.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        // Case-insensitive, sama seperti modul lain (uClean di kode lama).
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
                'warehouse_stock',
                'login_rejected',
                "{$user->employee_code} ({$user->name}) mencoba login ke Stock Warehouse tapi role tidak diizinkan",
                $user
            );

            Auth::guard('tally')->logout();

            return back()
                ->withErrors(['employee_code' => 'Akun ini tidak memiliki akses ke Stock Warehouse.'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        ActivityLogger::log(
            'warehouse_stock',
            'login',
            "{$user->employee_code} ({$user->name}) login ke Stock Warehouse",
            $user
        );

        return redirect()->intended(route('warehouse.stock.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user) {
            ActivityLogger::log(
                'warehouse_stock',
                'logout',
                "{$user->employee_code} ({$user->name}) logout dari Stock Warehouse",
                $user
            );
        }

        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('warehouse.stock.login');
    }
}
