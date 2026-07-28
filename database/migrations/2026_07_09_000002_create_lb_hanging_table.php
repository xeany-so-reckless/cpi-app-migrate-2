<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menggantikan sheet "Data_Hanging" di code.gs. Diisi terpisah oleh
     * proses hanging/counter (dicocokkan ke lb_penerimaan lewat
     * tanggal_penerimaan + no_rit, sama seperti kode lama - bukan foreign
     * key langsung, karena pencocokannya memang berbasis teks di kode asli).
     */
    public function up(): void
    {
        Schema::create('lb_hanging', function (Blueprint $table) {
            $table->id();

            $table->string('no_rit', 50);
            $table->string('jam_bongkar', 10)->nullable();
            $table->string('jam_selesai', 10)->nullable();

            $table->unsignedInteger('total_diterima')->default(0); // ekor netto hasil hanging
            $table->unsignedInteger('total_sj')->default(0);       // target ekor dari DTA
            $table->unsignedInteger('total_kosong')->default(0);

            // "OVER/PAS" atau "KURANG"
            $table->string('status', 20)->default('KURANG');

            // Grid hitung manual per blok (19 blok x 4 kolom), disimpan sebagai JSON
            $table->json('grid_json')->nullable();

            // Kunci pencocokan ke lb_penerimaan
            $table->date('tanggal_penerimaan');
            $table->string('no_po', 50)->nullable();

            $table->string('nama_tally')->nullable();
            $table->string('nama_foreman')->nullable();

            $table->timestamps();

            $table->unique(['tanggal_penerimaan', 'no_rit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_hanging');
    }
};
