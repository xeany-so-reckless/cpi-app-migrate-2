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
        .table-wrap { overflow-x: auto; }
        table.data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        table.data-table th, table.data-table td {
            padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--line); white-space: nowrap;
        }
        table.data-table th {
            font-family: 'JetBrains Mono', monospace; font-size: 0.65rem; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        table.data-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
        table.data-table tr:hover td { background: var(--primary-soft); }
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

        filterBulan.addEventListener('change', loadDashboard);
        loadDashboard();
    </script>
</body>
</html>