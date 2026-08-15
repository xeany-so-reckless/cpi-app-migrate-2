<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom no_po ke tabel produksi_harian.
     * Nullable supaya data lama (input manual by tanggal, sebelum revisi
     * ini) tetap aman tanpa error. Unique supaya 1 PO cuma bisa dipakai
     * untuk 1 kali input laporan produksi (sesuai keputusan bisnis).
     */
    public function up(): void
    {
        Schema::table('produksi_harian', function (Blueprint $table) {
            $table->string('no_po')->nullable()->unique()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('produksi_harian', function (Blueprint $table) {
            $table->dropColumn('no_po');
        });
    }
};
