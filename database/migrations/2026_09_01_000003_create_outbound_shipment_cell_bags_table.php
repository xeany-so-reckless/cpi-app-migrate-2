<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detail per bag yang dicentang & keluar dalam 1 shipment_cell.
     *
     * batch_id + nomor_bag NULL kalau bag ini berasal dari baris generik
     * "Stock Adjustment" (stock tanpa identitas batch asli, hasil upload
     * Excel) - dalam kasus ini keterangan diisi supaya tetap ada jejak.
     *
     * Baris di tabel ini JUGA berfungsi sebagai penanda "bag X di batch Y
     * sudah pernah keluar" - dipakai untuk exclude bag itu dari daftar
     * centang cell yang sama di Outbound berikutnya (supaya tidak keluar
     * dobel dan sisa bag yang belum di-OK tetap bisa dipilih lagi nanti).
     */
    public function up(): void
    {
        Schema::create('outbound_shipment_cell_bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_shipment_cell_id')->constrained('outbound_shipment_cells')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('serah_terima_batches');
            $table->unsignedTinyInteger('nomor_bag')->nullable(); // 1-10, null kalau generik (Stock Adjustment)
            $table->decimal('kg', 10, 2);
            $table->string('kode_produksi', 100)->nullable(); // snapshot buat histori, biar tidak hilang kalau batch diedit
            $table->string('keterangan', 150)->nullable(); // contoh isi: "Stock Adjustment" kalau generik
            $table->timestamps();

            $table->index(['batch_id', 'nomor_bag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_shipment_cell_bags');
    }
};
