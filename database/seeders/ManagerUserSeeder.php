<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManagerUserSeeder extends Seeder
{
    /**
     * Akun untuk modul PPIC (role MANAGER) - MGR01 (DENDI SUSILO).
     * Hanya bisa akses Dashboard PPIC.
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('name', 'manager')->value('id');

        if (! $roleId) {
            $this->command?->warn("Role 'manager' belum ada. Jalankan migration role terlebih dahulu.");
            return;
        }

        $employeeCode = 'MGR01';
        $userId = DB::table('users')->where('employee_code', $employeeCode)->value('id');

        if (! $userId) {
            $userId = DB::table('users')->insertGetId([
                'employee_code' => $employeeCode,
                'name'          => 'DENDI SUSILO',
                'password'      => Hash::make('dendi123#'),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } else {
            DB::table('users')->where('id', $userId)->update([
                'name'       => 'DENDI SUSILO',
                'password'   => Hash::make('dendi123#'),
                'updated_at' => now(),
            ]);
        }

        $alreadyHasRole = DB::table('role_user')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();

        if (! $alreadyHasRole) {
            DB::table('role_user')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
