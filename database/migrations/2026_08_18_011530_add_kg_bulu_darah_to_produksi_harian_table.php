<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom Kg Bulu Darah - input manual di form Input Produksi
     * Harian, ditaruh setelah kg_titik_nol supaya urutan kolom di DB
     * kurang lebih mengikuti urutan tampil di form.
     */
    public function up(): void
    {
        Schema::table('produksi_harian', function (Blueprint $table) {
            $table->decimal('kg_bulu_darah', 12, 2)->default(0)->after('kg_titik_nol');
        });
    }

    public function down(): void
    {
        Schema::table('produksi_harian', function (Blueprint $table) {
            $table->dropColumn('kg_bulu_darah');
        });
    }
};
