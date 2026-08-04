<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CellStockAdjustment extends Model
{
    protected $fillable = [
        'cell_id',
        'jumlah_sistem_sebelum',
        'jumlah_aktual',
        'selisih',
        'sumber',
        'nama_file',
        'user_id',
    ];

    public function cell(): BelongsTo
    {
        return $this->belongsTo(Cell::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
