<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock Warehouse - CPI App Migrate</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0f14;
            --surface: #131a22;
            --surface-hover: #182029;
            --line: #232e3a;
            --ice: #22d3ee;
            --ice-dim: #0e7490;
            --hazard: #fbbf24;
            --danger: #f87171;
            --text: #e7edf3;
            --muted: #7d8ea1;
            --muted-dim: #4b5a6b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body { background: var(--bg); color: var(--text); }

        .mono { font-family: 'JetBrains Mono', monospace; }
        .display { font-family: 'Barlow Condensed', sans-serif; }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 5%;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; color: var(--text); }
        .logo img { height: 38px; background: #fff; border-radius: 6px; padding: 3px 6px; }
        .back-link {
            color: var(--muted);
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            display: flex; align-items: center; gap: 4px;
        }
        .back-link:hover { color: var(--ice); }

        .page-header { padding: 34px 5% 20px; }
        .page-eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: var(--ice);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .page-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 2.6rem;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            color: #fff;
        }

        .stat-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            padding: 0 5% 26px;
        }
        .stat-box {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 16px 18px;
        }
        .stat-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.62rem;
            color: var(--muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .stat-value {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.9rem;
            color: #fff;
        }
        .stat-box.warn .stat-value { color: var(--hazard); }
        .stat-box.danger .stat-value { color: var(--danger); }
        .stat-box.ice .stat-value { color: var(--ice); }

        .filter-bar {
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
            padding: 0 5% 20px;
        }
        .filter-bar input, .filter-bar select {
            background: var(--surface);
            border: 1px solid var(--line);
            color: var(--text);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
        }
        .filter-bar input:focus, .filter-bar select:focus {
            outline: none;
            border-color: var(--ice-dim);
        }
        .filter-bar input { min-width: 220px; }
        .btn-reset {
            background: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-reset:hover { 
            background: #bb2d3b;
            border-color: #bb2d3b;
            color: #fff;
         }
                .btn-upload {
            background: #16a34a;
            border: 1px solid #16a34a;
            color: #fff;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-upload:hover {
            background: #15803d;
            border-color: #15803d;
            color: #fff;
        }

        .table-section { padding: 0 5% 60px; }
        .table-wrapper {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        thead th {
            background: #0e1620;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            white-space: nowrap;
        }
        tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--surface-hover); }

        .cell-chip {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 0.78rem;
            color: var(--ice);
            background: rgba(34,211,238,.08);
            border: 1px solid var(--ice-dim);
            padding: 3px 8px;
            border-radius: 5px;
            white-space: nowrap;
        }
        .produk-chip {
            display: inline-block;
            font-size: 0.72rem;
            color: var(--muted);
            background: #0e1620;
            border: 1px solid var(--line);
            padding: 2px 7px;
            border-radius: 5px;
            margin: 1px 2px 1px 0;
        }
        .num { font-family: 'JetBrains Mono', monospace; text-align: right; }

        .progress-track {
            width: 100%;
            height: 7px;
            background: #0e1620;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-fill { height: 100%; border-radius: 999px; }
        .progress-fill.ok { background: var(--ice); }
        .progress-fill.warn { background: var(--hazard); }
        .progress-fill.danger { background: var(--danger); }
        .persen-label { font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; font-weight: 700; }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .loading-state { text-align: center; padding: 50px; color: var(--muted); font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; }

        .cell-status-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
            animation: dot-pulse 1.6s ease-in-out infinite;
        }
        .cell-status-dot.ok { background: var(--ice); box-shadow: 0 0 6px var(--ice); }
        .cell-status-dot.warn { background: var(--hazard); box-shadow: 0 0 6px var(--hazard); }
        .cell-status-dot.danger { background: var(--danger); box-shadow: 0 0 6px var(--danger); }
        @keyframes dot-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .4; transform: scale(0.75); }
        }

        .toggle-group {
            display: inline-flex;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 3px;
            gap: 3px;
        }
        .toggle-btn {
            background: transparent;
            border: none;
            color: var(--muted);
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all .15s ease;
        }
        .toggle-btn.active { background: var(--ice); color: #05141a; }
        .toggle-btn:not(.active):hover { color: var(--text); }

        .cell-row { cursor: pointer; }
        .cell-row:hover { background: var(--surface-hover); }
        .expand-icon {
            font-size: 16px !important;
            vertical-align: middle;
            color: var(--muted-dim);
            transition: transform .15s ease;
        }
        .expand-icon.open { transform: rotate(180deg); color: var(--ice-text); }

        .detail-row td { padding: 0 !important; border-bottom: 1px solid var(--line); background: #f7fafc; }
        .warna-breakdown { display: flex; gap: 10px; padding: 14px 16px; flex-wrap: wrap; }
        .warna-card {
            flex: 1;
            min-width: 130px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-left: 4px solid var(--wc);
            border-radius: 8px;
            padding: 10px 14px;
        }
        .warna-card .label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--wc);
            margin-bottom: 4px;
        }
        .warna-card .value {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--text);
        }
        .warna-card .sub {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 2px;
            font-family: 'JetBrains Mono', monospace;
        }
        .warna-empty { padding: 14px 16px; font-size: 0.8rem; color: var(--muted); font-style: italic; }
    </style>
