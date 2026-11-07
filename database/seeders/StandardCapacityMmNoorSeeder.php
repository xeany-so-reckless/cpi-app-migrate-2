<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StandardCapacity;
use Illuminate\Database\Seeder;

class StandardCapacityMmNoorSeeder extends Seeder
{
    /**
     * Kode produk sumber: kapasitas standar produk 84
     * (YM PART 10 @1.2 KG FROZEN MM NOOR) disamakan dengan produk 53
     * (YM PART 10 @1.2 KG MTS FROZEN).
     */
    private const KODE_SUMBER = '53';

    /**
     * Kode produk baru yang menerima salinan kapasitas.
     */
    private const KODE_TUJUAN = ['84'];

    /**
     * Menyalin semua baris standard_capacities milik produk 53 ke
     * produk 84 (untuk setiap kombinasi lantai x tipe rak).
     *
     * Aman dijalankan ulang: pakai firstOrCreate, jadi baris yang sudah
     * ada (termasuk yang sudah disesuaikan gudang) TIDAK ditimpa.
     *
     * Jalankan SETELAH produk 84 ada di tabel products.
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
            $this->command?->warn('Produk '.self::KODE_SUMBER.' tidak punya baris di standard_capacities. Produk 84 akan memakai kapasitas_max cell.');

            return;
        }

        foreach (self::KODE_TUJUAN as $kode) {
            $produk = Product::where('code', $kode)->first();

            if (! $produk) {
                $this->command?->warn("Produk kode {$kode} belum ada. Tambahkan produknya dulu.");

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
