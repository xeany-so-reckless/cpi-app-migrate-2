<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cell simpanan: tidak terkunci produk (boleh campur produk), tidak
     * menerima dari Inbound/TPR, tidak muncul di Outbound, tidak bisa
     * di-upload Excel. Isinya HANYA dari fitur "Pindahkan" di Stock.
     */
    private const KODE_CELL_SIMPANAN = ['1C01', '1C02', '1C03', '1C04', '1C05', '1C06'];

    public function up(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->boolean('is_simpanan')->default(false)->after('is_active');
        });

        // Kalau cell tidak ditemukan (misal database lokal kosong), update
        // ini sekadar tidak mengubah apa-apa - tidak error.
        DB::table('cells')
            ->whereIn('kode_cell', self::KODE_CELL_SIMPANAN)
            ->update([
                'is_simpanan'        => true,
                'current_product_id' => null, // cell simpanan tidak pernah terkunci produk
            ]);
    }

    public function down(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->dropColumn('is_simpanan');
        });
    }
};
