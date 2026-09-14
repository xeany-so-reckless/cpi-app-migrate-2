<?php

namespace App\Http\Controllers\Ppic;

use App\Http\Controllers\Controller;
use App\Models\PpicPlan;
use App\Models\ProduksiFresh;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PpicDashboardController extends Controller
{
    public function index(): View
    {
        return view('ppic.dashboard');
    }

    /**
     * Data ringkasan & tren buat grafik Dashboard PPIC.
     * Default bulan berjalan kalau tidak dikasih parameter ?bulan=yyyy-MM.
     *
     * TIDAK DIUBAH - tetap dipakai untuk chart Plan vs Aktual & PO per
     * Jenis, filternya tetap per bulan seperti semula.
     */
    public function data(Request $request): JsonResponse
    {
        $bulan = $request->query('bulan', now()->format('Y-m'));

        $plans = PpicPlan::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->orderBy('tanggal')
            ->get();

        $trend = $plans->map(fn (PpicPlan $p) => [
            'tanggal'          => $p->tanggal->format('d/m'),
            'planEkor'         => $p->plan_ekor,
            'aktualEkor'       => $p->aktual_ekor,
            'persenSelisihEkor'=> $p->persen_selisih_ekor,
            'planKg'           => (float) $p->plan_kg,
            'aktualKg'         => (float) $p->aktual_kg,
            'persenSelisihKg'  => $p->persen_selisih_kg,
        ]);

        $summary = [
            'totalPlanEkor'   => $plans->sum('plan_ekor'),
            'totalAktualEkor' => $plans->sum('aktual_ekor'),
            'totalPlanKg'     => round($plans->sum('plan_kg'), 2),
            'totalAktualKg'   => round($plans->sum('aktual_kg'), 2),
            'jumlahHariTercatat' => $plans->count(),
        ];

        $poBulanIni = PurchaseOrder::whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])->get();
        $poByJenis = $poBulanIni->groupBy('jenis_po')->map->count();

        return response()->json([
            'bulan'         => $bulan,
            'trend'         => $trend,
            'summary'       => $summary,
            'totalPo'       => $poBulanIni->count(),
            'poByJenis'     => $poByJenis,
            'produksiFresh' => $this->produksiFreshRekap($poBulanIni),
        ]);
    }

    /**
     * BARU - Pemetaan KODE PRODUK ke JENIS PO (FEH0/FEH1/FEH2/FEHM).
     * Murni mapping statis di kode (TIDAK ada tabel/kolom baru di DB),
     * sesuai daftar yang dikonfirmasi manual:
     *
     *   1  - 36 -> FEH1
     *   47 - 53 -> FEH2
     *   58 - 60 -> FEH2
     *   54 - 57 -> FEHM
     *   61 - 68 -> FEH0
     *
     * Kode produk di luar range-range ini dianggap TIDAK terkait Serah
     * Terima -> return null (nanti no_po ditampilkan "-" di UI).
     */
    private function mapKodeProdukKeJenisPo(string $kodeProduk): ?string
    {
        // products.code bertipe varchar, jadi di-cast ke integer dulu
        // supaya perbandingan range angka (mis. "047" -> 47) akurat.
        $kode = (int) $kodeProduk;

        return match (true) {
            $kode >= 1 && $kode <= 36 => 'FEH1',
            $kode >= 47 && $kode <= 53 => 'FEH2',
            $kode >= 58 && $kode <= 60 => 'FEH2',
            $kode >= 54 && $kode <= 57 => 'FEHM',
            $kode >= 61 && $kode <= 68 => 'FEH0',
            default => null,
        };
    }

    /**
     * BARU - Rekap Serah Terima per Produk, filter pakai RENTANG TANGGAL
     * (dari - sampai), terpisah dari filter bulan di atas supaya tidak
     * mengganggu chart Plan/Aktual/PO yang sudah berjalan.
     *
     * Endpoint: GET /ppic/dashboard/serah-terima-data?dari=YYYY-MM-DD&sampai=YYYY-MM-DD&jenis_po=FEH0
     * Default rentang: 7 hari terakhir kalau parameter tidak dikirim
     * (konsisten dengan default Dashboard Rekap Serah Terima).
     * Parameter jenis_po OPSIONAL - kalau tidak dikirim/kosong, semua
     * jenis PO ditampilkan.
     *
     * Setiap baris = 1 BATCH (1 kode_produksi), diurutkan tanggal
     * terbaru dulu. Tidak ada grouping per produk - kalau 1 kode produk
     * punya beberapa batch dalam rentang tanggal ini, semuanya tampil
     * sebagai baris terpisah. Ringkasan total (jumlah batch, total bag,
     * total kg) disertakan di key 'summary' untuk ditampilkan di UI.
     */
    public function serahTerimaData(Request $request): JsonResponse
    {
        $dari = $request->query('dari') ?: now()->subDays(6)->format('Y-m-d');
        $sampai = $request->query('sampai') ?: now()->format('Y-m-d');

        // BARU - Filter opsional Jenis PO (FEH0/FEH1/FEH2/FEHM). Karena
        // jenis_po BUKAN kolom asli di tabel serah_terima_batches (hasil
        // mapping kode produk, dihitung setelah data ditarik), filter ini
        // diterapkan di collection PHP - BUKAN di query SQL.
        $jenisPoFilter = $request->query('jenis_po');

        // Ekspresi SQL sama seperti di Dashboard Rekap Serah Terima -
        // jumlahkan kg_bag_1 s.d kg_bag_10 jadi total kg per baris.
        $kgSumExpr = collect(range(1, 10))
            ->map(fn ($i) => "COALESCE(kg_bag_{$i}, 0)")
            ->implode(' + ');

        // Per BATCH - setiap baris hasil query = 1 kode_produksi, TIDAK
        // digrouping per produk lagi. Diurutkan tanggal terbaru dulu,
        // supaya batch yang baru masuk gampang dicek paling atas.
        $rows = DB::table('serah_terima_batches')
            ->join('products', 'products.id', '=', 'serah_terima_batches.produk_id')
            ->whereBetween('tanggal_produksi', [$dari, $sampai])
            ->selectRaw("
                serah_terima_batches.kode_produksi as kode_batch,
                serah_terima_batches.tanggal_produksi as tanggal_produksi,
                products.code as kode_produk,
                products.name as nama_produk,
                serah_terima_batches.jumlah_bag as jumlah_bag,
                ({$kgSumExpr}) as total_kg
            ")
            ->orderByDesc('serah_terima_batches.tanggal_produksi')
            ->orderByDesc('serah_terima_batches.id')
            ->get();

        // Lookup PO: [ "YYYY-MM-DD|JENIS_PO" => nomor_po ], diambil SEKALI
        // untuk seluruh rentang tanggal (bukan query berulang per baris)
        // supaya tetap ringan walau batch-nya banyak.
        $poLookup = PurchaseOrder::query()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->get(['tanggal', 'jenis_po', 'nomor_po'])
            ->keyBy(fn ($po) => $po->tanggal->format('Y-m-d').'|'.$po->jenis_po);

        $perBatch = $rows->map(function ($r) use ($poLookup) {
            $jenisPo = $this->mapKodeProdukKeJenisPo($r->kode_produk);
            $noPo = null;

            if ($jenisPo) {
                $key = $r->tanggal_produksi.'|'.$jenisPo;
                $noPo = $poLookup->get($key)?->nomor_po;
            }

            return [
                'no_po'            => $noPo ?? '-',
                'jenis_po'         => $jenisPo ?? '-',
                'kode_batch'       => $r->kode_batch,
                'tanggal_produksi' => $r->tanggal_produksi,
                'kode_produk'      => $r->kode_produk,
                'nama_produk'      => $r->nama_produk,
                'jumlah_bag'       => (int) $r->jumlah_bag,
                'total_kg'         => round((float) $r->total_kg, 1),
            ];
        });

        // Filter Jenis PO diterapkan DI SINI (setelah mapping), cuma
        // kalau parameter dikirim & bukan "semua"/kosong.
        if ($jenisPoFilter) {
            $perBatch = $perBatch->where('jenis_po', $jenisPoFilter);
        }

        $perBatch = $perBatch->values();

        return response()->json([
            'dari'      => $dari,
            'sampai'    => $sampai,
            'jenis_po'  => $jenisPoFilter,
            'per_batch' => $perBatch,
            'summary'   => [
                'total_batch' => $perBatch->count(),
                'total_bag'   => $perBatch->sum('jumlah_bag'),
                'total_kg'    => round($perBatch->sum('total_kg'), 1),
            ],
        ]);
    }

    /**
     * BARU - Rekap total Qty Produksi Fresh per PO, untuk PO-PO di bulan
     * yang sedang difilter. Cuma PO yang SUDAH ADA input Fresh-nya yang
     * ditampilkan (PO tanpa input Fresh sama sekali di-skip, supaya
     * tabel tidak penuh baris kosong).
     *
     * Ini murni tampilan READ-ONLY - tidak ada data yang ditulis balik
     * dari sini, PPIC cuma melihat rekap hasil input tim Produksi Fresh.
     */
    private function produksiFreshRekap($poBulanIni)
    {
        $nomorPoList = $poBulanIni->pluck('nomor_po');

        $freshRows = ProduksiFresh::whereIn('no_po', $nomorPoList)
            ->selectRaw('no_po, tipe_input, SUM(qty) as total_qty, COUNT(*) as jumlah_entri')
            ->groupBy('no_po', 'tipe_input')
            ->get()
            ->groupBy('no_po');

        return $poBulanIni->map(function (PurchaseOrder $po) use ($freshRows) {
            $rows = $freshRows->get($po->nomor_po, collect());

            $qtyMain = (float) ($rows->firstWhere('tipe_input', 'main')->total_qty ?? 0);
            $qtyByProduct = (float) ($rows->firstWhere('tipe_input', 'byproduct')->total_qty ?? 0);
            $jumlahEntri = (int) $rows->sum('jumlah_entri');

            return [
                'nomorPo'      => $po->nomor_po,
                'jenisPo'      => $po->jenis_po,
                'tanggalLabel' => $po->tanggal->format('d/m/Y'),
                'qtyMain'      => $qtyMain,
                'qtyByProduct' => $qtyByProduct,
                'qtyTotal'     => $qtyMain + $qtyByProduct,
                'jumlahEntri'  => $jumlahEntri,
            ];
        })->filter(fn ($row) => $row['jumlahEntri'] > 0)->values();
    }
}