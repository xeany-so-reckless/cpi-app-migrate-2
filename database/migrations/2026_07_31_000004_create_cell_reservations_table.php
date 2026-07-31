<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reservasi Cell oleh Tally Gudang (TWH), dibuat SEBELUM Tally Produksi
     * (TPR) input batch. TWH cukup pilih Cell (belum tahu produknya) ->
     * sistem hitung sisa kapasitas saat itu -> max_bag_allowed = MIN(10, sisa).
     *
     * TPR nanti pilih dari daftar reservasi yang masih PENDING, isi produk +
     * trolly, dengan jumlah_bag dibatasi oleh max_bag_allowed reservasi ini.
     * Begitu dipakai, status berubah jadi USED dan batch_id terisi.
     *
     * Validasi produk vs Cell (Master Produk-Cell) dilakukan di level
     * aplikasi saat TPR memilih reservasi + produk, bukan di database.
     */
    public function up(): void
    {
        Schema::create('cell_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cell_id')->constrained('cells');
            $table->unsignedTinyInteger('max_bag_allowed'); // MIN(10, sisa kapasitas saat reservasi dibuat)
            $table->enum('status', ['PENDING', 'USED', 'CANCELLED'])->default('PENDING');
            $table->foreignId('created_by_user_id')->constrained('users'); // TWH yang bikin reservasi
            $table->foreignId('batch_id')
                ->nullable()
                ->unique()
                ->constrained('serah_terima_batches')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cell_reservations');
    }
};
