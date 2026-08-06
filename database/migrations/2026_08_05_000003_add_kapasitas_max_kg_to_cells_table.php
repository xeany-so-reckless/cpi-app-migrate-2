<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kapasitas_max_kg - data ini sebenarnya sudah ada dari awal
     * di sheet "Kapasitas" Excel Denah CS (kolom KAPASITAS > KG, merged
     * header dengan BAG), cuma kelewat diambil pas ekstraksi awal yang
     * cuma ambil kolom BAG.
     */
    public function up(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->decimal('kapasitas_max_kg', 10, 2)->nullable()->after('kapasitas_max');
        });
    }

    public function down(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->dropColumn('kapasitas_max_kg');
        });
    }
};
