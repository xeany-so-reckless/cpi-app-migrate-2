<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BARU - Detail item dalam 1 Outbound Fresh. Setiap baris di sini
 * merepresentasikan TEPAT 1 baris hasil input Produksi Fresh (tabel
 * produksi_fresh) yang "dikeluarkan" lewat dokumen Outbound ini.
 *
 * produksi_fresh_id dibuat UNIQUE - ini kunci mekanisme "sekali pakai":
 * 1 baris produksi_fresh cuma boleh terpakai di SATU outbound saja,
 * tidak boleh dobel dan tidak ada konsep outbound sebagian dari 1 baris
 * (unit atomik, sama seperti 1 bag di sistem Frozen tidak bisa dipecah).
 * "Item yang masih tersedia untuk outbound" = baris produksi_fresh yang
 * tipe_input = 'main' DAN belum punya baris di tabel ini
 * (whereDoesntHave('outboundItem')).
 *
 * kode_produksi, kode_produk, nama_produk, dan qty DISALIN (snapshot) ke
 * sini di server saat store(), TIDAK dipercaya dari input client - sama
 * prinsipnya dengan kg/kode_produksi di outbound_shipment_cell_bags pada
 * modul Frozen. Ini menjaga histori tetap akurat walau data produk di
 * master Product berubah di kemudian hari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_fresh_shipment_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('outbound_fresh_shipment_id')
                ->constrained('outbound_fresh_shipments')
                ->cascadeOnDelete();

            // Unique - 1 baris produksi_fresh cuma boleh dipakai 1x untuk
            // outbound (mencegah barang yang sama ke-outbound dobel).
            $table->foreignId('produksi_fresh_id')
                ->unique()
                ->constrained('produksi_fresh')
                ->cascadeOnDelete();

            // Snapshot - diisi ulang dari server saat simpan, bukan dari
            // client, supaya histori akurat & tidak bisa dimanipulasi.
            $table->string('kode_produk', 50)->nullable();
            $table->string('nama_produk', 150)->nullable();
            $table->string('kode_produksi', 50);
            $table->decimal('qty', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_fresh_shipment_items');
    }
};
