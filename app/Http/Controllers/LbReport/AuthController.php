<?php

namespace App\Http\Controllers\LbReport;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthController extends Controller
{
    private const ALLOWED_ROLES = ['lb_penerimaan_awal', 'lb_penerimaan_akhir', 'lb_hanging', 'supervisor'];

    public function showLogin(): View
    {
        return view('lb-report.auth.login');
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
        if (! $user->hasAnyRole(self::ALLOWED_ROLES)) {
            Auth::guard('tally')->logout();

            return back()
                ->withErrors(['employee_code' => 'Akun ini tidak memiliki akses ke modul Report Harian LB.'])
                ->onlyInput('employee_code');
        }

        $request->session()->regenerate();

        ActivityLogger::log('report_lb', 'login', "{$user->employee_code} ({$user->name}) login ke Report Harian LB", $user);

        return redirect()->intended(route('lbreport.workspace'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        if ($user) {
            ActivityLogger::log('report_lb', 'logout', "{$user->employee_code} ({$user->name}) logout dari Report Harian LB", $user);
        }

        Auth::guard('tally')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('lbreport.login');
    }
}