<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferBag extends Model
{
    /**
     * Catatan 1 bag yang dipindahkan ke cell simpanan.
     * Bag tetap terhubung ke batch aslinya, jadi kode produksi, tanggal
     * produksi (warna) dan produk tetap bisa ditelusuri lewat batch().
     */
    protected $fillable = [
        'batch_id',
        'nomor_bag',
        'from_cell_id',
        'to_cell_id',
        'kg',
        'user_id',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'kg'             => 'float',
            'transferred_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SerahTerimaBatch::class, 'batch_id');
    }

    public function fromCell(): BelongsTo
    {
        return $this->belongsTo(Cell::class, 'from_cell_id');
    }

    public function toCell(): BelongsTo
    {
        return $this->belongsTo(Cell::class, 'to_cell_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
