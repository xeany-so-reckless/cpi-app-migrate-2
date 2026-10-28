<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CellsPaletSeeder extends Seeder
{
    /**
     * Master Cell tambahan untuk PALET (16 cell), sumber: file
     * "PALET UNTUK CELL STOCK.docx".
     *
     * Aturan:
     * - Semua palet ada di LANTAI 1.
     * - Cold Storage diambil dari angka depan nama palet
     *   (PALET 1.x -> CS 01, 2.x -> CS 02, 3.x -> CS 03, 4.x -> CS 04).
     * - tipe_rak = 'palet' (BUKAN 'besar'/'kecil') supaya lookup ke
     *   standard_capacities tidak ketemu -> kapasitas otomatis memakai
     *   kapasitas_max di bawah ini (190/285/380), apa pun produk yang
     *   mengisi. Penguncian per kode produk tetap berlaku seperti cell lain.
     * - kapasitas_max_kg dikosongkan dulu (belum ada datanya).
     *
     * Aman dijalankan ulang (updateOrInsert berdasarkan kode_cell).
     */
    public function run(): void
    {
        $cells = [
            ['kode_cell' => 'PALET 1.1 IN LIFT',  'cold_storage' => 'CS 01', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 1.2 OUT LIFT', 'cold_storage' => 'CS 01', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 1.1 IN',       'cold_storage' => 'CS 01', 'kapasitas_max' => 285],
            ['kode_cell' => 'PALET 1.2 OUT',      'cold_storage' => 'CS 01', 'kapasitas_max' => 380],
            ['kode_cell' => 'PALET 2.1 IN LIFT',  'cold_storage' => 'CS 02', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 2.2 OUT LIFT', 'cold_storage' => 'CS 02', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 2.1 IN',       'cold_storage' => 'CS 02', 'kapasitas_max' => 285],
            ['kode_cell' => 'PALET 2.2 OUT',      'cold_storage' => 'CS 02', 'kapasitas_max' => 380],
            ['kode_cell' => 'PALET 3.1 IN LIFT',  'cold_storage' => 'CS 03', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 3.2 OUT LIFT', 'cold_storage' => 'CS 03', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 3.1 IN',       'cold_storage' => 'CS 03', 'kapasitas_max' => 285],
            ['kode_cell' => 'PALET 3.2 OUT',      'cold_storage' => 'CS 03', 'kapasitas_max' => 380],
            ['kode_cell' => 'PALET 4.1 IN LIFT',  'cold_storage' => 'CS 04', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 4.2 OUT LIFT', 'cold_storage' => 'CS 04', 'kapasitas_max' => 190],
            ['kode_cell' => 'PALET 4.1 IN',       'cold_storage' => 'CS 04', 'kapasitas_max' => 285],
            ['kode_cell' => 'PALET 4.2 OUT',      'cold_storage' => 'CS 04', 'kapasitas_max' => 380],
        ];

        foreach ($cells as $cell) {
            DB::table('cells')->updateOrInsert(
                ['kode_cell' => $cell['kode_cell']],
                [
                    'cold_storage'     => $cell['cold_storage'],
                    'lantai'           => 'LANTAI 1',
                    'tipe_rak'         => 'palet',
                    'kapasitas_max'    => $cell['kapasitas_max'],
                    'kapasitas_max_kg' => null,
                    'is_active'        => true,
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ]
            );
        }

        $this->command?->info('Cell Palet: '.count($cells).' cell berhasil diisi/diperbarui.');
    }
}
