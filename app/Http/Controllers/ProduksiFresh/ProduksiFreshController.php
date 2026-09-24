<?php

namespace App\Http\Controllers\ProduksiFresh;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProduksiFresh;
use App\Models\PurchaseOrder;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf as PdfWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProduksiFreshController extends Controller
{
    /**
     * Metadata dokumen resmi FM-PROD-018 "PRODUCT FRESH" - statis,
     * sesuai kop pada template kosongan yang dipakai bagian Produksi.
     * Kalau nomor dokumen/revisi berubah di masa depan, cukup ubah di sini.
     */
    private const DOC_TITLE = 'PRODUCT FRESH';
    private const DOC_NUMBER = 'FM-PROD-018';
    private const DOC_EFFECTIVE_DATE = '01 September 2026';
    private const DOC_REVISION = '0.0/ -';
    private const COMPANY_LABEL = 'JOMBANG - INDONESIA';

    public function workspace(Request $request): View
    {
        return view('produksi-fresh.workspace', [
            'tipeInput' => $request->session()->get('produksi_fresh_tipe'),
        ]);
    }

    /**
     * Daftar semua PO dari PPIC untuk dropdown Nomor PO.
     *
     * DIUBAH: PO yang sudah TECO (Technically Complete, ditandai PPIC)
     * dikeluarkan dari daftar - sama seperti LbReportController, PO yang
     * sudah ditutup PPIC tidak boleh lagi dipilih untuk input baru.
     * Dashboard Produksi Bulanan SENGAJA TIDAK ikut difilter (keputusan
     * bisnis: TECO cuma mempengaruhi LB Report & Produksi Fresh).
     */
    public function listPurchaseOrders(): JsonResponse
    {
        $list = PurchaseOrder::whereNull('teco_at')
            ->orderByDesc('tanggal')
            ->get(['nomor_po', 'jenis_po', 'tanggal'])
            ->map(fn (PurchaseOrder $po) => [
                'nomorPo' => $po->nomor_po,
                'jenisPo' => $po->jenis_po,
                'tanggal' => $po->tanggal->format('d/m/Y'),
            ]);

        return response()->json($list);
    }

    /**
     * Daftar produk sesuai tipe yang dipilih SAAT LOGIN (dibaca dari
     * session, BUKAN dari query string) - mencegah user mengakali
     * filter dengan mengirim tipe lain lewat request langsung.
     */
    public function listProducts(Request $request): JsonResponse
    {
        $tipe = $request->session()->get('produksi_fresh_tipe');

        $query = Product::active();
        $query = $tipe === 'main' ? $query->main() : $query->byProduct();

        $list = $query->orderBy('display_order')
            ->get(['id', 'code', 'name', 'category_code'])
            ->map(fn (Product $p) => [
                'id'           => $p->id,
                'code'         => $p->code,
                'name'         => $p->name,
                'categoryCode' => $p->category_code,
            ]);

        return response()->json($list);
    }

    /**
     * Terima banyak baris draft sekaligus (pola "tambah ke draft, submit
     * semua" dari Apps Script lama), insert dalam SATU transaksi - kalau
     * ada 1 baris gagal validasi, semua baris dibatalkan (tidak ada yang
     * tersimpan sebagian).
     *
     * Kode Produksi DIHITUNG ULANG DI SINI (server), tidak dipercaya
     * dari nilai yang mungkin ditampilkan di form client.
     */
    public function store(Request $request): JsonResponse
    {
        $tipe = $request->session()->get('produksi_fresh_tipe');
        if (! $tipe) {
            abort(403, 'Sesi tidak valid, silakan login ulang.');
        }

        $data = $request->validate([
            'rows'               => ['required', 'array', 'min:1'],
            'rows.*.no_po'       => ['required', 'string', 'exists:purchase_orders,nomor_po'],
            'rows.*.kode_produk' => ['required', 'string'],
            'rows.*.qty'         => ['required', 'numeric', 'min:0.01'],
        ]);

        $user = $request->user('tally');

        $inserted = DB::transaction(function () use ($data, $tipe, $user) {
            $rows = [];

            foreach ($data['rows'] as $row) {
                $product = Product::byCode($row['kode_produk'])->active()->first();

                if (! $product) {
                    abort(422, "Kode produk '{$row['kode_produk']}' tidak ditemukan atau tidak aktif.");
                }

                if ($product->type !== $tipe) {
                    $labelTipe = $tipe === 'main' ? 'Main Product' : 'By Product';
                    abort(422, "Kode produk '{$row['kode_produk']}' bukan produk tipe {$labelTipe}.");
                }

                $kodeProduksi = ProduksiFresh::generateKodeProduksi($product->category_code ?? '00');

                $rows[] = ProduksiFresh::create([
                    'no_po'         => strtoupper(trim($row['no_po'])),
                    'user_id'       => $user->id,
                    'tipe_input'    => $tipe,
                    'produk_id'     => $product->id,
                    'kode_produksi' => $kodeProduksi,
                    'qty'           => $row['qty'],
                ]);
            }

            return $rows;
        });

        $noPoList = collect($data['rows'])->pluck('no_po')->unique()->implode(', ');

        ActivityLogger::log(
            'produksi_fresh',
            'create',
            "{$user->employee_code} ({$user->name}) input ".count($inserted)." data produksi Fresh (tipe: {$tipe}, PO: {$noPoList})",
            $user
        );

        return response()->json([
            'success' => true,
            'message' => count($inserted).' data berhasil disimpan ke sistem.',
        ]);
    }

    /**
     * BARU - Riwayat data yang sudah tersimpan, difilter sesuai
     * tipe_input dari session (pola sama seperti listProducts()) -
     * user tidak bisa melihat data tipe lain lewat manipulasi query
     * string. Mendukung filter opsional no_po & rentang tanggal, plus
     * pagination.
     */
    public function history(Request $request): JsonResponse
    {
        $tipe = $request->session()->get('produksi_fresh_tipe');
        if (! $tipe) {
            abort(403, 'Sesi tidak valid, silakan login ulang.');
        }

        $query = ProduksiFresh::with(['product:id,code,name', 'user:id,name,employee_code'])
            ->where('tipe_input', $tipe);

        if ($request->filled('no_po')) {
            $query->where('no_po', 'like', '%'.strtoupper($request->string('no_po')).'%');
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->date('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->date('tanggal_sampai'));
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $paginated = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => collect($paginated->items())->map(fn (ProduksiFresh $p) => [
                'id'           => $p->id,
                'noPo'         => $p->no_po,
                'kodeProduk'   => $p->product->code ?? '-',
                'namaProduk'   => $p->product->name ?? '-',
                'kodeProduksi' => $p->kode_produksi,
                'qty'          => (float) $p->qty,
                'inputOleh'    => $p->user->name ?? '-',
                'tanggal'      => $p->created_at->format('d/m/Y H:i'),
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    /**
     * BARU - Cetak Form Resmi (FM-PROD-018) dari data HISTORI yang sudah
     * tersimpan (satu No PO per file, sesuai layout template kosongan
     * yang diberikan bagian Produksi).
     *
     * DIUBAH: sekarang mendukung 2 format output lewat parameter 'format'
     * ('xlsx' default, atau 'pdf'). Query histori dan penyusunan
     * spreadsheet (buildFormFreshSpreadsheet) SAMA PERSIS untuk kedua
     * format - hanya writer-nya (dan Content-Type/ekstensi) yang
     * bercabang di bagian bawah method ini. PDF di-render lewat Dompdf
     * (paket dompdf/dompdf, dipanggil PhpSpreadsheet via
     * Writer\Pdf\Dompdf).
     *
     * Tanda tangan "Dibuat Oleh" berupa QR code (dibuat di browser
     * pakai bwip-js, sama seperti pola halaman Rekap tally-pro),
     * dikirim sebagai PNG base64 dan ditempel sebagai gambar asli
     * di dalam file - bukan cuma teks nama.
     *
     * Verifikasi ID+password TIDAK dilakukan di sini (sudah dilakukan
     * di frontend lewat endpoint stateless RekapController::verifySignature
     * / route 'tally.rekap.verify-signature'). Controller ini percaya
     * 'signer_name' yang dikirim karena hanya dipanggil setelah verifikasi
     * itu sukses - tapi tetap divalidasi formatnya di bawah.
     */
    public function exportXlsx(Request $request): StreamedResponse
    {
        $tipe = $request->session()->get('produksi_fresh_tipe');
        if (! $tipe) {
            abort(403, 'Sesi tidak valid, silakan login ulang.');
        }

        $data = $request->validate([
            'no_po'        => ['required', 'string', 'max:100'],
            'shift'        => ['required', 'string', 'in:1,2'],
            'signer_name'  => ['required', 'string', 'max:150'],
            'qr_base64'    => ['required', 'string'],
            'format'       => ['sometimes', 'string', 'in:xlsx,pdf'],
        ]);

        $format = $data['format'] ?? 'xlsx';
        $noPo = strtoupper(trim($data['no_po']));

        $rows = ProduksiFresh::with('product:id,code,name')
            ->where('tipe_input', $tipe)
            ->where('no_po', $noPo)
            ->orderBy('created_at')
            ->get();

        if ($rows->isEmpty()) {
            abort(422, "Tidak ada data histori tersimpan untuk No PO '{$noPo}'.");
        }

        $qrPath = $this->saveBase64Image($data['qr_base64'], 'qr_fresh_');

        try {
            $spreadsheet = $this->buildFormFreshSpreadsheet(
                $noPo,
                $data['shift'],
                $tipe,
                $rows,
                $data['signer_name'],
                $qrPath
            );

            if ($format === 'pdf') {
                $writer = new PdfWriter($spreadsheet);
                $extension = 'pdf';
                $contentType = 'application/pdf';
            } else {
                $writer = new Xlsx($spreadsheet);
                $extension = 'xlsx';
                $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            }

            $filename = 'Form_Fresh_'.$noPo.'_'.now()->format('Ymd_His').'.'.$extension;

            $user = $request->user('tally');
            ActivityLogger::log(
                'produksi_fresh',
                'export',
                "{$user->employee_code} ({$user->name}) cetak Form Resmi ".strtoupper($extension)." (No PO: {$noPo}, Shift: {$data['shift']}, ditandatangani: {$data['signer_name']})",
                $user
            );

            // PENTING: closure di streamDownload() baru benar-benar dieksekusi
            // Laravel SETELAH method ini return (bukan langsung saat ditulis).
            // Karena itu, hapus file temp QR harus dilakukan DI DALAM closure
            // ini, setelah $writer->save() selesai membaca gambarnya - kalau
            // dihapus di luar (misal lewat finally di sekitar return), file
            // akan sudah hilang duluan sebelum writer benar-benar memakainya,
            // menyebabkan "File ... does not exist" dari PhpSpreadsheet.
            return response()->streamDownload(function () use ($writer, $qrPath) {
                $writer->save('php://output');

                if ($qrPath && file_exists($qrPath)) {
                    @unlink($qrPath);
                }
            }, $filename, [
                'Content-Type' => $contentType,
            ]);
        } catch (\Throwable $e) {
            // Kalau gagal SEBELUM sempat streaming (misal error nyusun
            // spreadsheet), tetap bersihkan file temp di sini.
            if ($qrPath && file_exists($qrPath)) {
                @unlink($qrPath);
            }

            throw $e;
        }
    }

    /**
     * Decode PNG base64 (dataURL dari canvas browser) ke file sementara.
     * PhpSpreadsheet\Worksheet\Drawing butuh path file fisik, tidak bisa
     * langsung menerima base64/binary string.
     */
    private function saveBase64Image(string $base64, string $prefix): string
    {
        $raw = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $binary = base64_decode($raw);

        if ($binary === false) {
            abort(422, 'Data QR code tidak valid.');
        }

        $path = tempnam(sys_get_temp_dir(), $prefix).'.png';
        file_put_contents($path, $binary);

        return $path;
    }

    /**
     * Susun workbook sesuai layout template kosongan FM-PROD-018:
     * kop (logo + judul + no dokumen), header No PO/Date/Shift/Tipe,
     * tabel item (Kode, Nama Produk, Kode Produksi, Qty Pack/Bag & Kg,
     * Keterangan), lalu kotak tanda tangan "Dibuat Oleh" + QR.
     *
     * Dipakai untuk KEDUA format output (Excel & PDF) - method ini
     * tidak tahu dan tidak peduli mau di-export jadi apa, cukup
     * menghasilkan objek Spreadsheet yang lengkap. setFitToPage() di
     * bagian akhir ditambahkan supaya saat di-convert ke PDF (lewat
     * Dompdf) kolom paling kanan (area tanda tangan+QR) tidak terpotong
     * ke halaman berikutnya; untuk Excel native ini tidak berpengaruh.
     */
    private function buildFormFreshSpreadsheet(
        string $noPo,
        string $shift,
        string $tipeInput,
        $rows,
        string $signerName,
        string $qrImagePath
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Form Fresh '.$noPo);

        // Lebar kolom - A dikosongkan sebagai margin kiri (sesuai template,
        // area ini yang dipakai logo perusahaan sebagai gambar mengambang).
        $sheet->getColumnDimension('A')->setWidth(3);
        foreach (['B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(11);
        }
        foreach (['G', 'H', 'I', 'J'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(9);
        }
        foreach (['K', 'L'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(11);
        }

        // ---------- KOP SURAT ----------
        $sheet->setCellValue('B2', self::COMPANY_LABEL);
        $sheet->mergeCells('B2:D5');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        $sheet->setCellValue('E2', self::DOC_TITLE);
        $sheet->mergeCells('E2:I5');
        $sheet->getStyle('E2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('J2', 'No. Dokumen');
        $sheet->mergeCells('J2:J3');
        $sheet->setCellValue('K2', ': '.self::DOC_NUMBER);
        $sheet->mergeCells('K2:L3');

        $sheet->setCellValue('J4', 'Tanggal Efektif');
        $sheet->setCellValue('K4', ': '.self::DOC_EFFECTIVE_DATE);
        $sheet->mergeCells('K4:L4');

        $sheet->setCellValue('J5', 'Revisi / Tgl');
        $sheet->setCellValue('K5', ': '.self::DOC_REVISION);
        $sheet->mergeCells('K5:L5');

        // Logo perusahaan - gambar statis dari server, ditaruh mengapung
        // di kolom A (margin kiri kop), tidak terikat text di dalam cell.
        $logoRealPath = public_path('images/logo.png');
        if (file_exists($logoRealPath)) {
            $logo = new Drawing();
            $logo->setPath($logoRealPath);
            $logo->setHeight(55);
            $logo->setCoordinates('A2');
            $logo->setOffsetX(2);
            $logo->setOffsetY(2);
            $logo->setWorksheet($sheet);
        }

        // ---------- HEADER: NO PO / DATE / SHIFT / TIPE PRODUK ----------
        $sheet->setCellValue('B7', 'No PO');
        $sheet->mergeCells('B7:C7');
        $sheet->setCellValue('D7', ':');
        $sheet->setCellValueExplicit('E7', $noPo, DataType::TYPE_STRING);
        $sheet->mergeCells('E7:I7');
        $sheet->getStyle('E7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('J7', 'Shift');
        $sheet->setCellValue('K7', ':');
        $sheet->setCellValueExplicit('L7', $shift, DataType::TYPE_STRING);
        $sheet->getStyle('L7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('B8', 'DATE');
        $sheet->mergeCells('B8:C8');
        $sheet->setCellValue('D8', ':');
        $sheet->setCellValue('E8', now()->translatedFormat('d F Y'));
        $sheet->mergeCells('E8:I8');

        $sheet->setCellValue('B9', 'TIPE PRODUK');
        $sheet->setCellValue('D9', ':');
        $labelTipe = $tipeInput === 'main' ? 'MAIN PRODUCT' : 'BY PRODUCT';
        $sheet->setCellValue('E9', $labelTipe);
        $sheet->mergeCells('E9:I9');

        // ---------- TABEL ITEM ----------
        $headerRow = 10;
        $subHeaderRow = 11;

        $sheet->setCellValue('B'.$headerRow, 'Kode');
        $sheet->mergeCells("B{$headerRow}:B{$subHeaderRow}");
        $sheet->setCellValue('C'.$headerRow, 'Nama Produk');
        $sheet->mergeCells("C{$headerRow}:D{$subHeaderRow}");
        $sheet->setCellValue('E'.$headerRow, 'Kode Produksi');
        $sheet->mergeCells("E{$headerRow}:F{$subHeaderRow}");
        $sheet->setCellValue('G'.$headerRow, 'Quantity');
        $sheet->mergeCells("G{$headerRow}:J{$headerRow}");
        $sheet->setCellValue('K'.$headerRow, 'Keterangan');
        $sheet->mergeCells("K{$headerRow}:L{$subHeaderRow}");

        $sheet->setCellValue('G'.$subHeaderRow, 'Pack/ Bag');
        $sheet->mergeCells("G{$subHeaderRow}:H{$subHeaderRow}");
        $sheet->setCellValue('I'.$subHeaderRow, 'Kg');
        $sheet->mergeCells("I{$subHeaderRow}:J{$subHeaderRow}");

        $headerRange = "B{$headerRow}:L{$subHeaderRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');

        // ---------- BARIS DATA ----------
        $dataStartRow = $subHeaderRow + 1;
        $row = $dataStartRow;

        foreach ($rows as $item) {
            $sheet->setCellValue('B'.$row, $item->product->code ?? '-');

            $sheet->setCellValue('C'.$row, $item->product->name ?? '-');
            $sheet->mergeCells("C{$row}:D{$row}");

            $sheet->setCellValue('E'.$row, $item->kode_produksi);
            $sheet->mergeCells("E{$row}:F{$row}");

            // Qty tersimpan cuma 1 nilai -> masuk kolom Kg, Pack/Bag
            // dikosongkan (diisi manual kalau memang dibutuhkan).
            $sheet->mergeCells("G{$row}:H{$row}");

            $sheet->setCellValue('I'.$row, (float) $item->qty);
            $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('0.0');
            $sheet->mergeCells("I{$row}:J{$row}");

            // Keterangan dikosongkan - diisi manual kalau perlu.
            $sheet->mergeCells("K{$row}:L{$row}");

            $row++;
        }

        $lastDataRow = $row - 1;
        $tableRange = "B{$headerRow}:L{$lastDataRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("B{$dataStartRow}:L{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("C{$dataStartRow}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // ---------- TANDA TANGAN "DIBUAT OLEH" + QR ----------
        $signatureLabelRow = $lastDataRow + 3;
        $signatureBoxRow = $signatureLabelRow + 1;
        $signatureNameRow = $signatureBoxRow + 6;

        $sheet->setCellValue('I'.$signatureLabelRow, 'Dibuat Oleh,');
        $sheet->mergeCells("I{$signatureLabelRow}:L{$signatureLabelRow}");
        $sheet->getStyle('I'.$signatureLabelRow)->getFont()->setBold(true);
        $sheet->getStyle('I'.$signatureLabelRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("I{$signatureBoxRow}:L".($signatureBoxRow + 5));
        $sheet->getStyle("I{$signatureBoxRow}:L".($signatureBoxRow + 5))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $qrSize = 85;
        $qr = new Drawing();
        $qr->setPath($qrImagePath);
        $qr->setHeight($qrSize);
        $qr->setWidth($qrSize);
        $qr->setCoordinates('I'.$signatureBoxRow);
        $qr->setOffsetX((int) ((328 - $qrSize) / 2) - 20);
        $qr->setOffsetY((int) ((120 - $qrSize) / 2) - 14);
        $qr->setWorksheet($sheet);

        $sheet->setCellValue('I'.$signatureNameRow, '( '.$signerName.' )');
        $sheet->mergeCells("I{$signatureNameRow}:L{$signatureNameRow}");
        $sheet->getStyle('I'.$signatureNameRow)->getFont()->setBold(true);
        $sheet->getStyle('I'.$signatureNameRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1:M'.$signatureNameRow)->getFont()->setName('Arial')->setSize(9);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        // BARU - fit-to-page: penting terutama untuk export PDF (Dompdf)
        // supaya kolom L (area tanda tangan+QR) tidak terpotong ke
        // halaman kedua. Tidak berdampak ke tampilan file Excel native.
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        return $spreadsheet;
    }
}