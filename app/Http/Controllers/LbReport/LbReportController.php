<?php

namespace App\Http\Controllers\LbReport;

use App\Http\Controllers\Controller;
use App\Models\LbHanging;
use App\Models\LbPenerimaan;
use App\Models\PpicPlan;
use App\Models\PurchaseOrder;
use App\Models\UniformityRit;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LbReportController extends Controller
{
    /**
     * Dashboard - terbuka tanpa login (sama seperti aslinya).
     */
    public function dashboard(): View
    {
        return view('lb-report.dashboard');
    }

    /**
     * Daftar semua Nomor PO dari modul PPIC, dipakai buat dropdown
     * "No PO" di form Sebelum Bongkar. PO yang sudah TECO dikeluarkan.
     *
     * jumlahRit ikut dikirim supaya frontend bisa generate dropdown
     * "RIT-01".."RIT-{jumlah_rit}" begitu PO dipilih.
     */
    public function listPurchaseOrders(): JsonResponse
    {
        $list = PurchaseOrder::whereNull('teco_at')
            ->orderByDesc('tanggal')
            ->get(['nomor_po', 'jenis_po', 'tanggal', 'jumlah_rit'])
            ->map(fn (PurchaseOrder $po) => [
                'nomorPo'   => $po->nomor_po,
                'jenisPo'   => $po->jenis_po,
                'tanggal'   => $po->tanggal->format('d/m/Y'),
                'jumlahRit' => $po->jumlah_rit,
            ]);

        return response()->json($list);
    }

    /**
     * Halaman kerja (Input + Hanging dalam 1 tempat, tab berdasarkan role).
     */
    public function workspace(): View
    {
        return view('lb-report.workspace');
    }

    /**
     * Menggantikan ambilRekap() di code.gs.
     * Mendukung 3 mode filter: tanggal (harian), bulan (yyyy-MM), atau no_po.
     */
    public function rekap(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');
        $bulan = $request->query('bulan'); // format: yyyy-MM
        $po = $request->query('po');

        $query = LbPenerimaan::query();

        if ($po) {
            $query->where('no_po', strtoupper(trim($po)));
        } elseif ($bulan) {
            $query->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan]);
        } elseif ($tanggal) {
            $query->where('tanggal', $tanggal);
        } else {
            return response()->json($this->emptyRekap());
        }

        $rows = $query->get();

        $hasil = $this->emptyRekap();
        $hasUpdate = false;

        foreach ($rows as $row) {
            if ($row->status === 'Baru') {
                $hasUpdate = true;
            }

            $areaKey = preg_replace('/\D/', '', $row->area);

            $hasil['kgNetto'] += (float) $row->kg_netto;
            $hasil['ekorNetto'] += (int) $row->ekor_netto;
            $hasil['mati'] += (int) $row->ayam_mati;
            $hasil['totalKgDta'] += (float) $row->kg_dta;

            if (isset($hasil['area'][$areaKey])) {
                $hasil['area'][$areaKey]['kgDta'] += (float) $row->kg_dta;
                $hasil['area'][$areaKey]['kgNet'] += (float) $row->kg_netto;
            }

            $hasil['rincianRit'][] = [
                'jam'          => $row->jam_kedatangan,
                'tanggal'      => $row->tanggal->format('d/m/Y'),
                'noRit'        => $row->no_rit,
                'asal'         => $row->farm,
                'area'         => 'Area '.$areaKey,
                'po'           => $row->no_po,
                'kgDta'        => (float) $row->kg_dta,
                'ekorDta'      => (int) $row->ekor_dta,
                'kgNetto'      => (float) $row->kg_netto,
                'ekorNetto'    => (int) $row->ekor_netto,
                'mati'         => (int) $row->ayam_mati,
                'susutPercent' => (float) $row->susut_percent,
                'statusData'   => $row->status,
            ];
        }

        $hasil['hasUpdate'] = $hasUpdate;

        if ($hasil['totalKgDta'] > 0) {
            $hasil['persenSusut'] = round((($hasil['totalKgDta'] - $hasil['kgNetto']) / $hasil['totalKgDta']) * 100, 2);
        }

        foreach ($hasil['area'] as $key => $area) {
            if ($area['kgDta'] > 0) {
                $hasil['area'][$key]['persen'] = round((($area['kgDta'] - $area['kgNet']) / $area['kgDta']) * 100, 2);
            }
        }

        return response()->json(['harian' => $hasil]);
    }

    /**
     * Menggantikan ambilDetailTerintegrasi() di code.gs. Menggabungkan
     * data lb_penerimaan + lb_hanging + uniformity_rits.
     *
     * DIUBAH: nomor rit sekarang unik per PO (bukan per hari). Kalau
     * parameter `po` tidak dikirim dan rit itu ada di lebih dari satu PO
     * pada tanggal tsb, request ditolak (bukan diam-diam ambil yang pertama).
     */
    public function detail(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');
        $noRit = strtoupper(trim($request->query('no_rit', '')));
        $po = $request->query('po');

        $query = LbPenerimaan::where('no_rit', $noRit);
        if ($po) {
            $query->where('no_po', strtoupper(trim($po)));
        } elseif ($tanggal) {
            $query->where('tanggal', $tanggal);
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            return response()->json([
                'error'   => 'Data tidak ditemukan.',
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        if ($matches->count() > 1) {
            $daftarPo = $matches->pluck('no_po')->implode(', ');
            $pesan = "Rit '{$noRit}' ada di beberapa PO ({$daftarPo}). Sertakan No PO.";

            return response()->json(['error' => $pesan, 'message' => $pesan], 422);
        }

        $penerimaan = $matches->first();

        $abw = $penerimaan->ekor_netto > 0
            ? $penerimaan->kg_netto / $penerimaan->ekor_netto
            : ($penerimaan->ekor_dta > 0 ? $penerimaan->kg_dta / $penerimaan->ekor_dta : 0);

        $hanging = $this->queryHanging(
            $penerimaan->tanggal->format('Y-m-d'),
            $noRit,
            (string) $penerimaan->no_po
        )->first();

        // CATATAN: UniformityRit belum difilter per PO karena belum
        // diketahui apakah tabelnya punya kolom no_po. Kalau ada, tambahkan
        // ->where('no_po', $penerimaan->no_po) di bawah ini.
        $uniformity = UniformityRit::where('tanggal', $penerimaan->tanggal->format('Y-m-d'))
            ->where('no_rit', $noRit)
            ->first();

        return response()->json([
            'tanggal'          => $penerimaan->tanggal->format('d/m/Y'),
            'noRit'            => $penerimaan->no_rit,
            'size'             => $penerimaan->size ?: '-',
            'ekspedisi'        => $penerimaan->ekspedisi ?: '-',
            'noPolisi'         => $penerimaan->no_polisi ?: '-',
            'noDta'            => $penerimaan->no_dta ?: '-',
            'area'             => $penerimaan->area,
            'jamDatang'        => $penerimaan->jam_kedatangan,
            'totalEkorDta'     => (int) $penerimaan->ekor_dta,
            'totalKgDta'       => (float) $penerimaan->kg_dta,
            'abw'              => round($abw, 2),
            'noSppa'           => $penerimaan->no_sppa ?: '-',
            'jamHanging'       => $hanging ? "{$hanging->jam_bongkar} - {$hanging->jam_selesai}" : '-',
            'ayamDiterima'     => $hanging->total_diterima ?? 0,
            'statusHanging'    => $hanging->status ?? '-',
            'selisihEkor'      => $penerimaan->ekor_dta - ($hanging->total_diterima ?? 0),
            'mati'             => (int) $penerimaan->ayam_mati,
            'undersizeKg'      => (float) $penerimaan->kg_undersize,
            'beratRejectTotal' => (float) $penerimaan->kg_undersize + ($penerimaan->ayam_mati * $abw),
            'uniUndersize'     => $uniformity ? $uniformity->undersize_percent.'%' : '-',
            'uniSizeMasuk'     => $uniformity ? $uniformity->size_masuk_percent.'%' : '-',
            'uniOversize'      => $uniformity ? $uniformity->oversize_percent.'%' : '-',
            'kgBasah'          => (float) $penerimaan->kg_basah,
            'keterangan'       => $penerimaan->keterangan ?: '-',
            'po'               => $penerimaan->no_po ?: '-',
        ]);
    }

    /**
     * Menggantikan getRawDataPenerimaan() untuk export Excel dashboard.
     */
    public function rawData(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');

        $rows = LbPenerimaan::where('tanggal', $tanggal)->get();

        $aoa = [['Tanggal', 'Jam', 'No Rit', 'Area', 'Farm', 'Kg DTA', 'Ekor DTA', 'Kg Netto', 'Ekor Netto', 'Mati', 'Susut %']];
        foreach ($rows as $row) {
            $aoa[] = [
                $row->tanggal->format('Y-m-d'),
                $row->jam_kedatangan,
                $row->no_rit,
                $row->area,
                $row->farm,
                (float) $row->kg_dta,
                (int) $row->ekor_dta,
                (float) $row->kg_netto,
                (int) $row->ekor_netto,
                (int) $row->ayam_mati,
                (float) $row->susut_percent,
            ];
        }

        return response()->json($aoa);
    }

    /**
     * Menggantikan simpanDataSebelum() di code.gs. Role: lb_penerimaan_awal (APP).
     */
    public function storeSebelum(Request $request): JsonResponse
    {
        $this->authorizeRole($request, ['lb_penerimaan_awal', 'supervisor']);

        $data = $request->validate([
            'tanggal'        => ['required', 'date'],
            'no_rit'         => ['required', 'string', 'max:50'],
            'area'           => ['required', 'string'],
            'farm'           => ['required', 'string'],
            'size'           => ['required', 'string'],
            'jam_kedatangan' => ['required'],
            'ekspedisi'      => ['nullable', 'string'],
            'no_polisi'      => ['nullable', 'string'],
            'kg_dta'         => ['required', 'numeric', 'min:0'],
            'ekor_dta'       => ['required', 'integer', 'min:0'],
            'no_dta'         => ['nullable', 'string'],
            'no_sppa'        => ['nullable', 'string'],
            'no_po'          => ['required', 'string', 'exists:purchase_orders,nomor_po'],
        ]);

        $noRit = strtoupper(trim($data['no_rit']));
        $noPo = strtoupper(trim($data['no_po']));

        $po = PurchaseOrder::where('nomor_po', $noPo)->first();

        if (! preg_match('/^RIT-(\d+)$/', $noRit)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Nomor Rit '{$noRit}' tidak valid. Format harus RIT-xx (contoh: RIT-01).",
            ], 422);
        }

        // Batas jumlah rit HANYA berlaku untuk PO jenis FEHM, dimana PPIC
        // sudah menentukan jumlah_rit di depan. Jenis PO lain bebas.
        if ($po->jenis_po === 'FEHM') {
            $jumlahRitPo = $po->jumlah_rit ?? 0;

            if ($jumlahRitPo < 1) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "PO '{$noPo}' belum memiliki Jumlah Rit yang valid. Hubungi PPIC untuk melengkapi data PO.",
                ], 422);
            }

            preg_match('/^RIT-(\d+)$/', $noRit, $match);

            if ((int) $match[1] < 1 || (int) $match[1] > $jumlahRitPo) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Nomor Rit '{$noRit}' tidak valid untuk PO '{$noPo}'. PO ini hanya punya {$jumlahRitPo} rit (RIT-01 s/d RIT-".str_pad($jumlahRitPo, 2, '0', STR_PAD_LEFT).").",
                ], 422);
            }
        }

        // DIUBAH: nomor rit unik per PO per tanggal (bukan per tanggal saja).
        $duplikat = LbPenerimaan::where('tanggal', $data['tanggal'])
            ->where('no_po', $noPo)
            ->where('no_rit', $noRit)
            ->exists();

        if ($duplikat) {
            return response()->json([
                'status'  => 'error',
                'message' => "Nomor Rit '{$noRit}' sudah pernah didaftarkan pada PO '{$noPo}' tanggal {$data['tanggal']}!",
            ], 422);
        }

        $penerimaan = LbPenerimaan::create([
            'tanggal'        => $data['tanggal'],
            'jam_kedatangan' => $data['jam_kedatangan'],
            'no_rit'         => $noRit,
            'area'           => $data['area'],
            'farm'           => strtoupper(trim($data['farm'])),
            'kg_dta'         => $data['kg_dta'],
            'ekor_dta'       => $data['ekor_dta'],
            'status'         => 'Proses Bongkar',
            'ekspedisi'      => isset($data['ekspedisi']) ? strtoupper(trim($data['ekspedisi'])) : null,
            'no_polisi'      => isset($data['no_polisi']) ? strtoupper(trim($data['no_polisi'])) : null,
            'size'           => strtoupper(trim($data['size'])),
            'no_dta'         => isset($data['no_dta']) ? strtoupper(trim($data['no_dta'])) : null,
            'no_sppa'        => isset($data['no_sppa']) ? strtoupper(trim($data['no_sppa'])) : null,
            'no_po'          => $noPo,
        ]);

        ActivityLogger::log(
            'report_lb',
            'create',
            "{$request->user('tally')->employee_code} ({$request->user('tally')->name}) input data Sebelum Bongkar Rit {$noRit} (PO {$penerimaan->no_po})",
            $request->user('tally')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Data kedatangan berhasil disimpan dengan No PO: '.($penerimaan->no_po ?: '-'),
        ]);
    }

    /**
     * Menggantikan getDaftarRitByPO() di code.gs.
     */
    public function daftarRitByPo(Request $request): JsonResponse
    {
        $po = strtoupper(trim($request->query('no_po', '')));

        $rows = LbPenerimaan::where('no_po', $po)->orderByDesc('id')->get();

        return response()->json([
            'list' => $rows->map(fn (LbPenerimaan $r) => [
                'tanggal' => $r->tanggal->format('Y-m-d'),
                'noRit'   => $r->no_rit,
                'farm'    => $r->farm ?: '-',
                'status'  => $r->status ?: '-',
            ]),
        ]);
    }

    /**
     * Menggantikan getEkorNettoDariHanging() di code.gs. Dipakai di form
     * "Setelah Bongkar" untuk menarik Ekor Netto dari hasil hanging.
     *
     * DIUBAH: menerima no_po. Kalau kosong dan rit ada di beberapa PO
     * pada tanggal tsb, ditolak dengan pesan yang jelas.
     */
    public function ekorNettoHanging(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');
        $noRit = strtoupper(trim($request->query('no_rit', '')));
        $noPo = strtoupper(trim($request->query('no_po', '')));

        $rows = $this->queryHanging($tanggal, $noRit, $noPo)->get();

        if ($rows->isEmpty()) {
            $infoPo = $noPo !== '' ? " (PO {$noPo})" : '';

            return response()->json([
                'message' => "Data Rit '{$noRit}'{$infoPo} pada tanggal {$tanggal} belum diinput di bagian Hanging/Counter!",
            ], 404);
        }

        if ($rows->count() > 1) {
            return response()->json([
                'message' => "Rit '{$noRit}' ada di beberapa PO pada tanggal {$tanggal} ({$rows->pluck('no_po')->implode(', ')}). Isi No PO dulu.",
            ], 422);
        }

        $hanging = $rows->first();

        return response()->json([
            'ekorNetto' => $hanging->total_diterima,
            'noPo'      => $hanging->no_po,
        ]);
    }

    /**
     * Menggantikan simpanDataSetelah() di code.gs. Role: lb_penerimaan_akhir (LGS).
     *
     * DIUBAH: menerima no_po_update (nullable supaya frontend lama tidak
     * langsung rusak). Kalau kosong dan rit ambigu, ditolak. Kalau terisi,
     * dipakai sebagai filter sehingga tidak menimpa data PO lain.
     */
    public function storeSetelah(Request $request): JsonResponse
    {
        $this->authorizeRole($request, ['lb_penerimaan_akhir', 'supervisor']);

        $data = $request->validate([
            'tanggal_update'  => ['required', 'date'],
            'no_rit_update'   => ['required', 'string'],
            'no_po_update'    => ['nullable', 'string'],
            'kg_netto'        => ['required', 'numeric', 'min:0'],
            'ekor_netto'      => ['required', 'integer', 'min:0'],
            'ayam_mati'       => ['required', 'integer', 'min:0'],
            'kg_undersize'    => ['required', 'numeric', 'min:0'],
            'ekor_undersize'  => ['required', 'integer', 'min:0'],
            'kg_rphu'         => ['required', 'numeric', 'min:0'],
            'kg_basah'        => ['required', 'numeric', 'min:0'],
            'no_sppa'         => ['nullable', 'string'],
            'keterangan'      => ['nullable', 'string'],
        ]);

        $noRit = strtoupper(trim($data['no_rit_update']));
        $noPo = strtoupper(trim($data['no_po_update'] ?? ''));

        $query = LbPenerimaan::where('tanggal', $data['tanggal_update'])
            ->where('no_rit', $noRit);

        if ($noPo !== '') {
            $query->where('no_po', $noPo);
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            $infoPo = $noPo !== '' ? " (PO {$noPo})" : '';

            return response()->json([
                'message' => "Rit '{$noRit}'{$infoPo} pada tanggal {$data['tanggal_update']} TIDAK DITEMUKAN.",
            ], 404);
        }

        if ($matches->count() > 1) {
            return response()->json([
                'message' => "Rit '{$noRit}' ada di beberapa PO pada tanggal {$data['tanggal_update']} ({$matches->pluck('no_po')->implode(', ')}). Isi No PO dulu.",
            ], 422);
        }

        $penerimaan = $matches->first();

        $susutPercent = $penerimaan->kg_dta > 0
            ? (($penerimaan->kg_dta - $data['kg_netto']) / $penerimaan->kg_dta) * 100
            : 0;

        $penerimaan->update([
            'kg_netto'       => $data['kg_netto'],
            'ekor_netto'     => $data['ekor_netto'],
            'ayam_mati'      => $data['ayam_mati'],
            'susut_percent'  => round($susutPercent, 2),
            'status'         => 'Baru',
            'kg_undersize'   => $data['kg_undersize'],
            'ekor_undersize' => $data['ekor_undersize'],
            'berat_reject'   => $data['kg_undersize'] + ($data['ayam_mati'] * ($data['ekor_netto'] > 0 ? $data['kg_netto'] / $data['ekor_netto'] : 0)),
            'kg_rphu'        => $data['kg_rphu'],
            'kg_basah'       => $data['kg_basah'],
            'no_sppa'        => isset($data['no_sppa']) && $data['no_sppa'] !== '' ? strtoupper(trim($data['no_sppa'])) : null,
            'keterangan'     => $data['keterangan'] ?? null,
        ]);

        // Sinkronkan Aktual Ekor/Kg di PPIC Planning vs Aktual.
        $this->syncAktualPlanning($request, $penerimaan->tanggal->format('Y-m-d'));

        ActivityLogger::log(
            'report_lb',
            'update',
            "{$request->user('tally')->employee_code} ({$request->user('tally')->name}) input data Setelah Bongkar Rit {$noRit} (PO {$penerimaan->no_po})",
            $request->user('tally')
        );

        return response()->json(['status' => 'success', 'message' => 'Data berhasil di-update!']);
    }

    /**
     * Menggantikan getDetailHangingLengkap() di code.gs. Dipakai popup
     * "Lihat Rincian Hanging" di form Setelah Bongkar.
     *
     * DIUBAH: menerima no_po, tolak kalau ambigu.
     */
    public function detailHangingLengkap(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');
        $noRit = strtoupper(trim($request->query('no_rit', '')));
        $noPo = strtoupper(trim($request->query('no_po', '')));

        $rows = $this->queryHanging($tanggal, $noRit, $noPo)->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'message' => 'Data Hanging belum disimpan/diinput untuk Rit tersebut!',
            ], 404);
        }

        if ($rows->count() > 1) {
            return response()->json([
                'message' => "Rit '{$noRit}' ada di beberapa PO pada tanggal {$tanggal} ({$rows->pluck('no_po')->implode(', ')}). Isi No PO dulu.",
            ], 422);
        }

        $hanging = $rows->first();

        $queryPenerimaan = LbPenerimaan::where('tanggal', $tanggal)->where('no_rit', $noRit);
        if (! empty($hanging->no_po)) {
            $queryPenerimaan->where('no_po', $hanging->no_po);
        }
        $penerimaan = $queryPenerimaan->first();

        return response()->json([
            'tanggal'       => $tanggal,
            'noRit'         => $noRit,
            'jamBongkar'    => $hanging->jam_bongkar ?: '-',
            'jamSelesai'    => $hanging->jam_selesai ?: '-',
            'totalDiterima' => $hanging->total_diterima,
            'totalSJ'       => $hanging->total_sj,
            'totalKosong'   => $hanging->total_kosong,
            'gridData'      => $hanging->grid_json ?? [],
            'noPo'          => $hanging->no_po ?: ($penerimaan->no_po ?? ''),
            'namaTally'     => $hanging->nama_tally ?: '',
            'namaForeman'   => $hanging->nama_foreman ?: '',
            'farm'          => $penerimaan->farm ?? '-',
            'ekorDTA'       => $penerimaan->ekor_dta ?? 0,
            'ekspedisi'     => $penerimaan->ekspedisi ?? '-',
        ]);
    }

    /**
     * Menggantikan ambilDataRitase() di code.gs. Dipakai di halaman Hanging
     * untuk cari data rit sebelum mulai input grid.
     *
     * DIUBAH: kalau no_po kosong dan rit hari ini ada di lebih dari satu PO,
     * minta tally mengisi No PO (tidak lagi diam-diam ambil yang pertama).
     */
    public function ritase(Request $request): JsonResponse
    {
        $noRit = strtoupper(trim($request->query('no_rit', '')));
        $noPo = strtoupper(trim($request->query('no_po', '')));

        $query = LbPenerimaan::where('no_rit', $noRit);

        if ($noPo !== '') {
            $query->where('no_po', $noPo);
        } else {
            $query->where('tanggal', now()->format('Y-m-d'));
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            $pesan = $noPo !== ''
                ? "Rit '{$noRit}' tidak ditemukan dalam PO '{$noPo}'!"
                : "No Rit '{$noRit}' tidak ada di jadwal HARI INI! (Gunakan No PO jika data beda hari)";

            return response()->json(['status' => 'NOT_FOUND', 'message' => $pesan]);
        }

        if ($matches->count() > 1) {
            $daftarPo = $matches->pluck('no_po')->implode(', ');

            return response()->json([
                'status'  => 'NOT_FOUND',
                'message' => "Rit '{$noRit}' ada di beberapa PO ({$daftarPo}). Isi kolom No PO terlebih dahulu.",
            ]);
        }

        $penerimaan = $matches->first();

        $result = [
            'status'            => 'SUCCESS',
            'farm'              => $penerimaan->farm ?: '-',
            'size'              => $penerimaan->size ?: '-',
            'ekorSJ'            => (int) $penerimaan->ekor_dta,
            'kgSJ'              => (float) $penerimaan->kg_dta,
            'tanggalPenerimaan' => $penerimaan->tanggal->format('Y-m-d'),
            'noPo'              => $penerimaan->no_po ?: '',
        ];

        $hanging = $this->queryHanging(
            $result['tanggalPenerimaan'],
            $noRit,
            (string) $penerimaan->no_po
        )->first();

        if ($hanging) {
            $result['sudahAdaData'] = true;
            $result['isEdit'] = true;
            $result['dataHanging'] = [
                'jamBongkar'  => $hanging->jam_bongkar,
                'jamSelesai'  => $hanging->jam_selesai,
                'grid'        => $hanging->grid_json ?? [],
                'namaTally'   => $hanging->nama_tally,
                'namaForeman' => $hanging->nama_foreman,
            ];
        }

        return response()->json($result);
    }

    /**
     * Menggantikan simpanDataHanging() di code.gs. Role: lb_hanging (TLB).
     *
     * DIUBAH: no_po wajib dan jadi bagian kunci updateOrCreate, sehingga
     * hanging RIT-01 PO 666 tidak menimpa RIT-01 PO 665.
     */
    public function storeHanging(Request $request): JsonResponse
    {
        $this->authorizeRole($request, ['lb_hanging', 'supervisor']);

        $data = $request->validate([
            'no_rit'              => ['required', 'string'],
            'jam_bongkar'         => ['required'],
            'jam_selesai'         => ['required'],
            'total_sj'            => ['required', 'integer', 'min:0'],
            'total_diterima'      => ['required', 'integer', 'min:0'],
            'total_kosong'        => ['required', 'integer', 'min:0'],
            'grid'                => ['required', 'array'],
            'tanggal_penerimaan'  => ['required', 'date'],
            'no_po'               => ['required', 'string'],
            'nama_tally'          => ['required', 'string'],
            'nama_foreman'        => ['required', 'string'],
        ]);

        $noRit = strtoupper(trim($data['no_rit']));
        $noPo = strtoupper(trim($data['no_po']));
        $status = $data['total_diterima'] >= $data['total_sj'] ? 'OVER/PAS' : 'KURANG';

        $hanging = LbHanging::updateOrCreate(
            [
                'tanggal_penerimaan' => $data['tanggal_penerimaan'],
                'no_po'              => $noPo,
                'no_rit'             => $noRit,
            ],
            [
                'jam_bongkar'    => $data['jam_bongkar'],
                'jam_selesai'    => $data['jam_selesai'],
                'total_diterima' => $data['total_diterima'],
                'total_sj'       => $data['total_sj'],
                'total_kosong'   => $data['total_kosong'],
                'status'         => $status,
                'grid_json'      => $data['grid'],
                'nama_tally'     => $data['nama_tally'],
                'nama_foreman'   => strtoupper(trim($data['nama_foreman'])),
            ]
        );

        ActivityLogger::log(
            'report_lb',
            $hanging->wasRecentlyCreated ? 'create' : 'update',
            "{$request->user('tally')->employee_code} ({$request->user('tally')->name}) input data Hanging Rit {$noRit} (PO {$noPo})",
            $request->user('tally')
        );

        return response()->json([
            'status'  => $hanging->wasRecentlyCreated ? 'SUCCESS_INSERT' : 'SUCCESS_UPDATE',
            'message' => $hanging->wasRecentlyCreated ? 'Data Hanging Berhasil Disimpan!' : 'Perubahan Data Hanging berhasil disimpan!',
        ]);
    }

    private function authorizeRole(Request $request, array $roles): void
    {
        if (! $request->user('tally')->hasAnyRole($roles)) {
            abort(403, 'Anda tidak memiliki akses untuk aksi ini.');
        }
    }

    /**
     * BARU - Query dasar LbHanging berdasarkan tanggal + no_rit, dan
     * (opsional) no_po. Kalau $noPo kosong, filter PO tidak dipakai
     * sehingga pemanggil harus mengecek apakah hasilnya lebih dari 1.
     */
    private function queryHanging(?string $tanggal, string $noRit, string $noPo = '')
    {
        $q = LbHanging::where('tanggal_penerimaan', $tanggal)
            ->where('no_rit', $noRit);

        if ($noPo !== '') {
            $q->where('no_po', $noPo);
        }

        return $q;
    }

    /**
     * Sinkronisasi Aktual Ekor/Kg di PpicPlan untuk 1 tanggal, dipanggil
     * setiap kali data Setelah Bongkar disimpan/diubah lewat storeSetelah().
     */
    private function syncAktualPlanning(Request $request, string $tanggal): void
    {
        $sudahAda = PpicPlan::where('tanggal', $tanggal)->exists();

        PpicPlan::updateOrCreate(
            ['tanggal' => $tanggal],
            array_merge(
                PpicPlan::recalculateAktual($tanggal),
                $sudahAda ? [] : ['user_id' => $request->user('tally')->id]
            )
        );
    }

    private function emptyRekap(): array
    {
        return [
            'kgNetto'     => 0,
            'ekorNetto'   => 0,
            'mati'        => 0,
            'persenSusut' => 0,
            'totalKgDta'  => 0,
            'rincianRit'  => [],
            'area'        => [
                '1' => ['kgDta' => 0, 'kgNet' => 0, 'persen' => 0],
                '2' => ['kgDta' => 0, 'kgNet' => 0, 'persen' => 0],
                '3' => ['kgDta' => 0, 'kgNet' => 0, 'persen' => 0],
                '4' => ['kgDta' => 0, 'kgNet' => 0, 'persen' => 0],
            ],
            'hasUpdate' => false,
        ];
    }
}