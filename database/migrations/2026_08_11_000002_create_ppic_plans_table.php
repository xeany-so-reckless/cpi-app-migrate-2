<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data Planning vs Aktual (Ekor & KG), granularitas PER HARI - 1 baris
     * per tanggal, digabung semua produk jadi 1 angka.
     *
     * Selisih & Persentase Selisih (Ekor maupun KG) TIDAK disimpan disini
     * - dihitung otomatis lewat accessor di Model (sama pola seperti
     * total_kg di SerahTerimaBatch), supaya selalu konsisten kalau
     * plan/aktual-nya diedit ulang.
     *
     * aktual_ekor & aktual_kg untuk sekarang diisi MANUAL. Nanti kalau
     * sudah siap ditarik otomatis dari data produksi, kolom ini tetap
     * dipakai (cuma cara ngisinya yang berubah di Controller, bukan
     * struktur tabel).
     */
    public function up(): void
    {
        Schema::create('ppic_plans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->unsignedInteger('plan_ekor')->default(0);
            $table->unsignedInteger('aktual_ekor')->default(0);
            $table->decimal('plan_kg', 12, 2)->default(0);
            $table->decimal('aktual_kg', 12, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppic_plans');
    }
};
