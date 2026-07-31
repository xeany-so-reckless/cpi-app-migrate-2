<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'default_ekor',
        'category',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Menggantikan fungsi checkProductCode() di Input Tally Produksi v.3.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category)->orderBy('display_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Cell-cell yang sah untuk menyimpan produk ini (Master Produk-Cell).
     * Dipakai untuk validasi saat TPR memilih reservasi Cell dari TWH -
     * produk yang dipilih harus terdaftar di salah satu Cell reservasi itu.
     */
    public function cells(): BelongsToMany
    {
        return $this->belongsToMany(Cell::class, 'product_cell', 'produk_id', 'cell_id');
    }
}