<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasi data: setiap user yang sebelumnya cuma punya 1 role
     * (kolom enum `role`) dipindahkan jadi 1 baris di tabel pivot
     * `role_user`. Setelah itu kolom `role` lama dihapus total -
     * mulai sekarang, role SELALU dicek lewat relasi banyak-ke-banyak.
     */
    public function up(): void
    {
        // 1. Buat master data roles yang sudah pernah dipakai sejauh ini
        $roles = [
            ['name' => 'admin', 'label' => 'Admin'],
            ['name' => 'tally', 'label' => 'Tally (Tally Pro)'],
            ['name' => 'foreman', 'label' => 'Foreman / Approver (Tally Pro)'],
            ['name' => 'tally_produksi', 'label' => 'Tally Produksi (Serah Terima)'],
            ['name' => 'tally_gudang', 'label' => 'Tally Gudang (Serah Terima)'],
            ['name' => 'supervisor', 'label' => 'Supervisor'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore([
                'name'       => $role['name'],
                'label'      => $role['label'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Pindahkan role tunggal tiap user yang sudah ada ke pivot
        $users = DB::table('users')->select('id', 'role')->get();
        foreach ($users as $user) {
            if (empty($user->role)) {
                continue;
            }

            $roleId = DB::table('roles')->where('name', $user->role)->value('id');
            if ($roleId) {
                DB::table('role_user')->insertOrIgnore([
                    'user_id'    => $user->id,
                    'role_id'    => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Hapus kolom role lama - mulai sekarang wajib pakai relasi roles()
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['tally', 'foreman', 'admin', 'tally_produksi', 'tally_gudang', 'supervisor'])
                ->default('tally')
                ->after('password');
        });

        $roleUsers = DB::table('role_user')->get();
        foreach ($roleUsers as $ru) {
            $roleName = DB::table('roles')->where('id', $ru->role_id)->value('name');
            if ($roleName) {
                DB::table('users')->where('id', $ru->user_id)->update(['role' => $roleName]);
            }
        }
    }
};
