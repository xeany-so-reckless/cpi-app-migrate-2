<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PpicController extends Controller
{
    /**
     * Menu Utama PPIC - 3 pilihan: Planning vs Aktual, Input PO, Dashboard.
     */
    public function index(): View
    {
        return view('ppic.menu');
    }
}
