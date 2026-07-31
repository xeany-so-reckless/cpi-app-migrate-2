<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan alur approval ganda & independen:
     * - Admin Gudang: QC kedua di sisi gudang, generate QR sendiri
     * - Supervisor (SPV Produksi): approve sisi produksi, generate QR sendiri
     *
     * Barcode final (barcode_url, kolom lama) baru di-generate setelah
     * KEDUA approval berstatus APPROVED.
     *
     * Kolom status_approval (lama) dipertahankan untuk kompatibilitas
     * histori, tapi baiknya di masa depan dianggap deprecated dan
     * digantikan oleh kombinasi status_approval_admin_gudang +
     * status_approval_spv.
     */
    public function up(): void
    {
        Schema::table('serah_terima_batches', function (Blueprint $table) {
            // --- Admin Gudang ---
            $table->foreignId('admin_gudang_user_id')
                ->nullable()
                ->after('tally_gudang_user_id')
                ->constrained('users');
            $table->string('status_approval_admin_gudang', 30)
                ->default('BELUM APPROVED')
                ->after('admin_gudang_user_id');
            $table->string('qr_admin_gudang_url', 500)
                ->nullable()
                ->after('status_approval_admin_gudang');

            // --- SPV Produksi ---
            $table->string('status_approval_spv', 30)
                ->default('BELUM APPROVED')
                ->after('supervisor_user_id');
            $table->string('qr_spv_url', 500)
                ->nullable()
                ->after('status_approval_spv');
        });
    }

    public function down(): void
    {
        Schema::table('serah_terima_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_gudang_user_id');
            $table->dropColumn([
                'status_approval_admin_gudang',
                'qr_admin_gudang_url',
                'status_approval_spv',
                'qr_spv_url',
            ]);
        });
    }
};
