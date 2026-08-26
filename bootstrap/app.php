<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\PreventBackHistoryCache;
use App\Http\Middleware\RedirectIfItAuthenticated;
use App\Http\Middleware\RedirectIfLbReportAuthenticated;
use App\Http\Middleware\RedirectIfSerahTerimaAuthenticated;
use App\Http\Middleware\RedirectIfTallyAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\RedirectIfProduksiFreshAuthenticated;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest.tally' => RedirectIfTallyAuthenticated::class,
            'guest.serahterima' => RedirectIfSerahTerimaAuthenticated::class,
            'guest.lbreport' => RedirectIfLbReportAuthenticated::class,
            'guest.it' => RedirectIfItAuthenticated::class,
            'guest.produksifresh' => RedirectIfProduksiFreshAuthenticated::class, // BARU
            'no-cache' => PreventBackHistoryCache::class,
            'role' => EnsureUserHasRole::class,
        ]);

        // Default Laravel: kalau guard "auth:tally" mendeteksi belum login,
        // dia coba redirect ke route bernama "login" (yang tidak kita punya).
        // Karena guard "tally" dipakai bareng oleh beberapa modul, redirect-nya
        // perlu sadar konteks berdasarkan URL.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('serah-terima*')) {
                return route('serahterima.login');
            }

            if ($request->is('report-lb*')) {
                return route('lbreport.login');
            }

            if ($request->is('produksi-fresh*')) {
                return route('produksifresh.login');
            }

            if ($request->is('it*')) {
                return route('it.login');
            }

            if ($request->is('warehouse/stock*')) {
                return route('warehouse.stock.login');
            }

            return route('tally.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();