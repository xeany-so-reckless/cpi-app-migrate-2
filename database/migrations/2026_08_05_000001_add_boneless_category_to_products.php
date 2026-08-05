<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah 'boneless' ke enum category - dibutuhkan untuk produk
     * "SBB Mitra Fz" (kode Excel: SBBM) yang sebelumnya belum ada di
     * Master Produk, menyebabkan 24 cell di Denah CS tidak punya relasi
     * produk (kolom Kode/Nama Produk kosong di Stock Warehouse).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE products MODIFY COLUMN category ENUM('kw1','kw2','bahan_baku','parting','by_product','boneless') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE products MODIFY COLUMN category ENUM('kw1','kw2','bahan_baku','parting','by_product') NOT NULL");
    }
};
