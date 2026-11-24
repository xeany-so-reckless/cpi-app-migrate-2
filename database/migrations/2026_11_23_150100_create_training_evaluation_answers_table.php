<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_evaluation_id')
                ->constrained('training_evaluations')
                ->cascadeOnDelete();

            // Kode materi (kunci di config/training.php) dan nomor pertanyaan di materi itu
            $table->string('materi', 40);
            $table->unsignedTinyInteger('nomor');

            // 4 = Selalu, 3 = Sering, 2 = Kadang-Kadang, 1 = Tidak Pernah
            // (hanya untuk perhitungan; di layar selalu tampil sebagai teks)
            $table->unsignedTinyInteger('nilai');

            $table->unique(['training_evaluation_id', 'materi', 'nomor'], 'tea_unik');
            $table->index('materi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_evaluation_answers');
    }
};
