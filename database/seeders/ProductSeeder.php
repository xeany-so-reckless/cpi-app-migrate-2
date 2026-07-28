<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Sumber data digabung dari 2 tempat berbeda di kode lama:
     * - Nama & default ekor: `productDatabase` di Input Tally Produksi v.3
     * - Kategori & urutan tampil: `masterKW1_R`/`masterKW2_R`/dst
     *   di Rekap Hasil Produksi v.4
     *
     * [code, name, default_ekor, category, display_order]
     */
    public function run(): void
    {
        $products = [
            // === KW 1 (GRILLER) ===
            ['1', 'Yamiku Griller FZN (0.4-0.5) SLJ', 40, 'kw1', 1],
            ['2', 'Yamiku Griller FZN (0.5-0.6) SLJ', 40, 'kw1', 2],
            ['3', 'Yamiku Griller FZN (0.6-0.7) SLJ', 40, 'kw1', 3],
            ['4', 'Yamiku Griller FZN (0.7-0.8) SLJ', 30, 'kw1', 4],
            ['5', 'Yamiku Griller FZN (0.8-0.9) SLJ', 30, 'kw1', 5],
            ['6', 'Yamiku Griller FZN (0.9-1.0) SLJ', 30, 'kw1', 6],
            ['7', 'Yamiku Griller FZN (1.0-1.1) SLJ', 25, 'kw1', 7],
            ['8', 'Yamiku Griller FZN (1.1-1.2) SLJ', 25, 'kw1', 8],
            ['9', 'Yamiku Griller FZN (1.2-1.3) SLJ', 25, 'kw1', 9],
            ['10', 'Yamiku Griller FZN (1.3-1.4) SLJ', 20, 'kw1', 10],
            ['11', 'Yamiku Griller FZN (1.4-1.5) SLJ', 20, 'kw1', 11],
            ['12', 'Yamiku Griller FZN (1.5-1.6) SLJ', 15, 'kw1', 12],
            ['13', 'Yamiku Griller FZN (1.6-1.7) SLJ', 15, 'kw1', 13],
            ['14', 'Yamiku Griller FZN (1.7-1.8) SLJ', 15, 'kw1', 14],
            ['15', 'Yamiku Griller FZN (1.8-1.9) SLJ', 15, 'kw1', 15],
            ['16', 'Yamiku Griller FZN (1.9-2.0) SLJ', 15, 'kw1', 16],
            ['17', 'Yamiku Griller FZN 2.0 UP SLJ', 15, 'kw1', 17],

            // === KW 2 (GRILLER) ===
            ['18', 'Yamiku Griller Frozen (0.3-0.4) Super SL', 40, 'kw2', 1],
            ['19', 'Yamiku Griller Frozen (0.4-0.5) Super SL', 40, 'kw2', 2],
            ['20', 'Yamiku Griller Frozen (0.5-0.6) Super SL', 40, 'kw2', 3],
            ['21', 'Yamiku Griller Frozen (0.6-0.7) Super SL', 40, 'kw2', 4],
            ['22', 'Yamiku Griller Frozen (0.7-0.8) Super SL', 30, 'kw2', 5],
            ['23', 'Yamiku Griller Frozen (0.8-0.9) Super SL', 30, 'kw2', 6],
            ['24', 'Yamiku Griller Frozen (0.9-1.0) Super SL', 30, 'kw2', 7],
            ['25', 'Yamiku Griller Frozen (1.0-1.1) SUPER SL', 25, 'kw2', 8],
            ['26', 'Yamiku Griller Frozen (1.1-1.2) SUPER SL', 25, 'kw2', 9],
            ['27', 'Yamiku Griller Frozen (1.2-1.3) SUPER SL', 25, 'kw2', 10],
            ['28', 'Yamiku Griller Frozen (1.3-1.4) SUPER SL', 20, 'kw2', 11],
            ['29', 'Yamiku Griller Frozen (1.4-1.5) SUPER SL', 20, 'kw2', 12],
            ['30', 'Yamiku Griller Frozen (1.5-1.6) SUPER SL', 15, 'kw2', 13],
            ['31', 'Yamiku Griller Frozen (1.6-1.7) SUPER SL', 15, 'kw2', 14],
            ['32', 'Yamiku Griller Frozen (1.7-1.8) SUPER SL', 15, 'kw2', 15],
            ['33', 'Yamiku Griller Frozen (1.8-1.9) SUPER SL', 15, 'kw2', 16],
            ['34', 'Yamiku Griller Frozen (1.9-2.0) SUPER SL', 15, 'kw2', 17],
            ['35', 'Yamiku Griller Frozen (2.0 UP) SUPER SL', 15, 'kw2', 18],
            ['36', 'Yamiku Griller Mix', 4, 'kw2', 19],

            // === BAHAN BAKU ===
            ['37', 'GRILLER 1.0 BB TOBYS', 0, 'bahan_baku', 1],
            ['38', 'GRILLER 1.1 BB SAE', 0, 'bahan_baku', 2],
            ['39', 'GRILLER 0.7 BB SAE', 0, 'bahan_baku', 3],
            ['40', 'GRILLER 1.2 BB AA', 0, 'bahan_baku', 4],
            ['41', 'GRILLER 1.0 BB DBESTO ORI', 0, 'bahan_baku', 5],
            ['42', 'GRILLER 1.1 BB DBESTO ORI', 0, 'bahan_baku', 6],
            ['43', 'GRILLER 1.0 BB LAZATTO', 0, 'bahan_baku', 7],
            ['44', 'GRILLER 1.1 BB LAZATTO', 0, 'bahan_baku', 8],
            ['45', 'GRILLER BAHAN MBG', 0, 'bahan_baku', 9],
            ['46', 'GRILLER BB OTHERS PARTING', 0, 'bahan_baku', 10],

            // === PARTING & MARINASI ===
            ['47', 'YM PART 9@1.0 KG FRESH IKI', 0, 'parting', 1],
            ['48', 'YM PART 9 @1.1 KG FRESH SAE', 0, 'parting', 2],
            ['49', 'YM PART 4 @0.7 KG FRESH', 0, 'parting', 3],
            ['50', 'YM PART 9 @1.2 KG FROZEN', 25, 'parting', 4],
            ['51', 'YM PART 9@1.0 KG FROZEN', 25, 'parting', 5],
            ['52', 'YM PART 9 @1.1 KG FROZEN', 25, 'parting', 6],
            ['53', 'YM PART 10 @1.2 KG MTS FROZEN', 25, 'parting', 7],
            ['54', 'YM PART 9 @1.0 KG FZ DBESTO ORI', 25, 'parting', 8],
            ['55', 'YM PART 9 @1.0 KG FZ LAZATTO', 25, 'parting', 9],
            ['56', 'YM PART 9 @1.1 KG FZ LAZATTO', 25, 'parting', 10],
            ['57', 'YM PART 10 @0.9 KG FZ M3 FC', 20, 'parting', 11],
            ['58', 'YM PART 15 @1.3 KG FZ MBG', 30, 'parting', 12],
            ['69', 'YM PART 4 @0.7 KG FZ', 30, 'parting', 13],

            // === BY PRODUCT EVIS & OTHERS ===
            ['59', 'YM TUNGGIR 1 KG FZ', 25, 'by_product', 1],
            ['60', 'MARAS 1 KG FZ', 25, 'by_product', 2],
            ['61', 'HATI + AMPELA KOTOR FROZEN', 4, 'by_product', 3],
            ['62', 'CEKER KOTOR FROZEN', 4, 'by_product', 4],
            ['63', 'AMPELA KOTOR FROZEN', 4, 'by_product', 5],
            ['64', 'KEPALA LEHER 5 KG FZ', 4, 'by_product', 6],
            ['65', 'HATI BERSIH 5 KG FZ', 4, 'by_product', 7],
            ['66', 'HATI BERSIH 1 KG FZ', 25, 'by_product', 8],
            ['67', 'JANTUNG BERSIH 1 KG FZ', 25, 'by_product', 9],
            ['68', 'AMPELA BERSIH 1 KG FZ', 25, 'by_product', 10],
            ['70', 'HJA KOTOR 1 KG FZ', 25, 'by_product', 11],
        ];

        foreach ($products as [$code, $name, $ekor, $category, $order]) {
            Product::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'default_ekor' => $ekor,
                    'category' => $category,
                    'display_order' => $order,
                    'is_active' => true,
                ]
            );
        }
    }
}
