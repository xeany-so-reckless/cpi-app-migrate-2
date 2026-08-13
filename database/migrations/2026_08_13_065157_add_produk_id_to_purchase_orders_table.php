<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom produk_id ke tabel purchase_orders.
     * Kolom ini nullable karena hanya wajib diisi untuk jenis_po = FEHM
     * (validasi wajib-nya ditangani di Controller, bukan di level DB).
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('produk_id')
                ->nullable()
                ->after('nomor_po')
                ->constrained('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produk_id');
        });
    }
};
