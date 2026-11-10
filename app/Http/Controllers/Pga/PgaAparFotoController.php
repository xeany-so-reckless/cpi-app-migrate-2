<?php

namespace App\Http\Controllers\Pga;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Menampilkan foto inspeksi lewat Laravel (bukan lewat link public/storage).
 * Tidak butuh storage:link, tidak kena 403 dari web server, dan foto hanya
 * bisa dibuka oleh PGA yang sedang login.
 */
class PgaAparFotoController extends Controller
{
    public function __invoke(AparInspection $inspeksi): BinaryFileResponse
    {
        $disk = Storage::disk('public');

        abort_unless($inspeksi->foto_path && $disk->exists($inspeksi->foto_path), 404, 'Foto tidak ditemukan.');

        return response()->file($disk->path($inspeksi->foto_path), [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
