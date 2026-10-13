<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ganti unique index dari `tanggal` ke `no_po`,
     * supaya satu tanggal bisa punya lebih dari satu PO.
     */
    public function up(): void
    {
        Schema::table('produksi_harian', function (Blueprint $table) {
            $table->dropUnique('produksi_harian_tanggal_unique');
            $table->unique('no_po');
        });
    }

    public function down(): void
    {
        Schema::table('produksi_harian', function (Blueprint $table) {
            $table->dropUnique(['no_po']);
            $table->unique('tanggal');
        });
    }
};
