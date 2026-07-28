<?php

namespace App\Http\Controllers\TallyPro;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class TallyInputController extends Controller
{
    /**
     * Menampilkan halaman Input Tally.
     * Menggantikan "Input Tally Produksi v.3" di Apps Script lama.
     *
     * Data tetap sementara di layar (tidak disimpan ke DB), sesuai
     * keputusan bisnis: alur ini tetap sama seperti versi asli.
     * Yang berubah: master produk sekarang diambil dari database,
     * bukan array hardcode di JavaScript.
     */
    public function index(): View
    {
        $products = Product::query()
            ->active()
            ->orderBy('category')
            ->orderBy('display_order')
            ->get(['id', 'code', 'name', 'default_ekor', 'category'])
            ->map(fn (Product $p) => [
                'code'         => $p->code,
                'name'         => $p->name,
                'default_ekor' => $p->default_ekor,
            ])
            ->values();

        return view('tally-pro.input', [
            'products' => $products,
        ]);
    }
}
