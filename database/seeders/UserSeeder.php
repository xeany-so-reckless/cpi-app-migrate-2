<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Data ini diambil langsung dari getUserDatabase() di code.gs
     * (identik dengan usersRekap di Rekap Hasil Produksi v.4).
     *
     * PENTING: password asli di kode lama plaintext (mis. "1234", "abcd").
     * Di sini di-hash pakai bcrypt sebelum disimpan.
     * Sangat disarankan user mengganti password ini setelah go-live.
     */
    public function run(): void
    {
        $users = [
            // Role: tally
            ['employee_code' => 'TLY01', 'name' => 'Eka Rahmadianti', 'password' => '1234', 'role' => 'tally'],
            ['employee_code' => 'TLY02', 'name' => 'Wara Avrilia', 'password' => '5678', 'role' => 'tally'],
            ['employee_code' => 'TLY03', 'name' => 'Nilam Andhika Kurniasari', 'password' => '9101', 'role' => 'tally'],
            ['employee_code' => 'TLY04', 'name' => 'Eisa Faikhatul', 'password' => '1213', 'role' => 'tally'],

            // Role: foreman (prefix "APP" di kode lama, bertindak sebagai approver/foreman)
            ['employee_code' => 'APP01', 'name' => 'Andi Setiawan', 'password' => 'abcd', 'role' => 'foreman'],
            ['employee_code' => 'APP02', 'name' => 'M. Lutfi Alfiansyah', 'password' => 'efgh', 'role' => 'foreman'],
            ['employee_code' => 'APP03', 'name' => 'Rina Pratiwi', 'password' => 'ijkl', 'role' => 'foreman'],
            ['employee_code' => 'APP04', 'name' => 'Yuni Sri Lestari', 'password' => 'mnop', 'role' => 'foreman'],
            ['employee_code' => 'APP05', 'name' => 'M. Faisal Hanafi', 'password' => 'qrst', 'role' => 'foreman'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['employee_code' => $user['employee_code']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
