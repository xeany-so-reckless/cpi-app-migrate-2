<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rekap per Cell dalam 1 DO (1 DO boleh mencakup beberapa Cell).
     * total_bag/total_kg di sini murni untuk tampilan histori - sumber
     * kebenaran pengurangan stock tetap di baris cell_stock_adjustments
     * yang terhubung lewat cell_stock_adjustment_id.
     */
    public function up(): void
    {
        Schema::create('outbound_shipment_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_shipment_id')->constrained('outbound_shipments')->cascadeOnDelete();
            $table->foreignId('cell_id')->constrained('cells');
            $table->unsignedInteger('total_bag');
            $table->decimal('total_kg', 10, 2);

            // Relasi ke baris penyesuaian stock (sumber='outbound') yang
            // dibuat otomatis saat shipment ini disimpan. Nullable +
            // nullOnDelete supaya kalau adjustment-nya suatu saat dihapus,
            // histori shipment ini tidak ikut hilang.
            $table->foreignId('cell_stock_adjustment_id')
                ->nullable()
                ->unique()
                ->constrained('cell_stock_adjustments')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_shipment_cells');
    }
};
