<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role baru untuk modul PGA - dipakai akun PGA01, PGA02, PGA03.
     */
    public function up(): void
    {
        $exists = DB::table('roles')->where('name', 'pga')->exists();

        if (! $exists) {
            DB::table('roles')->insert([
                'name'       => 'pga',
                'label'      => 'PGA (Dashboard, APAR, Buku Tamu)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'pga')->delete();
    }
};
