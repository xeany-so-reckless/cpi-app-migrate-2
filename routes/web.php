<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TallyPro\AuthController;
use App\Http\Controllers\TallyPro\TallyInputController;
use App\Http\Controllers\TallyPro\RekapController;
use App\Http\Controllers\SerahTerima\AuthController as SerahTerimaAuthController;
use App\Http\Controllers\SerahTerima\SerahTerimaController;
use App\Http\Controllers\Uniformity\UniformityController;
use App\Http\Controllers\LbReport\AuthController as LbReportAuthController;
use App\Http\Controllers\LbReport\LbReportController;
use App\Http\Controllers\ProduksiDashboard\ProduksiDashboardController;
use App\Http\Controllers\It\AuthController as ItAuthController;
use App\Http\Controllers\It\ItController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ==================== TALLY PRO ====================
Route::prefix('tally-pro')->name('tally.')->group(function () {

    Route::middleware('guest.tally')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware(['auth:tally', 'role:tally,foreman', 'no-cache'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/input', [TallyInputController::class, 'index'])->name('input');

        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap');
        Route::post('/rekap/verify-signature', [RekapController::class, 'verifySignature'])->name('rekap.verify-signature');
    });
});

// ==================== SERAH TERIMA PRODUKSI ====================
Route::prefix('serah-terima')->name('serahterima.')->group(function () {

    Route::middleware('guest.serahterima')->group(function () {
        Route::get('/login', [SerahTerimaAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [SerahTerimaAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware(['auth:tally', 'role:tally_produksi,tally_gudang,supervisor', 'no-cache'])->group(function () {
        Route::post('/logout', [SerahTerimaAuthController::class, 'logout'])->name('logout');

        Route::get('/', [SerahTerimaController::class, 'index'])->name('index');
        Route::get('/data', [SerahTerimaController::class, 'data'])->name('data');

        Route::post('/batches', [SerahTerimaController::class, 'store'])->name('batches.store');
        Route::put('/batches/{batch}', [SerahTerimaController::class, 'update'])->name('batches.update');
        Route::post('/batches/{batch}/bag/verify-all', [SerahTerimaController::class, 'verifyAllBags'])->name('batches.bag.verify-all');
        Route::post('/batches/{batch}/bag/{bagIndex}', [SerahTerimaController::class, 'updateBagStatus'])->name('batches.bag.update');
        Route::post('/batches/{batch}/finalize', [SerahTerimaController::class, 'finalize'])->name('batches.finalize');
        Route::post('/batches/{batch}/approve', [SerahTerimaController::class, 'approve'])->name('batches.approve');
        Route::delete('/batches/{batch}', [SerahTerimaController::class, 'destroy'])->name('batches.destroy');
    });
});

// ==================== UNIFORMITY LIVE BIRDS ====================
// Tidak ada login/guard di modul ini (sesuai aslinya) - akses terbuka.
// Form Input dikunci pakai PIN (verify-pin), dan tanda tangan Export
// dikunci pakai ID+password role foreman (verify-signature).
Route::prefix('uniformity')->name('uniformity.')->group(function () {
    Route::get('/', [UniformityController::class, 'index'])->name('index');
    Route::get('/export', [UniformityController::class, 'exportPage'])->name('export');

    Route::get('/data', [UniformityController::class, 'data'])->name('data');
    Route::get('/rekap', [UniformityController::class, 'rekap'])->name('rekap');

    Route::post('/verify-pin', [UniformityController::class, 'verifyPin'])->name('verify-pin');
    Route::post('/rits', [UniformityController::class, 'store'])->name('rits.store');
    Route::post('/verify-signature', [UniformityController::class, 'verifySignature'])->name('verify-signature');
});

// ==================== REPORT HARIAN BAHAN BAKU LIVE BIRDS ====================
Route::prefix('report-lb')->name('lbreport.')->group(function () {

    // Dashboard terbuka tanpa login, sama seperti aslinya.
    Route::get('/', [LbReportController::class, 'dashboard'])->name('dashboard');
    Route::get('/rekap-data', [LbReportController::class, 'rekap'])->name('rekap-data');
    Route::get('/detail', [LbReportController::class, 'detail'])->name('detail');
    Route::get('/raw-data', [LbReportController::class, 'rawData'])->name('raw-data');

    Route::middleware('guest.lbreport')->group(function () {
        Route::get('/login', [LbReportAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [LbReportAuthController::class, 'login'])->name('login.attempt');
    });

    // Workspace (Input Sebelum/Setelah Bongkar + Hanging) - butuh login.
    // Pembatasan role per-aksi (misal storeSebelum cuma untuk lb_penerimaan_awal)
    // dilakukan di dalam controller lewat authorizeRole().
    Route::middleware(['auth:tally', 'role:lb_penerimaan_awal,lb_penerimaan_akhir,lb_hanging,supervisor', 'no-cache'])->group(function () {
        Route::post('/logout', [LbReportAuthController::class, 'logout'])->name('logout');
        Route::get('/workspace', [LbReportController::class, 'workspace'])->name('workspace');

        Route::post('/sebelum', [LbReportController::class, 'storeSebelum'])->name('sebelum.store');
        Route::post('/setelah', [LbReportController::class, 'storeSetelah'])->name('setelah.store');
        Route::post('/hanging', [LbReportController::class, 'storeHanging'])->name('hanging.store');

        Route::get('/daftar-rit-po', [LbReportController::class, 'daftarRitByPo'])->name('daftar-rit-po');
        Route::get('/ekor-netto-hanging', [LbReportController::class, 'ekorNettoHanging'])->name('ekor-netto-hanging');
        Route::get('/detail-hanging', [LbReportController::class, 'detailHangingLengkap'])->name('detail-hanging');
        Route::get('/ritase', [LbReportController::class, 'ritase'])->name('ritase');
    });
});

// ==================== DASHBOARD PRODUKSI BULANAN ====================
// Dashboard terbuka tanpa login. Simpan/Edit divalidasi stateless
// (employee_code + password role supervisor dikirim tiap request).
Route::prefix('produksi-dashboard')->name('produksi-dashboard.')->group(function () {
    Route::get('/', [ProduksiDashboardController::class, 'index'])->name('index');
    Route::get('/data', [ProduksiDashboardController::class, 'data'])->name('data');
    Route::get('/latest-update', [ProduksiDashboardController::class, 'latestUpdateInfo'])->name('latest-update');
    Route::post('/verify-signature', [ProduksiDashboardController::class, 'verifySignature'])->name('verify-signature');
    Route::post('/store', [ProduksiDashboardController::class, 'store'])->name('store');
    Route::post('/update', [ProduksiDashboardController::class, 'update'])->name('update');
});

// ==================== IT - RIWAYAT LOG AKTIVITAS ====================
Route::prefix('it')->name('it.')->group(function () {

    Route::middleware('guest.it')->group(function () {
        Route::get('/login', [ItAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ItAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware(['auth:tally', 'role:it', 'no-cache'])->group(function () {
        Route::post('/logout', [ItAuthController::class, 'logout'])->name('logout');

        Route::get('/', [ItController::class, 'index'])->name('index');
        Route::get('/data', [ItController::class, 'data'])->name('data');
        Route::get('/filter-options', [ItController::class, 'filterOptions'])->name('filter-options');
    });
});