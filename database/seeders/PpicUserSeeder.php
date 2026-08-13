<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PpicUserSeeder extends Seeder
{
    /**
     * Akun untuk modul PPIC - PPIC01 (ACHMAD CHOIRON MUKMININ).
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('name', 'ppic')->value('id');

        if (! $roleId) {
            $this->command?->warn("Role 'ppic' belum ada. Jalankan migration role terlebih dahulu.");
            return;
        }

        $employeeCode = 'PPIC01';
        $userId = DB::table('users')->where('employee_code', $employeeCode)->value('id');

        if (! $userId) {
            $userId = DB::table('users')->insertGetId([
                'employee_code' => $employeeCode,
                'name'          => 'ACHMAD CHOIRON MUKMININ',
                'password'      => Hash::make('ppic123'),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } else {
            DB::table('users')->where('id', $userId)->update([
                'name'       => 'ACHMAD CHOIRON MUKMININ',
                'password'   => Hash::make('ppic123'),
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
