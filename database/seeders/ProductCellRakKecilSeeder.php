<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCellRakKecilSeeder extends Seeder
{
    /**
     * Relasi Produk <-> Cell Rak Kecil (RK), dari sheet "Kapasitas" file
     * Denah CS RAK KECIL. Semua 60 cell RK berhasil dipetakan penuh ke
     * Master Produk (tidak ada yang belum terdaftar seperti kasus SBBM
     * di cell utama).
     *
     * Wajib dijalankan SETELAH CellsRakKecilSeeder dan seeder Products.
     */
    public function run(): void
    {
        $mapping = [
            '1' => ['4B12 (RK)'],
            '4' => ['4B07 (RK)', '4C12 (RK)'],
            '6' => ['4B43 (RK)', '4B45 (RK)', '4B48 (RK)', '4C48 (RK)'],
            '7' => ['4A07 (RK)', '4A12 (RK)', '4C43 (RK)', '4C45 (RK)'],
            '8' => ['4A43 (RK)', '4A45 (RK)', '4A48 (RK)', '4C07 (RK)'],
            '9' => ['3A01 (RK)', '3A03 (RK)', '3A06 (RK)', '3C01 (RK)', '3C03 (RK)'],
            '10' => ['3A37 (RK)', '3A47 (RK)', '3C37 (RK)'],
            '11' => ['3B01 (RK)', '3B03 (RK)', '3B06 (RK)', '3C06 (RK)'],
            '16' => ['3B37 (RK)', '3B47 (RK)', '3C47 (RK)'],
            '19' => ['2C12 (RK)'],
            '21' => ['2C07 (RK)'],
            '24' => ['2C43 (RK)', '2C45 (RK)', '2C48 (RK)'],
            '25' => ['2B07 (RK)', '2B12 (RK)'],
            '26' => ['2B43 (RK)', '2B45 (RK)', '2B48 (RK)'],
            '27' => ['1B45 (RK)', '1B48 (RK)'],
            '28' => ['1B07 (RK)', '1B12 (RK)', '1B43 (RK)'],
            '29' => ['1C07 (RK)', '1C12 (RK)', '1C43 (RK)'],
            '34' => ['1C45 (RK)'],
            '35' => ['1C48 (RK)'],
            '52' => ['2A07 (RK)', '2A12 (RK)', '2A43 (RK)', '2A45 (RK)', '2A48 (RK)'],
            '64' => ['1A45 (RK)', '1A48 (RK)'],
            '67' => ['1A07 (RK)', '1A12 (RK)', '1A43 (RK)'],
        ];

        $skippedProduk = [];
        $skippedCell = [];

        foreach ($mapping as $kodeProduk => $kodeCells) {
            $produkId = DB::table('products')->where('code', $kodeProduk)->value('id');

            if (! $produkId) {
                $skippedProduk[] = $kodeProduk;
                continue;
            }

            foreach ($kodeCells as $kodeCell) {
                $cellId = DB::table('cells')->where('kode_cell', $kodeCell)->value('id');

                if (! $cellId) {
                    $skippedCell[] = $kodeCell;
                    continue;
                }

                DB::table('product_cell')->updateOrInsert(
                    ['produk_id' => $produkId, 'cell_id' => $cellId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        if (! empty($skippedProduk)) {
            $this->command?->warn('Kode produk tidak ditemukan di tabel products: '.implode(', ', $skippedProduk));
        }
        if (! empty($skippedCell)) {
            $this->command?->warn('Kode cell tidak ditemukan di tabel cells: '.implode(', ', array_unique($skippedCell)));
        }
    }
}
