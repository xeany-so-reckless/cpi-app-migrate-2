<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LbReportUserSeeder extends Seeder
{
    /**
     * Menggantikan userDB hardcode di HalamanInput.html & HalamanHanging.html.
     *
     * APP01-05 SUDAH ADA di tabel users (role 'foreman' dari Tally Pro) -
     * di sini cuma DITAMBAH role baru, bukan dibuat user baru, supaya
     * mereka tetap punya akses ke Tally Pro sekaligus modul ini.
     */
    public function run(): void
    {
        $roleAwal = Role::firstOrCreate(
            ['name' => 'lb_penerimaan_awal'],
            ['label' => 'Penerimaan Awal LB (dulu: APP)']
        );
        $roleAkhir = Role::firstOrCreate(
            ['name' => 'lb_penerimaan_akhir'],
            ['label' => 'Penerimaan Akhir LB (dulu: LGS)']
        );
        $roleHanging = Role::firstOrCreate(
            ['name' => 'lb_hanging'],
            ['label' => 'Hanging/Counter LB (dulu: TLB)']
        );

        // APP01-05: tambah role, JANGAN buat user baru (sudah ada, role foreman tetap dipertahankan)
        foreach (['APP01', 'APP02', 'APP03', 'APP04', 'APP05'] as $code) {
            $user = User::where('employee_code', $code)->first();
            if ($user) {
                $user->roles()->syncWithoutDetaching([$roleAwal->id]);
            }
        }

        // LGS01-03: user baru
        $lgsUsers = [
            ['employee_code' => 'LGS01', 'password' => '1234', 'name' => 'Erien Endriani'],
            ['employee_code' => 'LGS02', 'password' => '5678', 'name' => 'Daffa Ditya'],
            ['employee_code' => 'LGS03', 'password' => '9101', 'name' => 'Hubab Al Fahmi'],
        ];
        foreach ($lgsUsers as $u) {
            $user = User::updateOrCreate(
                ['employee_code' => $u['employee_code']],
                ['name' => $u['name'], 'password' => Hash::make($u['password'])]
            );
            $user->roles()->syncWithoutDetaching([$roleAkhir->id]);
        }

        // TLB01-03: user baru
        $tlbUsers = [
            ['employee_code' => 'TLB01', 'password' => 'abcd', 'name' => 'Tata Diah A.'],
            ['employee_code' => 'TLB02', 'password' => 'efgh', 'name' => 'M. Chairizal'],
            ['employee_code' => 'TLB03', 'password' => 'ijkl', 'name' => 'Ayu Fitra'],
        ];
        foreach ($tlbUsers as $u) {
            $user = User::updateOrCreate(
                ['employee_code' => $u['employee_code']],
                ['name' => $u['name'], 'password' => Hash::make($u['password'])]
            );
            $user->roles()->syncWithoutDetaching([$roleHanging->id]);
        }

        // SPV: reuse SPV01 (role 'supervisor') yang sudah ada dari Serah Terima.
        // Tidak perlu user baru untuk SPVPROD/SPVLOGS versi lama.
    }
}
