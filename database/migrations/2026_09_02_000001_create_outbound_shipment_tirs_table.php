<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar Tir per DO - khusus truk besar (misal tronton bisa sampai
     * 20 tir/kompartemen). Opsional sepenuhnya - kalau customer pakai
     * mobil kecil, DO tersebut tidak akan punya baris di tabel ini sama
     * sekali. Tidak terkait ke Cell tertentu, murni catatan tambahan
     * untuk 1 DO secara keseluruhan.
     *
     * tir_ke adalah LABEL URUTAN OTOMATIS (Tir 1, Tir 2, dst) - bukan
     * nomor segel/identitas manual. User cuma mengisi jumlah_bag untuk
     * tiap tir. Totalnya murni untuk tampilan informasi, TIDAK divalidasi
     * harus sama dengan total bag dari Cell yang dipilih.
     */
    public function up(): void
    {
        Schema::create('outbound_shipment_tirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_shipment_id')->constrained('outbound_shipments')->cascadeOnDelete();
            $table->unsignedTinyInteger('tir_ke'); // label urutan otomatis: 1, 2, 3, ... maksimal 20
            $table->unsignedInteger('jumlah_bag'); // jumlah bag yang dimuat di tir ini
            $table->timestamps();

            $table->unique(['outbound_shipment_id', 'tir_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_shipment_tirs');
    }
};
