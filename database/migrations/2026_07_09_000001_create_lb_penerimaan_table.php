<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menggantikan sheet "Data_Penerimaan" di code.gs (modul Report Harian
     * Bahan Baku Live Birds). 1 baris = 1 rit truk, diisi bertahap:
     * - Tahap 1 (role APP): kolom "sebelum bongkar"
     * - Tahap 2 (role LGS): kolom "setelah bongkar" (hasil timbang)
     */
    public function up(): void
    {
        Schema::create('lb_penerimaan', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');
            $table->time('jam_kedatangan');
            $table->string('no_rit', 50);
            $table->string('area', 20); // "Area 1".."Area 4"
            $table->string('farm');

            // Data awal (sebelum bongkar / DTA - Data Truk Awal)
            $table->decimal('kg_dta', 10, 1);
            $table->unsignedInteger('ekor_dta');

            // Data hasil timbang (setelah bongkar), default 0 sebelum diisi
            $table->decimal('kg_netto', 10, 1)->default(0);
            $table->unsignedInteger('ekor_netto')->default(0);
            $table->unsignedInteger('ayam_mati')->default(0);
            $table->decimal('susut_percent', 5, 2)->default(0);

            // Status: "Proses Bongkar" -> "Baru" (baru diupdate) -> "Lama" (sudah dilihat di dashboard)
            $table->string('status', 20)->default('Proses Bongkar');

            $table->decimal('kg_undersize', 10, 2)->default(0);
            $table->unsignedInteger('ekor_undersize')->default(0);
            $table->decimal('berat_reject', 10, 2)->default(0);

            // Metadata truk
            $table->string('ekspedisi')->nullable();
            $table->string('no_polisi')->nullable();
            $table->string('size')->nullable();
            $table->string('no_dta')->nullable();
            $table->string('no_sppa')->nullable();

            $table->decimal('kg_rphu', 10, 1)->default(0); // Kg Bruto
            $table->decimal('kg_basah', 10, 1)->default(0);
            $table->string('keterangan')->nullable();
            $table->string('no_po', 50)->nullable();

            $table->timestamps();

            // Menggantikan validasi duplikat manual (tanggal + no_rit)
            $table->unique(['tanggal', 'no_rit']);
            $table->index('no_po');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_penerimaan');
    }
};
