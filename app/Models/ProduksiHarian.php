<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiHarian extends Model
{
    protected $table = 'produksi_harian';

    protected $fillable = [
        'tanggal',
        'kg_dta',
        'ekor_dta',
        'kg_netto',
        'ayam_mati',
        'kg_titik_nol',
        'kg_fg_bp',
        'kg_by_product',
        'pct_kw2',
        'pct_defect',
        'prod_griller',
        'prod_parting',
        'prod_marinasi',
        'total_hasil',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Menggantikan semua kalkulasi di getProductionData() (code.gs lama).
     * Dipanggil dari Controller untuk dikirim sebagai JSON ke frontend,
     * bentuknya persis meniru object yang dulu dikembalikan Apps Script,
     * supaya JS di halaman (Chart.js dkk) tidak perlu banyak berubah.
     */
    public function toDashboardArray(): array
    {
        $abw = $this->ekor_dta > 0 ? $this->kg_dta / $this->ekor_dta : 0;
        $kgSusut = $this->kg_dta - $this->kg_netto;
        $pctSusut = $this->kg_dta > 0 ? ($kgSusut / $this->kg_dta) * 100 : 0;

        $pctGriller = $this->total_hasil > 0 ? ($this->prod_griller / $this->total_hasil) * 100 : 0;
        $pctParting = $this->total_hasil > 0 ? ($this->prod_parting / $this->total_hasil) * 100 : 0;
        $pctMarinasi = $this->total_hasil > 0 ? ($this->prod_marinasi / $this->total_hasil) * 100 : 0;

        $yieldTitikNol = $this->kg_netto > 0 ? ($this->kg_titik_nol / $this->kg_netto) * 100 : 0;
        $yieldFgBp = $this->kg_netto > 0 ? ($this->kg_fg_bp / $this->kg_netto) * 100 : 0;
        $yieldByProduct = $this->kg_netto > 0 ? ($this->kg_by_product / $this->kg_netto) * 100 : 0;

        $tanggalStr = $this->tanggal->format('Y-m-d');

        return [
            'tanggal'        => $tanggalStr,
            'bulan'          => substr($tanggalStr, 0, 7),
            'tahun'          => substr($tanggalStr, 0, 4),
            'kgDta'          => (float) $this->kg_dta,
            'ekorDta'        => (int) $this->ekor_dta,
            'kgNetto'        => (float) $this->kg_netto,
            'ayamMati'       => (int) $this->ayam_mati,
            'abw'            => $abw,
            'kgSusut'        => $kgSusut,
            'pctSusut'       => $pctSusut,
            'kgTitikNol'     => (float) $this->kg_titik_nol,
            'kgFgBp'         => (float) $this->kg_fg_bp,
            'kgByProduct'    => (float) $this->kg_by_product,
            'pctKw2'         => (float) $this->pct_kw2,
            'pctDefect'      => (float) $this->pct_defect,
            'prodGriller'    => (float) $this->prod_griller,
            'prodParting'    => (float) $this->prod_parting,
            'prodMarinasi'   => (float) $this->prod_marinasi,
            'totalHasil'     => (float) $this->total_hasil,
            'pctGriller'     => $pctGriller,
            'pctParting'     => $pctParting,
            'pctMarinasi'    => $pctMarinasi,
            'yieldTitikNol'  => $yieldTitikNol,
            'yieldFgBp'      => $yieldFgBp,
            'yieldByProduct' => $yieldByProduct,
        ];
    }
}
