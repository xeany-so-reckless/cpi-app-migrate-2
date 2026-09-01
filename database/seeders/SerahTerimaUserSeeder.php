<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SerahTerimaUserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role tersedia dulu
        $roles = [
            ['name' => 'tally_produksi', 'label' => 'Tally Produksi'],
            ['name' => 'tally_gudang',   'label' => 'Tally Gudang'],
            ['name' => 'supervisor',     'label' => 'Supervisor'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['name' => $r['name']], $r);
        }

        // Baru proses user seperti biasa
        $users = [
            ['employee_code' => 'TPR01', 'password' => 'prod123', 'name' => 'Siti Nur Habibah', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR02', 'password' => 'prod456', 'name' => 'Erna Dianti', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR03', 'password' => 'prod789', 'name' => 'Nilam Andhika Kurniasari', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR04', 'password' => 'prod101', 'name' => 'Salsabilla Calista P.', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR05', 'password' => 'prod112', 'name' => 'Natasya Amelia Putri', 'role' => 'tally_produksi'],
            ['employee_code' => 'TWH01', 'password' => 'wh123', 'name' => 'Vemas', 'role' => 'tally_gudang'],
            ['employee_code' => 'TWH02', 'password' => 'wh456', 'name' => 'Endi', 'role' => 'tally_gudang'],
            ['employee_code' => 'TWH03', 'password' => 'wh789', 'name' => 'Wahyu H.', 'role' => 'tally_gudang'],
            ['employee_code' => 'SPV01', 'password' => 'super123', 'name' => 'Bobby Andi', 'role' => 'supervisor'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['employee_code' => $u['employee_code']],
                [
                    'name'     => $u['name'],
                    'password' => $u['password'],
                ]
            );

            $role = Role::where('name', $u['role'])->first();

            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }
    }
}