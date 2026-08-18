<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Warehouse\WarehouseDashboardController;
use App\Http\Controllers\Warehouse\StockController;
use App\Http\Controllers\Warehouse\AuthController as WarehouseStockAuthController;
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
use App\Http\Controllers\Ppic\AuthController as PpicAuthController;
use App\Http\Controllers\Ppic\PpicController;
use App\Http\Controllers\Ppic\PlanningController;
use App\Http\Controllers\Ppic\PurchaseOrderController;
use App\Http\Controllers\Ppic\PpicDashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ==================== DASHBOARD WAREHOUSE ====================
// Terbuka tanpa login (sama seperti dashboard utama) - proteksi login
// ada di masing-masing menu tujuan (Inbound -> serahterima.login, dst).
Route::get('/warehouse', [WarehouseDashboardController::class, 'index'])->name('warehouse.dashboard');

// ==================== STOCK WAREHOUSE ====================
// Khusus admin_gudang & supervisor_gudang (ADMG01, SPVG) - login terpisah
// dari Serah Terima, walau akun & guard-nya sama (tabel users/tally).
Route::prefix('warehouse/stock')->name('warehouse.stock.')->group(function () {
    Route::get('/login', [WarehouseStockAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WarehouseStockAuthController::class, 'login'])->name('login.attempt');

    Route::middleware(['auth:tally', 'role:admin_gudang,supervisor_gudang', 'no-cache'])->group(function () {
        Route::post('/logout', [WarehouseStockAuthController::class, 'logout'])->name('logout');

        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/data', [StockController::class, 'data'])->name('data');
        Route::get('/filter-options', [StockController::class, 'filterOptions'])->name('filter-options');

            // Baru: upload Excel penyesuaian stock cell
    Route::post('/upload', [StockController::class, 'uploadExcel'])->name('upload');

    // Baru: detail batch inbound per cell (dipanggil saat baris di-expand)
    Route::get('/{cell}/batches', [StockController::class, 'batches'])->name('batches');
    });
});

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

    // Role ditambah: admin_gudang & supervisor_gudang (approval kedua sisi
    // gudang, sejajar SPV Produksi) supaya bisa akses halaman & endpoint
    // approve-admin-gudang di bawah.
    Route::middleware(['auth:tally', 'role:tally_produksi,tally_gudang,supervisor,admin_gudang,supervisor_gudang', 'no-cache'])->group(function () {
        Route::post('/logout', [SerahTerimaAuthController::class, 'logout'])->name('logout');

        Route::get('/', [SerahTerimaController::class, 'index'])->name('index');
        Route::get('/data', [SerahTerimaController::class, 'data'])->name('data');

        // --- BARU: Reservasi Cell oleh TWH (sebelum TPR input batch) ---
        Route::get('/cells', [SerahTerimaController::class, 'listCells'])->name('cells.index');
        Route::post('/cell-reservations', [SerahTerimaController::class, 'storeCellReservation'])->name('cell-reservations.store');
        Route::get('/cell-reservations', [SerahTerimaController::class, 'listCellReservations'])->name('cell-reservations.index');

        Route::post('/batches', [SerahTerimaController::class, 'store'])->name('batches.store');
        Route::put('/batches/{batch}', [SerahTerimaController::class, 'update'])->name('batches.update');
        Route::post('/batches/{batch}/bag/verify-all', [SerahTerimaController::class, 'verifyAllBags'])->name('batches.bag.verify-all');
        Route::post('/batches/{batch}/bag/{bagIndex}', [SerahTerimaController::class, 'updateBagStatus'])->name('batches.bag.update');

        // --- DIHAPUS: route finalize (kode_cell manual) sudah tidak dipakai,
        // digantikan alur reservasi Cell di atas ---
        // Route::post('/batches/{batch}/finalize', [SerahTerimaController::class, 'finalize'])->name('batches.finalize');

        // --- DIUBAH: approve() lama dipecah jadi 2 approval independen ---
        Route::post('/batches/{batch}/approve-admin-gudang', [SerahTerimaController::class, 'approveAdminGudang'])->name('batches.approve-admin-gudang');
        Route::post('/batches/{batch}/approve-spv', [SerahTerimaController::class, 'approveSpv'])->name('batches.approve-spv');

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
    Route::get('/purchase-orders', [LbReportController::class, 'listPurchaseOrders'])->name('purchase-orders');

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
    Route::get('/purchase-orders', [ProduksiDashboardController::class, 'listPurchaseOrders'])->name('purchase-orders');
    Route::get('/po-summary', [ProduksiDashboardController::class, 'poSummary'])->name('po-summary'); // BARU
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

// ==================== PPIC ====================
// Login terpisah, khusus role 'ppic' (akun PPIC01).
Route::prefix('ppic')->name('ppic.')->group(function () {

    Route::get('/login', [PpicAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PpicAuthController::class, 'login'])->name('login.attempt');

    Route::middleware(['auth:tally', 'role:ppic', 'no-cache'])->group(function () {
        Route::post('/logout', [PpicAuthController::class, 'logout'])->name('logout');

        Route::get('/', [PpicController::class, 'index'])->name('index');

        // --- Planning vs Aktual ---
        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/', [PlanningController::class, 'index'])->name('index');
            Route::get('/data', [PlanningController::class, 'data'])->name('data');
            Route::post('/', [PlanningController::class, 'store'])->name('store');
            Route::delete('/{plan}', [PlanningController::class, 'destroy'])->name('destroy');
        });

        // --- Input PO ---
        Route::prefix('purchase-order')->name('purchase-order.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/data', [PurchaseOrderController::class, 'data'])->name('data');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
            Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
        });

        // --- Dashboard ---
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [PpicDashboardController::class, 'index'])->name('index');
            Route::get('/data', [PpicDashboardController::class, 'data'])->name('data');
        });
    });
});