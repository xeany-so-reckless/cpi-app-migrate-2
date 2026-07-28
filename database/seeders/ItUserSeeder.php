<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ItUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'it'],
            ['label' => 'IT - Riwayat Log Aktivitas']
        );

        $user = User::updateOrCreate(
            ['employee_code' => 'UMAM'],
            [
                'name'     => 'Umam',
                'password' => Hash::make('kedungsari123'),
            ]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
