<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom teco_at (Technically Complete, istilah SAP) - PPIC
     * bisa menandai PO sebagai "selesai secara teknis" dan membukanya
     * kembali (unTECO) kapan saja. Pakai timestamp (bukan boolean)
     * supaya sekalian tercatat KAPAN PO itu di-TECO, berguna untuk audit.
     *
     * null = PO masih aktif (belum TECO).
     * ada isinya = PO sudah TECO sejak waktu tersebut.
     *
     * Efek: PO yang sudah TECO otomatis hilang dari dropdown "Nomor PO"
     * di form Sebelum Bongkar (LB Report) - lihat
     * LbReportController::listPurchaseOrders(). Tidak berpengaruh ke
     * Dashboard Produksi Bulanan (PO TECO tetap bisa dipilih di sana).
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('teco_at')->nullable()->after('jumlah_rit');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('teco_at');
        });
    }
};
