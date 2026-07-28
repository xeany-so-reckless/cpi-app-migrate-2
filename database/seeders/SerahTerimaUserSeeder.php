<?php
 
namespace Database\Seeders;
 
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
 
class SerahTerimaUserSeeder extends Seeder
{
    /**
     * Menggantikan konstanta USERS di code.gs (modul Serah Terima).
     * Pakai updateOrCreate supaya aman dijalankan berkali-kali
     * (tidak akan duplikat / error kalau di-run ulang).
     */
    public function run(): void
    {
        $users = [
            ['employee_code' => 'TPR01', 'password' => 'prod123', 'name' => 'Siti Nur Habibah', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR02', 'password' => 'prod456', 'name' => 'Erna Dianti', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR03', 'password' => 'prod789', 'name' => 'Nilam Andhika Kurniasari', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR04', 'password' => 'prod101', 'name' => 'Vika Melinda', 'role' => 'tally_produksi'],
            ['employee_code' => 'TPR05', 'password' => 'prod112', 'name' => 'Natasya Amelia Putri', 'role' => 'tally_produksi'],
            ['employee_code' => 'TWH01', 'password' => 'wh123', 'name' => 'Vemas', 'role' => 'tally_gudang'],
            ['employee_code' => 'TWH02', 'password' => 'wh456', 'name' => 'Endi', 'role' => 'tally_gudang'],
            ['employee_code' => 'TWH03', 'password' => 'wh789', 'name' => 'Wahyu H.', 'role' => 'tally_gudang'],
            ['employee_code' => 'SPV01', 'password' => 'super123', 'name' => 'Bobby Andi', 'role' => 'supervisor'],
        ];
 
        foreach ($users as $u) {
            User::updateOrCreate(
                ['employee_code' => $u['employee_code']],
                [
                    'name'     => $u['name'],
                    'password' => Hash::make($u['password']),
                    'role'     => $u['role'],
                ]
            );
        }
    }
}