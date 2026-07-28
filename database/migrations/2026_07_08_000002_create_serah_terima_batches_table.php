<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menggantikan sheet "Serah_Terima" (32 kolom) di Apps Script lama.
     *
     * Struktur tetap flat (10 kolom kg + 10 kolom status per bag) sesuai
     * keputusan bisnis, bukan dinormalisasi ke tabel anak terpisah.
     *
     * `namaItem` di kode lama sekarang diganti relasi ke tabel `produk`
     * yang sudah ada (produk_id), karena kode item (1-70) sama persis
     * dengan yang dipakai di modul Tally Pro.
     */
    public function up(): void
    {
        Schema::create('serah_terima_batches', function (Blueprint $table) {
            $table->id();

            // Kode traceability unik, hasil generate otomatis
            // (menggantikan generateKodeProduksiOtomatis() di code.gs).
            $table->string('kode_produksi', 30)->unique();

            $table->date('tanggal_produksi');
            $table->string('no_trolly', 50);
            $table->foreignId('produk_id')->constrained('products');
            $table->unsignedTinyInteger('jumlah_bag');

            // 10 slot berat per bag (kolom F-O di sheet asli)
            $table->decimal('kg_bag_1', 8, 1)->default(0);
            $table->decimal('kg_bag_2', 8, 1)->default(0);
            $table->decimal('kg_bag_3', 8, 1)->default(0);
            $table->decimal('kg_bag_4', 8, 1)->default(0);
            $table->decimal('kg_bag_5', 8, 1)->default(0);
            $table->decimal('kg_bag_6', 8, 1)->default(0);
            $table->decimal('kg_bag_7', 8, 1)->default(0);
            $table->decimal('kg_bag_8', 8, 1)->default(0);
            $table->decimal('kg_bag_9', 8, 1)->default(0);
            $table->decimal('kg_bag_10', 8, 1)->default(0);

            // 10 slot status per bag (kolom P-Y di sheet asli)
            // Nilai: PENDING, OK VERIFIED, TOLAK (REJECT), atau "-" kalau slot tidak dipakai.
            $table->string('status_bag_1', 20)->default('-');
            $table->string('status_bag_2', 20)->default('-');
            $table->string('status_bag_3', 20)->default('-');
            $table->string('status_bag_4', 20)->default('-');
            $table->string('status_bag_5', 20)->default('-');
            $table->string('status_bag_6', 20)->default('-');
            $table->string('status_bag_7', 20)->default('-');
            $table->string('status_bag_8', 20)->default('-');
            $table->string('status_bag_9', 20)->default('-');
            $table->string('status_bag_10', 20)->default('-');

            // Diisi Tally Gudang saat finalize
            $table->string('kode_cell', 50)->nullable();

            // Nilai: BELUM APPROVED, VERIFIED & APPROVED
            $table->string('status_approval', 30)->default('BELUM APPROVED');

            // Siapa yang mengerjakan tiap tahap
            $table->foreignId('tally_produksi_user_id')->constrained('users');
            $table->foreignId('tally_gudang_user_id')->nullable()->constrained('users');
            $table->foreignId('supervisor_user_id')->nullable()->constrained('users');

            // QR Tally Produksi & barcode final Supervisor disimpan supaya
            // histori tidak berubah walau data lain di-update belakangan.
            // QR Tally Gudang tidak disimpan - dibuat ulang saat tampil,
            // sama seperti perilaku kode asli.
            $table->string('qr_prod_url', 500)->nullable();
            $table->string('barcode_url', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serah_terima_batches');
    }
};
