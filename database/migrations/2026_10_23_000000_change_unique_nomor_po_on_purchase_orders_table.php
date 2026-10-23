<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsep baru: satu nomor PO boleh punya maksimal 2 baris (khusus
     * FEH2 dan FEHM, beda tanggal). Unique tunggal di nomor_po diganti
     * unique komposit (nomor_po, tanggal) sebagai pagar di level DB
     * supaya nomor PO yang sama tidak bisa dobel di tanggal yang sama.
     *
     * Aturan lain (jenis sama, produk sama untuk FEHM, maksimal 2 baris,
     * FEH0/FEH1/FEHT tetap unik) dijaga di PurchaseOrderController.
     *
     * Catatan: baris soft-deleted tetap ikut dicek oleh unique ini
     * karena barisnya masih ada secara fisik di tabel.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique('purchase_orders_nomor_po_unique');
            $table->unique(['nomor_po', 'tanggal'], 'purchase_orders_nomor_po_tanggal_unique');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique('purchase_orders_nomor_po_tanggal_unique');
            $table->unique('nomor_po', 'purchase_orders_nomor_po_unique');
        });
    }
};
