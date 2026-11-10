<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat pemindahan bag antar cell. Satu baris = SATU KALI pemindahan
     * satu bag (batch_id + nomor_bag) dari from_cell_id ke to_cell_id.
     *
     * Bag boleh dipindah berkali-kali (cell biasa -> simpanan -> cell biasa
     * -> simpanan, dst), jadi satu bag bisa punya BANYAK baris. Lokasi bag
     * SAAT INI = to_cell_id dari baris dengan id TERBESAR untuk bag itu
     * (kalau belum pernah dipindah, lokasinya cell reservasi aslinya).
     *
     * Baris tidak pernah diubah/dihapus (hanya ikut terhapus kalau batch-nya
     * dihapus Supervisor), jadi tabel ini sekaligus jadi jejak audit.
     *
     * Perhitungan stock per cell tetap sederhana: total yang masuk dikurangi
     * total yang keluar, jadi angka tetap benar walau bag berpindah-pindah.
     */
    public function up(): void
    {
        Schema::create('stock_transfer_bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('serah_terima_batches')->cascadeOnDelete();
            $table->unsignedTinyInteger('nomor_bag'); // 1-10
            $table->foreignId('from_cell_id')->constrained('cells');
            $table->foreignId('to_cell_id')->constrained('cells');
            $table->decimal('kg', 10, 2); // snapshot kg bag saat dipindah
            $table->foreignId('user_id')->constrained('users'); // ADM / SPVG yang memindahkan
            $table->timestamp('transferred_at')->useCurrent();
            $table->timestamps();

            // BUKAN unique lagi - satu bag boleh punya beberapa riwayat.
            // Index ini mempercepat pencarian "pemindahan terakhir bag X".
            $table->index(['batch_id', 'nomor_bag']);
            $table->index('from_cell_id');
            $table->index('to_cell_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_bags');
    }
};
