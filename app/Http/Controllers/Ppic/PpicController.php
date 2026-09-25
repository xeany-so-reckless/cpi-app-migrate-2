<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PpicController extends Controller
{
    /**
     * Menu Utama PPIC - 3 pilihan: Planning vs Aktual, Input PO, Dashboard.
     *
     * Role 'manager' (MGR01) cuma boleh akses Dashboard - jadi begitu
     * masuk ke sini langsung di-redirect ke Dashboard, gak pernah lihat
     * card Planning vs Aktual & Input PO.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::guard('tally')->user();
        /** @var User|null $user */

        if ($user && $user->hasAnyRole(['manager']) && ! $user->hasAnyRole(['ppic'])) {
            return redirect()->route('ppic.dashboard.index');
        }

        return view('ppic.menu');
    }
}