<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel baru untuk modul Produksi Fresh (menggantikan sheet
     * Google Apps Script lama). Satu baris = satu item produk yang
     * diinput (bukan satu laporan harian gabungan seperti
     * produksi_harian) - polanya "tambah ke draft, submit banyak
     * sekaligus".
     *
     * no_po: string biasa (bukan foreign key ke purchase_orders),
     * divalidasi exists:purchase_orders,nomor_po di level Controller -
     * pola sama seperti lb_penerimaan.no_po. PO yang sudah TECO TETAP
     * boleh dipilih di sini (keputusan bisnis: TECO cuma mempengaruhi
     * LB Report).
     *
     * tipe_input: 'main' atau 'byproduct' - dipilih user saat login,
     * dicatat di tiap baris untuk keperluan audit/rekap nanti.
     *
     * kode_produksi: hasil generate otomatis di server (lihat
     * ProduksiFresh::generateKodeProduksi()), TIDAK dipercaya dari input
     * client meskipun form menampilkannya untuk preview.
     */
    public function up(): void
    {
        Schema::create('produksi_fresh', function (Blueprint $table) {
            $table->id();
            $table->string('no_po', 100);
            $table->foreignId('user_id')->constrained('users');
            $table->string('tipe_input', 20);
            $table->foreignId('produk_id')->constrained('products');
            $table->string('kode_produksi', 50);
            $table->decimal('qty', 12, 2);
            $table->timestamps();

            $table->index('no_po');
            $table->index('tipe_input');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_fresh');
    }
};
