<?php

namespace App\Http\Controllers\It;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('it.auth.login');
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
                ->withErrors(['employee_code' => 'ID atau Password salah!'])
                ->onlyInput('employee_code');
        }

        $user = Auth::guard('tally')->user();



        /** @var User|null $user */
        if (! $user->hasRole('it')) {
            Auth::guard('tally')->logout();

            return back()
                ->withErrors(['employee_code' => 'Akun ini tidak memiliki akses ke menu IT.'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('it.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('it.login');
    }
}
