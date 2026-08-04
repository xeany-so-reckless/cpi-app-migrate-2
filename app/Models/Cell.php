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