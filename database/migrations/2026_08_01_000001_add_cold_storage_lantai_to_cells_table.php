<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom cold_storage (CS 01-04) dan lantai (LANTAI 1-3) ke
     * tabel cells - dibutuhkan untuk filter di menu Stock Warehouse.
     * Data aslinya sudah ada di sheet "Kapasitas" Excel Denah CS, tapi
     * sebelumnya cuma kode_cell + kapasitas_max yang diambil ke seeder.
     */
    public function up(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->string('cold_storage', 10)->nullable()->after('kode_cell'); // contoh: "CS 01"
            $table->string('lantai', 20)->nullable()->after('cold_storage');    // contoh: "LANTAI 1"
        });
    }

    public function down(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->dropColumn(['cold_storage', 'lantai']);
        });
    }
};
