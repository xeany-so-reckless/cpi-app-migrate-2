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
     * Total 60 cell rak kecil, sudah termasuk kapasitas_max_kg.
     */
    public function run(): void
    {
        $cells = [
            ['kode_cell' => '4A07 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 568.4],
            ['kode_cell' => '4A12 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 568.4],
            ['kode_cell' => '4A43 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 638.4],
            ['kode_cell' => '4A45 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 638.4],
            ['kode_cell' => '4A48 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 638.4],
            ['kode_cell' => '4B07 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 456.4],
            ['kode_cell' => '4B12 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 32, 'kapasitas_max_kg' => 361.6],
            ['kode_cell' => '4B43 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 535.2],
            ['kode_cell' => '4B45 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 535.2],
            ['kode_cell' => '4B48 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 535.2],
            ['kode_cell' => '4C07 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 684],
            ['kode_cell' => '4C12 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 34, 'kapasitas_max_kg' => 554.2],
            ['kode_cell' => '4C43 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 609],
            ['kode_cell' => '4C45 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 609],
            ['kode_cell' => '4C48 (RK)', 'cold_storage' => 'CS 04', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 669],
            ['kode_cell' => '3A01 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 607.2],
            ['kode_cell' => '3A03 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 607.2],
            ['kode_cell' => '3A06 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 607.2],
            ['kode_cell' => '3A37 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 596.4],
            ['kode_cell' => '3A47 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 596.4],
            ['kode_cell' => '3B01 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 559.2],
            ['kode_cell' => '3B03 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 559.2],
            ['kode_cell' => '3B06 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 559.2],
            ['kode_cell' => '3B37 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 571.2],
            ['kode_cell' => '3B47 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 571.2],
            ['kode_cell' => '3C01 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 607.2],
            ['kode_cell' => '3C03 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 607.2],
            ['kode_cell' => '3C06 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 652.4],
            ['kode_cell' => '3C37 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 639],
            ['kode_cell' => '3C47 (RK)', 'cold_storage' => 'CS 03', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 666.4],
            ['kode_cell' => '2A07 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2A12 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2A43 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2A45 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2A48 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2B07 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 487.2],
            ['kode_cell' => '2B12 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 487.2],
            ['kode_cell' => '2B43 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2B45 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2B48 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 547.2],
            ['kode_cell' => '2C07 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 32, 'kapasitas_max_kg' => 617.6],
            ['kode_cell' => '2C12 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 42, 'kapasitas_max_kg' => 474.6],
            ['kode_cell' => '2C43 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 669],
            ['kode_cell' => '2C45 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 669],
            ['kode_cell' => '2C48 (RK)', 'cold_storage' => 'CS 02', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 30, 'kapasitas_max_kg' => 669],
            ['kode_cell' => '1A07 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 568.4],
            ['kode_cell' => '1A12 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 568.4],
            ['kode_cell' => '1A43 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 568.4],
            ['kode_cell' => '1A45 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 428.4],
            ['kode_cell' => '1A48 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 1', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 428.4],
            ['kode_cell' => '1B07 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 511.2],
            ['kode_cell' => '1B12 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 511.2],
            ['kode_cell' => '1B43 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 511.2],
            ['kode_cell' => '1B45 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 20, 'kapasitas_max_kg' => 506],
            ['kode_cell' => '1B48 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 2', 'kapasitas_max' => 20, 'kapasitas_max_kg' => 506],
            ['kode_cell' => '1C07 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 652.4],
            ['kode_cell' => '1C12 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 652.4],
            ['kode_cell' => '1C43 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 652.4],
            ['kode_cell' => '1C45 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 28, 'kapasitas_max_kg' => 666.4],
            ['kode_cell' => '1C48 (RK)', 'cold_storage' => 'CS 01', 'lantai' => 'LANTAI 3', 'kapasitas_max' => 24, 'kapasitas_max_kg' => 607.2],
        ];

        foreach ($cells as $cell) {
            DB::table('cells')->updateOrInsert(
                ['kode_cell' => $cell['kode_cell']],
                [
                    'cold_storage'     => $cell['cold_storage'],
                    'lantai'           => $cell['lantai'],
                    'kapasitas_max'    => $cell['kapasitas_max'],
                    'kapasitas_max_kg' => $cell['kapasitas_max_kg'],
                    'is_active'        => true,
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ]
            );
        }
    }
}