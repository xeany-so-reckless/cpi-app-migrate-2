<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LbReportSpvlogsSeeder extends Seeder
{
    /**
     * Menambah user SPVLOGS (Angga Manggodo) dengan akses setara LGS,
     * yaitu role 'lb_penerimaan_akhir' (tab "Setelah Bongkar").
     *
     * Aman dijalankan berulang (updateOrCreate + syncWithoutDetaching).
     * Catatan: menjalankan ulang akan mereset password SPVLOGS ke nilai di bawah.
     *
     * Jalankan:
     *   php artisan db:seed --class=LbReportSpvlogsSeeder
     */
    public function run(): void
    {
        $roleAkhir = Role::firstOrCreate(
            ['name' => 'lb_penerimaan_akhir'],
            ['label' => 'Penerimaan Akhir LB (dulu: LGS)']
        );

        $user = User::updateOrCreate(
            ['employee_code' => 'SPVLOGS'],
            [
                'name'     => 'Angga Manggodo',
                'password' => Hash::make('spv456'),
            ]
        );

        $user->roles()->syncWithoutDetaching([$roleAkhir->id]);
    }
}
