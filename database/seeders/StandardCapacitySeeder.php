<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StandardCapacitySeeder extends Seeder
{
    /**
     * STEP 4 - Master data kapasitas standar per produk, sumber dari file
     * standar final gudang (STANDARD_GUDANG_BUWARUUU_FIXXXXX.xlsx, Sheet1).
     *
     * Pencocokan produk pakai kolom "Kode Produk" di Excel -> dicocokkan
     * ke kolom `products.code` (BUKAN products.id, BUKAN Kode SKU - sesuai
     * konfirmasi: id != code, contoh Yamiku Tunggir id=60 tapi code=59).
     *
     * Baris yang SUDAH DIKELUARKAN dari data di bawah (sesuai konfirmasi):
     * - Kode Produk = 0 (SBB Butterfly Size 2,6 Up, SBL Mitra Fz) - produk
     *   belum ada di sistem.
     * - 2 baris sampah/header nyasar di tengah data Excel (Kode Produk
     *   berisi "#N/A").
     *
     * `nama_referensi` HANYA untuk memudahkan verifikasi manual saat baca
     * kode ini (mencocokkan visual dengan Excel) - TIDAK dipakai sama
     * sekali untuk pencocokan produk, dan TIDAK disimpan ke database.
     *
     * Kalau ada Kode Produk yang TIDAK ketemu di products.code (selain 2
     * kasus di atas yang sudah dikonfirmasi), baris itu DILAPORKAN lewat
     * console (bukan diam-diam dilewati) supaya ketahuan ada mismatch
     * yang belum diduga.
     */
    public function run(): void
    {
        $rows = [
            ['kode_produk' => 1, 'nama_referensi' => 'Yamiku Griller Fzn (0.4-0.5) SLJ', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 84, 'lt2_kecil' => 24, 'lt3_besar' => 105, 'lt3_kecil' => 30],
            ['kode_produk' => 2, 'nama_referensi' => 'Yamiku Griller Fzn (0.5-0.6) SLJ', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 84, 'lt2_kecil' => 24, 'lt3_besar' => 105, 'lt3_kecil' => 30],
            ['kode_produk' => 3, 'nama_referensi' => 'Yamiku Griller Fzn (0.6-0.7) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 28, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 4, 'nama_referensi' => 'Yamiku Griller Fzn (0.7-0.8) SLJ', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 84, 'lt2_kecil' => 24, 'lt3_besar' => 105, 'lt3_kecil' => 30],
            ['kode_produk' => 5, 'nama_referensi' => 'Yamiku Griller Fzn (0.8-0.9) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 28, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 6, 'nama_referensi' => 'Yamiku Griller Fzn (0.9-1.0) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 28, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 7, 'nama_referensi' => 'Yamiku Griller Fzn (1.0-1.1) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 8, 'nama_referensi' => 'Yamiku Griller Fzn (1.1-1.2) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 9, 'nama_referensi' => 'Yamiku Griller Fzn (1.2-1.3) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 10, 'nama_referensi' => 'Yamiku Griller Fzn (1.3-1.4) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 11, 'nama_referensi' => 'Yamiku Griller Fzn (1.4-1.5) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 12, 'nama_referensi' => 'Yamiku Griller Fzn (1.5-1.6) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 13, 'nama_referensi' => 'Yamiku Griller Fzn (1.6-1.7) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 14, 'nama_referensi' => 'Yamiku Griller Fzn (1.7-1.8) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 15, 'nama_referensi' => 'Yamiku Griller Fzn (1.8-1.9) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 16, 'nama_referensi' => 'Yamiku Griller Fzn (1.9-2.0) SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 17, 'nama_referensi' => 'Yamiku Griller Fzn 2.0 Up SLJ', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 18, 'nama_referensi' => 'Yamiku Griller Frozen (0.3-0.4) Super SL', 'lt1_besar' => 105, 'lt1_kecil' => 32, 'lt2_besar' => 91, 'lt2_kecil' => 28, 'lt3_besar' => 112, 'lt3_kecil' => 32],
            ['kode_produk' => 19, 'nama_referensi' => 'Yamiku Griller Frozen (0.4-0.5) Super SL', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 84, 'lt2_kecil' => 24, 'lt3_besar' => 105, 'lt3_kecil' => 30],
            ['kode_produk' => 20, 'nama_referensi' => 'Yamiku Griller Frozen (0.5-0.6) Super SL', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 84, 'lt2_kecil' => 24, 'lt3_besar' => 105, 'lt3_kecil' => 30],
            ['kode_produk' => 21, 'nama_referensi' => 'Yamiku Griller Frozen (0.6-0.7) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 28, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 22, 'nama_referensi' => 'Yamiku Griller Frozen (0.7-0.8) Super SL', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 84, 'lt2_kecil' => 24, 'lt3_besar' => 105, 'lt3_kecil' => 30],
            ['kode_produk' => 23, 'nama_referensi' => 'Yamiku Griller Frozen (0.8-0.9) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 28, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 24, 'nama_referensi' => 'Yamiku Griller Frozen (0.9-1.0) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 28, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 25, 'nama_referensi' => 'Yamiku Griller Frozen (1.0-1.1) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 26, 'nama_referensi' => 'Yamiku Griller Frozen (1.1-1.2) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 27, 'nama_referensi' => 'Yamiku Griller Frozen (1.2-1.3) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 28, 'nama_referensi' => 'Yamiku Griller Frozen (1.3-1.4) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 29, 'nama_referensi' => 'Yamiku Griller Frozen (1.4-1.5) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 30, 'nama_referensi' => 'Yamiku Griller Frozen (1.5-1.6) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 31, 'nama_referensi' => 'Yamiku Griller Frozen (1.6-1.7) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 32, 'nama_referensi' => 'Yamiku Griller Frozen (1.7-1.8) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 33, 'nama_referensi' => 'Yamiku Griller Frozen (1.8-1.9) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 34, 'nama_referensi' => 'Yamiku Griller Frozen (1.9-2.0) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 35, 'nama_referensi' => 'Yamiku Griller Frozen (2.0 UP) Super SL', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 36, 'nama_referensi' => 'Griller Mix Frozen', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 36, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 69, 'nama_referensi' => 'Yamiku Parting 4 @700 Gr Frozen', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 51, 'nama_referensi' => 'Yamiku Parting 9 @1.0 Kg Frozen', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 52, 'nama_referensi' => 'Yamiku Parting 9 @1.1 Kg  Frozen', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 50, 'nama_referensi' => 'Yamiku Parting 9 @1.2 Kg Frozen', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 53, 'nama_referensi' => 'YM Part 10 @1.2 Kg Frz MTS', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 58, 'nama_referensi' => 'PARTING 15 @1.3 KG FROZEN', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 57, 'nama_referensi' => 'Yamiku P-10 (0,9 - 1,0) Froz M3 FC', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 54, 'nama_referensi' => 'Parting 9 AY 1.0-1.1 KG (22)', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 56, 'nama_referensi' => 'PARTING 9 Ay 1.1-1.2 Kg (12)', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 55, 'nama_referensi' => 'PARTING 9 Ay 1.0-1.1 Kg (12)', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 71, 'nama_referensi' => 'SBB Mitra Fz', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 84, 'lt2_kecil' => 24, 'lt3_besar' => 105, 'lt3_kecil' => 30],
            ['kode_produk' => 64, 'nama_referensi' => 'Kepala Leher Frozen', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 36, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 62, 'nama_referensi' => 'Ceker By Product 5 Kg Frozen', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 36, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 70, 'nama_referensi' => 'Hati+Ampela Kotor Frozen', 'lt1_besar' => 98, 'lt1_kecil' => 28, 'lt2_besar' => 36, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 68, 'nama_referensi' => 'Yamiku Ampela Bersih 1 Kg Frozen', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 67, 'nama_referensi' => 'YM Jantung 1 Kg Fz', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
            ['kode_produk' => 59, 'nama_referensi' => 'Yamiku Tunggir 1 Kg Frozen', 'lt1_besar' => 84, 'lt1_kecil' => 28, 'lt2_besar' => 72, 'lt2_kecil' => 24, 'lt3_besar' => 90, 'lt3_kecil' => 30],
        ];

        $berhasil = 0;
        $tidakKetemu = [];

        foreach ($rows as $row) {
            $product = Product::where('code', $row['kode_produk'])->first();

            if (! $product) {
                $tidakKetemu[] = "Kode Produk {$row['kode_produk']} ({$row['nama_referensi']}) - tidak ketemu di products.code";
                continue;
            }

            $kombinasi = [
                ['lantai' => 'LANTAI 1', 'tipe_rak' => 'besar', 'kapasitas_bag' => $row['lt1_besar']],
                ['lantai' => 'LANTAI 1', 'tipe_rak' => 'kecil', 'kapasitas_bag' => $row['lt1_kecil']],
                ['lantai' => 'LANTAI 2', 'tipe_rak' => 'besar', 'kapasitas_bag' => $row['lt2_besar']],
                ['lantai' => 'LANTAI 2', 'tipe_rak' => 'kecil', 'kapasitas_bag' => $row['lt2_kecil']],
                ['lantai' => 'LANTAI 3', 'tipe_rak' => 'besar', 'kapasitas_bag' => $row['lt3_besar']],
                ['lantai' => 'LANTAI 3', 'tipe_rak' => 'kecil', 'kapasitas_bag' => $row['lt3_kecil']],
            ];

            foreach ($kombinasi as $k) {
                DB::table('standard_capacities')->updateOrInsert(
                    [
                        'product_id' => $product->id,
                        'lantai'     => $k['lantai'],
                        'tipe_rak'   => $k['tipe_rak'],
                    ],
                    [
                        'kapasitas_bag' => $k['kapasitas_bag'],
                        'updated_at'    => now(),
                        'created_at'    => now(),
                    ]
                );
            }

            $berhasil++;
        }

        $this->command?->info("Standard Capacity: {$berhasil} produk berhasil diisi ke standard_capacities.");

        if (count($tidakKetemu) > 0) {
            $this->command?->warn('Standard Capacity: '.count($tidakKetemu).' Kode Produk TIDAK ketemu di sistem:');
            foreach ($tidakKetemu as $pesan) {
                $this->command?->warn('  - '.$pesan);
            }
        }
    }
}
