<?php

namespace App\Http\Controllers\TallyPro;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login Tally Pro.
     * Menggantikan tampilan #login-box di Index.html lama.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('tally')->check()) {
            return redirect()->route('tally.input');
        }

        return view('tally-pro.auth.login');
    }

    /**
     * Proses login. Menggantikan fungsi prosesLogin() yang dulu
     * mencocokkan userDatabase di sisi client (tidak aman).
     * Sekarang validasi employee_code + password (hashed) di server.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        if (! Auth::guard('tally')->attempt($credentials)) {
            return back()
                ->withErrors(['employee_code' => 'ID atau Password salah!'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        $user = Auth::guard('tally')->user();
        ActivityLogger::log('tally_pro', 'login', "{$user->employee_code} ({$user->name}) login ke Tally Pro", $user);

        return redirect()->intended(route('tally.input'));
    }

    /**
     * Logout dari sesi Tally Pro saja (guard tally),
     * tidak memengaruhi sesi dashboard utama / menu lain.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user) {
            ActivityLogger::log('tally_pro', 'logout', "{$user->employee_code} ({$user->name}) logout dari Tally Pro", $user);
        }

        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tally.login');
    }
}