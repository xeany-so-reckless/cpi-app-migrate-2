<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role baru untuk modul PPIC - khusus MANAGER (akses Dashboard
     * PPIC saja, tanpa Planning vs Aktual & Input PO). Dipakai akun
     * MGR01.
     */
    public function up(): void
    {
        $exists = DB::table('roles')->where('name', 'manager')->exists();

        if (! $exists) {
            DB::table('roles')->insert([
                'name'       => 'manager',
                'label'      => 'manager',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'manager')->delete();
    }
};
