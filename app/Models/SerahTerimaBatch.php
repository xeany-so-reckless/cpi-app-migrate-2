<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerahTerimaBatch extends Model
{
    /**
     * Menggantikan generateKodeProduksiOtomatis() di code.gs.
     * Format: JBG + kode-tahun + kode-bulan + tanggal + J + kode-kategori + kode-hari + 0
     */
    public static function generateKodeProduksi(string $tanggalProduksi, string $kodeItem): string
    {
        [$tahun, $bulan, $tanggal] = array_map('intval', explode('-', $tanggalProduksi));

        $dateObj = \Carbon\Carbon::create($tahun, $bulan, $tanggal);
        $hariIndex = $dateObj->dayOfWeek; // Minggu=0 ... Sabtu=6, sama seperti getDay() di JS

        $kodeTahun = self::kodeTahunDariAngka($tahun);
        $kodeBulan = chr(65 + ($bulan - 1));
        $kodeTanggal = $tanggal < 10 ? '0'.$tanggal : (string) $tanggal;

        $itemNum = (int) $kodeItem;
        $kodeKategori = '01';
        if (($itemNum >= 50 && $itemNum <= 58) || $itemNum === 69) {
            $kodeKategori = '02';
        }

        $kodeHari = in_array($hariIndex, [2, 4, 6], true) ? 'BB' : 'AA';

        return "JBG{$kodeTahun}{$kodeBulan}{$kodeTanggal}J{$kodeKategori}{$kodeHari}0";
    }

    /**
     * Menggantikan getKodeDatePrefixServer() di code.gs.
     * Dipakai untuk filter tanggal tanpa perlu kolom tanggal terpisah,
     * cukup cocokkan awalan kode_produksi.
     */
    public static function getKodeDatePrefix(string $tanggalInputStr): string
    {
        [$tahun, $bulan, $tanggal] = array_map('intval', explode('-', $tanggalInputStr));

        $kodeTahun = self::kodeTahunDariAngka($tahun);
        $kodeBulan = chr(65 + ($bulan - 1));
        $kodeTanggal = $tanggal < 10 ? '0'.$tanggal : (string) $tanggal;

        return "JBG{$kodeTahun}{$kodeBulan}{$kodeTanggal}";
    }

    private static function kodeTahunDariAngka(int $tahun): string
    {
        $baseYear = 2026;
        $baseCharIndex = 16;
        $yearDiff = $tahun - $baseYear;
        $targetYearIndex = ($baseCharIndex + $yearDiff) % 26;
        if ($targetYearIndex < 0) {
            $targetYearIndex += 26;
        }

        return chr(65 + $targetYearIndex);
    }

    protected $fillable = [
        'kode_produksi',
        'tanggal_produksi',
        'no_trolly',
        'produk_id',
        'jumlah_bag',
        'kg_bag_1', 'kg_bag_2', 'kg_bag_3', 'kg_bag_4', 'kg_bag_5',
        'kg_bag_6', 'kg_bag_7', 'kg_bag_8', 'kg_bag_9', 'kg_bag_10',
        'status_bag_1', 'status_bag_2', 'status_bag_3', 'status_bag_4', 'status_bag_5',
        'status_bag_6', 'status_bag_7', 'status_bag_8', 'status_bag_9', 'status_bag_10',
        'kode_cell',
        'status_approval',
        'tally_produksi_user_id',
        'tally_gudang_user_id',
        'supervisor_user_id',
        'qr_prod_url',
        'barcode_url',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_produksi' => 'date',
        ];
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function tallyProduksi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tally_produksi_user_id');
    }

    public function tallyGudang(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tally_gudang_user_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    /**
     * Ambil 10 slot kg sebagai array (index 0-9), menggantikan
     * kgBags array di kode lama. Cuma isi mengikuti jumlah_bag,
     * sisanya diisi 0.
     */
    public function getKgBagsArrayAttribute(): array
    {
        return collect(range(1, 10))
            ->map(fn ($i) => (float) $this->{"kg_bag_{$i}"})
            ->toArray();
    }

    /**
     * Ambil 10 slot status sebagai array (index 0-9), menggantikan
     * statusBags array di kode lama.
     */
    public function getStatusBagsArrayAttribute(): array
    {
        return collect(range(1, 10))
            ->map(fn ($i) => $this->{"status_bag_{$i}"})
            ->toArray();
    }

    /**
     * Total kg bersih - menjumlahkan semua bag KECUALI yang statusnya
     * TOLAK (REJECT), sama seperti logic getDataByDate() di kode lama.
     */
    public function getTotalKgAttribute(): float
    {
        $total = 0;
        for ($i = 1; $i <= $this->jumlah_bag; $i++) {
            if ($this->{"status_bag_{$i}"} !== 'TOLAK (REJECT)') {
                $total += (float) $this->{"kg_bag_{$i}"};
            }
        }

        return $total;
    }
}
