<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom type & category_code ke tabel products, dipakai
     * modul Produksi Fresh (menggantikan dictionary PRODUCTS hardcode
     * di Apps Script lama).
     *
     * type: 'main' atau 'byproduct' - menentukan apakah produk ini
     * boleh diinput lewat form tipe Main Product atau By Product.
     *
     * category_code: kode 2 digit (contoh: '01', '02', '05') dipakai
     * sebagai bagian dari algoritma generate Kode Produksi Batch
     * (lihat ProduksiFresh::generateKodeProduksi()).
     *
     * Nullable karena produk-produk lama (dipakai untuk PO jenis FEHM,
     * tidak berkaitan dengan modul Produksi Fresh) tidak perlu diisi
     * kolom ini.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->after('name');
            $table->string('category_code', 2)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'category_code']);
        });
    }
};
