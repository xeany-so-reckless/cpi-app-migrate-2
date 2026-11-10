<?php

namespace App\Http\Controllers\Pga;

use App\Http\Controllers\Controller;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Halaman cetak QR code form inspeksi APAR (satu QR umum untuk semua APAR).
 * QR berisi URL form publik (route apar.form, yaitu /apar).
 */
class PgaQrController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'url'    => ['nullable', 'url:http,https', 'max:255'],
            'ukuran' => ['nullable', Rule::in(['stiker', 'besar'])],
            'jumlah' => ['nullable', 'integer', 'min:1', 'max:48'],
        ], [
            'url.url'      => 'Alamat harus lengkap dan diawali http:// atau https://',
            'url.max'      => 'Alamat terlalu panjang (maksimal 255 karakter).',
            'jumlah.min'   => 'Jumlah label minimal 1.',
            'jumlah.max'   => 'Jumlah label maksimal 48 per cetak.',
            'jumlah.integer' => 'Jumlah label harus berupa angka.',
        ]);

        // Default: alamat form di host yang sedang dipakai membuka halaman ini.
        // Di komputer lokal ini biasanya "localhost" -> HP tidak bisa membukanya,
        // jadi PGA/IT mengganti dengan IP atau nama host server yang sebenarnya.
        $url    = $f['url'] ?? route('apar.form');
        $ukuran = $f['ukuran'] ?? 'stiker';
        $jumlah = (int) ($f['jumlah'] ?? ($ukuran === 'besar' ? 1 : 6));

        $host  = strtolower((string) parse_url($url, PHP_URL_HOST));
        $lokal = in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);

        $qrUri   = null;
        $qrError = null;

        if (! class_exists(Builder::class)) {
            $qrError = 'Library QR belum terpasang. Jalankan di folder project: composer require endroid/qr-code';
        } else {
            try {
                $result = (new Builder(
                    writer: new SvgWriter(),
                    data: $url,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 600,
                    margin: 10,
                    roundBlockSizeMode: RoundBlockSizeMode::Margin,
                ))->build();

                $qrUri = $result->getDataUri();
            } catch (Throwable $e) {
                report($e);
                $qrError = 'QR gagal dibuat: ' . $e->getMessage();
            }
        }

        return view('pga.apar.qr', compact('url', 'ukuran', 'jumlah', 'lokal', 'qrUri', 'qrError'));
    }
}
