<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StandardCapacity;
use Illuminate\Database\Seeder;

class StandardCapacityCpSeeder extends Seeder
{
    /**
     * Kode produk sumber: kapasitas standar produk CP disamakan dengan
     * produk 61 (HATI + AMPELA KOTOR FROZEN) sesuai konfirmasi SPV Gudang.
     */
    private const KODE_SUMBER = '61';

    /**
     * Kode produk baru yang menerima salinan kapasitas.
     */
    private const KODE_TUJUAN = ['79', '80', '81', '82'];

    /**
     * Menyalin semua baris standard_capacities milik produk 61 ke
     * produk 79-82 (untuk setiap kombinasi lantai x tipe rak).
     *
     * Aman dijalankan ulang: pakai firstOrCreate, jadi baris yang sudah
     * ada (termasuk yang sudah disesuaikan gudang) TIDAK ditimpa.
     *
     * Jalankan SETELAH ProductSeeder, karena produk 79-82 harus sudah ada.
     */
    public function run(): void
    {
        $sumber = Product::where('code', self::KODE_SUMBER)->first();

        if (! $sumber) {
            $this->command?->warn('Produk sumber kode '.self::KODE_SUMBER.' tidak ditemukan. Tidak ada yang disalin.');

            return;
        }

        $barisSumber = StandardCapacity::where('product_id', $sumber->id)->get();

        if ($barisSumber->isEmpty()) {
            $this->command?->warn('Produk 61 tidak punya baris di standard_capacities. Produk 79-82 akan memakai kapasitas_max cell.');

            return;
        }

        foreach (self::KODE_TUJUAN as $kode) {
            $produk = Product::where('code', $kode)->first();

            if (! $produk) {
                $this->command?->warn("Produk kode {$kode} belum ada. Jalankan ProductSeeder dulu.");

                continue;
            }

            foreach ($barisSumber as $baris) {
                StandardCapacity::firstOrCreate(
                    [
                        'product_id' => $produk->id,
                        'lantai'     => $baris->lantai,
                        'tipe_rak'   => $baris->tipe_rak,
                    ],
                    [
                        'kapasitas_bag' => $baris->kapasitas_bag,
                    ]
                );
            }

            $this->command?->info("Kode {$kode}: {$barisSumber->count()} kombinasi kapasitas diproses.");
        }
    }
}
