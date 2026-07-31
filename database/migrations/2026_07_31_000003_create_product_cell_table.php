<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relasi many-to-many: 1 produk boleh punya beberapa pilihan Cell
     * yang sah untuk menyimpan produk tsb. Dipakai untuk memfilter
     * dropdown Kode Cell di sisi Tally Gudang, supaya cuma Cell yang
     * memang diperuntukkan untuk produk itu yang muncul sebagai opsi.
     */
    public function up(): void
    {
        Schema::create('product_cell', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('cell_id')->constrained('cells')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['produk_id', 'cell_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cell');
    }
};
