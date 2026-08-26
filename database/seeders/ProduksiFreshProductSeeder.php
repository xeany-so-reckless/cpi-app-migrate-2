<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProduksiFreshProductSeeder extends Seeder
{
    /**
     * Data produk untuk modul Produksi Fresh (menggantikan dictionary
     * PRODUCTS hardcode di Apps Script lama).
     *
     * Kode 47, 48, 49 SUDAH ADA di tabel products dengan nama yang
     * cocok - baris ini cuma di-UPDATE (isi type & category_code),
     * tidak menimpa name/default_ekor/category/dll yang sudah ada.
     *
     * Kode 71 SENGAJA TIDAK dipakai - sudah dipakai produk lain
     * ("SBB Mitra Fz") yang tidak berhubungan dengan modul ini.
     * "Yamiku Griller FRESH (1.0-1.1)" yang di Apps Script memakai
     * kode 71, di sistem baru dipindah ke kode 78 (kesepakatan).
     *
     * Kode 72, 73, 74, 75, 76, 77, 78 BELUM ADA - baris baru di-insert.
     * Kolom lain (default_ekor, category, display_order, is_active)
     * diisi nilai aman/default karena tidak relevan untuk modul Fresh
     * ini - sesuaikan manual di database kalau ternyata dibutuhkan
     * modul lain juga.
     */
    private const PRODUCTS_TO_UPDATE = [
        ['code' => '47', 'type' => 'main', 'category_code' => '02'],
        ['code' => '48', 'type' => 'main', 'category_code' => '02'],
        ['code' => '49', 'type' => 'main', 'category_code' => '02'],
    ];

    private const PRODUCTS_TO_INSERT = [
        ['code' => '78', 'name' => 'Yamiku Griller FRESH (1.0-1.1)', 'type' => 'main', 'category_code' => '01', 'category' => 'kw1'],
        ['code' => '72', 'name' => 'Yamiku Griller FRESH (1.1-1.2)', 'type' => 'main', 'category_code' => '01', 'category' => 'kw1'],
        ['code' => '73', 'name' => 'Yamiku Griller FRESH (0.9-1.0)', 'type' => 'main', 'category_code' => '01', 'category' => 'kw1'],
        ['code' => '74', 'name' => 'HATI + AMPELA (GIBLET) FRESH', 'type' => 'byproduct', 'category_code' => '05', 'category' => 'by_product'],
        ['code' => '75', 'name' => 'KEPALA LEHER FRESH', 'type' => 'byproduct', 'category_code' => '05', 'category' => 'by_product'],
        ['code' => '76', 'name' => 'CEKER BY PRODUCT FRESH', 'type' => 'byproduct', 'category_code' => '05', 'category' => 'by_product'],
        ['code' => '77', 'name' => 'USUS FRESH', 'type' => 'byproduct', 'category_code' => '05', 'category' => 'by_product'],
    ];

    public function run(): void
    {
        foreach (self::PRODUCTS_TO_UPDATE as $data) {
            $product = Product::where('code', $data['code'])->first();

            if (! $product) {
                $this->command->warn("Kode {$data['code']} tidak ditemukan di tabel products - dilewati (seharusnya sudah ada).");
                continue;
            }

            $product->update([
                'type'          => $data['type'],
                'category_code' => $data['category_code'],
            ]);
        }

        foreach (self::PRODUCTS_TO_INSERT as $data) {
            $exists = Product::where('code', $data['code'])->exists();

            if ($exists) {
                $this->command->warn("Kode {$data['code']} sudah ada di tabel products - dilewati supaya tidak menimpa data yang mungkin sudah dipakai modul lain.");
                continue;
            }

            Product::create([
                'code'          => $data['code'],
                'name'          => $data['name'],
                'default_ekor'  => 0,
                'category'      => $data['category'],
                'display_order' => 0,
                'is_active'     => true,
                'type'          => $data['type'],
                'category_code' => $data['category_code'],
            ]);
        }

        $this->command->info('Seeder produk Produksi Fresh selesai: 3 produk di-update, hingga 7 produk baru di-insert.');
    }
}
