<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class TallyByProductUserSeeder extends Seeder
{
    /**
     * Membuat user TBP01-TBP04 (dari dictionary USERS di Apps Script
     * lama, modul Produksi Fresh) dan meng-assign role tally_by_product.
     *
     * Aman dijalankan berkali-kali (idempotent) - pakai updateOrCreate
     * supaya tidak membuat duplikat kalau seeder ini dijalankan ulang,
     * dan attach role cuma kalau belum ter-attach sebelumnya.
     */
    public function run(): void
    {
        $role = Role::where('name', 'tally_by_product')->first();

        if (! $role) {
            $this->command->error('Role tally_by_product belum ada. Jalankan migration add_tally_by_product_role dulu.');
            return;
        }

        $users = [
            ['employee_code' => 'TBP01', 'name' => 'Rohmad Akbar Maulana', 'password' => '1234'],
            ['employee_code' => 'TBP02', 'name' => 'Khusnul Khulukin', 'password' => '5678'],
            ['employee_code' => 'TBP03', 'name' => 'M. Romi', 'password' => '9101'],
            ['employee_code' => 'TBP04', 'name' => 'Vicky Fitriandi', 'password' => '1213'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['employee_code' => $data['employee_code']],
                ['name' => $data['name'], 'password' => $data['password']]
            );

            if (! $user->roles->contains($role->id)) {
                $user->roles()->attach($role->id);
            }
        }

        $this->command->info('4 user Tally By Product berhasil dibuat/diperbarui dan role di-assign.');
    }
}
