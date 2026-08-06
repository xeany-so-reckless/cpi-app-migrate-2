<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom KG ke cell_stock_adjustments - dibutuhkan supaya fitur
     * Outbound nanti bisa hitung kg keluar, walau stock-nya berasal dari
     * penyesuaian manual (upload Excel), bukan cuma dari Inbound asli
     * yang sudah punya berat per bag (kg_bag_1..10 di serah_terima_batches).
     */
    public function up(): void
    {
        Schema::table('cell_stock_adjustments', function (Blueprint $table) {
            $table->decimal('kg_sistem_sebelum', 10, 2)->default(0)->after('selisih');
            $table->decimal('kg_aktual', 10, 2)->default(0)->after('kg_sistem_sebelum');
            $table->decimal('selisih_kg', 10, 2)->default(0)->after('kg_aktual');
        });
    }

    public function down(): void
    {
        Schema::table('cell_stock_adjustments', function (Blueprint $table) {
            $table->dropColumn(['kg_sistem_sebelum', 'kg_aktual', 'selisih_kg']);
        });
    }
};
