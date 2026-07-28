<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Koreksi: kode_produksi TERNYATA tidak unique per baris di kode asli -
     * 1 kode bisa dipakai banyak troli di tanggal & kategori yang sama
     * (yang unik adalah kombinasi kode_produksi + no_trolly).
     */
    public function up(): void
    {
        Schema::table('serah_terima_batches', function (Blueprint $table) {
            $table->dropUnique(['kode_produksi']);
            $table->index('kode_produksi');
            $table->unique(['kode_produksi', 'no_trolly']);
        });
    }

    public function down(): void
    {
        Schema::table('serah_terima_batches', function (Blueprint $table) {
            $table->dropUnique(['kode_produksi', 'no_trolly']);
            $table->dropIndex(['kode_produksi']);
            $table->unique('kode_produksi');
        });
    }
};
