<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * STEP 1 - Kolom penanda produk yang SEDANG mengunci/mengisi Cell ini.
     *
     * - null            => Cell kosong, boleh diisi produk apapun (kode
     *                      produk manapun).
     * - berisi produk X  => Cell terkunci ke produk X. Reservasi/batch
     *                      untuk produk lain akan ditolak sampai Cell ini
     *                      kembali ke stock 0 (lock dilepas otomatis lewat
     *                      Cell::releaseJikaKosong()).
     *
     * Sengaja NULLABLE dan tidak diisi default apapun di migration ini -
     * pengisian awal untuk Cell yang SUDAH terisi sekarang (supaya tidak
     * dianggap "kosong" tiba-tiba begitu kolom ini live) dilakukan di
     * Step 3 (proses terpisah, bukan bagian dari migration).
     */
    public function up(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->foreignId('current_product_id')
                ->nullable()
                ->after('kapasitas_max_kg')
                ->constrained('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->dropForeign(['current_product_id']);
            $table->dropColumn('current_product_id');
        });
    }
};
