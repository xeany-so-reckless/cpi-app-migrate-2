<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Outbound - CPI App Migrate</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8f4ee; --surface: #ffffff; --surface-hover: #fbf6ee; --line: #ede2d3;
            --amber: #c2701a; --amber-dim: #9a5813; --amber-soft: #fbead6;
            --danger: #dc2626; --text: #16232e; --muted: #7a6f60;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); }
        .mono { font-family: 'JetBrains Mono', monospace; }

        nav {
            display: flex; justify-content: space-between; align-items: center; padding: 14px 5%;
            background: var(--surface); border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; color: var(--text); }
        .nav-link { color: var(--muted); text-decoration: none; font-size: 0.82rem; font-weight: 600; }
        .nav-link:hover { color: var(--amber-dim); }

        .page-header { padding: 30px 5% 18px; }
        .page-eyebrow { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--amber-dim); letter-spacing: 3px; text-transform: uppercase; margin-bottom: 8px; }
        .page-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 2.4rem; text-transform: uppercase; color: var(--text); }

        .btn { border: none; border-radius: 9px; padding: 9px 16px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .15s ease; }
        .btn-amber { background: var(--amber); color: #fff; }
        .btn-amber:hover { background: var(--amber-dim); }
        .btn-outline { background: #fff; color: var(--muted); border: 1px solid var(--line); }
        .btn-outline:hover { border-color: var(--amber); color: var(--amber-dim); }

        .container { padding: 0 5% 60px; max-width: 1200px; margin: 0 auto; }

        .filter-bar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 20px; }
        .filter-bar input, .filter-bar select {
            background: var(--surface); border: 1px solid var(--line); color: var(--text);
            border-radius: 8px; padding: 9px 12px; font-size: 0.85rem; height: 42px;
        }
        .filter-bar input:focus, .filter-bar select:focus { outline: none; border-color: var(--amber); }
        .filter-bar input[type="text"] { min-width: 200px; }

        .table-wrapper { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        thead th {
            background: #fbf8f2; color: var(--muted); font-family: 'JetBrains Mono', monospace; font-size: 0.65rem;
            letter-spacing: 1px; text-transform: uppercase; text-align: left; padding: 12px 14px;
            border-bottom: 1px solid var(--line); white-space: nowrap;
        }
        tbody td { padding: 12px 14px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr.data-row:hover { background: var(--surface-hover); cursor: pointer; }
        .num { font-family: 'JetBrains Mono', monospace; text-align: right; }
        .do-chip { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 0.78rem; color: var(--amber-dim); background: var(--amber-soft); padding: 3px 8px; border-radius: 5px; white-space: nowrap; }
        .empty-state, .loading-state { text-align: center; padding: 40px; color: var(--muted); font-size: 0.85rem; }

        .expand-icon { font-size: 16px !important; vertical-align: middle; color: var(--muted); transition: transform .15s ease; }
        .expand-icon.open { transform: rotate(180deg); color: var(--amber-dim); }

        .detail-row td { padding: 0 !important; background: #fbf8f2; border-bottom: 1px solid var(--line); }
        .detail-inner { padding: 16px 20px; }
        .detail-cell-block { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; }
        .detail-cell-block:last-child { margin-bottom: 0; }
        .detail-cell-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.82rem; }
        .bag-row { display: flex; gap: 10px; padding: 5px 0; font-size: 0.78rem; color: var(--muted); border-top: 1px dashed var(--line); }
        .bag-row:first-of-type { border-top: none; }
        .tir-summary { font-size: 0.8rem; color: var(--muted); margin-top: 10px; }
        .detail-actions { text-align: right; margin-top: 12px; }

        /* ==================== AREA CETAK PDF (tersembunyi) ==================== */
        #pdfPrintArea {
            position: fixed; top: -99999px; left: -99999px;
            width: 210mm; padding: 12mm; background: #fff; font-family: Arial, sans-serif; color: #000;
        }
        #pdfPrintArea .pdf-header { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 14px; }
        #pdfPrintArea .pdf-header img { height: 50px; }
        #pdfPrintArea .pdf-title { font-size: 16pt; font-weight: bold; }
        #pdfPrintArea .pdf-subtitle { font-size: 9pt; color: #444; }
        #pdfPrintArea .pdf-info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px 20px; font-size: 9pt; margin-bottom: 14px; }
        #pdfPrintArea .pdf-info-grid b { display: inline-block; width: 90px; }
        #pdfPrintArea table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 12px; }
        #pdfPrintArea th, #pdfPrintArea td { border: 1px solid #000; padding: 4px 6px; text-align: left; }
        #pdfPrintArea th { background: #eee; font-weight: bold; }
        #pdfPrintArea .pdf-section-title { font-size: 10pt; font-weight: bold; margin: 10px 0 6px; }
    </style>
</head>
<body>

@php
    $currentUser = auth()->guard('tally')->user();
@endphp

<nav>
    <div class="logo">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="height:38px;">
        <span>RIWAYAT OUTBOUND</span>
    </div>
    <div style="display:flex; align-items:center; gap:16px;">
        <a href="{{ route('warehouse.outbound.index') }}" class="nav-link">← Kembali ke Input Outbound</a>
        <span class="mono" style="font-size:0.78rem; color: var(--muted);">{{ $currentUser->name }}</span>
    </div>
</nav>

<div class="page-header">
    <div class="page-eyebrow">Histori &middot; Barang Keluar</div>
    <div class="page-title">Riwayat Outbound</div>
</div>

<div class="container">

    <div class="filter-bar">
        <input type="date" id="filterTanggal">
        <select id="filterChecker">
            <option value="">Semua Checker</option>
            @foreach ($checkers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <input type="text" id="filterSearch" placeholder="Cari No DO...">
        <button class="btn btn-outline" onclick="resetFilters()">Reset Filter</button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Tanggal</th>
                    <th>No DO</th>
                    <th>Customer</th>
                    <th>Checker</th>
                    <th class="num">Jml Cell</th>
                    <th class="num">Total Bag</th>
                    <th class="num">Total Kg</th>
                    <th style="width:120px;"></th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
                <tr><td colspan="9" class="loading-state">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ==================== AREA CETAK PDF (diisi dinamis via JS, tidak terlihat di layar) ==================== --}}
<div id="pdfPrintArea">
    <div class="pdf-header">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
        <div>
            <div class="pdf-title">BUKTI PENGELUARAN BARANG (OUTBOUND)</div>
            <div class="pdf-subtitle">CPI Jombang Plant - Warehouse Department</div>
        </div>
    </div>
    <div class="pdf-info-grid" id="pdfInfoGrid"></div>
    <div class="pdf-section-title">Rincian Cell &amp; Bag</div>
    <div id="pdfCellTables"></div>
    <div id="pdfTirSection"></div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let allHistoryData = [];
    let expandedRows = new Set();
    let detailCache = {};

    async function apiFetch(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan pada server.');
        return data;
    }

    function buildQuery() {
        const params = new URLSearchParams();
        const tanggal = document.getElementById('filterTanggal').value;
        const checker = document.getElementById('filterChecker').value;
        const search = document.getElementById('filterSearch').value.trim();
        if (tanggal) params.set('tanggal', tanggal);
        if (checker) params.set('checker_user_id', checker);
        if (search) params.set('search', search);
        return params.toString();
    }

    async function loadHistoryData() {
        const tbody = document.getElementById('historyTableBody');
        tbody.innerHTML = `<tr><td colspan="9" class="loading-state">Memuat data...</td></tr>`;
        try {
            const qs = buildQuery();
            allHistoryData = await apiFetch(`{{ route('warehouse.outbound.history.data') }}?${qs}`);
            renderTable();
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Gagal memuat data: ${err.message}</td></tr>`;
        }
    }

    function renderTable() {
        const tbody = document.getElementById('historyTableBody');

        if (allHistoryData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Tidak ada data Outbound yang cocok dengan filter ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = allHistoryData.map(d => {
            const isOpen = expandedRows.has(d.id);
            const mainRow = `
                <tr class="data-row" onclick="toggleExpand(${d.id})">
                    <td><span class="material-symbols-outlined expand-icon ${isOpen ? 'open' : ''}">expand_more</span></td>
                    <td>${d.tanggal}</td>
                    <td><span class="do-chip">${d.noDo}</span></td>
                    <td>${d.namaCustomer}</td>
                    <td>${d.checkerNama}</td>
                    <td class="num">${d.jumlahCell}</td>
                    <td class="num">${d.totalBag}</td>
                    <td class="num">${d.totalKg.toLocaleString('id-ID', {maximumFractionDigits:1})}</td>
                    <td onclick="event.stopPropagation()">
                        <button class="btn btn-outline" style="padding:6px 10px;" onclick="downloadPdf(${d.id})">
                            <span class="material-symbols-outlined" style="font-size:15px;">picture_as_pdf</span> PDF
                        </button>
                    </td>
                </tr>
            `;

            const detailRow = isOpen ? `
                <tr class="detail-row">
                    <td colspan="9">
                        <div class="detail-inner" id="detailInner-${d.id}">
                            <div class="loading-state">Memuat detail...</div>
                        </div>
                    </td>
                </tr>
            ` : '';

            return mainRow + detailRow;
        }).join('');
    }

    async function toggleExpand(id) {
        if (expandedRows.has(id)) {
            expandedRows.delete(id);
            renderTable();
            return;
        }
        expandedRows.add(id);
        renderTable();

        try {
            const detail = await getDetail(id);
            renderDetailInto(id, detail);
        } catch (err) {
            const container = document.getElementById(`detailInner-${id}`);
            if (container) container.innerHTML = `<div class="empty-state">Gagal memuat detail: ${err.message}</div>`;
        }
    }

    async function getDetail(id) {
        if (detailCache[id]) return detailCache[id];
        const detail = await apiFetch(`{{ url('warehouse/outbound/history') }}/${id}`);
        detailCache[id] = detail;
        return detail;
    }

    function renderDetailInto(id, detail) {
        const container = document.getElementById(`detailInner-${id}`);
        if (!container) return;

        const cellsHtml = detail.cells.map(c => `
            <div class="detail-cell-block">
                <div class="detail-cell-header">
                    <span class="do-chip">${c.kodeCell}</span>
                    <span class="mono">${c.totalBag} Bag &middot; ${c.totalKg.toLocaleString('id-ID', {maximumFractionDigits:1})} Kg</span>
                </div>
                ${c.bags.map(b => `
                    <div class="bag-row">
                        <span style="width:70px;">${b.nomorBag ? 'Bag #' + b.nomorBag : (b.keterangan || 'Stock Adj.')}</span>
                        <span style="flex:1;">${b.kodeProduksi ?? '-'}</span>
                        <span class="mono">${Number(b.kg).toLocaleString('id-ID', {maximumFractionDigits:1})} Kg</span>
                    </div>
                `).join('')}
            </div>
        `).join('');

        const tirHtml = detail.tirs.length > 0 ? `
            <div class="tir-summary">
                <b>Tir:</b> ${detail.tirs.map(t => `Tir ${t.tirKe} (${t.jumlahBag} Bag)`).join(', ')}
            </div>
        ` : '';

        container.innerHTML = `
            ${cellsHtml || '<div class="empty-state">Tidak ada data Cell.</div>'}
            ${tirHtml}
            <div class="detail-actions">
                <button class="btn btn-amber" onclick="downloadPdf(${id})">
                    <span class="material-symbols-outlined" style="font-size:16px;">picture_as_pdf</span> Download PDF
                </button>
            </div>
        `;
    }

    // ==================== DOWNLOAD PDF (client-side, html2pdf.js) ====================
    async function downloadPdf(id) {
        try {
            const detail = await getDetail(id);
            populatePrintArea(detail);

            const opt = {
                margin: 0,
                filename: `Outbound_${detail.noDo}_${detail.tanggal}.pdf`,
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            };

            await html2pdf().set(opt).from(document.getElementById('pdfPrintArea')).save();
        } catch (err) {
            Swal.fire({ title: 'Gagal Membuat PDF', text: err.message, icon: 'error' });
        }
    }

    function populatePrintArea(detail) {
        document.getElementById('pdfInfoGrid').innerHTML = `
            <div><b>No DO</b>: ${detail.noDo}</div>
            <div><b>Tanggal</b>: ${detail.tanggal}</div>
            <div><b>Jam Muat</b>: ${detail.jamMuat}</div>
            <div><b>Customer</b>: ${detail.namaCustomer}</div>
            <div><b>No Polisi</b>: ${detail.noPol}</div>
            <div><b>Driver</b>: ${detail.namaDriver}</div>
            <div><b>Checker</b>: ${detail.checkerNama}</div>
        `;

        document.getElementById('pdfCellTables').innerHTML = detail.cells.map(c => `
            <table>
                <thead>
                    <tr><th colspan="3">Cell ${c.kodeCell} &mdash; ${c.totalBag} Bag / ${Number(c.totalKg).toLocaleString('id-ID', {maximumFractionDigits:1})} Kg</th></tr>
                    <tr><th>Bag / Keterangan</th><th>Kode Produksi</th><th>Kg</th></tr>
                </thead>
                <tbody>
                    ${c.bags.map(b => `
                        <tr>
                            <td>${b.nomorBag ? 'Bag #' + b.nomorBag : (b.keterangan || 'Stock Adjustment')}</td>
                            <td>${b.kodeProduksi ?? '-'}</td>
                            <td>${Number(b.kg).toLocaleString('id-ID', {maximumFractionDigits:1})}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `).join('');

        const tirSection = document.getElementById('pdfTirSection');
        if (detail.tirs.length > 0) {
            tirSection.innerHTML = `
                <div class="pdf-section-title">Data Tir</div>
                <table>
                    <thead><tr><th>Tir</th><th>Jumlah Bag</th></tr></thead>
                    <tbody>
                        ${detail.tirs.map(t => `<tr><td>Tir ${t.tirKe}</td><td>${t.jumlahBag}</td></tr>`).join('')}
                    </tbody>
                </table>
            `;
        } else {
            tirSection.innerHTML = '';
        }
    }

    function resetFilters() {
        document.getElementById('filterTanggal').value = '';
        document.getElementById('filterChecker').value = '';
        document.getElementById('filterSearch').value = '';
        loadHistoryData();
    }

    document.getElementById('filterTanggal').addEventListener('change', loadHistoryData);
    document.getElementById('filterChecker').addEventListener('change', loadHistoryData);
    document.getElementById('filterSearch').addEventListener('input', debounce(loadHistoryData, 400));

    function debounce(fn, delay) {
        let timer;
        return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); };
    }

    loadHistoryData();
</script>
</body>
</html>
