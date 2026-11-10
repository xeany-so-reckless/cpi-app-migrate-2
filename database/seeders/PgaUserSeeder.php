<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PgaUserSeeder extends Seeder
{
    /**
     * Akun modul PGA: PGA01, PGA02, PGA03.
     * GANTI nama & password di bawah sebelum dijalankan di server.
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('name', 'pga')->value('id');

        if (! $roleId) {
            $this->command?->warn("Role 'pga' belum ada. Jalankan migration terlebih dahulu.");
            return;
        }

        $users = [
            ['employee_code' => 'PGA01', 'name' => 'PGA 01', 'password' => 'GANTI_PASSWORD_01'],
            ['employee_code' => 'PGA02', 'name' => 'PGA 02', 'password' => 'GANTI_PASSWORD_02'],
            ['employee_code' => 'PGA03', 'name' => 'PGA 03', 'password' => 'GANTI_PASSWORD_03'],
        ];

        foreach ($users as $u) {
            $userId = DB::table('users')->where('employee_code', $u['employee_code'])->value('id');

            if (! $userId) {
                $userId = DB::table('users')->insertGetId([
                    'employee_code' => $u['employee_code'],
                    'name'          => $u['name'],
                    'password'      => Hash::make($u['password']),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } else {
                DB::table('users')->where('id', $userId)->update([
                    'name'       => $u['name'],
                    'password'   => Hash::make($u['password']),
                    'updated_at' => now(),
                ]);
            }

            DB::table('role_user')->insertOrIgnore([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
