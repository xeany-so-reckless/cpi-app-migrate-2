<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * Menggantikan array hardcoded di code.gs (getUserDatabase) dan
     * duplikatnya di Rekap Hasil Produksi v.4 (usersRekap).
     * Semua user (Tally & Foreman/Approver) digabung jadi satu tabel,
     * dengan kolom `role` untuk membedakan hak akses/tampilan approval.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ID lama seperti "TLY01", "APP01" dipertahankan sebagai kolom
            // employee_code, dipakai untuk login (bukan email).
            $table->string('employee_code', 20)->unique();

            $table->string('name');

            // Password di-hash pakai bcrypt (Hash::make), tidak lagi plaintext.
            $table->string('password');

            // Menggantikan pembedaan prefix "TLY" vs "APP" di kode lama.
            $table->enum('role', ['tally', 'foreman', 'admin'])->default('tally');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
