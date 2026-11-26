<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan produk pada tiap bag yang keluar.
     *
     * Dibutuhkan terutama untuk baris generik "Stock Adjustment" (batch_id
     * NULL), yang tidak punya batch sehingga nama produknya tidak bisa
     * ditelusuri lagi saat Riwayat / PDF Tally Loading dibuka. Untuk bag
     * batch, kolom ini boleh tetap NULL (produknya sudah ada di batch).
     *
     * Nullable supaya data Outbound lama tetap aman tanpa backfill.
     */
    public function up(): void
    {
        Schema::table('outbound_shipment_cell_bags', function (Blueprint $table) {
            $table->foreignId('produk_id')
                ->nullable()
                ->after('kode_produksi')
                ->constrained('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('outbound_shipment_cell_bags', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produk_id');
        });
    }
};
