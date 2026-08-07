<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cell extends Model
{
    protected $fillable = [
        'kode_cell',
        'cold_storage',
        'lantai',
        'kapasitas_max',
        'kapasitas_max_kg',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_cell', 'cell_id', 'produk_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(CellReservation::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(CellStockAdjustment::class);
    }

    /**
     * Total selisih dari semua penyesuaian (upload Excel) yang pernah ada
     * untuk cell ini. Boleh negatif (kalau fisik lebih sedikit dari
     * hitungan sistem) atau positif (fisik lebih banyak).
     */
    public function totalAdjustment(): int
    {
        return (int) $this->adjustments()->sum('selisih');
    }

    /**
     * Total selisih KG dari semua penyesuaian (upload Excel) - dipakai
     * untuk hitung total kg per cell, dikombinasikan dengan kg dari batch
     * Inbound asli (kg_bag_1..10 di serah_terima_batches).
     */
    public function totalAdjustmentKg(): float
    {
        return (float) $this->adjustments()->sum('selisih_kg');
    }

    /**
     * Total kg terpakai saat ini. Beda dengan versi bag, disini kg
     * PENDING reservation TIDAK dihitung (karena belum ada berat pasti
     * sebelum TPR input kg per bag-nya) - cuma dari batch yang statusnya
     * sudah USED (kg_bag_1..10 asli) + penyesuaian kg dari upload Excel.
     */
    public function terpakaiKg(): float
    {
        $usedKg = $this->reservations()
            ->where('status', 'USED')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->selectRaw('COALESCE(SUM(
                kg_bag_1 + kg_bag_2 + kg_bag_3 + kg_bag_4 + kg_bag_5 +
                kg_bag_6 + kg_bag_7 + kg_bag_8 + kg_bag_9 + kg_bag_10
            ), 0) as total')
            ->value('total');

        return (float) $usedKg + $this->totalAdjustmentKg();
    }

    public function sisaKapasitasKg(): float
    {
        if ($this->kapasitas_max_kg === null) {
            return 0;
        }

        return max(0, (float) $this->kapasitas_max_kg - $this->terpakaiKg());
    }

    /**
     * Breakdown stock per warna (kuartal produksi), gabungan dari semua
     * penyesuaian (upload Excel) yang pernah ada untuk cell ini.
     * Dipakai buat tampilan "klik cell -> lihat rincian per warna".
     *
     * Merah = Jan-Mar, Biru = Apr-Jun, Hijau = Jul-Sep, Kuning = Okt-Des.
     */
    public function breakdownWarna(): array
    {
        $row = $this->adjustments()
            ->selectRaw('
                COALESCE(SUM(bag_merah), 0) as bag_merah,
                COALESCE(SUM(bag_biru), 0) as bag_biru,
                COALESCE(SUM(bag_hijau), 0) as bag_hijau,
                COALESCE(SUM(bag_kuning), 0) as bag_kuning,
                COALESCE(SUM(kg_merah), 0) as kg_merah,
                COALESCE(SUM(kg_biru), 0) as kg_biru,
                COALESCE(SUM(kg_hijau), 0) as kg_hijau,
                COALESCE(SUM(kg_kuning), 0) as kg_kuning
            ')
            ->first();

        return [
            'merah'  => ['bag' => (int) $row->bag_merah, 'kg' => (float) $row->kg_merah],
            'biru'   => ['bag' => (int) $row->bag_biru, 'kg' => (float) $row->kg_biru],
            'hijau'  => ['bag' => (int) $row->bag_hijau, 'kg' => (float) $row->kg_hijau],
            'kuning' => ['bag' => (int) $row->bag_kuning, 'kg' => (float) $row->kg_kuning],
        ];
    }

    /**
     * Sisa kapasitas (dalam bag) saat ini.
     * Terpakai = SUM bag dari reservasi yang sudah USED (jumlah_bag batch
     * sebenarnya) + reservasi yang masih PENDING (pakai max_bag_allowed,
     * supaya tidak dobel-reservasi lebih dari fisik yang tersedia)
     * + total penyesuaian manual (upload Excel) yang pernah dilakukan.
     */
    public function sisaKapasitas(): int
    {
        $terpakaiUsed = $this->reservations()
            ->where('status', 'USED')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->sum('serah_terima_batches.jumlah_bag');

        $terpakaiPending = $this->reservations()
            ->where('status', 'PENDING')
            ->sum('max_bag_allowed');

        $adjustment = $this->totalAdjustment();

        return max(0, $this->kapasitas_max - $terpakaiUsed - $terpakaiPending - $adjustment);
    }
}