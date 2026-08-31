<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header dokumen pengiriman (DO) yang diinput Checker saat barang
     * selesai dimuat. Tidak menyimpan angka stock sama sekali - murni
     * data administratif pengiriman (tanggal, No DO, customer, dst).
     * Efek pengurangan stock dicatat terpisah lewat cell_stock_adjustments
     * (sumber='outbound'), bukan di tabel ini.
     */
    public function up(): void
    {
        Schema::create('outbound_shipments', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_do', 100);
            $table->string('nama_customer', 150);
            $table->time('jam_muat');
            $table->string('no_pol', 20);
            $table->string('nama_driver', 100);
            $table->foreignId('checker_user_id')->constrained('users');
            $table->enum('status', ['SELESAI'])->default('SELESAI');
            $table->timestamps();

            $table->index('no_do');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_shipments');
    }
};
