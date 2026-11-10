<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jejak edit oleh PGA: siapa & kapan terakhir mengubah hasil inspeksi.
     */
    public function up(): void
    {
        Schema::table('apar_inspections', function (Blueprint $table) {
            $table->string('diedit_oleh', 150)->nullable()->after('ip_address');
            $table->timestamp('diedit_pada')->nullable()->after('diedit_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('apar_inspections', function (Blueprint $table) {
            $table->dropColumn(['diedit_oleh', 'diedit_pada']);
        });
    }
};
