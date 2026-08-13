<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpicPlan extends Model
{
    protected $fillable = [
        'tanggal',
        'plan_ekor',
        'aktual_ekor',
        'plan_kg',
        'aktual_kg',
        'keterangan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Selisih Ekor = Aktual - Plan. Positif berarti aktual lebih besar
     * dari plan, negatif berarti kurang dari target.
     */
    public function getSelisihEkorAttribute(): int
    {
        return $this->aktual_ekor - $this->plan_ekor;
    }

    /**
     * Persentase Selisih Ekor terhadap Plan. 0 kalau plan_ekor = 0
     * (menghindari divide by zero).
     */
    public function getPersenSelisihEkorAttribute(): float
    {
        if ($this->plan_ekor <= 0) {
            return 0;
        }

        return round(($this->selisih_ekor / $this->plan_ekor) * 100, 2);
    }

    /**
     * Selisih KG = Aktual - Plan.
     */
    public function getSelisihKgAttribute(): float
    {
        return round($this->aktual_kg - $this->plan_kg, 2);
    }

    /**
     * Persentase Selisih KG terhadap Plan. 0 kalau plan_kg = 0.
     */
    public function getPersenSelisihKgAttribute(): float
    {
        if ($this->plan_kg <= 0) {
            return 0;
        }

        return round(($this->selisih_kg / $this->plan_kg) * 100, 2);
    }
}
