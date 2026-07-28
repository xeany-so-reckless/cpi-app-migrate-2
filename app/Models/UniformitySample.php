<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformitySample extends Model
{
    protected $fillable = [
        'uniformity_rit_id',
        'sample_index',
        'berat',
    ];

    public function uniformityRit(): BelongsTo
    {
        return $this->belongsTo(UniformityRit::class);
    }
}
