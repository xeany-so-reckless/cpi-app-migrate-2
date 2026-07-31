<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminGudangUserSeeder extends Seeder
{
    /**
     * Membuat 2 akun untuk approval kedua sisi gudang (Serah Terima):
     * - ADMG01 -> role 'admin_gudang'
     * - SPVG   -> role 'supervisor_gudang'
     *
     * Kedua role punya jobdesk & akses yang IDENTIK (QC ulang hasil TWH),
     * sengaja dipisah rolenya atas permintaan meski tugasnya sama -
     * jadi siapa saja dari keduanya yang login duluan boleh approve.
     */
    public function run(): void
    {
        $accounts = [
            [
                'employee_code' => 'SPVG',
                'name'          => 'M. Nico Puji F',
                'password'      => 'Spvwhjbg',
                'role_name'     => 'supervisor_gudang',
            ],
            [
                'employee_code' => 'ADMG01',
                'name'          => 'Titis Akhadiyah',
                'password'      => 'Admwhjbg',
                'role_name'     => 'admin_gudang',
            ],
        ];

        foreach ($accounts as $account) {
            $roleId = DB::table('roles')->where('name', $account['role_name'])->value('id');

            if (! $roleId) {
                $this->command?->warn("Role '{$account['role_name']}' belum ada. Jalankan migration role terlebih dahulu.");
                continue;
            }

            $userId = DB::table('users')->where('employee_code', $account['employee_code'])->value('id');

            if (! $userId) {
                $userId = DB::table('users')->insertGetId([
                    'employee_code' => $account['employee_code'],
                    'name'          => $account['name'],
                    'password'      => Hash::make($account['password']),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } else {
                // Akun sudah ada -> pastikan nama & password sesuai, jangan duplikat insert.
                DB::table('users')->where('id', $userId)->update([
                    'name'       => $account['name'],
                    'password'   => Hash::make($account['password']),
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
}
