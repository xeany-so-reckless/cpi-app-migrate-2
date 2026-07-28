<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
 
/**
 * Mencegah browser menyimpan cache halaman (termasuk bfcache Chrome/Firefox)
 * untuk halaman yang butuh login.
 *
 * Tanpa ini: setelah logout, klik tombol "Forward"/"Back" di browser bisa
 * menampilkan snapshot halaman terakhir (misal halaman Input) walau sesi
 * sudah benar-benar dihapus di server. Ini murni tampilan cache browser,
 * bukan berarti sesi masih aktif - tapi tetap membingungkan/berisiko,
 * jadi kita matikan cache-nya di level HTTP header.
 */
class PreventBackHistoryCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
 
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
 
        return $response;
    }
}