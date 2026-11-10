<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apar_inspections', function (Blueprint $table) {
            $table->id();

            // --- Identitas (diisi manual oleh inspektor) ---
            $table->string('nama_inspektor', 100);
            $table->string('jabatan', 100);
            $table->date('tanggal_inspeksi');
            $table->time('jam_inspeksi');
            $table->string('nomor_apar', 50);
            $table->string('jenis_apar', 20);   // co2 | foam | dry_powder | gas
            $table->string('lokasi_apar', 150);

            // --- Kondisi komponen: 'baik' | 'rusak' ---
            $table->string('pin_segel', 10);
            $table->string('selang', 10);
            $table->string('klem_selang', 10);
            $table->string('nozzle', 10);
            $table->string('pressure_gauge', 10)->nullable(); // hanya dry_powder
            $table->string('handle', 10);
            $table->string('kondisi_fisik', 10);
            $table->string('roda', 10)->default('tidak_ada'); // baik | rusak | tidak_ada (bukan APAB)

            $table->text('keterangan')->nullable();
            $table->string('foto_path');

            // true bila ada minimal 1 komponen berstatus 'rusak'
            $table->boolean('has_findings')->default(false);

            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('tanggal_inspeksi');
            $table->index('has_findings');
            $table->index('nomor_apar');
            $table->index('jenis_apar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apar_inspections');
    }
};
