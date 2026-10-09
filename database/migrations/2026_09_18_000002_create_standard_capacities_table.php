<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * STEP 2 (REVISI) - Master data kapasitas standar per (produk x
     * lantai x tipe rak), sumbernya dari Excel standar gudang.
     *
     * Ini yang menggantikan peran kolom statis `kapasitas_max` di Cell
     * SAAT Cell sedang terkunci ke suatu produk: kapasitas bag-nya
     * di-lookup dari sini berdasarkan lantai & tipe_rak milik Cell
     * tersebut. `kapasitas_max` di tabel `cells` TETAP ada & tidak
     * dihapus - dipakai sebagai fallback saat Cell masih kosong, atau
     * saat kombinasi produk+lantai+tipe_rak belum ada datanya di sini.
     *
     * REVISI dari draft sebelumnya:
     * - `lantai` diganti dari integer -> string, SAMA PERSIS dengan
     *   kolom `cells.lantai` (isinya "LANTAI 1", "LANTAI 2", "LANTAI 3").
     * - `tipe_rak` isinya 'besar' / 'kecil', SAMA PERSIS dengan kolom
     *   `cells.tipe_rak` dari migration Step 1B.
     *
     * Data isinya BELUM diisi di migration ini - itu Step 4 (seeder
     * terpisah), menunggu file standar final dari gudang.
     */
    public function up(): void
    {
        Schema::create('standard_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('lantai', 20);   // contoh: "LANTAI 1" - samakan dgn cells.lantai
            $table->string('tipe_rak');     // 'besar' / 'kecil' - samakan dgn cells.tipe_rak
            $table->unsignedInteger('kapasitas_bag');
            $table->timestamps();

            // 1 produk hanya boleh punya 1 angka kapasitas untuk kombinasi
            // lantai + tipe rak yang sama - mencegah data standar ganda/bentrok.
            $table->unique(['product_id', 'lantai', 'tipe_rak']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_capacities');
    }
};
