<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BonelessProductSeeder extends Seeder
{
    /**
     * Tambah produk "SBB Mitra Fz" (kategori Boneless) yang sebelumnya
     * belum ada di Master Produk - dibutuhkan supaya 24 cell terkait
     * (2A04, 2A05, dst) punya relasi produk yang valid.
     *
     * Wajib dijalankan SETELAH migration 2026_08_05_000001 (nambah enum
     * 'boneless'), dan SEBELUM ProductCellSeeder dijalankan ulang.
     */
    public function run(): void
    {
        DB::table('products')->updateOrInsert(
            ['code' => '71'],
            [
                'name'          => 'SBB Mitra Fz',
                'default_ekor'  => 0,
                'category'      => 'boneless',
                'display_order' => 1,
                'is_active'     => true,
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );
    }
}
