<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom jumlah_rit ke tabel purchase_orders.
     * Wajib diisi untuk SEMUA jenis PO (validasi wajib-nya di Controller).
     * Pakai default(0) supaya data PO lama (sebelum kolom ini ada) tidak
     * error / NULL, otomatis kebagian nilai 0.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_rit')->default(0)->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('jumlah_rit');
        });
    }
};
