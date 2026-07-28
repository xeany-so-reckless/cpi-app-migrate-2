<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menggantikan sheet "Data_Input" di code.gs (modul Dashboard Produksi
     * Bulanan / Dashboard Yield). 1 baris = 1 hari produksi.
     *
     * Kolom kalkulasi (ABW, %Susut, Yield, dll) TIDAK disimpan di sini -
     * tetap dihitung on-the-fly di model, persis seperti getProductionData()
     * di kode lama, supaya selalu konsisten kalau angka mentahnya diedit.
     */
    public function up(): void
    {
        Schema::create('produksi_harian', function (Blueprint $table) {
            $table->id();

            // unique -> menggantikan validasi duplikat tanggal yang tadinya
            // tidak ada sama sekali di kode asli (sesuai keputusan bisnis).
            $table->date('tanggal')->unique();

            $table->decimal('kg_dta', 10, 1)->default(0);
            $table->unsignedInteger('ekor_dta')->default(0);
            $table->decimal('kg_netto', 10, 1)->default(0);
            $table->unsignedInteger('ayam_mati')->default(0);

            $table->decimal('kg_titik_nol', 10, 1)->default(0);
            $table->decimal('kg_fg_bp', 10, 1)->default(0);
            $table->decimal('kg_by_product', 10, 1)->default(0);

            $table->decimal('pct_kw2', 5, 2)->default(0);
            $table->decimal('pct_defect', 5, 2)->default(0);

            $table->decimal('prod_griller', 10, 1)->default(0);
            $table->decimal('prod_parting', 10, 1)->default(0);
            $table->decimal('prod_marinasi', 10, 1)->default(0);
            $table->decimal('total_hasil', 10, 1)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_harian');
    }
};
