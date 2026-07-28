<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LbPenerimaan extends Model
{
    protected $table = 'lb_penerimaan';

    protected $fillable = [
        'tanggal',
        'jam_kedatangan',
        'no_rit',
        'area',
        'farm',
        'kg_dta',
        'ekor_dta',
        'kg_netto',
        'ekor_netto',
        'ayam_mati',
        'susut_percent',
        'status',
        'kg_undersize',
        'ekor_undersize',
        'berat_reject',
        'ekspedisi',
        'no_polisi',
        'size',
        'no_dta',
        'no_sppa',
        'kg_rphu',
        'kg_basah',
        'keterangan',
        'no_po',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Cari data hanging yang cocok (dicocokkan lewat tanggal + no_rit,
     * sama seperti getEkorNettoDariHanging() / ambilDetailTerintegrasi()
     * di code.gs lama).
     */
    public function hanging(): ?LbHanging
    {
        return LbHanging::where('tanggal_penerimaan', $this->tanggal->format('Y-m-d'))
            ->where('no_rit', $this->no_rit)
            ->first();
    }

    /**
     * Standar % susut yang diizinkan per Area, dipakai untuk pewarnaan
     * di dashboard (menggantikan stdSusut di JS lama).
     */
    public static function standarSusutPerArea(string $area): float
    {
        return match ($area) {
            'Area 1' => 2.5,
            'Area 2' => 3.0,
            'Area 3' => 3.5,
            'Area 4' => 4.0,
            default  => 4.0,
        };
    }
}
