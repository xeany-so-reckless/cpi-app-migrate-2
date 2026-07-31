<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CellReservation extends Model
{
    protected $fillable = [
        'cell_id',
        'max_bag_allowed',
        'status',
        'created_by_user_id',
        'batch_id',
    ];

    public function cell(): BelongsTo
    {
        return $this->belongsTo(Cell::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SerahTerimaBatch::class, 'batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
