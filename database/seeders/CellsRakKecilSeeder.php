<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CellsRakKecilSeeder extends Seeder
{
    /**
     * Master Cell tambahan untuk Rak Kecil (RK), diambil dari sheet
     * "Kapasitas" file Denah CS RAK KECIL (25-09_Denah_CS_RAK_KECIL.xlsx).
     * Kode cell RK memakai suffix " (RK)" (misal "4A07 (RK)") supaya
     * tidak bentrok dengan kode cell utama yang sudah ada (misal "4A07").
     * Total 60 cell rak kecil.
     */
    public function run(): void
    {
        $cells = [
            ['kode_cell' => '4A07 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '4A12 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '4A43 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '4A45 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '4A48 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '4B07 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '4B12 (RK)', 'kapasitas_max' => 32],
            ['kode_cell' => '4B43 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '4B45 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '4B48 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '4C07 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '4C12 (RK)', 'kapasitas_max' => 34],
            ['kode_cell' => '4C43 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '4C45 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '4C48 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '3A01 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3A03 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3A06 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3A37 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '3A47 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '3B01 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3B03 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3B06 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3B37 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3B47 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3C01 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3C03 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '3C06 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '3C37 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '3C47 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '2A07 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2A12 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2A43 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2A45 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2A48 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2B07 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2B12 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2B43 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2B45 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2B48 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '2C07 (RK)', 'kapasitas_max' => 32],
            ['kode_cell' => '2C12 (RK)', 'kapasitas_max' => 42],
            ['kode_cell' => '2C43 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '2C45 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '2C48 (RK)', 'kapasitas_max' => 30],
            ['kode_cell' => '1A07 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1A12 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1A43 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1A45 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1A48 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1B07 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '1B12 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '1B43 (RK)', 'kapasitas_max' => 24],
            ['kode_cell' => '1B45 (RK)', 'kapasitas_max' => 20],
            ['kode_cell' => '1B48 (RK)', 'kapasitas_max' => 20],
            ['kode_cell' => '1C07 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1C12 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1C43 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1C45 (RK)', 'kapasitas_max' => 28],
            ['kode_cell' => '1C48 (RK)', 'kapasitas_max' => 24],
        ];

        foreach ($cells as $cell) {
            DB::table('cells')->updateOrInsert(
                ['kode_cell' => $cell['kode_cell']],
                [
                    'kapasitas_max' => $cell['kapasitas_max'],
                    'is_active'     => true,
                    'updated_at'    => now(),
                    'created_at'    => now(),
                ]
            );
        }
    }
}
