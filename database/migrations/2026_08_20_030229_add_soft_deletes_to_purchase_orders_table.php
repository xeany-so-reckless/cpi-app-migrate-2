<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom deleted_at (soft delete standar Laravel) supaya PO
     * yang "dihapus" PPIC tidak benar-benar hilang dari database -
     * cukup ditandai deleted_at, bisa di-restore kalau ternyata salah
     * hapus (lihat PurchaseOrderController::restore()).
     *
     * Semua query normal lewat Model PurchaseOrder (data(), listPurchaseOrders()
     * di LB Report, exists:purchase_orders,nomor_po di validasi) otomatis
     * mengecualikan PO yang soft-deleted begitu Model pakai trait
     * SoftDeletes - tidak perlu ubah query manapun secara manual.
     *
     * Nomor PO yang sudah dihapus TETAP terkunci (tidak bisa dipakai
     * ulang) selama belum di-restore, karena constraint unique nomor_po
     * masih mendeteksi baris yang soft-deleted (barisnya masih ada
     * secara fisik di tabel).
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
