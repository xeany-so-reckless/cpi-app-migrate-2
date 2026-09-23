<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BARU - Tambah kolom keterangan (per item, bukan per dokumen) di
 * outbound_fresh_shipment_items. Diisi manual oleh Checker saat
 * submit Outbound Fresh, misal untuk catat selisih kg antara qty
 * hasil Produksi Fresh vs hasil timbang ulang di Warehouse.
 *
 * Nullable & string 255 - sekadar catatan singkat, bukan paragraf
 * panjang. TIDAK mempengaruhi qty/stok yang tersimpan sama sekali,
 * jadi tidak perlu validasi angka apapun di server - murni teks bebas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outbound_fresh_shipment_items', function (Blueprint $table) {
            $table->string('keterangan', 255)->nullable()->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('outbound_fresh_shipment_items', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
