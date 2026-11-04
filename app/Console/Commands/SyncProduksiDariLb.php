<?php

namespace App\Console\Commands;

use App\Models\LbPenerimaan;
use App\Models\ProduksiHarian;
use Illuminate\Console\Command;

class SyncProduksiDariLb extends Command
{
    protected $signature = 'produksi:sync-lb
                            {--po= : Hitung ulang 1 nomor PO saja}
                            {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Hitung ulang kg_dta, ekor_dta, kg_netto, ayam_mati di produksi_harian dari data Report LB';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = ProduksiHarian::orderBy('tanggal');

        if ($po = $this->option('po')) {
            $query->where('no_po', strtoupper(trim($po)));
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->warn('Tidak ada data produksi_harian yang cocok.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('MODE DRY-RUN: tidak ada data yang disimpan.');
        }

        $diubah = 0;
        $dilewati = 0;
        $tabel = [];

        foreach ($rows as $row) {
            $noPo = $row->no_po;

            // Sama seperti recalculateDariLb(): kalau PO tidak punya rit
            // sama sekali di LB, angka dibiarkan.
            if (LbPenerimaan::where('no_po', $noPo)->count() === 0) {
                $dilewati++;
                continue;
            }

            $sebelum = LbPenerimaan::where('no_po', $noPo)
                ->selectRaw('SUM(kg_dta) as kg_dta, SUM(ekor_dta) as ekor_dta')
                ->first();

            $setelah = LbPenerimaan::where('no_po', $noPo)
                ->where('status', '!=', 'Proses Bongkar')
                ->selectRaw('SUM(kg_netto) as kg_netto, SUM(ayam_mati) as ayam_mati')
                ->first();

            $baru = [
                'kg_dta'    => (float) ($sebelum->kg_dta ?? 0),
                'ekor_dta'  => (int) ($sebelum->ekor_dta ?? 0),
                'kg_netto'  => (float) ($setelah->kg_netto ?? 0),
                'ayam_mati' => (int) ($setelah->ayam_mati ?? 0),
            ];

            $lama = [
                'kg_dta'    => (float) $row->kg_dta,
                'ekor_dta'  => (int) $row->ekor_dta,
                'kg_netto'  => (float) $row->kg_netto,
                'ayam_mati' => (int) $row->ayam_mati,
            ];

            if ($lama == $baru) {
                continue;
            }

            $tabel[] = [
                $noPo,
                "{$lama['kg_dta']} -> {$baru['kg_dta']}",
                "{$lama['ekor_dta']} -> {$baru['ekor_dta']}",
                "{$lama['kg_netto']} -> {$baru['kg_netto']}",
                "{$lama['ayam_mati']} -> {$baru['ayam_mati']}",
            ];

            if (! $dryRun) {
                $row->updateQuietly($baru);
            }

            $diubah++;
        }

        if ($tabel) {
            $this->table(['No PO', 'Kg DTA', 'Ekor DTA', 'Kg Netto', 'Mati'], $tabel);
        }

        $verb = $dryRun ? 'akan diubah' : 'diubah';
        $this->info("Selesai. {$diubah} PO {$verb}, {$dilewati} PO dilewati (tidak ada data LB).");

        return self::SUCCESS;
    }
}