</head>
<body>

    @php
        $currentUser = auth()->guard('tally')->user();
    @endphp

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
            <span>STOCK WAREHOUSE</span>
        </div>
        <div style="display:flex; align-items:center; gap:16px;">
            <span class="mono" style="font-size:0.78rem; color: var(--muted);">{{ $currentUser->name }}</span>
            <form id="logoutForm" method="POST" action="{{ route('warehouse.stock.logout') }}">
                @csrf
                <button type="button" class="btn-reset" style="cursor:pointer;" onclick="confirmLogout()">Keluar</button>
            </form>
        </div>
    </nav>

    <div class="page-header">
        <div class="page-eyebrow">Real-time Monitoring</div>
        <div class="page-title">Stock Warehouse</div>
    </div>

    <div style="padding: 0 5% 16px; display:flex; justify-content:flex-end;">
        <div class="toggle-group">
            <button class="toggle-btn active" id="btnViewBag" onclick="setViewMode('bag')">BY BAG</button>
            <button class="toggle-btn" id="btnViewKg" onclick="setViewMode('kg')">BY KG</button>
        </div>
    </div>

    <div class="stat-strip" id="statStrip">
        <div class="stat-box">
            <div class="stat-label">Total Cell</div>
            <div class="stat-value" id="statTotalCell">-</div>
        </div>
        <div class="stat-box ice">
            <div class="stat-label">Total Kapasitas (<span id="unitLabel1">Bag</span>)</div>
            <div class="stat-value" id="statTotalKapasitas">-</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Terpakai (<span id="unitLabel2">Bag</span>)</div>
            <div class="stat-value" id="statTotalTerpakai">-</div>
        </div>
        <div class="stat-box warn">
            <div class="stat-label">Cell Terisi >90%</div>
            <div class="stat-value" id="statCellPenuh">-</div>
        </div>
    </div>

    <div class="filter-bar">
        <input type="text" id="filterSearch" placeholder="Cari kode cell atau nama produk...">
        <select id="filterColdStorage"><option value="">Semua Cold Storage</option></select>
        <select id="filterLantai"><option value="">Semua Lantai</option></select>
        <select id="filterKategori"><option value="">Semua Kategori</option></select>
        <button class="btn-reset" onclick="resetFilters()">Reset Filter</button>
        <button class="btn-upload" onclick="document.getElementById('excelUploadInput').click()">
            <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">upload_file</span> Upload Excel
        </button>
        <input type="file" id="excelUploadInput" accept=".xlsx,.xls" style="display:none;" onchange="handleExcelUpload(this)">
    </div>

    <div class="table-section">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kode Cell</th>
                        <th>Cold Storage</th>
                        <th>Lantai</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th class="num">Kapasitas (<span id="unitLabel3">Bag</span>)</th>
                        <th class="num">Terpakai (<span id="unitLabel4">Bag</span>)</th>
                        <th class="num">Sisa (<span id="unitLabel5">Bag</span>)</th>
                        <th style="width: 160px;">% Terisi</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    <tr><td colspan="9" class="loading-state">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let allStockData = [];
        let expandedCells = new Set();
        let viewMode = 'bag'; // 'bag' atau 'kg'

        function confirmLogout() {
            Swal.fire({
                title: 'Keluar dari Sistem?',
                html: '<div style="font-size:14px;color:#64798c;">Anda akan keluar dari <b>Stock Warehouse</b>.</div>',
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64798c',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }

        function setViewMode(mode) {
            viewMode = mode;
            document.getElementById('btnViewBag').classList.toggle('active', mode === 'bag');
            document.getElementById('btnViewKg').classList.toggle('active', mode === 'kg');

            const unitText = mode === 'bag' ? 'Bag' : 'Kg';
            ['unitLabel1', 'unitLabel2', 'unitLabel3', 'unitLabel4', 'unitLabel5'].forEach(id => {
                document.getElementById(id).innerText = unitText;
            });

            renderStats(allStockData);
            renderTable(allStockData);
        }

        async function handleExcelUpload(input) {
            const file = input.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            Swal.fire({
                title: 'Memproses Excel...',
                html: 'Mohon tunggu, sedang membaca & menyesuaikan data.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const res = await fetch(`{{ route('warehouse.stock.upload') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData,
                });
                const result = await res.json();

                if (!res.ok) {
                    throw new Error(result.message || 'Gagal memproses file.');
                }

                let dilewatiHtml = '';
                if (result.dilewati.length > 0) {
                    dilewatiHtml = `<div style="text-align:left; max-height:200px; overflow-y:auto; margin-top:12px; font-size:12px;">
                        <b>Dilewati (${result.dilewati.length}):</b>
                        <ul>${result.dilewati.map(d => `<li>Baris ${d.baris} (${d.kode_cell}): ${d.alasan}</li>`).join('')}</ul>
                    </div>`;
                }

                await Swal.fire({
                    title: 'Selesai!',
                    html: `<div>${result.berhasil} cell berhasil disesuaikan.</div>${dilewatiHtml}`,
                    icon: result.dilewati.length > 0 ? 'warning' : 'success',
                });

                loadStockData();
            } catch (err) {
                Swal.fire({ title: 'Gagal', text: err.message, icon: 'error' });
            } finally {
                input.value = '';
            }
        }

        async function loadFilterOptions() {
            try {
                const res = await fetch(`{{ route('warehouse.stock.filter-options') }}`);
                const opts = await res.json();

                const csSelect = document.getElementById('filterColdStorage');
                opts.coldStorage.forEach(v => {
                    csSelect.innerHTML += `<option value="${v}">${v}</option>`;
                });

                const lantaiSelect = document.getElementById('filterLantai');
                opts.lantai.forEach(v => {
                    lantaiSelect.innerHTML += `<option value="${v}">${v}</option>`;
                });

const kategoriLabels = {
    'kw1': 'Premium',
    'kw2': 'Super',
};
const kategoriDihapus = ['bahan_baku'];

const kategoriSelect = document.getElementById('filterKategori');
opts.kategori
    .filter(v => !kategoriDihapus.includes(v))
    .forEach(v => {
        const label = kategoriLabels[v] || v;
        kategoriSelect.innerHTML += `<option value="${v}">${label}</option>`;
    });
            } catch (err) {
                console.error('Gagal memuat filter options:', err);
            }
        }

        function buildQueryParams() {
            const params = new URLSearchParams();
            const search = document.getElementById('filterSearch').value.trim();
            const cs = document.getElementById('filterColdStorage').value;
            const lantai = document.getElementById('filterLantai').value;
            const kategori = document.getElementById('filterKategori').value;

            if (search) params.set('search', search);
            if (cs) params.set('cold_storage', cs);
            if (lantai) params.set('lantai', lantai);
            if (kategori) params.set('kategori', kategori);

            return params.toString();
        }

        async function loadStockData() {
            const tbody = document.getElementById('stockTableBody');
            tbody.innerHTML = `<tr><td colspan="9" class="loading-state">Memuat data...</td></tr>`;

            try {
                const qs = buildQueryParams();
                const res = await fetch(`{{ route('warehouse.stock.data') }}?${qs}`);
                const data = await res.json();
                allStockData = data;
                renderTable(data);
                renderStats(data);
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Gagal memuat data: ${err.message}</td></tr>`;
            }
        }

        function renderStats(data) {
            const totalCell = data.length;
            let totalKapasitas, totalTerpakai, cellPenuh;

            if (viewMode === 'bag') {
                totalKapasitas = data.reduce((a, b) => a + Number(b.kapasitasMax || 0), 0);
                totalTerpakai = data.reduce((a, b) => a + Number(b.terpakai || 0), 0);
                cellPenuh = data.filter(d => d.persenTerisi > 90).length;
            } else {
                totalKapasitas = data.reduce((a, b) => a + Number(b.kapasitasMaxKg || 0), 0);
                totalTerpakai = data.reduce((a, b) => a + Number(b.terpakaiKg || 0), 0);
                cellPenuh = data.filter(d => d.persenTerisiKg > 90).length;
            }

            document.getElementById('statTotalCell').innerText = totalCell.toLocaleString('id-ID');
            document.getElementById('statTotalKapasitas').innerText = totalKapasitas.toLocaleString('id-ID', { maximumFractionDigits: 1 });
            document.getElementById('statTotalTerpakai').innerText = totalTerpakai.toLocaleString('id-ID', { maximumFractionDigits: 1 });
            document.getElementById('statCellPenuh').innerText = cellPenuh.toLocaleString('id-ID');
        }

        function progressClass(persen) {
            if (persen > 90) return 'danger';
            if (persen >= 70) return 'warn';
            return 'ok';
        }

        function renderTable(data) {
            const tbody = document.getElementById('stockTableBody');

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Tidak ada cell yang cocok dengan filter ini.</td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(d => {
                const kodeChips = d.produk.length > 0
                    ? d.produk.map(p => `<span class="produk-chip">${p.code}</span>`).join('')
                    : `<span class="produk-chip">-</span>`;
                const namaChips = d.produk.length > 0
                    ? d.produk.map(p => `<span class="produk-chip">${p.name}</span>`).join('')
                    : `<span class="produk-chip">-</span>`;

                const fmt = (v) => (v === null || v === undefined || v === '-') ? '-' : Number(v).toLocaleString('id-ID', { maximumFractionDigits: 1 });

                let kapasitas, terpakai, sisa, persen;
                if (viewMode === 'bag') {
                    kapasitas = fmt(d.kapasitasMax);
                    terpakai = fmt(d.terpakai);
                    sisa = fmt(d.sisa);
                    persen = d.persenTerisi;
                } else {
                    kapasitas = fmt(d.kapasitasMaxKg);
                    terpakai = fmt(d.terpakaiKg);
                    sisa = fmt(d.sisaKg);
                    persen = d.persenTerisiKg ?? 0;
                }

                const pClass = progressClass(persen);
                const isExpanded = expandedCells.has(d.id);

                const mainRow = `
                    <tr class="cell-row" onclick="toggleExpand(${d.id})">
                        <td>
                            <span class="material-symbols-outlined expand-icon ${isExpanded ? 'open' : ''}">expand_more</span>
                            <span class="cell-status-dot ${pClass}"></span><span class="cell-chip">${d.kodeCell}</span>
                        </td>
                        <td>${d.coldStorage ?? '-'}</td>
                        <td>${d.lantai ?? '-'}</td>
                        <td>${kodeChips}</td>
                        <td>${namaChips}</td>
                        <td class="num">${kapasitas}</td>
                        <td class="num">${terpakai}</td>
                        <td class="num">${sisa}</td>
                        <td>
                            <span class="persen-label">${persen}%</span>
                            <div class="progress-track">
                                <div class="progress-fill ${pClass}" style="width: ${Math.min(100, persen)}%;"></div>
                            </div>
                        </td>
                    </tr>
                `;

                const detailRow = isExpanded ? `
                    <tr class="detail-row">
                        <td colspan="9">${renderWarnaBreakdown(d.breakdownWarna)}</td>
                    </tr>
                ` : '';

                return mainRow + detailRow;
            }).join('');
        }

        function toggleExpand(cellId) {
            if (expandedCells.has(cellId)) {
                expandedCells.delete(cellId);
            } else {
                expandedCells.add(cellId);
            }
            renderTable(allStockData);
        }

        function renderWarnaBreakdown(w) {
            if (!w) return `<div class="warna-empty">Belum ada data breakdown warna untuk cell ini.</div>`;

            const fmt = (v) => Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 1 });
            const total = (w.merah?.bag || 0) + (w.biru?.bag || 0) + (w.hijau?.bag || 0) + (w.kuning?.bag || 0);

            if (total === 0) {
                return `<div class="warna-empty">Belum ada breakdown warna untuk cell ini (upload Excel dengan kolom BAG MERAH/BIRU/HIJAU/KUNING).</div>`;
            }

            const cards = [
                { key: 'merah', label: 'Merah (Jan-Mar)', color: '#ef4444' },
                { key: 'biru', label: 'Biru (Apr-Jun)', color: '#3b82f6' },
                { key: 'hijau', label: 'Hijau (Jul-Sep)', color: '#22c55e' },
                { key: 'kuning', label: 'Kuning (Okt-Des)', color: '#eab308' },
            ];

            return `<div class="warna-breakdown">` + cards.map(c => `
                <div class="warna-card" style="--wc: ${c.color};">
                    <div class="label">${c.label}</div>
                    <div class="value">${fmt(w[c.key]?.bag)} Bag</div>
                    <div class="sub">${fmt(w[c.key]?.kg)} Kg</div>
                </div>
            `).join('') + `</div>`;
        }

        function resetFilters() {
            document.getElementById('filterSearch').value = '';
            document.getElementById('filterColdStorage').value = '';
            document.getElementById('filterLantai').value = '';
            document.getElementById('filterKategori').value = '';
            loadStockData();
        }

        document.getElementById('filterSearch').addEventListener('input', debounce(loadStockData, 400));
        document.getElementById('filterColdStorage').addEventListener('change', loadStockData);
        document.getElementById('filterLantai').addEventListener('change', loadStockData);
        document.getElementById('filterKategori').addEventListener('change', loadStockData);

        function debounce(fn, delay) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn(...args), delay);
            };
        }

        loadFilterOptions();
        loadStockData();
    </script>
</body>
</html>