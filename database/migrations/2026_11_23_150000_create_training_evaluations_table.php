<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('nik', 30)->index();
            $table->date('tanggal_pengisian')->index();
            $table->string('bagian', 50)->index();

            // Daftar kode materi yang dicentang, mis. ["company_profile","k3",...]
            $table->json('materi_diikuti');

            // true bila ada minimal satu jawaban "Kadang-Kadang" / "Tidak Pernah"
            $table->boolean('has_findings')->default(false)->index();

            $table->string('ip_address', 45)->nullable();

            // Jejak edit dari sisi PGA (sama seperti apar_inspections)
            $table->string('diedit_oleh', 150)->nullable();
            $table->timestamp('diedit_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_evaluations');
    }
};
