<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menambahkan role baru untuk modul Serah Terima Produksi:
     * tally_produksi, tally_gudang, supervisor.
     *
     * Dipakai raw SQL (bukan Schema::table()->change()) karena mengubah
     * kolom enum lewat Doctrine DBAL kurang presisi untuk tipe enum di MySQL.
     * ALTER TABLE MODIFY COLUMN lebih eksplisit dan aman.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM('tally', 'foreman', 'admin', 'tally_produksi', 'tally_gudang', 'supervisor')
            NOT NULL DEFAULT 'tally'
        ");
    }

    public function down(): void
    {
        // Perhatian: rollback ini akan GAGAL kalau sudah ada baris user
        // dengan role baru (tally_produksi/tally_gudang/supervisor) yang
        // belum dihapus/diubah dulu.
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM('tally', 'foreman', 'admin')
            NOT NULL DEFAULT 'tally'
        ");
    }
};
