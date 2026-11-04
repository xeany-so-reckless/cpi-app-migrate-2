<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduksiHarian extends Model
{
    protected $table = 'produksi_harian';

    protected $fillable = [
        'tanggal',
        'no_po',
        'kg_dta',
        'ekor_dta',
        'kg_netto',
        'ayam_mati',
        'kg_titik_nol',
        'kg_bulu_darah',
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
     * Relasi ke PO (PPIC) yang jadi dasar tanggal & kunci unik laporan
     * produksi ini. Key custom karena purchase_orders pakai nomor_po,
     * bukan id, sebagai identitas PO.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'no_po', 'nomor_po');
    }

    /**
     * BARU - Hitung ulang 4 field yang bersumber dari Report Harian
     * Bahan Baku LB untuk 1 PO, lalu update baris produksi_harian
     * dengan no_po tersebut (kalau ada).
     *
     * Logic SAMA dengan ProduksiDashboardController::poSummary():
     * - kg_dta & ekor_dta  : SUM semua rit PO ini.
     * - kg_netto & ayam_mati: SUM hanya rit yang statusnya sudah lewat
     *   "Setelah Bongkar" (status != 'Proses Bongkar').
     *
     * Selalu hitung ULANG PENUH (bukan incremental), sama seperti
     * PpicPlan::recalculateAktual().
     *
     * Kalau PO belum diinput di dashboard produksi -> tidak ada yang
     * di-update. Kalau semua rit PO sudah terhapus (total 0) -> angka
     * dibiarkan seperti terakhir supaya data tidak hilang karena salah
     * hapus.
     *
     * Pakai updateQuietly supaya tidak memicu event berantai.
     */
    public static function recalculateDariLb(?string $noPo): void
    {
        $noPo = strtoupper(trim((string) $noPo));

        if ($noPo === '') {
            return;
        }

        $row = static::where('no_po', $noPo)->first();

        if (! $row) {
            return;
        }

        if (LbPenerimaan::where('no_po', $noPo)->count() === 0) {
            return;
        }

        $sebelum = LbPenerimaan::where('no_po', $noPo)
            ->selectRaw('SUM(kg_dta) as total_kg_dta, SUM(ekor_dta) as total_ekor_dta')
            ->first();

        $setelah = LbPenerimaan::where('no_po', $noPo)
            ->where('status', '!=', 'Proses Bongkar')
            ->selectRaw('SUM(kg_netto) as total_kg_netto, SUM(ayam_mati) as total_ayam_mati')
            ->first();

        $row->updateQuietly([
            'kg_dta'    => (float) ($sebelum->total_kg_dta ?? 0),
            'ekor_dta'  => (int) ($sebelum->total_ekor_dta ?? 0),
            'kg_netto'  => (float) ($setelah->total_kg_netto ?? 0),
            'ayam_mati' => (int) ($setelah->total_ayam_mati ?? 0),
        ]);
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
            'noPo'           => $this->no_po,
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
            'kgBuluDarah'    => (float) $this->kg_bulu_darah,
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