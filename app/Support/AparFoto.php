<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Simpan foto inspeksi APAR (dikompres dengan GD) & hapus foto lama.
 * Dipakai form publik (/apar) dan edit oleh PGA.
 */
class AparFoto
{
    private const MAX_SISI = 1280; // px, sisi terpanjang
    private const QUALITY  = 75;   // kualitas JPEG

    public static function simpan(UploadedFile $file): string
    {
        $dir    = 'apar/' . now('Asia/Jakarta')->format('Y/m');
        $name   = Str::uuid()->toString();
        $path   = $file->getRealPath();
        $binary = file_get_contents($path);

        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            $img = @imagecreatefromstring($binary);

            if ($img !== false) {
                $img = self::putarSesuaiExif($img, $path);
                $img = self::perkecil($img);

                ob_start();
                imagejpeg($img, null, self::QUALITY);
                $jpeg = ob_get_clean();

                $target = "{$dir}/{$name}.jpg";
                Storage::disk('public')->put($target, $jpeg);

                return $target;
            }
        }

        // Cadangan bila GD tidak aktif: simpan apa adanya
        $target = "{$dir}/{$name}." . ($file->extension() ?: 'jpg');
        Storage::disk('public')->put($target, $binary);

        return $target;
    }

    public static function hapus(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private static function putarSesuaiExif(\GdImage $img, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $img;
        }

        $exif        = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $angle = match ($orientation) {
            3       => 180,
            6       => -90,
            8       => 90,
            default => 0,
        };

        if ($angle !== 0) {
            $rotated = imagerotate($img, $angle, 0);
            if ($rotated !== false) {
                return $rotated;
            }
        }

        return $img;
    }

    private static function perkecil(\GdImage $img): \GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);

        $scale = min(1, self::MAX_SISI / max($w, $h));
        $nw    = max(1, (int) round($w * $scale));
        $nh    = max(1, (int) round($h * $scale));

        $canvas = imagecreatetruecolor($nw, $nh);
        // Latar putih supaya PNG transparan tidak jadi hitam saat jadi JPEG
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        return $canvas;
    }
}
