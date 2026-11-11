<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rincian isi 1 Tir per produk (item). Dipakai untuk form Tally Tir
     * (FM-WH-016) yang berbentuk matriks: baris = item, kolom = No. Tir.
     * 1 Tir boleh berisi lebih dari 1 item, jadi 1 baris di
     * outbound_shipment_tirs punya banyak baris di tabel ini.
     *
     * produk_id NULL = baris "Stock Adjustment" (stock tanpa identitas
     * produk/batch). Kalau nanti diputuskan Stock Adjustment tidak ikut
     * dirinci per tir, kolom ini cukup tidak pernah diisi NULL.
     *
     * outbound_shipment_tirs.jumlah_bag tetap dipakai dan diisi dengan
     * hasil penjumlahan semua item di tir tersebut, supaya data lama dan
     * tampilan ringkasan yang sudah ada tidak rusak.
     *
     * Foreign key ke tabel 'products' (model Product) tanpa cascade,
     * supaya produk yang sudah pernah tercatat di tir tidak bisa
     * terhapus diam-diam dan berubah jadi terbaca "Stock Adjustment".
     */
    public function up(): void
    {
        Schema::create('outbound_shipment_tir_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_shipment_tir_id')
                ->constrained('outbound_shipment_tirs')
                ->cascadeOnDelete();
            $table->foreignId('produk_id')->nullable()->constrained('products');
            $table->unsignedInteger('jumlah_bag');
            $table->timestamps();

            $table->unique(['outbound_shipment_tir_id', 'produk_id'], 'tir_items_tir_produk_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_shipment_tir_items');
    }
};
