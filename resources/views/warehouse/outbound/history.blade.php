<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Outbound - CPI App Migrate</title>
    @include('partials.favicon')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    {{-- BARU - Library QR Code (client-side) untuk tanda tangan Warehouse di PDF Tally Loading --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
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

        .page-header { padding: 30px 5% 18px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 14px; }
        .page-eyebrow { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--amber-dim); letter-spacing: 3px; text-transform: uppercase; margin-bottom: 8px; }
        .page-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 2.4rem; text-transform: uppercase; color: var(--text); }

        /* BARU - Toggle Tab Frozen / Fresh */
        .tab-toggle {
            display: inline-flex; background: var(--surface); border: 1px solid var(--line);
            border-radius: 11px; padding: 4px; gap: 4px;
        }
        .tab-toggle button {
            border: none; background: transparent; cursor: pointer;
            padding: 9px 20px; border-radius: 8px; font-weight: 700; font-size: 0.85rem;
            color: var(--muted); transition: all .15s ease;
            font-family: 'Inter', sans-serif;
        }
        .tab-toggle button.active { background: var(--amber); color: #fff; }
        .tab-toggle button:not(.active):hover { background: var(--surface-hover); color: var(--amber-dim); }

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

        /* ============================================================
           AREA CETAK PDF - FORM TALLY LOADING (FM-WH-002), A4 PORTRAIT
           Dipakai bersama oleh Frozen & Fresh. Semua selector di-scope
           ke .tl-root supaya tidak bentrok dengan style halaman di atas.
           ============================================================ */
        #pdfHost { position: fixed; top: -99999px; left: -99999px; pointer-events: none; }
        #pdfMeasure { width: 194mm; }
        #pdfPrintArea { width: 210mm; }

        .tl-root, .tl-root * { font-family: Arial, Helvetica, sans-serif; }
        .tl-root { color: #000; background: #fff; }

        .tl-page {
            width: 210mm; height: 296mm; padding: 8mm 8mm 12mm; box-sizing: border-box;
            position: relative; overflow: hidden; background: #fff; page-break-after: always;
        }
        .tl-page.tl-last { page-break-after: auto; }
        .tl-pageno { position: absolute; right: 8mm; bottom: 5mm; font-size: 7pt; color: #444; }

        .tl-item { padding-bottom: 3mm; }
        .tl-mini { font-size: 7.5pt; color: #333; border-bottom: 0.3mm solid #000; padding-bottom: 1mm; }

        .tl-root table.tl-t { width: 194mm; border-collapse: collapse; table-layout: fixed; font-size: 8pt; }
        .tl-root table.tl-t td {
            border: 0.25mm solid #000; padding: 0 0.8mm; height: 5mm; line-height: 1.1;
            text-align: center; vertical-align: middle; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
        }
        .tl-root table.tl-t td.l { text-align: left; }
        .tl-root table.tl-t td.r { text-align: right; padding-right: 1.5mm; }
        .tl-root table.tl-t tr.h6 td { height: 6mm; }
        .tl-root table.tl-t tr.h55 td { height: 5.5mm; }
        .tl-root table.tl-t td.sm { font-size: 7pt; }
        .tl-root .tl-lbl { display: inline-block; width: 23mm; font-weight: bold; }

        .tl-root .tl-note { font-size: 8pt; line-height: 1.3; word-break: break-word; }

        .tl-root table.tl-sign { width: 194mm; border-collapse: collapse; table-layout: fixed; font-size: 8.5pt; }
        .tl-root table.tl-sign td { border: 0; padding: 0; text-align: center; vertical-align: middle; }
        .tl-root table.tl-sign td.ttl { height: 5mm; }
        .tl-root table.tl-sign td.sp { height: 25mm; }
        .tl-root table.tl-sign td.sp img { width: 22mm; height: 22mm; image-rendering: pixelated; }
        .tl-root table.tl-sign td.nm { height: 5mm; }
        .tl-root table.tl-sign td.rl { height: 5mm; font-weight: bold; }
        .tl-root .tl-foot { font-size: 7.5pt; margin-top: 3mm; }
    </style>
</head>
<body>

@php
    $currentUser = auth()->guard('tally')->user();
@endphp

<nav>
    <div class="logo">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height:38px;">
        <span>RIWAYAT OUTBOUND</span>
    </div>
    <div style="display:flex; align-items:center; gap:16px;">
        <a href="{{ route('warehouse.outbound.index') }}" class="nav-link">← Kembali ke Input Outbound</a>
        <span class="mono" style="font-size:0.78rem; color: var(--muted);">{{ $currentUser->name }}</span>
    </div>
</nav>

<div class="page-header">
    <div>
        <div class="page-eyebrow">Histori &middot; Barang Keluar</div>
        <div class="page-title">Riwayat Outbound</div>
    </div>

    {{-- BARU - Toggle Tab Frozen / Fresh --}}
    <div class="tab-toggle" id="tabToggle">
        <button type="button" class="active" data-tab="frozen" onclick="setActiveTab('frozen')">Frozen</button>
        <button type="button" data-tab="fresh" onclick="setActiveTab('fresh')">Fresh</button>
    </div>
</div>

<div class="container">

    {{-- ============================================================ --}}
    {{-- ================  TAB FROZEN (SUDAH ADA)  =================== --}}
    {{-- ============================================================ --}}
    <div id="panelHistoryFrozen">

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

    {{-- ============================================================ --}}
    {{-- ==================  TAB FRESH (BARU)  ======================= --}}
    {{-- ============================================================ --}}
    <div id="panelHistoryFresh" style="display:none;">

        <div class="filter-bar">
            <input type="date" id="filterTanggalFresh">
            <select id="filterCheckerFresh">
                <option value="">Semua Checker</option>
                @foreach ($checkers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="text" id="filterSearchFresh" placeholder="Cari No PO...">
            <button class="btn btn-outline" onclick="resetFreshFilters()">Reset Filter</button>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Tanggal</th>
                        <th>No PO</th>
                        <th>Customer</th>
                        <th>Checker</th>
                        <th class="num">Jml Item</th>
                        <th class="num">Total Qty</th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody id="historyFreshTableBody">
                    <tr><td colspan="8" class="loading-state">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ==================== AREA CETAK PDF TALLY LOADING (diisi dinamis via JS) ====================
     Dipakai bersama Frozen & Fresh. #pdfHost disembunyikan di luar layar;
     #pdfPrintArea (anak di dalamnya) yang dikirim ke html2pdf, sengaja
     TANPA position fixed supaya hasil clone html2pdf tidak ikut keluar layar.
     #pdfMeasure dipakai untuk mengukur tinggi asli tiap bagian sebelum
     dibagi ke halaman (paginasi). --}}
<div id="pdfHost">
    <div id="pdfMeasure" class="tl-root"></div>
    <div id="pdfPrintArea" class="tl-root"></div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const PDF_LOGO_URL = "{{ asset('images/logo.png') }}";

    // BARU - Tab aktif saat ini: 'frozen' atau 'fresh'.
    let activeTab = 'frozen';

    function setActiveTab(tab) {
        activeTab = tab;

        document.getElementById('panelHistoryFrozen').style.display = tab === 'frozen' ? 'block' : 'none';
        document.getElementById('panelHistoryFresh').style.display = tab === 'fresh' ? 'block' : 'none';

        document.querySelectorAll('#tabToggle button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });

        // Muat data begitu tab dibuka pertama kali (lazy-load Fresh
        // supaya tidak fetch 2 endpoint sekaligus saat halaman dibuka).
        if (tab === 'fresh' && !freshLoadedOnce) {
            freshLoadedOnce = true;
            loadFreshHistoryData();
        }
    }

    // ============================================================
    // ==================  TAB FROZEN (SUDAH ADA)  =================
    // ============================================================
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

    // ============================================================
    // ==================  TAB FRESH (BARU)  =======================
    // ============================================================
    let allFreshHistoryData = [];
    let expandedFreshRows = new Set();
    let freshDetailCache = {};
    let freshLoadedOnce = false; // lazy-load, lihat setActiveTab()

    function buildFreshQuery() {
        const params = new URLSearchParams();
        const tanggal = document.getElementById('filterTanggalFresh').value;
        const checker = document.getElementById('filterCheckerFresh').value;
        const search = document.getElementById('filterSearchFresh').value.trim();
        if (tanggal) params.set('tanggal', tanggal);
        if (checker) params.set('checker_user_id', checker);
        if (search) params.set('search', search);
        return params.toString();
    }

    async function loadFreshHistoryData() {
        const tbody = document.getElementById('historyFreshTableBody');
        tbody.innerHTML = `<tr><td colspan="8" class="loading-state">Memuat data...</td></tr>`;
        try {
            const qs = buildFreshQuery();
            allFreshHistoryData = await apiFetch(`{{ route('warehouse.outbound.fresh.history.data') }}?${qs}`);
            renderFreshTable();
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Gagal memuat data: ${err.message}</td></tr>`;
        }
    }

    function renderFreshTable() {
        const tbody = document.getElementById('historyFreshTableBody');

        if (allFreshHistoryData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Tidak ada data Outbound Fresh yang cocok dengan filter ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = allFreshHistoryData.map(d => {
            const isOpen = expandedFreshRows.has(d.id);
            const mainRow = `
                <tr class="data-row" onclick="toggleFreshExpand(${d.id})">
                    <td><span class="material-symbols-outlined expand-icon ${isOpen ? 'open' : ''}">expand_more</span></td>
                    <td>${d.tanggal}</td>
                    <td><span class="do-chip">${d.noPo}</span></td>
                    <td>${d.namaCustomer}</td>
                    <td>${d.checkerNama}</td>
                    <td class="num">${d.jumlahItem}</td>
                    <td class="num">${Number(d.totalQty).toLocaleString('id-ID', {maximumFractionDigits:2})}</td>
                    <td onclick="event.stopPropagation()">
                        <button class="btn btn-outline" style="padding:6px 10px;" onclick="downloadFreshPdf(${d.id})">
                            <span class="material-symbols-outlined" style="font-size:15px;">picture_as_pdf</span> PDF
                        </button>
                    </td>
                </tr>
            `;

            const detailRow = isOpen ? `
                <tr class="detail-row">
                    <td colspan="8">
                        <div class="detail-inner" id="detailFreshInner-${d.id}">
                            <div class="loading-state">Memuat detail...</div>
                        </div>
                    </td>
                </tr>
            ` : '';

            return mainRow + detailRow;
        }).join('');
    }

    async function toggleFreshExpand(id) {
        if (expandedFreshRows.has(id)) {
            expandedFreshRows.delete(id);
            renderFreshTable();
            return;
        }
        expandedFreshRows.add(id);
        renderFreshTable();

        try {
            const detail = await getFreshDetail(id);
            renderFreshDetailInto(id, detail);
        } catch (err) {
            const container = document.getElementById(`detailFreshInner-${id}`);
            if (container) container.innerHTML = `<div class="empty-state">Gagal memuat detail: ${err.message}</div>`;
        }
    }

    async function getFreshDetail(id) {
        if (freshDetailCache[id]) return freshDetailCache[id];
        const detail = await apiFetch(`{{ url('warehouse/outbound/fresh/history') }}/${id}`);
        freshDetailCache[id] = detail;
        return detail;
    }

    function renderFreshDetailInto(id, detail) {
        const container = document.getElementById(`detailFreshInner-${id}`);
        if (!container) return;

        const itemsHtml = detail.items.map(item => `
    <div class="bag-row">
        <span style="width:110px;">${item.kodeProduksi}</span>
        <span style="flex:1;">${item.kodeProduk ?? '-'} &middot; ${item.namaProduk ?? '-'}</span>
        <span class="mono">${Number(item.qty).toLocaleString('id-ID', {maximumFractionDigits:2})} Qty</span>
        ${item.keterangan ? `<span style="color:var(--danger); font-style:italic;">${item.keterangan}</span>` : ''}
    </div>
`).join('');

        container.innerHTML = `
            <div class="detail-cell-block">
                ${itemsHtml || '<div class="empty-state">Tidak ada data item.</div>'}
            </div>
            <div class="detail-actions">
                <button class="btn btn-amber" onclick="downloadFreshPdf(${id})">
                    <span class="material-symbols-outlined" style="font-size:16px;">picture_as_pdf</span> Download PDF
                </button>
            </div>
        `;
    }

    function resetFreshFilters() {
        document.getElementById('filterTanggalFresh').value = '';
        document.getElementById('filterCheckerFresh').value = '';
        document.getElementById('filterSearchFresh').value = '';
        loadFreshHistoryData();
    }

    document.getElementById('filterTanggalFresh').addEventListener('change', loadFreshHistoryData);
    document.getElementById('filterCheckerFresh').addEventListener('change', loadFreshHistoryData);
    document.getElementById('filterSearchFresh').addEventListener('input', debounce(loadFreshHistoryData, 400));

    // ============================================================
    // ==========  PDF FORM TALLY LOADING (FM-WH-002) - BARU  ======
    // ============================================================
    // Format A4 portrait, dipakai bersama Frozen & Fresh.
    //  - 1 blok per produk, grid 10 kolom x 10 baris = maks 100 bag per
    //    blok (kalau lebih, blok baru dengan nama produk yang sama).
    //  - Bag Stock Adjustment (Frozen) jadi blok sendiri.
    //  - Pengisian grid urut ke bawah per kolom, sesuai urutan saat muat.
    //  - Tinggi tiap bagian DIUKUR dulu (#pdfMeasure), baru dibagi ke
    //    halaman - jadi blok tidak pernah terpotong di tengah.
    // ============================================================
    const PDF_MM_PX = 96 / 25.4;
    const PDF_CONTENT_LIMIT_MM = 276;  // 296 (tinggi halaman) - 8 (atas) - 12 (bawah)
    const PDF_BAG_PER_BLOCK = 100;     // 10 kolom x 10 baris

    let pdfBusy = false;

    function pdfEsc(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function pdfNum(n) {
        return Number(n).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function pdfHariTanggal(ymd) {
        const d = new Date(ymd + 'T00:00:00');
        if (isNaN(d)) return ymd;
        return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }

    function pdfJam(j) {
        return String(j ?? '').substring(0, 5);
    }

    function pdfFileSafe(s) {
        return String(s ?? '').replace(/[\\/:*?"<>|]/g, '-');
    }

    function pdfMakeQr(text) {
        try {
            // QR hanya mendukung teks ASCII dengan aman - buang aksen/karakter khusus.
            const clean = String(text).normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^\x20-\x7E]/g, '');
            const qr = qrcode(0, 'M');
            qr.addData(clean);
            qr.make();
            return qr.createDataURL(6, 0);
        } catch (e) {
            return '';
        }
    }

    // ---------- Header form (logo, judul, kotak dokumen, info pengiriman) ----------
    function pdfHeadHtml(doc) {
        const colInfo = '<col style="width:64.67mm"><col style="width:64.67mm"><col style="width:64.66mm">';

        return `
            <table class="tl-t">
                <colgroup><col style="width:55mm"><col style="width:80mm"><col style="width:59mm"></colgroup>
                <tr class="h6">
                    <td rowspan="3">
                        <img src="${PDF_LOGO_URL}" alt="" style="height:10mm; display:block; margin:0 auto 1mm;">
                        <div style="font-weight:bold; font-size:8pt;">JOMBANG - INDONESIA</div>
                    </td>
                    <td rowspan="3">
                        <div style="font-weight:bold; font-size:14pt;">FORM TALLY LOADING</div>
                        <div style="font-size:8pt; margin-top:1.5mm;">${pdfEsc(doc.refLabel)} : <b>${pdfEsc(doc.refValue)}</b></div>
                    </td>
                    <td class="l sm">No Dokumen &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: FM-WH-002</td>
                </tr>
                <tr class="h6"><td class="l sm">Tanggal Efektif : 15 November 2023</td></tr>
                <tr class="h6"><td class="l sm">Revisi/ Tgl &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 01/01 Agustus 2024</td></tr>
            </table>
            <div style="height:2mm;"></div>
            <table class="tl-t">
                <colgroup>${colInfo}</colgroup>
                <tr class="h6">
                    <td class="l"><span class="tl-lbl">Hari / Tanggal</span>: ${pdfEsc(pdfHariTanggal(doc.tanggal))}</td>
                    <td class="l"><span class="tl-lbl">Tujuan</span>: ${pdfEsc(doc.tujuan)}</td>
                    <td class="l"><span class="tl-lbl">No Mobil</span>: ${pdfEsc(doc.noPol)}</td>
                </tr>
                <tr class="h6">
                    <td class="l"><span class="tl-lbl">Shift</span>: ${pdfEsc(pdfJam(doc.jamMuat))}</td>
                    <td class="l"><span class="tl-lbl">No Seal</span>:</td>
                    <td class="l"><span class="tl-lbl">Driver</span>: ${pdfEsc(doc.driver)}</td>
                </tr>
            </table>
        `;
    }

    // ---------- 1 blok produk (maks 100 bag) ----------
    function pdfBlockHtml(nama, entries, L) {
        const grid = Array.from({ length: 10 }, () => Array(10).fill(null)); // [baris][kolom]
        const colTotal = Array(10).fill(0);
        const colUsed = Array(10).fill(false);
        let count = 0;
        let kg = 0;

        entries.forEach((e, i) => {
            const c = Math.floor(i / 10);
            const r = i % 10;
            grid[r][c] = e.kg;
            colTotal[c] += e.kg;
            colUsed[c] = true;
            count += e.count;
            kg += e.kg;
        });

        const colgroup = `<colgroup><col style="width:12mm">${'<col style="width:18.2mm">'.repeat(10)}</colgroup>`;
        const headCells = Array(10).fill(`<td>${pdfEsc(L.kolom)}</td>`).join('');

        const rows = grid.map((row, r) => `
            <tr>
                <td>${r + 1}</td>
                ${row.map(v => `<td class="r">${v === null ? '' : pdfNum(v)}</td>`).join('')}
            </tr>
        `).join('');

        const totalCells = colTotal.map((t, c) => `<td class="r"><b>${colUsed[c] ? pdfNum(t) : ''}</b></td>`).join('');

        const html = `
            <table class="tl-t">
                ${colgroup}
                <tr class="h6">
                    <td colspan="9" class="l"><b>Nama Produk</b> : ${pdfEsc(nama)}</td>
                    <td colspan="2" class="l"><b>Pack</b> :</td>
                </tr>
                <tr><td>No</td>${headCells}</tr>
                ${rows}
                <tr class="h55"><td><b>Total</b></td>${totalCells}</tr>
                <tr class="h55">
                    <td class="l"><b>Jumlah</b></td>
                    <td colspan="3"></td>
                    <td class="r"><b>${pdfNum(count)}</b></td>
                    <td class="l">${pdfEsc(L.karung)}</td>
                    <td class="l"><b>Jumlah</b></td>
                    <td colspan="2"></td>
                    <td class="r"><b>${pdfNum(kg)}</b></td>
                    <td class="l">${pdfEsc(L.total)}</td>
                </tr>
            </table>
        `;

        return { html, count, kg };
    }

    // ---------- Grand Total + tanda tangan + catatan warna kertas ----------
    function pdfFooterHtml(doc, grandCount, grandKg, qrUrl) {
        const L = doc.labels;
        const colgroup = `<colgroup><col style="width:12mm">${'<col style="width:18.2mm">'.repeat(10)}</colgroup>`;
        const blank = '(' + '&nbsp;'.repeat(26) + ')';

        return `
            <table class="tl-t">
                ${colgroup}
                <tr class="h6">
                    <td colspan="8" class="l"><b>GRAND TOTAL</b></td>
                    <td colspan="2" class="r"><b>${pdfNum(grandCount)}</b></td>
                    <td class="l">${pdfEsc(L.karung)}</td>
                </tr>
                <tr class="h6">
                    <td colspan="8" class="l"><b>GRAND TOTAL</b></td>
                    <td colspan="2" class="r"><b>${pdfNum(grandKg)}</b></td>
                    <td class="l">${pdfEsc(L.total)}</td>
                </tr>
            </table>
            <div style="height:5mm;"></div>
            <table class="tl-sign">
                <colgroup><col style="width:64.67mm"><col style="width:64.67mm"><col style="width:64.66mm"></colgroup>
                <tr>
                    <td class="ttl">Dibuat oleh,</td>
                    <td class="ttl">Dicheck oleh,</td>
                    <td class="ttl">Diterima oleh,</td>
                </tr>
                <tr>
                    <td class="sp">${qrUrl ? `<img src="${qrUrl}" alt="">` : ''}</td>
                    <td class="sp"></td>
                    <td class="sp"></td>
                </tr>
                <tr>
                    <td class="nm">( ${pdfEsc(doc.checkerNama)} )</td>
                    <td class="nm">${blank}</td>
                    <td class="nm">( ${pdfEsc(doc.driver)} )</td>
                </tr>
                <tr>
                    <td class="rl">WAREHOUSE</td>
                    <td class="rl">LOGISTIK</td>
                    <td class="rl">DRIVER</td>
                </tr>
            </table>
            <div class="tl-foot">1. Putih : Customer; &nbsp;2. Merah : Warehouse; &nbsp;3. Kuning : Logistik</div>
        `;
    }

    // ---------- Ukur tinggi tiap bagian (mm) di DOM tersembunyi ----------
    function pdfMeasure(htmls) {
        const m = document.getElementById('pdfMeasure');
        m.innerHTML = htmls.join('');
        const heights = Array.from(m.children).map(el => el.getBoundingClientRect().height / PDF_MM_PX);
        m.innerHTML = '';
        return heights;
    }

    // ---------- Susun semua bagian jadi halaman-halaman A4 ----------
    function pdfBuildPagesHtml(doc) {
        const L = doc.labels;
        const item = (html) => `<div class="tl-item">${html}</div>`;

        const qrText = `Dibuat oleh: ${doc.checkerNama} (${doc.checkerKode}) | ${doc.refLabel}: ${doc.refValue} | ${doc.tanggal} ${pdfJam(doc.jamMuat)}`;
        const qrUrl = pdfMakeQr(qrText);

        const items = [item(pdfHeadHtml(doc))];
        let grandCount = 0;
        let grandKg = 0;

        doc.groups.forEach(g => {
            for (let i = 0; i < g.entries.length; i += PDF_BAG_PER_BLOCK) {
                const r = pdfBlockHtml(g.nama, g.entries.slice(i, i + PDF_BAG_PER_BLOCK), L);
                grandCount += r.count;
                grandKg += r.kg;
                items.push(item(r.html));
            }
        });

        // Catatan (keterangan item) - tiap catatan 1 bagian sendiri supaya
        // kalau panjang bisa pindah halaman dengan rapi.
        doc.notes.forEach((n, idx) => {
            const title = idx === 0 ? '<div style="font-weight:bold; font-size:8.5pt; margin-bottom:1mm;">Catatan :</div>' : '';
            items.push(item(`${title}<div class="tl-note">${pdfEsc(n)}</div>`));
        });

        items.push(item(pdfFooterHtml(doc, grandCount, grandKg, qrUrl)));

        const mini = item(`<div class="tl-mini">FORM TALLY LOADING &nbsp;|&nbsp; FM-WH-002 &nbsp;|&nbsp; ${pdfEsc(doc.refLabel)}: ${pdfEsc(doc.refValue)}</div>`);

        const heights = pdfMeasure([...items, mini]);
        const miniH = heights.pop();

        const pages = [];
        let cur = [];
        let used = 0;
        let hasContent = false;

        items.forEach((html, i) => {
            const h = heights[i];
            if (hasContent && used + h > PDF_CONTENT_LIMIT_MM) {
                pages.push(cur);
                cur = [mini];
                used = miniH;
                hasContent = false;
            }
            cur.push(html);
            used += h;
            hasContent = true;
        });
        pages.push(cur);

        return pages.map((p, idx) => `
            <div class="tl-page ${idx === pages.length - 1 ? 'tl-last' : ''}">
                ${p.join('')}
                <div class="tl-pageno">Halaman ${idx + 1} dari ${pages.length}</div>
            </div>
        `).join('');
    }

    function pdfWaitImages(container) {
        const imgs = Array.from(container.querySelectorAll('img'));
        return Promise.all(imgs.map(img => img.complete ? Promise.resolve() : new Promise(res => {
            img.onload = res;
            img.onerror = res;
        })));
    }

    async function pdfRender(doc, filename) {
        const area = document.getElementById('pdfPrintArea');
        area.innerHTML = pdfBuildPagesHtml(doc);

        try {
            await pdfWaitImages(area);

            const opt = {
                margin: 0,
                filename: filename,
                image: { type: 'png' },
                html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff', scrollX: 0, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css'] },
            };

            await html2pdf().set(opt).from(area).save();
        } finally {
            area.innerHTML = '';
        }
    }

    // ---------- Susun data DO Frozen -> struktur dokumen Tally Loading ----------
    function pdfBuildFrozenDoc(detail) {
        const groups = new Map(); // urutan kemunculan = urutan saat muat

        detail.cells.forEach(c => {
            const batchBagCount = c.bags.filter(b => b.nomorBag !== null && b.nomorBag !== undefined).length;
            const genericCount = Math.max(0, Number(c.totalBag) - batchBagCount);

            c.bags.forEach(b => {
                const isGeneric = b.nomorBag === null || b.nomorBag === undefined;
                const nama = isGeneric ? 'Stock Adjustment' : (b.namaProduk || '-');
                const key = isGeneric ? '__generic__' : 'p:' + nama;

                if (!groups.has(key)) groups.set(key, { nama, entries: [] });
                groups.get(key).entries.push({
                    kg: Number(b.kg),
                    count: isGeneric ? (genericCount > 0 ? genericCount : 1) : 1,
                });
            });
        });

        return {
            refLabel: 'No DO',
            refValue: detail.noDo,
            tanggal: detail.tanggal,
            jamMuat: detail.jamMuat,
            tujuan: detail.namaCustomer,
            noPol: detail.noPol,
            driver: detail.namaDriver,
            checkerNama: detail.checkerNama,
            checkerKode: detail.checkerKode,
            labels: { kolom: 'Kg', karung: 'Karung', total: 'Kg' },
            groups: Array.from(groups.values()),
            notes: [],
        };
    }

    // ---------- Susun data PO Fresh -> struktur dokumen Tally Loading ----------
    function pdfBuildFreshDoc(detail) {
        const groups = new Map();
        const notes = [];

        detail.items.forEach(item => {
            const nama = item.namaProduk || '-';
            if (!groups.has(nama)) groups.set(nama, { nama, entries: [] });
            groups.get(nama).entries.push({ kg: Number(item.qty), count: 1 });

            if (item.keterangan) {
                notes.push(`${item.kodeProduksi ?? '-'} – ${item.keterangan}`);
            }
        });

        return {
            refLabel: 'No PO',
            refValue: detail.noPo,
            tanggal: detail.tanggal,
            jamMuat: detail.jamMuat,
            tujuan: detail.namaCustomer,
            noPol: detail.noPol,
            driver: detail.namaDriver,
            checkerNama: detail.checkerNama,
            checkerKode: detail.checkerKode,
            labels: { kolom: 'Qty', karung: 'Item', total: 'Qty' },
            groups: Array.from(groups.values()),
            notes,
        };
    }

    async function pdfRunWithLoading(buildFn) {
        if (pdfBusy) return;
        pdfBusy = true;

        Swal.fire({
            title: 'Membuat PDF...',
            text: 'Mohon tunggu sebentar.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            await buildFn();
            Swal.close();
        } catch (err) {
            Swal.fire({ title: 'Gagal Membuat PDF', text: err.message, icon: 'error' });
        } finally {
            pdfBusy = false;
        }
    }

    // ==================== DOWNLOAD PDF - FROZEN ====================
    function downloadPdf(id) {
        return pdfRunWithLoading(async () => {
            const detail = await getDetail(id);
            await pdfRender(
                pdfBuildFrozenDoc(detail),
                `Tally_Loading_${pdfFileSafe(detail.noDo)}_${detail.tanggal}.pdf`
            );
        });
    }

    // ==================== DOWNLOAD PDF - FRESH ====================
    function downloadFreshPdf(id) {
        return pdfRunWithLoading(async () => {
            const detail = await getFreshDetail(id);
            await pdfRender(
                pdfBuildFreshDoc(detail),
                `Tally_Loading_Fresh_${pdfFileSafe(detail.noPo)}_${detail.tanggal}.pdf`
            );
        });
    }

    // Tab Frozen dimuat langsung saat halaman terbuka (default tab).
    // Tab Fresh baru dimuat sekali saat pertama kali di-klik (lihat setActiveTab()).
    loadHistoryData();
</script>
</body>
</html>