<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat aktivitas lintas semua modul (Tally Pro, Serah Terima,
     * Uniformity, Report LB, Dashboard Produksi). Dipakai oleh menu
     * khusus role "it".
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Nullable + employee_code disimpan terpisah, supaya log tetap
            // kebaca walau user-nya suatu saat dihapus dari tabel users.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code', 20)->nullable();
            $table->string('user_name')->nullable();

            // Modul asal aktivitas, misal: tally_pro, serah_terima, uniformity,
            // report_lb, produksi_dashboard, auth
            $table->string('module', 40);

            // Jenis aksi, misal: login, logout, create, update, delete,
            // approve, verify, sign
            $table->string('action', 40);

            // Deskripsi manusiawi, misal: "TLY01 (Eka) generate rekap tanggal 2026-07-13"
            $table->string('description', 500);

            $table->string('ip_address', 45)->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['module', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
