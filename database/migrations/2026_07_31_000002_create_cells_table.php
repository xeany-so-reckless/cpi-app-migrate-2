<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master data lokasi Cell Cold Storage beserta kapasitas maksimalnya
     * (satuan: jumlah Bag). Dipakai untuk cek sisa kapasitas real-time
     * saat Tally Gudang assign Kode Cell.
     */
    public function up(): void
    {
        Schema::create('cells', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cell', 50)->unique();
            $table->unsignedInteger('kapasitas_max'); // satuan: jumlah bag
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cells');
    }
};
