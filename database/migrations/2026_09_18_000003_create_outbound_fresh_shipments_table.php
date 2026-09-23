<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BARU - Header 1 dokumen Outbound Fresh (analog outbound_shipments untuk
 * Frozen, tapi TANPA konsep Cell/Tir karena produk fresh tidak masuk
 * cell stock - cuma "lewat" saja di warehouse).
 *
 * Perbedaan utama dari outbound_shipments (Frozen):
 * - no_do -> no_po (dipilih dari data yang SUDAH ADA di produksi_fresh,
 *   bukan input bebas seperti No DO Frozen).
 * - Tidak ada relasi ke Cell sama sekali.
 * - Tidak ada tabel Tir terpisah (fitur Tir memang khusus truk besar dari
 *   cold storage Frozen, tidak dipakai untuk Fresh).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_fresh_shipments', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');

            // No PO - BUKAN foreign key ke purchase_orders. Sengaja
            // disamakan gayanya dengan produksi_fresh.no_po (string biasa,
            // disimpan uppercase), karena 1 No PO yang sama dipakai untuk
            // mencari baris produksi_fresh terkait, bukan untuk relasi
            // langsung ke tabel purchase_orders.
            $table->string('no_po', 100);

            $table->string('nama_customer', 150);
            $table->time('jam_muat');
            $table->string('no_pol', 20);
            $table->string('nama_driver', 100);

            $table->foreignId('checker_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Disamakan pola dengan outbound_shipments.status - saat ini
            // cuma diisi 'SELESAI', tapi kolom dibiarkan ada untuk
            // konsistensi & kemungkinan status lain di masa depan.
            $table->string('status', 20)->default('SELESAI');

            $table->timestamps();

            $table->index('no_po');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_fresh_shipments');
    }
};
