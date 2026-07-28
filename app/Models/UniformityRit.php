<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniformityRit extends Model
{
    protected $fillable = [
        'tanggal',
        'no_rit',
        'asal_kandang',
        'size_min',
        'size_max',
        'kg_dta',
        'ekor_dta',
        'rerata_abw',
        'jumlah_sample',
        'undersize_percent',
        'size_masuk_percent',
        'oversize_percent',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function samples(): HasMany
    {
        return $this->hasMany(UniformitySample::class);
    }

    /**
     * Hitung ulang rerata_abw, jumlah_sample, dan 3 persentase klasifikasi
     * berdasarkan data di uniformity_samples + range size_min/size_max.
     * Menggantikan hitungAbw() & hitungKalkulasiUniformity() di JS lama,
     * tapi dijalankan di server supaya konsisten dan tidak bisa dimanipulasi
     * dari browser.
     */
    public function recalculateFromSamples(): void
    {
        $samples = $this->samples()->pluck('berat');
        $total = $samples->count();

        $this->jumlah_sample = $total;
        $this->rerata_abw = $this->ekor_dta > 0 ? round($this->kg_dta / $this->ekor_dta, 3) : 0;

        if ($total === 0 || $this->size_min <= 0 || $this->size_max <= 0) {
            $this->undersize_percent = 0;
            $this->size_masuk_percent = 0;
            $this->oversize_percent = 0;

            return;
        }

        $under = $samples->filter(fn ($berat) => $berat < ($this->size_min - 0.0001))->count();
        $over = $samples->filter(fn ($berat) => $berat > ($this->size_max + 0.0001))->count();
        $masuk = $total - $under - $over;

        $this->undersize_percent = round(($under / $total) * 100, 1);
        $this->size_masuk_percent = round(($masuk / $total) * 100, 1);
        $this->oversize_percent = round(($over / $total) * 100, 1);
    }
}
