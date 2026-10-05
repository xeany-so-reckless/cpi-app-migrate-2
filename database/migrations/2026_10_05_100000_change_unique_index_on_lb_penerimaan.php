<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lb_penerimaan', function (Blueprint $table) {
            $table->dropUnique('lb_penerimaan_tanggal_no_rit_unique');
            $table->unique(
                ['tanggal', 'no_po', 'no_rit'],
                'lb_penerimaan_tanggal_po_rit_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('lb_penerimaan', function (Blueprint $table) {
            $table->dropUnique('lb_penerimaan_tanggal_po_rit_unique');
            $table->unique(
                ['tanggal', 'no_rit'],
                'lb_penerimaan_tanggal_no_rit_unique'
            );
        });
    }
};
