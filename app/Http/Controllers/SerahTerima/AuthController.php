<?php

namespace App\Http\Controllers\SerahTerima;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthController extends Controller
{
    // Ditambah 'admin_gudang' & 'supervisor_gudang' - approval kedua sisi
    // gudang (sejajar SPV Produksi), butuh akses login ke modul ini juga.
    private const ALLOWED_ROLES = ['tally_produksi', 'tally_gudang', 'supervisor', 'admin_gudang', 'supervisor_gudang'];

    /**
     * Menggantikan #loginPage di Index.html lama.
     */
    public function showLogin(): View
    {
        return view('serah-terima.auth.login');
    }

    /**
     * Menggantikan fungsi loginUser() di code.gs.
     *
     * Reuse guard "tally" (tabel users sama dengan Tally Pro), tapi
     * ditolak kalau role user bukan salah satu role modul ini -
     * supaya user Tally Pro (misal TLY01) tidak bisa "nyasar" masuk
     * ke modul Serah Terima walau tabel usernya sama.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        // Login lewat guard "tally" seperti biasa (case-insensitive,
        // menggantikan uClean = username.toUpperCase() di kode lama).
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
                'serah_terima',
                'login_rejected',
                "{$user->employee_code} ({$user->name}) mencoba login ke Serah Terima tapi role tidak diizinkan",
                $user
            );

            Auth::guard('tally')->logout();

            return back()
                ->withErrors(['employee_code' => 'Akun ini tidak memiliki akses ke modul Serah Terima.'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        ActivityLogger::log(
            'serah_terima',
            'login',
            "{$user->employee_code} ({$user->name}) login ke Serah Terima",
            $user
        );

        return redirect()->intended(route('serahterima.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user) {
            ActivityLogger::log(
                'serah_terima',
                'logout',
                "{$user->employee_code} ({$user->name}) logout dari Serah Terima",
                $user
            );
        }

        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('serahterima.login');
    }
}