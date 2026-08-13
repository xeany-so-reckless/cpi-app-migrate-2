<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data PO (Purchase Order) yang diinput PPIC lewat menu Input PO.
     * nomor_po dibuat unique supaya tidak ada duplikat nomor PO yang
     * sama tercatat 2x.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_po', 100);
            $table->string('nomor_po', 100)->unique();
            $table->date('tanggal');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
