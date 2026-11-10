<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock Warehouse</title>
    @include('partials.favicon')
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
        .badge-simpanan {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--hazard);
            background: rgba(251,191,36,.1);
            border: 1px solid rgba(251,191,36,.45);
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
            vertical-align: middle;
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
        .expand-icon.open { transform: rotate(180deg); color: var(--ice); }

        .detail-row td { padding: 0 !important; border-bottom: 1px solid var(--line); background: #0e1620; }
        .detail-row:hover { background: #0e1620; }
        .section-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--muted);
        }
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

        /* ===== Pindah stock ke cell simpanan ===== */
        .transfer-bar {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 10px;
        }
        .transfer-info { font-size: 0.8rem; color: var(--muted); }
        .transfer-info b { color: var(--ice); font-family: 'JetBrains Mono', monospace; }
        .transfer-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn-link {
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
        }
        .btn-link:hover { color: var(--ice); }
        .btn-transfer {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--hazard);
            border: 1px solid var(--hazard);
            color: #1a1300;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s ease;
        }
        .btn-transfer:hover:not(:disabled) { background: #f59e0b; border-color: #f59e0b; }
        .btn-transfer:disabled { opacity: .35; cursor: not-allowed; }

        .batch-scroll { max-height: 420px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; }
        .batch-table { width: 100%; border-collapse: collapse; }
        .batch-table thead th {
            position: sticky; top: 0; z-index: 1;
            font-size: 0.62rem; padding: 8px 10px;
        }
        .batch-table td { padding: 6px 10px; font-size: 0.75rem; border-bottom: 1px solid var(--line); }
        .batch-head-row td { background: #121c27; font-weight: 600; padding-top: 9px; padding-bottom: 9px; }
        .bag-row td { color: var(--muted); }
        .batch-table tbody tr:hover { background: transparent; }
        .batch-table input[type="checkbox"] { accent-color: var(--ice); width: 15px; height: 15px; cursor: pointer; }
    </style>
</head>
<body>

    @php
        $currentUser = auth()->guard('tally')->user();
        $canTransfer = $currentUser && $currentUser->hasAnyRole(['admin_gudang', 'supervisor_gudang']);
    @endphp

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
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
        <select id="filterWarna">
            <option value="">Semua Warna</option>
            <option value="merah">Merah (Jan-Mar)</option>
            <option value="biru">Biru (Apr-Jun)</option>
            <option value="hijau">Hijau (Jul-Sep)</option>
            <option value="kuning">Kuning (Okt-Des)</option>
        </select>
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
        const canTransfer = @json($canTransfer);

        let allStockData = [];
        let expandedCells = new Set();
        let batchCache = {};        // cellId -> array batch (hasil fetch on-demand)
        let batchLoading = new Set(); // cellId yang sedang di-fetch (cegah fetch ganda)
        let selectedBags = {};      // cellId -> Set of "batchId-nomorBag" yang dicentang
        let viewMode = 'bag';       // 'bag' atau 'kg'
        let isTransferring = false;

        function esc(v) {
            return String(v ?? '').replace(/[&<>"']/g, ch => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[ch]));
        }

        const fmtNum = (v) => (v === null || v === undefined || v === '-')
            ? '-'
            : Number(v).toLocaleString('id-ID', { maximumFractionDigits: 1 });

        function getCellData(cellId) {
            return allStockData.find(d => d.id === cellId) || null;
        }

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

        // Filter warna dilakukan di sisi klien dari data yang sudah dimuat.
        // Cell lolos kalau bag warna terpilih > 0 (tidak tergantung mode BAG/KG).
        function getFilteredData() {
            const warna = document.getElementById('filterWarna').value;
            if (!warna) return allStockData;

            return allStockData.filter(d => Number(d.breakdownWarna?.[warna]?.bag || 0) > 0);
        }

        function renderAll() {
            const data = getFilteredData();
            renderTable(data);
            renderStats(data);
        }

        function setViewMode(mode) {
            viewMode = mode;
            document.getElementById('btnViewBag').classList.toggle('active', mode === 'bag');
            document.getElementById('btnViewKg').classList.toggle('active', mode === 'kg');

            const unitText = mode === 'bag' ? 'Bag' : 'Kg';
            ['unitLabel1', 'unitLabel2', 'unitLabel3', 'unitLabel4', 'unitLabel5'].forEach(id => {
                document.getElementById(id).innerText = unitText;
            });

            renderAll();
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
                        <ul>${result.dilewati.map(d => `<li>Baris ${esc(d.baris)} (${esc(d.kode_cell)}): ${esc(d.alasan)}</li>`).join('')}</ul>
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

        // Catatan: filter warna sengaja TIDAK ikut dikirim ke server,
        // karena difilter di klien lewat getFilteredData().
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
                batchCache = {}; // data baru dimuat, cache batch lama tidak dipakai lagi
                renderAll();
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
                    ? d.produk.map(p => `<span class="produk-chip">${esc(p.code)}</span>`).join('')
                    : `<span class="produk-chip" style="opacity:.6;">Kosong</span>`;
                const namaChips = d.produk.length > 0
                    ? d.produk.map(p => `<span class="produk-chip">${esc(p.name)}</span>`).join('')
                    : `<span class="produk-chip" style="opacity:.6;">Kosong</span>`;

                let kapasitas, terpakai, sisa, persen;
                if (viewMode === 'bag') {
                    kapasitas = fmtNum(d.kapasitasMax);
                    terpakai = fmtNum(d.terpakai);
                    sisa = fmtNum(d.sisa);
                    persen = d.persenTerisi;
                } else {
                    kapasitas = fmtNum(d.kapasitasMaxKg);
                    terpakai = fmtNum(d.terpakaiKg);
                    sisa = fmtNum(d.sisaKg);
                    persen = d.persenTerisiKg ?? 0;
                }

                const pClass = progressClass(persen);
                const isExpanded = expandedCells.has(d.id);
                const badge = d.isSimpanan ? `<span class="badge-simpanan">Simpanan</span>` : '';

                const mainRow = `
                    <tr class="cell-row" onclick="toggleExpand(${d.id})">
                        <td>
                            <span class="material-symbols-outlined expand-icon ${isExpanded ? 'open' : ''}">expand_more</span>
                            <span class="cell-status-dot ${pClass}"></span><span class="cell-chip">${esc(d.kodeCell)}</span>${badge}
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

                const sectionTitle = d.isSimpanan ? 'Isi Cell Simpanan (hasil pindahan)' : 'Batch Asli (Inbound)';

                const detailRow = isExpanded ? `
                    <tr class="detail-row">
                        <td colspan="9">
                            ${renderWarnaBreakdown(d.breakdownWarna, d.isSimpanan)}
                            <div style="padding: 4px 16px 16px;">
                                <div class="section-label" style="margin-bottom:8px;">${sectionTitle}</div>
                                <div id="batchList-${d.id}">
                                    <div class="warna-empty">Memuat data batch...</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                ` : '';

                return mainRow + detailRow;
            }).join('');

            // Muat (atau render dari cache) isi tiap cell yang sedang terbuka.
            // Dipanggil di sini supaya detail tetap terisi setelah tabel
            // dirender ulang (ganti mode, filter warna, muat ulang data).
            data.forEach(d => {
                if (expandedCells.has(d.id)) loadBatchesForCell(d.id);
            });
        }

        function toggleExpand(cellId) {
            if (expandedCells.has(cellId)) {
                expandedCells.delete(cellId);
            } else {
                expandedCells.add(cellId);
            }
            renderTable(getFilteredData());
        }

        async function loadBatchesForCell(cellId) {
            if (batchCache[cellId]) {
                renderBatchListInto(cellId, batchCache[cellId]);
                return;
            }
            if (batchLoading.has(cellId)) return;

            batchLoading.add(cellId);
            try {
                const res = await fetch(`{{ url('warehouse/stock') }}/${cellId}/batches`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();
                batchCache[cellId] = data;
                renderBatchListInto(cellId, data);
            } catch (err) {
                const container = document.getElementById(`batchList-${cellId}`);
                if (container) container.innerHTML = `<div class="warna-empty">Gagal memuat data batch: ${esc(err.message)}</div>`;
            } finally {
                batchLoading.delete(cellId);
            }
        }

        // ---------- Seleksi bag (checkbox) ----------

        const bagKey = (batchId, nomorBag) => `${batchId}-${nomorBag}`;

        function getSelectionSet(cellId) {
            if (!selectedBags[cellId]) selectedBags[cellId] = new Set();
            return selectedBags[cellId];
        }

        // Hanya menghitung bag yang BENAR-BENAR ada di daftar saat ini,
        // jadi centang basi (bag yang sudah pindah/keluar) tidak ikut.
        function getSelectionSummary(cellId) {
            const sel = getSelectionSet(cellId);
            const bags = [];
            const produkIds = new Set();
            let kg = 0;

            (batchCache[cellId] || []).forEach(b => {
                b.bags.forEach(bg => {
                    if (sel.has(bagKey(b.batchId, bg.nomorBag))) {
                        bags.push({ batch_id: b.batchId, nomor_bag: bg.nomorBag });
                        produkIds.add(b.produkId);
                        kg += Number(bg.kg || 0);
                    }
                });
            });

            return {
                count: bags.length,
                kg: Math.round(kg * 100) / 100,
                bags,
                produkIds: [...produkIds],
            };
        }

        function rerenderBatchList(cellId) {
            if (batchCache[cellId]) renderBatchListInto(cellId, batchCache[cellId]);
        }

        function toggleBag(cellId, batchId, nomorBag, checked) {
            const sel = getSelectionSet(cellId);
            const key = bagKey(batchId, nomorBag);
            if (checked) sel.add(key); else sel.delete(key);
            rerenderBatchList(cellId);
        }

        function toggleBatchAll(cellId, batchId, checked) {
            const sel = getSelectionSet(cellId);
            const batch = (batchCache[cellId] || []).find(b => b.batchId === batchId);
            if (!batch) return;

            batch.bags.forEach(bg => {
                const key = bagKey(batchId, bg.nomorBag);
                if (checked) sel.add(key); else sel.delete(key);
            });
            rerenderBatchList(cellId);
        }

        function selectAllCell(cellId, checked) {
            const sel = getSelectionSet(cellId);
            (batchCache[cellId] || []).forEach(b => {
                b.bags.forEach(bg => {
                    const key = bagKey(b.batchId, bg.nomorBag);
                    if (checked) sel.add(key); else sel.delete(key);
                });
            });
            rerenderBatchList(cellId);
        }

        // ---------- Render daftar batch ----------

        function renderBatchListInto(cellId, batches) {
            const container = document.getElementById(`batchList-${cellId}`);
            if (!container) return;

            const cellData = getCellData(cellId);
            const isSimpanan = !!cellData?.isSimpanan;
            const canSelect = canTransfer;

            // Pertahankan posisi scroll supaya centang tidak melompat ke atas.
            const oldScroll = container.querySelector('.batch-scroll');
            const scrollTop = oldScroll ? oldScroll.scrollTop : 0;

            container.innerHTML =
                (canSelect && batches.length > 0 ? renderTransferBar(cellId) : '') +
                renderBatchTable(cellId, batches, isSimpanan, canSelect);

            const newScroll = container.querySelector('.batch-scroll');
            if (newScroll) newScroll.scrollTop = scrollTop;

            // Status "sebagian" untuk checkbox pilih-semua per batch.
            if (canSelect) {
                const sel = getSelectionSet(cellId);
                container.querySelectorAll('.batch-all').forEach(cb => {
                    const batch = batches.find(b => String(b.batchId) === cb.dataset.batch);
                    if (!batch) return;
                    const n = batch.bags.filter(bg => sel.has(bagKey(batch.batchId, bg.nomorBag))).length;
                    cb.indeterminate = n > 0 && n < batch.bags.length;
                });
            }
        }

        function renderTransferBar(cellId) {
            const { count, kg } = getSelectionSummary(cellId);
            const dariSimpanan = !!getCellData(cellId)?.isSimpanan;

            return `
                <div class="transfer-bar">
                    <div class="transfer-info"><b>${fmtNum(count)}</b> bag dipilih &middot; <b>${fmtNum(kg)}</b> kg</div>
                    <div class="transfer-actions">
                        <button type="button" class="btn-link" onclick="selectAllCell(${cellId}, true)">Pilih semua</button>
                        <button type="button" class="btn-link" onclick="selectAllCell(${cellId}, false)">Kosongkan pilihan</button>
                        <button type="button" class="btn-transfer" ${count === 0 ? 'disabled' : ''} onclick="startTransfer(${cellId})">
                            <span class="material-symbols-outlined" style="font-size:16px;">move_down</span>
                            ${dariSimpanan ? 'Pindahkan ke Cell Biasa' : 'Pindahkan ke Cell Simpanan'}
                        </button>
                    </div>
                </div>
            `;
        }

        function renderBatchTable(cellId, batches, isSimpanan, canSelect) {
            if (!batches || batches.length === 0) {
                return `<div class="warna-empty">${isSimpanan
                    ? 'Belum ada stock di cell simpanan ini.'
                    : 'Belum ada batch asli (Inbound) yang tercatat untuk cell ini.'}</div>`;
            }

            const sel = getSelectionSet(cellId);

            const rows = batches.map(b => {
                const total = b.bags.length;
                const nSel = b.bags.filter(bg => sel.has(bagKey(b.batchId, bg.nomorBag))).length;

                const headCheck = canSelect
                    ? `<input type="checkbox" class="batch-all" data-batch="${b.batchId}" ${nSel === total && total > 0 ? 'checked' : ''}
                           onchange="toggleBatchAll(${cellId}, ${b.batchId}, this.checked)" title="Pilih semua bag di batch ini">`
                    : '';

                const headRow = `
                    <tr class="batch-head-row">
                        <td style="width:34px;">${headCheck}</td>
                        <td class="mono">${esc(b.kodeProduksi)}</td>
                        <td>${esc(b.namaProduk ?? '-')}</td>
                        <td>${esc(b.noTrolly ?? '-')}</td>
                        <td>${esc(b.tanggalProduksi ?? '-')}</td>
                        <td class="num">${b.jumlahBag} bag</td>
                        <td class="num">${fmtNum(b.totalKg)} kg</td>
                    </tr>
                `;

                const bagRows = b.bags.map(bg => {
                    const checked = sel.has(bagKey(b.batchId, bg.nomorBag));
                    const check = canSelect
                        ? `<input type="checkbox" ${checked ? 'checked' : ''}
                               onchange="toggleBag(${cellId}, ${b.batchId}, ${bg.nomorBag}, this.checked)">`
                        : '';
                    const asal = bg.asalCell ? ` &middot; asal Cell <b style="color:var(--hazard);">${esc(bg.asalCell)}</b>` : '';

                    return `
                        <tr class="bag-row">
                            <td>${check}</td>
                            <td colspan="4" style="padding-left:22px;">Bag #${bg.nomorBag}${asal}</td>
                            <td></td>
                            <td class="num">${fmtNum(bg.kg)}</td>
                        </tr>
                    `;
                }).join('');

                return headRow + bagRows;
            }).join('');

            return `
                <div class="batch-scroll">
                    <table class="batch-table">
                        <thead>
                            <tr>
                                <th style="width:34px;"></th>
                                <th>Kode Produksi</th>
                                <th>Produk</th>
                                <th>No Trolly</th>
                                <th>Tanggal</th>
                                <th class="num">Bag</th>
                                <th class="num">Kg</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        function renderWarnaBreakdown(w, isSimpanan) {
            if (!w) return `<div class="warna-empty">Belum ada data breakdown warna untuk cell ini.</div>`;

            const fmt = (v) => Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 1 });
            const total = (w.merah?.bag || 0) + (w.biru?.bag || 0) + (w.hijau?.bag || 0) + (w.kuning?.bag || 0);

            if (total === 0) {
                return isSimpanan
                    ? `<div class="warna-empty">Belum ada stock di cell simpanan ini.</div>`
                    : `<div class="warna-empty">Belum ada breakdown warna untuk cell ini (upload Excel dengan kolom BAG MERAH/BIRU/HIJAU/KUNING).</div>`;
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

        // ---------- Pindahkan bag ke cell simpanan ----------

        async function startTransfer(cellId) {
            if (isTransferring) return;

            const cellData = getCellData(cellId);
            const summary = getSelectionSummary(cellId);

                        if (!cellData || summary.count === 0) return;

            const dariSimpanan = !!cellData.isSimpanan;

            // Dari cell simpanan: wajib satu produk per proses.
            if (dariSimpanan && summary.produkIds.length > 1) {
                Swal.fire({
                    title: 'Produk berbeda',
                    text: 'Bag yang dipilih berasal dari lebih dari satu produk. Pindahkan satu produk per proses.',
                    icon: 'warning',
                });
                return;
            }

            // 1. Ambil daftar tujuan + sisa kapasitas live.
            //    Dari cell biasa  -> 6 cell simpanan.
            //    Dari simpanan    -> cell biasa yang terkunci produk yang sama.
            const urlTujuan = dariSimpanan
                ? `{{ route('warehouse.stock.tujuan-produk') }}?produk_id=${encodeURIComponent(summary.produkIds[0])}`
                : `{{ route('warehouse.stock.tujuan-simpanan') }}`;

            let tujuan;
            try {
                Swal.fire({
                    title: 'Memuat cell tujuan...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                const res = await fetch(urlTujuan, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                tujuan = await res.json();
            } catch (err) {
                Swal.fire({ title: 'Gagal', text: `Tidak bisa memuat cell tujuan: ${err.message}`, icon: 'error' });
                return;
            }

            if (!tujuan || tujuan.length === 0) {
                Swal.fire({
                    title: 'Tidak ada cell tujuan',
                    text: dariSimpanan
                        ? 'Belum ada cell yang terkunci untuk produk ini. Bag dari cell simpanan hanya bisa dipindah ke cell yang sudah terkunci produk yang sama.'
                        : 'Belum ada cell simpanan yang aktif.',
                    icon: 'warning',
                });
                return;
            }

            // 2. Modal pilih tujuan. Cell yang kapasitasnya tidak cukup
            //    dinonaktifkan (server tetap memvalidasi ulang).
            const opsi = tujuan.map(t => {
                const cukupBag = Number(t.sisa) >= summary.count;
                const cukupKg = t.sisaKg === null || Number(t.sisaKg) + 0.001 >= summary.kg;
                const ok = cukupBag && cukupKg;

                return `
                    <label style="display:flex; align-items:center; gap:10px; padding:9px 12px; border:1px solid #d5dde5; border-radius:8px; margin-bottom:8px; cursor:${ok ? 'pointer' : 'not-allowed'}; opacity:${ok ? 1 : 0.5};">
                        <input type="radio" name="tujuanCell" value="${t.id}" ${ok ? '' : 'disabled'}>
                        <span style="font-size:13px;">
                            <b>${esc(t.kodeCell)}</b>
                            &mdash; sisa ${fmtNum(t.sisa)} bag / ${t.sisaKg === null ? '-' : fmtNum(t.sisaKg) + ' kg'}
                            ${ok ? '' : '<span style="color:#dc2626;">(kapasitas tidak cukup)</span>'}
                        </span>
                    </label>
                `;
            }).join('');

             const hasil = await Swal.fire({
                title: dariSimpanan ? 'Pindahkan ke Cell Biasa' : 'Pindahkan ke Cell Simpanan',
                html: `
                    <div style="text-align:left; font-size:13px; color:#475569; margin-bottom:12px;">
                        Dari Cell <b>${esc(cellData.kodeCell)}</b>:
                        <b>${fmtNum(summary.count)} bag</b> (${fmtNum(summary.kg)} kg).<br>
                        Pilih cell tujuan:
                    </div>
                    <div style="text-align:left;">${opsi}</div>
                    <div style="text-align:left; font-size:12px; color:#b45309; margin-top:10px;">
                        ${dariSimpanan
                            ? 'Bag hanya bisa dipindah ke cell yang terkunci produk yang sama, dan tidak ada fitur batalkan.'
                            : 'Bag di cell simpanan tidak bisa di-Outbound sebelum dipindah ke cell biasa.'}
                    </div>
                `,
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Pindahkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#64798c',
                preConfirm: () => {
                    const picked = Swal.getPopup().querySelector('input[name="tujuanCell"]:checked');
                    if (!picked) {
                        Swal.showValidationMessage('Pilih salah satu cell tujuan.');
                        return false;
                    }
                    return Number(picked.value);
                },
            });

            if (!hasil.isConfirmed) return;

            await submitTransfer(cellId, hasil.value, summary);
        }

        async function submitTransfer(fromCellId, toCellId, summary) {
            if (isTransferring) return;
            isTransferring = true;

            Swal.fire({
                title: 'Memindahkan stock...',
                html: 'Mohon tunggu, jangan tutup halaman ini.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const res = await fetch(`{{ route('warehouse.stock.transfer') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        from_cell_id: fromCellId,
                        to_cell_id: toCellId,
                        bags: summary.bags,
                    }),
                });

                const result = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(result.message || `Gagal memindahkan stock (HTTP ${res.status}).`);
                }

                await Swal.fire({ title: 'Berhasil!', text: result.message, icon: 'success' });

                // Bersihkan pilihan & cache, lalu muat ulang angka terbaru.
                selectedBags[fromCellId] = new Set();
                batchCache = {};
                await loadStockData();
            } catch (err) {
                await Swal.fire({ title: 'Gagal', text: err.message, icon: 'error' });

                // Kalau gagal karena data berubah (bag sudah keluar/pindah),
                // muat ulang daftar batch cell ini supaya tampilannya sesuai.
                delete batchCache[fromCellId];
                loadBatchesForCell(fromCellId);
            } finally {
                isTransferring = false;
            }
        }

        function resetFilters() {
            document.getElementById('filterSearch').value = '';
            document.getElementById('filterColdStorage').value = '';
            document.getElementById('filterLantai').value = '';
            document.getElementById('filterKategori').value = '';
            document.getElementById('filterWarna').value = '';
            loadStockData();
        }

        document.getElementById('filterSearch').addEventListener('input', debounce(loadStockData, 400));
        document.getElementById('filterColdStorage').addEventListener('change', loadStockData);
        document.getElementById('filterLantai').addEventListener('change', loadStockData);
        document.getElementById('filterKategori').addEventListener('change', loadStockData);
        // Filter warna: tanpa request ke server, cukup render ulang.
        document.getElementById('filterWarna').addEventListener('change', renderAll);

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