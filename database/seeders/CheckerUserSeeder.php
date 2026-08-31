<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CheckerUserSeeder extends Seeder
{
    /**
     * Seeder user role Checker untuk modul Outbound (Warehouse).
     * Password disimpan plain di sini cuma untuk referensi awal -
     * kolom `password` di tabel users otomatis di-hash lewat cast
     * 'hashed' di Model User, jadi aman langsung pakai Hash::make()
     * di bawah (Eloquent cast akan hash ulang kalau perlu, tapi kita
     * hash manual di sini supaya konsisten dengan updateOrCreate).
     */
    public function run(): void
    {
        // 1. Pastikan role 'checker' ada di master roles.
        $role = Role::firstOrCreate(
            ['name' => 'checker'],
            ['label' => 'Checker (Warehouse Outbound)']
        );

        $checkers = [
            [
                'employee_code' => 'CWH01',
                'name'          => 'ALDO VELLYAN P.',
                'password'      => '1234',
            ],
            [
                'employee_code' => 'CWH02',
                'name'          => 'RESNU WAHYU A.',
                'password'      => '5678',
            ],
            [
                'employee_code' => 'CWH03',
                'name'          => 'BIMA RACHMAN HADI',
                'password'      => 'abcd',
            ],
            [
                'employee_code' => 'CWH04',
                'name'          => 'AHMAD SYARIFUL ANAM',
                'password'      => 'Efgh',
            ],
        ];

        foreach ($checkers as $data) {
            $user = User::updateOrCreate(
                ['employee_code' => $data['employee_code']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make($data['password']),
                ]
            );

            // Attach role 'checker' kalau belum ada - syncWithoutDetaching
            // supaya tidak menghapus role lain yang mungkin sudah dipegang
            // user ini (misal kalau employee_code ini kebetulan sudah
            // punya role lain di modul lain).
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
