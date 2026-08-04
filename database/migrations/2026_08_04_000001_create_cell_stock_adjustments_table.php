<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat penyesuaian stock per Cell - dipakai saat Admin/Supervisor
     * Gudang upload Excel data real dari lapangan (JUMLAH (BAG) di file
     * dibandingkan dengan hitungan sistem saat itu, selisihnya disimpan
     * sebagai adjustment permanen).
     *
     * Perhitungan stock selanjutnya (Cell::sisaKapasitas(), StockController)
     * akan menjumlahkan SEMUA adjustment yang pernah ada untuk cell itu.
     */
    public function up(): void
    {
        Schema::create('cell_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cell_id')->constrained('cells');
            $table->integer('jumlah_sistem_sebelum'); // hasil hitung sistem SEBELUM adjustment ini
            $table->integer('jumlah_aktual');          // dari kolom "JUMLAH (BAG)" di Excel
            $table->integer('selisih');                // jumlah_aktual - jumlah_sistem_sebelum (boleh negatif)
            $table->string('sumber', 100)->default('upload_excel'); // jaga-jaga kalau nanti ada sumber lain
            $table->string('nama_file', 255)->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index('cell_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cell_stock_adjustments');
    }
};
