<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * Menggantikan DUA sumber data yang sebelumnya terpisah dan duplikat:
     * 1. `productDatabase` di Input Tally Produksi v.3 (kode, nama, default ekor)
     * 2. `masterKW1_R`, `masterKW2_R`, `masterBahanBaku_R`, `masterParting_R`,
     *    `masterByProduct_R` di Rekap Hasil Produksi v.4 (kode, deskripsi, kategori)
     *
     * Sekarang jadi satu sumber kebenaran (single source of truth) dengan
     * tambahan kolom `category` agar rekap tetap bisa mengelompokkan produk
     * tanpa perlu daftar terpisah.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Kode produk lama (angka 1-70 di kode asli), dijadikan string
            // agar fleksibel kalau ke depan formatnya berubah.
            $table->string('code', 20)->unique();

            $table->string('name');

            // Dipakai untuk autofill jumlah ekor saat kode diketik
            // (persis seperti fungsi checkProductCode di Input Tally).
            $table->unsignedInteger('default_ekor')->default(0);

            // Kategori untuk pengelompokan di halaman Rekap.
            $table->enum('category', [
                'kw1',          // AYAM UTUH - KW 1 (GRILLER)
                'kw2',          // AYAM UTUH - KW 2 (GRILLER)
                'bahan_baku',   // BAHAN BAKU
                'parting',      // PARTING & MARINASI
                'by_product',   // BY PRODUCT EVIS & OTHERS
            ]);

            // Nomor urut tampilan di dalam kategorinya masing-masing
            // (menggantikan field "no" di master array lama).
            $table->unsignedInteger('display_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
