<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menambahkan 2 role baru untuk approval kedua di alur Serah Terima
     * (sejajar dengan 'supervisor' produksi, khusus sisi gudang):
     * - admin_gudang       : dipakai akun ADMG01
     * - supervisor_gudang  : dipakai akun SPVG
     *
     * Keduanya punya jobdesk & akses yang IDENTIK (QC kedua hasil TWH,
     * generate QR sendiri) - sengaja dipisah jadi 2 role atas permintaan,
     * bukan karena beda wewenang.
     */
    public function up(): void
    {
        $roles = [
            ['name' => 'admin_gudang', 'label' => 'Admin Gudang (Serah Terima)'],
            ['name' => 'supervisor_gudang', 'label' => 'Supervisor Gudang (Serah Terima)'],
        ];

        foreach ($roles as $role) {
            $exists = DB::table('roles')->where('name', $role['name'])->exists();

            if (! $exists) {
                DB::table('roles')->insert([
                    'name'       => $role['name'],
                    'label'      => $role['label'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('name', ['admin_gudang', 'supervisor_gudang'])->delete();
    }
};
