<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Isi no_po yang kosong di lb_hanging dari data lb_penerimaan
        DB::statement("
            UPDATE lb_hanging h
            JOIN lb_penerimaan p
              ON p.tanggal = h.tanggal_penerimaan AND p.no_rit = h.no_rit
            SET h.no_po = p.no_po
            WHERE h.no_po IS NULL OR h.no_po = ''
        ");

        // 2. Ganti unique index + tambah index biasa untuk no_po
        Schema::table('lb_hanging', function (Blueprint $table) {
            $table->dropUnique('lb_hanging_tanggal_penerimaan_no_rit_unique');
            $table->unique(
                ['tanggal_penerimaan', 'no_po', 'no_rit'],
                'lb_hanging_tanggal_po_rit_unique'
            );
            $table->index('no_po', 'lb_hanging_no_po_index');
        });
    }

    public function down(): void
    {
        Schema::table('lb_hanging', function (Blueprint $table) {
            $table->dropIndex('lb_hanging_no_po_index');
            $table->dropUnique('lb_hanging_tanggal_po_rit_unique');
            $table->unique(
                ['tanggal_penerimaan', 'no_rit'],
                'lb_hanging_tanggal_penerimaan_no_rit_unique'
            );
        });
    }
};
