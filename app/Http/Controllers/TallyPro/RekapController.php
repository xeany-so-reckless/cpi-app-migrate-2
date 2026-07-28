<?php

namespace App\Http\Controllers\TallyPro;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RekapController extends Controller
{
    /**
     * Label tampilan untuk tiap kategori produk.
     * Key harus sama dengan value kolom `category` di tabel produk.
     */
    private const CATEGORY_LABELS = [
        'kw1'        => 'AYAM UTUH - KW 1 (GRILLER)',
        'kw2'        => 'AYAM UTUH - KW 2 (GRILLER)',
        'bahan_baku' => 'BAHAN BAKU',
        'parting'    => 'PARTING & MARINASI',
        'by_product' => 'BY PRODUCT EVIS & OTHERS',
    ];

    /**
     * Menampilkan halaman Rekap.
     * Menggantikan "Rekap Hasil Produksi v.4" di Apps Script lama.
     *
     * Data ekor/kg tetap diisi lewat import Excel (sesuai keputusan bisnis:
     * alur ini dipertahankan sama seperti versi asli). Yang berubah:
     * master produk per kategori sekarang dari database, bukan array
     * hardcode di JavaScript.
     */
    public function index(): View
    {
        $productsByCategory = Product::query()
            ->active()
            ->orderBy('category')
            ->orderBy('display_order')
            ->get(['code', 'name', 'category', 'display_order'])
            ->groupBy('category')
            ->map(fn ($items) => $items->map(fn (Product $p) => [
                'code' => $p->code,
                'name' => $p->name,
            ])->values());

        return view('tally-pro.rekap', [
            'categoryLabels'      => self::CATEGORY_LABELS,
            'productsByCategory'  => $productsByCategory,
        ]);
    }

    /**
     * Menggantikan pengecekan usersRekap.tally / usersRekap.approver
     * di JS lama. Sekarang employee_code + password dicek ke database
     * (aman), bukan dicocokkan ke array yang bisa dilihat lewat inspect
     * element.
     *
     * Stateless: tidak membuat sesi login baru, dan tidak menyimpan
     * histori approval ke database (sesuai keputusan bisnis).
     */
    public function verifySignature(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        $user = User::where('employee_code', $credentials['employee_code'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'valid'   => false,
                'message' => 'ID Pengguna atau Password salah!',
            ], 422);
        }

        $effectiveRole = $user->roles->pluck('name')->intersect(['tally', 'foreman'])->first();

        $labelRole = $effectiveRole === 'foreman' ? 'Foreman/Forelady' : 'Tally';
        ActivityLogger::log(
            'tally_pro',
            'sign',
            "{$user->employee_code} ({$user->name}) tanda tangan Rekap sebagai {$labelRole}",
            $user
        );

        return response()->json([
            'valid' => true,
            'name'  => $user->name,
            'role'  => $effectiveRole,
        ]);
    }
}