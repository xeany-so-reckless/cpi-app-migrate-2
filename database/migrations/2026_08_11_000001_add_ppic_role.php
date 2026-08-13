<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role baru untuk modul PPIC (Production Planning & Inventory
     * Control) - dipakai akun PPIC01.
     */
    public function up(): void
    {
        $exists = DB::table('roles')->where('name', 'ppic')->exists();

        if (! $exists) {
            DB::table('roles')->insert([
                'name'       => 'ppic',
                'label'      => 'PPIC (Production Planning & Inventory Control)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'ppic')->delete();
    }
};
