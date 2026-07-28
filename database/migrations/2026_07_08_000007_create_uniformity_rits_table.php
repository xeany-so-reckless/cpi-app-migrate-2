<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menggantikan sheet "Data_Rit" di Apps Script lama (modul Uniformity
     * Live Birds). 1 baris = 1 rit/truk yang sudah selesai sampling.
     */
    public function up(): void
    {
        Schema::create('uniformity_rits', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');
            $table->string('no_rit', 50);
            $table->string('asal_kandang');

            // Range acuan size yang dipakai untuk klasifikasi Undersize/Masuk/Oversize
            $table->decimal('size_min', 6, 2);
            $table->decimal('size_max', 6, 2);

            $table->decimal('kg_dta', 10, 1);
            $table->unsignedInteger('ekor_dta');
            $table->decimal('rerata_abw', 8, 3)->default(0); // kg_dta / ekor_dta

            $table->unsignedSmallInteger('jumlah_sample')->default(0);

            // Hasil kalkulasi klasifikasi berdasarkan sample (lihat uniformity_samples)
            $table->decimal('undersize_percent', 5, 1)->default(0);
            $table->decimal('size_masuk_percent', 5, 1)->default(0);
            $table->decimal('oversize_percent', 5, 1)->default(0);

            $table->timestamps();

            // 1 no_rit hanya boleh 1 kali per tanggal (menggantikan validasi
            // duplikat manual di kode lama).
            $table->unique(['tanggal', 'no_rit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniformity_rits');
    }
};
