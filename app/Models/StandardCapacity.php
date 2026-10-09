<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardCapacity extends Model
{
    protected $fillable = [
        'product_id',
        'lantai',
        'tipe_rak',
        'kapasitas_bag',
    ];

    /**
     * Sumber kebenaran kapasitas bag untuk kombinasi (produk x lantai x
     * tipe rak) tertentu - hasil digitalisasi standar dari gudang.
     * Dipakai oleh Cell::kapasitasBagUntukProduk() sebagai lookup utama,
     * dengan fallback ke Cell::kapasitas_max statis kalau kombinasinya
     * belum ada di sini.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Helper query utama yang nanti dipakai dari Cell::kapasitasBagUntukProduk().
     * Dibuat sebagai scope supaya pemanggilan dari Cell tetap singkat & jelas:
     * StandardCapacity::untukProdukDanCell($produkId, $lantai, $tipeRak)->first()?->kapasitas_bag
     */
    public function scopeUntukProdukDanCell($query, int $produkId, string $lantai, string $tipeRak)
    {
        return $query->where('product_id', $produkId)
            ->where('lantai', $lantai)
            ->where('tipe_rak', $tipeRak);
    }
}
