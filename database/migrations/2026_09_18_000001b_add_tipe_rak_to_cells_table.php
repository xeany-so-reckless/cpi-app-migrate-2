<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * STEP 1B - Atribut FISIK Cell: Rak Besar / Rak Kecil.
     *
     * Ini properti permanen milik Cell (tidak berubah walau produk yang
     * mengisi berganti-ganti) - beda dengan `current_product_id` (Step 1)
     * yang dinamis. Dipakai sebagai kunci lookup ke `standard_capacities`
     * (Step 2) bersama `lantai`.
     *
     * BACKFILL: data yang sudah ada sekarang ditandai lewat suffix
     * "(RK)" pada kode_cell (contoh: "1C43 (RK)" = rak kecil,
     * "1A01" = rak besar). Backfill ini HANYA untuk data existing saat
     * migration ini dijalankan - Cell baru ke depannya HARUS diisi
     * tipe_rak-nya secara manual (lewat form/seeder terpisah), TIDAK lagi
     * di-derive otomatis dari suffix kode_cell, supaya tidak tergantung
     * konsistensi penulisan suffix yang rawan typo.
     *
     * Pencarian suffix dibuat longgar (case-insensitive, boleh dengan
     * atau tanpa spasi sebelum kurung) untuk jaga-jaga variasi penulisan
     * yang sudah ada di data sekarang.
     */
    public function up(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->string('tipe_rak')->nullable()->after('lantai'); // 'besar' | 'kecil'
        });

        // Backfill data existing berdasarkan suffix "(RK)" di kode_cell.
        // Regex: cocok "(RK)" longgar spasi/kapital, di mana saja dalam kode_cell.
        DB::table('cells')
            ->where('kode_cell', 'REGEXP', '\\(\\s*RK\\s*\\)')
            ->update(['tipe_rak' => 'kecil']);

        DB::table('cells')
            ->whereNull('tipe_rak')
            ->update(['tipe_rak' => 'besar']);
    }

    public function down(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->dropColumn('tipe_rak');
        });
    }
};
