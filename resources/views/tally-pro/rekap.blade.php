@extends('tally-pro.layout')

@section('title', 'Rekap Hasil Produksi')

@push('styles')
<style>
    html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }

    .rekap-layer-wrapper {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 7pt;
        background: #333;
        padding: 15px;
        border-radius: 8px;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    .control-panel {
        display: table; width: 100%; background: white; padding: 15px; border-radius: 8px;
        margin: 0 auto 15px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3); box-sizing: border-box;
    }

    .step-box { display: table-cell; width: 50%; padding: 10px; border: 1px solid #eee; border-radius: 6px; vertical-align: top; }
    .step-active { border-left: 4px solid #4f46e5; background: #f0f4ff; }

    /*
     * Font & ukuran dasar didefinisikan LANGSUNG di .page-print-area
     * (bukan diwarisi dari .rekap-layer-wrapper), karena saat dicetak
     * elemen ini disalin ke luar wrapper.
     * --fit dipakai auto-fit: 1 = ukuran normal, <1 = diperkecil.
     */
    .page-print-area {
        --fit: 1;
        width: 287mm;
        height: 195mm;
        padding: 8mm;
        margin: 0 auto;
        background: white;
        color: #000;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 7pt;
        line-height: normal;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    .main-layout-table { width: 100%; border-collapse: collapse; border: none !important; margin-top: 5px; }
    .main-layout-table > tbody > tr > td { border: none !important; vertical-align: top; padding: 0; }

    .left-data-column { width: 73%; padding-right: 8px; }
    .right-approval-column { width: 27%; border-left: 1px dashed #999 !important; padding-left: 12px; }

    .page-print-area table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
    .page-print-area table.data-table, .page-print-area table.data-table th, .page-print-area table.data-table td { border: 1px solid black; }
    .page-print-area th, .page-print-area td {
        padding: calc(1.5px * var(--fit)) 2px;
        text-align: center;
        font-size: calc(5.5pt * var(--fit));
        color: #000;
    }
    .bg-gray { background-color: #e2e2e2 !important; font-weight: bold; }
    .title { font-size: 10pt; font-weight: bold; text-align: center; }

    .approval-block {
        width: 100%;
        border: 1px solid #000 !important;
        border-collapse: collapse;
        margin-top: 5px;
    }
    .approval-block th { background: #f2f2f2; font-size: 7pt; font-weight: bold; padding: 5px; border: 1px solid #000 !important; }
    .approval-block td { border: 1px solid #000 !important; padding: 8px 4px; height: 62px; vertical-align: top; font-size: 6.5pt; text-align: center; }

    .btn-rekap { padding: 8px 12px; cursor: pointer; border: none; border-radius: 4px; font-weight: bold; width: 100%; margin-top: 5px; text-align: center;}
    .btn-rekap:disabled { opacity: .6; cursor: wait; }
    .btn-main { background: #4f46e5; color: white; }
    .btn-sec { background: #d32f2f; color: white; }
    .control-panel input { width: 95%; padding: 5px; margin: 3px 0; box-sizing: border-box; font-size: 13px; }

    .badge { padding: 2px 8px; border-radius: 4px; background: #ccc; font-size: 10px; font-weight: bold; color: #333; }
    .badge.on { background: #22c55e; color: white; }
    .col-desc { text-align: left !important; padding-left: 4px !important; }
</style>
@endpush

@section('content')
<div class="rekap-layer-wrapper">

    <div class="control-panel">
        <div class="step-box">
            <h4 style="margin:0 0 10px 0; color: #2c3e50;">1. Data Produksi</h4>
            <label style="font-size: 14px; color: #555;">Import Excel Konsolidasi:</label>
            <input type="file" id="fileInputRekap" accept=".xlsx, .xls">
            <button class="btn-rekap btn-sec" id="btnGenerateRekap" onclick="generateAndLoadDraft()" type="button">Generate & Load 1 Halaman PDF</button>
        </div>

        <div id="approvalBoxRekap" class="step-box">
            <h4 style="margin:0 0 10px 0; color: #2c3e50;">2. Digital Approval (Sisi Kanan Kertas)</h4>
            <div style="display:flex; justify-content: space-between; margin-bottom:5px;">
                <span id="badgeTallyRekap" class="badge">Tally ✘</span>
                <span id="badgeAppRekap" class="badge">Foreman ✘</span>
            </div>
            <div style="display:flex; gap:5px;">
                <input type="text" id="userIdRekap" placeholder="ID Pengguna" style="width:48%; display:inline-block;">
                <input type="password" id="passwordRekap" placeholder="Password" style="width:48%; display:inline-block;">
            </div>
            <button class="btn-rekap btn-main" id="btnApprovalRekap" onclick="prosesApprovalRekap()" type="button">Sahkan & Tanda Tangani</button>
        </div>
    </div>

    <div class="page-print-area" id="reportAreaPrint">

        <table class="data-table" style="margin-bottom: 5px;">
    <tr>
        <td rowspan="4" width="12%" style="text-align:center; vertical-align: middle; font-weight: bold; font-size: 14pt; background: #fafafa;">
          <img src="{{ asset('images/logo.png') }}" alt="CPI LOGO" style="max-width: 100%; max-height: 45px; object-fit: contain;">
        </td>
        <td rowspan="4" class="title">FORM HASIL PRODUKSI KARKAS</td>
        <td width="10%">No Dokumen</td><td width="15%">: FM-PROD-005</td>
    </tr>
    <tr><td>Tanggal Efektif</td><td>: 17 Jan 2024</td></tr>
    <tr><td>Revisi / Tgl</td><td>: 01 / 30 Okt 2025</td></tr>
</table>

        <input type="date" id="inputDateRekap" style="font-family: inherit; font-weight: bold; border:none; margin-bottom: 5px; width: auto; font-size: 11pt; background: transparent; color: black;">
        <select id="shiftRekap" style="font-family: inherit; font-weight: bold; border:none; margin-bottom: 5px; margin-left: 15px; font-size: 11pt; background: transparent; color: black;">
    <option value="1">Shift 1</option>
    <option value="2">Shift 2</option>
</select>
        <table class="main-layout-table">
            <tr>
                <td class="left-data-column">
                    <div style="display: table; width: 100%; margin-bottom: 4px;">
                        @foreach (['kw1' => '33%', 'kw2' => '34%', 'bahan_baku' => '33%'] as $cat => $width)
                            <div style="display: table-cell; width: {{ $width }}; padding-right: 4px;">
                                <table id="table_{{ $cat }}" class="data-table">
                                    <tr class="bg-gray"><td colspan="5">{{ $categoryLabels[$cat] }}</td></tr>
                                    <tr class="bg-gray"><th>NO</th><th>KODE</th><th>DESCRIPTION</th><th>EKOR</th><th>KG</th></tr>
                                    @foreach ($productsByCategory->get($cat, collect()) as $i => $p)
                                        <tr data-rekap-kode="{{ $p['code'] }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $p['code'] }}</td>
                                            <td class="col-desc">{{ $p['name'] }}</td>
                                            <td class="r-v-e">0</td>
                                            <td class="r-v-k">0</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray"><td colspan="3">TOTAL</td><td id="table_{{ $cat }}-rE">0</td><td id="table_{{ $cat }}-rK">0</td></tr>
                                </table>
                            </div>
                        @endforeach
                    </div>

                    <div style="display: table; width: 100%;">
                        @foreach (['parting' => '50%', 'by_product' => '50%'] as $cat => $width)
                            <div style="display: table-cell; width: {{ $width }}; padding-right: 4px;">
                                <table id="table_{{ $cat }}" class="data-table">
                                    <tr class="bg-gray"><td colspan="5">{{ $categoryLabels[$cat] }}</td></tr>
                                    <tr class="bg-gray"><th>NO</th><th>KODE</th><th>DESCRIPTION</th><th>EKOR</th><th>KG</th></tr>
                                    @foreach ($productsByCategory->get($cat, collect()) as $i => $p)
                                        <tr data-rekap-kode="{{ $p['code'] }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $p['code'] }}</td>
                                            <td class="col-desc">{{ $p['name'] }}</td>
                                            <td class="r-v-e">0</td>
                                            <td class="r-v-k">0</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray"><td colspan="3">TOTAL</td><td id="table_{{ $cat }}-rE">0</td><td id="table_{{ $cat }}-rK">0</td></tr>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </td>

                <td class="right-approval-column">
                    <div style="font-weight: bold; margin-bottom: 5px; text-align: center; background: #e2e2e2; padding: 3px; border: 1px solid #000; font-size: 6.5pt;">
                         DIGITAL APPROVED
                    </div>

                    <table class="approval-block">
                        <tr><th>Dibuat Oleh: Tally</th></tr>
                        <tr>
                            <td id="zoneTally">
                                <span style="color:#777; font-style:italic;"><br>[Menunggu Tanda Tangan Tally]</span>
                            </td>
                        </tr>
                        <tr class="bg-gray"><td>( <span id="txtNamaTally">____________________</span> )</td></tr>
                    </table>

                    <div style="height: 10px;"></div>

                    <table class="approval-block">
                        <tr><th>Diperiksa Oleh: Foreman / Forelady</th></tr>
                        <tr>
                            <td id="zoneForeman">
                                <span style="color:#777; font-style:italic;"><br>[Menunggu Otorisasi Foreman]</span>
                            </td>
                        </tr>
                        <tr class="bg-gray"><td>( <span id="txtNamaForeman">____________________</span> )</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bwip-js/dist/bwip-js-min.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let currentPdfBlobRekap = null;
    let statusApprovalRekap = { tally: false, approver: false };
    let isBusyRekap = false;

    document.getElementById('inputDateRekap').value = new Date().toISOString().split('T')[0];

    // ------------------------------------------------------------------
    // KONSTANTA RENDER PDF (tetap, tidak bergantung perangkat)
    // ------------------------------------------------------------------
    const PX_PER_MM        = 96 / 25.4;
    const REPORT_WIDTH_MM  = 287;
    const REPORT_HEIGHT_MM = 195;
    const REPORT_MAX_H_PX  = Math.floor(REPORT_HEIGHT_MM * PX_PER_MM);
    const CANVAS_SCALE     = 2;

    // --- IMPORT EXCEL ---
    document.getElementById('fileInputRekap').addEventListener('change', function(e) {
        if (!e.target.files[0]) return;
        const reader = new FileReader();
        reader.onload = (event) => {
            const wb = XLSX.read(new Uint8Array(event.target.result), { type: 'array' });
            const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1 });
            rows.forEach(r => {
                const target = document.querySelector(`tr[data-rekap-kode="${r[0]}"]`);
                if (target) {
                    target.querySelector('.r-v-e').innerText = r[2] || 0;
                    target.querySelector('.r-v-k').innerText = r[3] || 0;
                }
            });

            updateTotalsRekap();
        };
        reader.readAsArrayBuffer(e.target.files[0]);
    });

    function updateTotalsRekap() {
        ['table_kw1', 'table_kw2', 'table_bahan_baku', 'table_parting', 'table_by_product'].forEach(id => {
            const table = document.getElementById(id);
            if (!table) return;
            let te = 0, tk = 0;
            table.querySelectorAll('.r-v-e').forEach(td => te += parseFloat(td.innerText) || 0);
            table.querySelectorAll('.r-v-k').forEach(td => tk += parseFloat(td.innerText) || 0);
            document.getElementById(`${id}-rE`).innerText = te;
            document.getElementById(`${id}-rK`).innerText = tk.toFixed(2);
        });
    }

    // ------------------------------------------------------------------
    // PEMBUAT PDF TUNGGAL
    // Dipakai oleh Generate Draft maupun Approval.
    //
    // Cara kerja:
    //  1. Salin (clone) laporan ke kontainer tetap berukuran 287mm,
    //     sehingga layout tidak bergantung ukuran jendela / zoom / scroll.
    //  2. Input tanggal & shift diganti teks biasa (hasil sama di semua
    //     browser, termasuk tablet).
    //  3. Tunggu font & gambar siap.
    //  4. Auto-fit: kalau tinggi melebihi 195mm, ukuran font & padding
    //     diperkecil bertahap sampai muat (baris TOTAL tidak terpotong).
    //  5. Render html2canvas dengan opsi tetap.
    // ------------------------------------------------------------------
    function waitForAssets(root) {
        const fontsReady = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
        const imgs = Array.from(root.querySelectorAll('img')).map(img => {
            if (img.complete && img.naturalWidth > 0) return Promise.resolve();
            return new Promise(res => { img.onload = img.onerror = () => res(); });
        });
        return Promise.all([fontsReady, ...imgs]);
    }

    function autoFitRekap(clone) {
        let fit = 1;
        clone.style.setProperty('--fit', fit);
        let guard = 0;
        while (clone.offsetHeight > REPORT_MAX_H_PX && fit > 0.6 && guard < 40) {
            fit = Math.round((fit - 0.02) * 100) / 100;
            clone.style.setProperty('--fit', fit);
            guard++;
        }
        return fit;
    }

    async function buildPdfBlobRekap() {
        const source = document.getElementById('reportAreaPrint');

        const dateVal  = document.getElementById('inputDateRekap').value;
        const shiftSel = document.getElementById('shiftRekap');
        const shiftTxt = shiftSel.options[shiftSel.selectedIndex].text;

        let dateTxt = dateVal;
        if (dateVal) {
            const d = new Date(dateVal + 'T00:00:00');
            dateTxt = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        }

        const wrapper = document.createElement('div');
        wrapper.style.cssText =
            `position:fixed; left:0; top:0; width:${REPORT_WIDTH_MM}mm; z-index:-1; ` +
            `opacity:0; pointer-events:none; background:#fff;`;

        const clone = source.cloneNode(true);

        // Ganti input/select dengan teks (sebelum id dihapus)
        const mkSpan = (text, extra) => {
            const s = document.createElement('span');
            s.textContent = text;
            s.style.cssText = 'font-family: Arial, Helvetica, sans-serif; font-weight:bold; font-size:11pt; color:#000; display:inline-block; margin-bottom:5px;' + (extra || '');
            return s;
        };
        clone.querySelector('#inputDateRekap').replaceWith(mkSpan(dateTxt));
        clone.querySelector('#shiftRekap').replaceWith(mkSpan(shiftTxt, 'margin-left:15px;'));

        // Hindari id ganda dengan elemen asli
        clone.removeAttribute('id');
        clone.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));

        // Ukuran tetap, tinggi mengikuti isi (minimal 195mm)
        clone.style.width     = REPORT_WIDTH_MM + 'mm';
        clone.style.height    = 'auto';
        clone.style.minHeight = REPORT_HEIGHT_MM + 'mm';
        clone.style.margin    = '0';
        clone.style.boxShadow = 'none';

        wrapper.appendChild(clone);
        document.body.appendChild(wrapper);

        try {
            await waitForAssets(clone);
            autoFitRekap(clone);

            const opt = {
                margin: 0,
                filename: 'rekap.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: CANVAS_SCALE,
                    useCORS: true,
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    x: 0,
                    y: 0,
                    windowWidth: 1280,
                    windowHeight: 900,
                    backgroundColor: '#ffffff'
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            return await html2pdf().set(opt).from(clone).outputPdf('blob');
        } finally {
            wrapper.remove();
        }
    }

    function setBusyRekap(state) {
        isBusyRekap = state;
        document.getElementById('btnGenerateRekap').disabled = state;
        document.getElementById('btnApprovalRekap').disabled = state;
    }

    function downloadBlobRekap(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        setTimeout(() => {
            link.remove();
            URL.revokeObjectURL(url);
        }, 2000);
    }

    // --- GENERATE DRAFT PDF ---
    async function generateAndLoadDraft() {
        if (isBusyRekap) return;
        setBusyRekap(true);
        try {
            currentPdfBlobRekap = await buildPdfBlobRekap();
            document.getElementById('approvalBoxRekap').classList.add('step-active');
            alert("Draft Berhasil Disatukan Jadi 1 Halaman! Silakan lanjut proses Tanda Tangan.");
        } catch (err) {
            console.error(err);
            alert("Gagal membuat draft PDF, coba lagi.");
        } finally {
            setBusyRekap(false);
        }
    }

    // --- PROSES APPROVAL (validasi ke server) ---
    async function prosesApprovalRekap() {
        if (isBusyRekap) return;
        if (!currentPdfBlobRekap) return alert("Silakan muat Draft Laporan terlebih dahulu pada Langkah 1!");

        const idVal = document.getElementById('userIdRekap').value;
        const passVal = document.getElementById('passwordRekap').value;

        setBusyRekap(true);
        try {
            let result;
            try {
                const response = await fetch('{{ route('tally.rekap.verify-signature') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ employee_code: idVal, password: passVal }),
                });
                result = await response.json();
            } catch (err) {
                return alert("Gagal menghubungi server, coba lagi.");
            }

            if (!result.valid) {
                return alert(result.message || "ID Pengguna atau Password salah!");
            }

            const isTally = result.role === 'tally';
            if (isTally && statusApprovalRekap.tally) return alert("Tally sudah membubuhkan tanda tangan!");
            if (!isTally && statusApprovalRekap.approver) return alert("Foreman sudah membubuhkan tanda tangan!");

            const canvas = document.createElement('canvas');
            bwipjs.toCanvas(canvas, {
                bcid: 'qrcode',
                text: `APPROVED BY CPI JOMBANG\nJABATAN: ${isTally ? 'TALLY' : 'FOREMAN'}\nNAMA: ${result.name}\nID: ${idVal}\nDATE: ${new Date().toLocaleString('id-ID')}`,
                scale: 2
            });

            const qrDataUrl = canvas.toDataURL();

            if (isTally) {
                statusApprovalRekap.tally = true;
                document.getElementById('zoneTally').innerHTML = `<img src="${qrDataUrl}" style="width:55px; height:55px; margin-top:2px;">`;
                document.getElementById('txtNamaTally').innerText = result.name;
                document.getElementById('badgeTallyRekap').className = "badge on";
                document.getElementById('badgeTallyRekap').innerText = "Tally ✓";
            } else {
                statusApprovalRekap.approver = true;
                document.getElementById('zoneForeman').innerHTML = `<img src="${qrDataUrl}" style="width:55px; height:55px; margin-top:2px;">`;
                document.getElementById('txtNamaForeman').innerText = result.name;
                document.getElementById('badgeAppRekap').className = "badge on";
                document.getElementById('badgeAppRekap').innerText = "Foreman ✓";
            }

            currentPdfBlobRekap = await buildPdfBlobRekap();

            if (statusApprovalRekap.tally && statusApprovalRekap.approver) {
                const tgl = document.getElementById('inputDateRekap').value;
                downloadBlobRekap(currentPdfBlobRekap, `FINAL_REPORT_1HALAMAN_${tgl}.pdf`);
                alert("LENGKAP! Dokumen 1 Halaman Konsolidasi & QR Code Tanda Tangan Berhasil Diunduh.");
            } else {
                alert(`Tanda tangan digital ${result.name} berhasil dimasukkan ke kolom kanan.`);
            }

            document.getElementById('userIdRekap').value = "";
            document.getElementById('passwordRekap').value = "";
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan saat memproses tanda tangan, coba lagi.");
        } finally {
            setBusyRekap(false);
        }
    }
</script>
@endpush