<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah breakdown warna (kuartal produksi) ke cell_stock_adjustments:
     * - Merah  : Januari - Maret
     * - Biru   : April - Juni
     * - Hijau  : Juli - September
     * - Kuning : Oktober - Desember
     *
     * Ini breakdown TAMBAHAN dari total yang sudah ada (jumlah_aktual,
     * kg_aktual) - bukan pengganti. Total tetap dipakai buat hitung
     * kapasitas/sisa seperti biasa; breakdown ini murni buat detail
     * tampilan (klik cell -> lihat rincian per warna).
     */
    public function up(): void
    {
        Schema::table('cell_stock_adjustments', function (Blueprint $table) {
            $table->integer('bag_merah')->default(0)->after('selisih_kg');
            $table->integer('bag_biru')->default(0)->after('bag_merah');
            $table->integer('bag_hijau')->default(0)->after('bag_biru');
            $table->integer('bag_kuning')->default(0)->after('bag_hijau');

            $table->decimal('kg_merah', 10, 2)->default(0)->after('bag_kuning');
            $table->decimal('kg_biru', 10, 2)->default(0)->after('kg_merah');
            $table->decimal('kg_hijau', 10, 2)->default(0)->after('kg_biru');
            $table->decimal('kg_kuning', 10, 2)->default(0)->after('kg_hijau');
        });
    }

    public function down(): void
    {
        Schema::table('cell_stock_adjustments', function (Blueprint $table) {
            $table->dropColumn([
                'bag_merah', 'bag_biru', 'bag_hijau', 'bag_kuning',
                'kg_merah', 'kg_biru', 'kg_hijau', 'kg_kuning',
            ]);
        });
    }
};
