<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPIC - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f6fb; --surface: #ffffff; --line: #e1e3f0;
            --primary: #4f46e5; --primary-soft: #eef2ff;
            --text: #1e1b2e; --muted: #6b7280; --success: #16a34a; --danger: #dc2626;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); }
        nav {
            display: flex; justify-content: space-between; align-items: center; padding: 14px 5%;
            background: var(--surface); border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; }
        .logo img { height: 38px; }
        .back-link { color: var(--muted); text-decoration: none; font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        .back-link:hover { color: var(--primary); }

        .page-header { padding: 30px 5% 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .page-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 2.2rem; text-transform: uppercase; }
        .month-picker input {
            height: 40px; border-radius: 8px; border: 1px solid var(--line); padding: 0 12px; font-size: 0.85rem;
        }

        .stat-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; padding: 0 5% 26px; }
        .stat-box { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; padding: 16px 18px; }
        .stat-label { font-family: 'JetBrains Mono', monospace; font-size: 0.62rem; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; }
        .stat-value { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1.7rem; color: var(--text); }
        .stat-box.primary .stat-value { color: var(--primary); }

        .chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 16px; padding: 0 5% 26px; }
        .chart-card { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 18px; }
        .chart-card h4 { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .chart-body { height: 260px; position: relative; }
        .empty-state { text-align: center; padding: 40px; color: var(--muted); font-size: 0.85rem; }

        .section-card {
            background: var(--surface); border: 1px solid var(--line); border-radius: 14px;
            padding: 18px; margin: 0 5% 60px;
        }
        .section-card h4 {
            font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--muted);
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;
        }
        .section-head {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
            margin-bottom: 12px;
        }
        .section-head h4 { margin-bottom: 0; }
        .date-range-filter { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .date-range-filter input[type="date"] {
            height: 36px; border-radius: 8px; border: 1px solid var(--line); padding: 0 10px; font-size: 0.82rem;
        }
        .date-range-filter select {
            height: 36px; border-radius: 8px; border: 1px solid var(--line); padding: 0 10px; font-size: 0.82rem;
            background: #fff; color: var(--text);
        }
        .date-range-filter span { color: var(--muted); font-size: 0.8rem; }
        .btn-terapkan {
            height: 36px; border: none; border-radius: 8px; padding: 0 14px; font-size: 0.8rem; font-weight: 700;
            background: var(--primary); color: #fff; cursor: pointer; transition: .15s;
        }
        .btn-terapkan:hover { background: #4338ca; }

        .table-wrap { overflow-x: auto; }
        table.data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        table.data-table th, table.data-table td {
            padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--line); white-space: nowrap;
        }
        table.data-table th {
            font-family: 'JetBrains Mono', monospace; font-size: 0.65rem; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        table.data-table th.num, table.data-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
        table.data-table tr:hover td { background: var(--primary-soft); }
        table.data-table td.mono-cell { font-family: 'JetBrains Mono', monospace; font-size: 0.78rem; }
        .summary-total {
            display: flex; gap: 24px; padding: 14px 4px 4px; font-size: 0.85rem; color: var(--muted);
        }
        .summary-total b { color: var(--text); font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body>

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
            <span>PPIC - Dashboard</span>
        </div>
        <a href="{{ route('ppic.index') }}" class="back-link">
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span> Menu Utama
        </a>
    </nav>

    <div class="page-header">
        <div class="page-title">Dashboard</div>
        <div class="month-picker">
            <input type="month" id="filterBulan">
        </div>
    </div>

    <div class="stat-strip" id="statStrip">
        <div class="stat-box primary"><div class="stat-label">Total Plan Ekor</div><div class="stat-value" id="statPlanEkor">-</div></div>
        <div class="stat-box"><div class="stat-label">Total Aktual Ekor</div><div class="stat-value" id="statAktualEkor">-</div></div>
        <div class="stat-box primary"><div class="stat-label">Total Plan Kg</div><div class="stat-value" id="statPlanKg">-</div></div>
        <div class="stat-box"><div class="stat-label">Total Aktual Kg</div><div class="stat-value" id="statAktualKg">-</div></div>
        <div class="stat-box"><div class="stat-label">Total PO Bulan Ini</div><div class="stat-value" id="statTotalPo">-</div></div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h4>Tren Plan vs Aktual Ekor</h4>
            <div class="chart-body"><canvas id="chartEkor"></canvas></div>
        </div>
        <div class="chart-card">
            <h4>Tren Plan vs Aktual KG</h4>
            <div class="chart-body"><canvas id="chartKg"></canvas></div>
        </div>
        <div class="chart-card">
            <h4>% Selisih Ekor per Hari</h4>
            <div class="chart-body"><canvas id="chartPersenEkor"></canvas></div>
        </div>
        <div class="chart-card">
            <h4>PO per Jenis</h4>
            <div class="chart-body"><canvas id="chartPo"></canvas></div>
        </div>
    </div>

    <div class="section-card">
        <h4>Rekap Produksi Fresh per PO</h4>
        <div class="table-wrap">
            <table class="data-table">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Jenis PO</th>
                    <th>Tanggal</th>
                    <th class="num">Qty Main</th>
                    <th class="num">Qty By-Product</th>
                    <th class="num">Qty Total</th>
                    <th class="num">Jumlah Entri</th>
                </tr>
            </thead>
                <tbody id="tblProduksiFreshBody">
                    <tr><td colspan="7" class="empty-state">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== BARU: REKAP SERAH TERIMA PER PRODUK ==================== --}}
    <div class="section-card">
        <div class="section-head">
            <h4>Rekap Serah Terima per Produk</h4>
            <div class="date-range-filter">
                <span class="material-symbols-outlined" style="font-size:16px; color: var(--muted);">calendar_month</span>
                <input type="date" id="stDari">
                <span>s/d</span>
                <input type="date" id="stSampai">
                <select id="stJenisPo" onchange="onJenisPoChange()">
                    <option value="">Semua Jenis PO</option>
                    <option value="FEH0">FEH0</option>
                    <option value="FEH1">FEH1</option>
                    <option value="FEH2">FEH2</option>
                    <option value="FEHM">FEHM</option>
                </select>
                <select id="stKodeProduk">
                    <option value="">Semua Produk</option>
                </select>
                <button class="btn-terapkan" onclick="loadSerahTerima()">Terapkan</button>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Jenis PO</th>
                        <th>Kode Batch</th>
                        <th>Tanggal</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th class="num">Jumlah Bag</th>
                        <th class="num">Total Kg</th>
                    </tr>
                </thead>
                <tbody id="tblSerahTerimaBody">
                    <tr><td colspan="8" class="empty-state">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="summary-total" id="stSummaryTotal"></div>
    </div>

    <script>
        let charts = {};
        const filterBulan = document.getElementById('filterBulan');
        filterBulan.value = new Date().toISOString().substring(0, 7);

        async function loadDashboard() {
            try {
                const res = await fetch(`{{ route('ppic.dashboard.data') }}?bulan=${filterBulan.value}`);
                const data = await res.json();
                renderStats(data.summary, data.totalPo);
                renderCharts(data.trend, data.poByJenis);
                renderProduksiFresh(data.produksiFresh);
            } catch (err) {
                console.error(err);
            }
        }

        function renderStats(s, totalPo) {
            document.getElementById('statPlanEkor').innerText = Number(s.totalPlanEkor).toLocaleString('id-ID');
            document.getElementById('statAktualEkor').innerText = Number(s.totalAktualEkor).toLocaleString('id-ID');
            document.getElementById('statPlanKg').innerText = Number(s.totalPlanKg).toLocaleString('id-ID', { maximumFractionDigits: 1 });
            document.getElementById('statAktualKg').innerText = Number(s.totalAktualKg).toLocaleString('id-ID', { maximumFractionDigits: 1 });
            document.getElementById('statTotalPo').innerText = totalPo;
        }

        function renderCharts(trend, poByJenis) {
            Object.values(charts).forEach(c => c && c.destroy());

            if (trend.length === 0) {
                ['chartEkor', 'chartKg', 'chartPersenEkor'].forEach(id => {
                    document.getElementById(id).parentElement.innerHTML = `<div class="empty-state">Belum ada data bulan ini.</div>`;
                });
            } else {
                const labels = trend.map(t => t.tanggal);

                charts.ekor = new Chart(document.getElementById('chartEkor'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            { label: 'Plan', data: trend.map(t => t.planEkor), borderColor: '#a5b4fc', backgroundColor: 'rgba(165,180,252,.15)', fill: true, tension: 0.2 },
                            { label: 'Aktual', data: trend.map(t => t.aktualEkor), borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,.15)', fill: true, tension: 0.2 },
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });

                charts.kg = new Chart(document.getElementById('chartKg'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            { label: 'Plan Kg', data: trend.map(t => t.planKg), borderColor: '#fbbf24', backgroundColor: 'rgba(251,191,36,.15)', fill: true, tension: 0.2 },
                            { label: 'Aktual Kg', data: trend.map(t => t.aktualKg), borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.15)', fill: true, tension: 0.2 },
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });

                charts.persen = new Chart(document.getElementById('chartPersenEkor'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: '% Selisih Ekor',
                            data: trend.map(t => t.persenSelisihEkor),
                            backgroundColor: trend.map(t => t.persenSelisihEkor >= 0 ? 'rgba(22,163,74,.6)' : 'rgba(220,38,38,.6)'),
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            const poLabels = Object.keys(poByJenis);
            if (poLabels.length === 0) {
                document.getElementById('chartPo').parentElement.innerHTML = `<div class="empty-state">Belum ada PO bulan ini.</div>`;
            } else {
                charts.po = new Chart(document.getElementById('chartPo'), {
                    type: 'doughnut',
                    data: {
                        labels: poLabels,
                        datasets: [{ data: Object.values(poByJenis), backgroundColor: ['#4f46e5', '#a5b4fc', '#fbbf24', '#16a34a', '#dc2626'] }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }
        }

        function renderProduksiFresh(rows) {
            const tbody = document.getElementById('tblProduksiFreshBody');

            if (!rows || rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Belum ada input Produksi Fresh bulan ini.</td></tr>`;
                return;
            }

            tbody.innerHTML = rows.map(r => `
                <tr>
                    <td>${r.nomorPo}</td>
                    <td>${r.jenisPo}</td>
                    <td>${r.tanggalLabel}</td>
                    <td class="num">${Number(r.qtyMain).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="num">${Number(r.qtyByProduct).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="num">${Number(r.qtyTotal).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                    <td class="num">${r.jumlahEntri}</td>
                </tr>
            `).join('');
        }

        // ==================== BARU: REKAP SERAH TERIMA (FILTER RENTANG TANGGAL) ====================

        async function loadSerahTerima() {
            const dari = document.getElementById('stDari').value;
            const sampai = document.getElementById('stSampai').value;
            const jenisPo = document.getElementById('stJenisPo').value;
            const kodeProduk = document.getElementById('stKodeProduk').value;
            const tbody = document.getElementById('tblSerahTerimaBody');
            tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Memuat data...</td></tr>`;
            document.getElementById('stSummaryTotal').innerHTML = '';

            try {
                const res = await fetch(`{{ route('ppic.dashboard.serah-terima-data') }}?dari=${encodeURIComponent(dari)}&sampai=${encodeURIComponent(sampai)}&jenis_po=${encodeURIComponent(jenisPo)}&kode_produk=${encodeURIComponent(kodeProduk)}`);
                if (!res.ok) throw new Error('Gagal memuat data.');
                const data = await res.json();
                renderSerahTerima(data.per_batch);
                renderSerahTerimaSummary(data.summary);
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Gagal memuat data: ${err.message}</td></tr>`;
            }
        }

        // BARU - Isi dropdown "Nama Produk", DIPERSEMPIT sesuai Jenis PO
        // yang sedang dipilih (dropdown kosong = Semua Jenis PO -> semua
        // produk yang relevan ke Serah Terima ditampilkan).
        async function loadProdukOptions() {
            const jenisPo = document.getElementById('stJenisPo').value;
            const select = document.getElementById('stKodeProduk');
            select.innerHTML = `<option value="">Memuat...</option>`;

            try {
                const res = await fetch(`{{ route('ppic.dashboard.serah-terima-produk-list') }}?jenis_po=${encodeURIComponent(jenisPo)}`);
                if (!res.ok) throw new Error('Gagal memuat daftar produk.');
                const produk = await res.json();

                select.innerHTML = `<option value="">Semua Produk</option>` + produk.map(p =>
                    `<option value="${p.code}">${p.code} - ${p.name}</option>`
                ).join('');
            } catch (err) {
                select.innerHTML = `<option value="">Gagal memuat produk</option>`;
                console.error(err);
            }
        }

        // Setiap kali Jenis PO diganti, dropdown produk di-refresh ulang
        // (pilihan lama otomatis reset ke "Semua Produk").
        function onJenisPoChange() {
            loadProdukOptions();
        }

        function renderSerahTerima(rows) {
            const tbody = document.getElementById('tblSerahTerimaBody');

            if (!rows || rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Belum ada data Serah Terima pada rentang tanggal ini.</td></tr>`;
                return;
            }

            tbody.innerHTML = rows.map(r => `
                <tr>
                    <td class="mono-cell">${r.no_po}</td>
                    <td class="mono-cell">${r.jenis_po}</td>
                    <td class="mono-cell">${r.kode_batch}</td>
                    <td>${r.tanggal_produksi}</td>
                    <td>${r.kode_produk}</td>
                    <td>${r.nama_produk}</td>
                    <td class="num">${Number(r.jumlah_bag).toLocaleString('id-ID')}</td>
                    <td class="num">${Number(r.total_kg).toLocaleString('id-ID', { maximumFractionDigits: 1 })}</td>
                </tr>
            `).join('');
        }

        function renderSerahTerimaSummary(summary) {
            if (!summary) return;
            document.getElementById('stSummaryTotal').innerHTML = `
                <div>Jumlah Batch: <b>${Number(summary.total_batch).toLocaleString('id-ID')}</b></div>
                <div>Total Bag: <b>${Number(summary.total_bag).toLocaleString('id-ID')}</b></div>
                <div>Total Kg: <b>${Number(summary.total_kg).toLocaleString('id-ID', { maximumFractionDigits: 1 })}</b></div>
            `;
        }

        function initSerahTerimaDefaultRange() {
            const sampai = new Date();
            const dari = new Date();
            dari.setDate(dari.getDate() - 6);
            document.getElementById('stDari').value = dari.toISOString().split('T')[0];
            document.getElementById('stSampai').value = sampai.toISOString().split('T')[0];
        }

        filterBulan.addEventListener('change', loadDashboard);
        loadDashboard();

        initSerahTerimaDefaultRange();
        loadProdukOptions();
        loadSerahTerima();
    </script>
</body>
</html>