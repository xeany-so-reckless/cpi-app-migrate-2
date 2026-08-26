<?php

namespace App\Http\Controllers\ProduksiFresh;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('produksi-fresh.login');
    }

    /**
     * BEDA dari modul lain: selain employee_code + password, form ini
     * juga minta "Tipe Otorisasi" (main/byproduct), menggantikan dropdown
     * loginType di Apps Script lama.
     *
     * Tipe yang dipilih divalidasi terhadap role user (foreman -> main,
     * tally_by_product -> byproduct, supervisor -> boleh keduanya), lalu
     * disimpan di SESSION - dipakai server di semua request berikutnya
     * (listProducts, store), TIDAK PERNAH dipercaya ulang dari input
     * client supaya tidak bisa dimanipulasi (misal tally_by_product
     * memaksa submit sebagai main lewat request langsung).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
            'tipe_input'    => ['required', 'in:main,byproduct'],
        ]);

        $employeeCode = strtoupper(trim($credentials['employee_code']));
        $user = User::where('employee_code', $employeeCode)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['login' => 'ID atau Password salah!'])->withInput();
        }

        $tipe = $credentials['tipe_input'];
        $rolesForType = $tipe === 'main'
            ? ['foreman', 'supervisor']
            : ['tally_by_product', 'supervisor'];

        if (! $user->hasAnyRole($rolesForType)) {
            $label = $tipe === 'main' ? 'Main Product' : 'By Product';

            return back()->withErrors(['login' => "Akses ditolak: Anda tidak memiliki wewenang untuk tipe {$label}."])->withInput();
        }

        Auth::guard('tally')->login($user);
        $request->session()->put('produksi_fresh_tipe', $tipe);

        return redirect()->route('produksifresh.workspace');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('produksi_fresh_tipe');
        Auth::guard('tally')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('produksifresh.login');
    }
}
