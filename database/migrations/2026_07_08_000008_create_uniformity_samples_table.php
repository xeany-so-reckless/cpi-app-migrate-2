<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menggantikan sheet "Data_Uniformity" di Apps Script lama.
     * Aslinya 1 baris = 1 rit dengan sampai 200 kolom berat sample.
     * Di sini dinormalisasi: 1 baris = 1 ekor sample, supaya tidak
     * perlu ratusan kolom flat.
     */
    public function up(): void
    {
        Schema::create('uniformity_samples', function (Blueprint $table) {
            $table->id();

            $table->foreignId('uniformity_rit_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sample_index'); // urutan input, 1-200
            $table->decimal('berat', 6, 2); // berat individual ekor (kg)

            $table->timestamps();

            $table->unique(['uniformity_rit_id', 'sample_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniformity_samples');
    }
};
