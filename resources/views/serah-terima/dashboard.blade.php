<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Rekap - Serah Terima Hasil Produksi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --ink: #0b1220;
            --canvas: #eef2f6;
            --surface: #ffffff;
            --line: #e1e7ef;
            --muted: #64748b;
            --accent: #0f7a3d;
            --accent-dark: #0a5129;
            --accent-soft: #e3f5ea;
            --amber: #b45309;
            --amber-soft: #fef3c7;
            --red: #b91c1c;
            --red-soft: #fee2e2;
            --indigo: #3730a3;
            --indigo-soft: #e6e4fb;
            --blue: #1d4ed8;
            --blue-soft: #eef6ff;
        }

        body {
            background-color: var(--canvas);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
        }

        .mono-tag { font-family: 'JetBrains Mono', monospace; letter-spacing: 0.02em; }

        /* ---------- NAVBAR (sama seperti index.blade.php) ---------- */
        .navbar-custom { background-color: var(--ink); border-bottom: 1px solid rgba(255,255,255,.08); }
        .navbar-custom .navbar-brand { font-family: 'Manrope', sans-serif; font-weight: 800; letter-spacing: -0.01em; }
        .navbar-custom .navbar-brand small {
            display: block; font-family: 'Inter', sans-serif; font-weight: 500; font-size: 10px;
            letter-spacing: 0.08em; text-transform: uppercase; color: #94a3b8;
        }
        .btn-ghost-nav {
            background: transparent; border: 1px solid rgba(255,255,255,.25); color: #f1f5f9;
            font-weight: 600; border-radius: 999px; padding: 6px 16px; font-size: 13px; transition: .2s;
            text-decoration: none; display: inline-block;
        }
        .btn-ghost-nav:hover { background: rgba(255,255,255,.1); color: #fff; }

        h5.fw-bold, h6.fw-bold, .card h5 { font-family: 'Manrope', sans-serif; font-weight: 800; color: var(--ink); }

        label {
            font-family: 'Inter', sans-serif; font-weight: 600; font-size: 12.5px; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .form-control, .form-select { border-radius: 9px; border: 1px solid var(--line); }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 .2rem var(--accent-soft); }

        .btn { border-radius: 9px; font-family: 'Inter', sans-serif; font-weight: 600; }
        .btn-success { background: var(--accent); border-color: var(--accent); }
        .btn-success:hover { background: var(--accent-dark); border-color: var(--accent-dark); }

        .card { border: 1px solid var(--line); border-radius: 14px; }

        /* ---------- SUMMARY CARDS ---------- */
        .summary-card {
            background: var(--surface); border: 1px solid var(--line); border-radius: 14px;
            padding: 18px 20px;
        }
        .summary-card .summary-label {
            font-family: 'Inter', sans-serif; font-weight: 600; font-size: 12px; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px;
        }
        .summary-card .summary-value {
            font-family: 'Manrope', sans-serif; font-weight: 800; font-size: 26px; color: var(--ink);
        }
        .summary-card .summary-value small { font-size: 14px; font-weight: 600; color: var(--muted); }

        .chart-wrap { position: relative; height: 320px; }
        .chart-wrap.chart-small { height: 260px; }

        .table-rekap { font-size: 13px; }
        .table-rekap th {
            background: var(--ink); color: #fff; font-family: 'Inter', sans-serif; font-weight: 600;
            font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.02em;
        }
        .table-rekap td { vertical-align: middle; }
        .rank-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--accent-soft); color: var(--accent-dark);
            font-weight: 700; font-size: 11px;
        }

        .empty-state {
            border: 1px dashed var(--line); border-radius: 14px; background: var(--surface);
            padding: 40px 20px; text-align: center; color: var(--muted); font-family: 'Inter', sans-serif;
        }

        .loading-overlay { text-align: center; color: var(--muted); padding: 30px; font-size: 13px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-custom px-4 py-3 d-flex justify-content-between">
        <span class="navbar-brand text-white mb-0">
            Dashboard Rekap Produksi
            <small>CPI Jombang Plant &middot; Serah Terima Hasil Produksi</small>
        </span>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('serahterima.index') }}" class="btn-ghost-nav">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Input Tally
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4 mb-5">

        <!-- ================= FILTER RENTANG TANGGAL ================= -->
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded shadow-sm flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="fw-bold text-secondary text-uppercase small" style="font-size: 8.5pt;">
                    <i class="fa-solid fa-calendar-days me-1"></i> Rentang Tanggal:
                </span>
                <input type="date" id="filter_dari" class="form-control form-control-sm d-inline-block w-auto shadow-sm">
                <span class="text-muted small">s/d</span>
                <input type="date" id="filter_sampai" class="form-control form-control-sm d-inline-block w-auto shadow-sm">
                <button class="btn btn-success btn-sm fw-bold shadow-sm" onclick="loadDashboard()">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Terapkan
                </button>
                <button class="btn btn-outline-secondary btn-sm fw-bold shadow-sm" onclick="setQuickRange(7)">7 Hari</button>
                <button class="btn btn-outline-secondary btn-sm fw-bold shadow-sm" onclick="setQuickRange(30)">30 Hari</button>
            </div>
            <div id="rangeInfo" class="text-muted small"></div>
        </div>

        <div id="dashboardLoading" class="loading-overlay">Memuat data dashboard...</div>

        <div id="dashboardContent" class="d-none">

            <!-- ================= RINGKASAN ================= -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="summary-card">
                        <div class="summary-label">Total Bag</div>
                        <div class="summary-value" id="sum_total_bag">0 <small>bag</small></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card">
                        <div class="summary-label">Total Berat</div>
                        <div class="summary-value" id="sum_total_kg">0 <small>kg</small></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card">
                        <div class="summary-label">Jumlah Batch / Trolly</div>
                        <div class="summary-value" id="sum_total_batch">0 <small>batch</small></div>
                    </div>
                </div>
            </div>

            <!-- ================= TREND CHART ================= -->
            <div class="card p-3 shadow-sm mb-3">
                <h6 class="fw-bold mb-3">Trend Produksi Harian (Total Bag & Kg)</h6>
                <div class="chart-wrap">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>

            <div class="row g-3">
                <!-- ================= BREAKDOWN PRODUK ================= -->
                <div class="col-lg-6">
                    <div class="card p-3 shadow-sm h-100">
                        <h6 class="fw-bold mb-3">Breakdown per Produk</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-rekap m-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th>Kode</th>
                                        <th>Nama Produk</th>
                                        <th class="text-end">Bag</th>
                                        <th class="text-end">Kg</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelProduk"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= BREAKDOWN CELL ================= -->
                <div class="col-lg-6">
                    <div class="card p-3 shadow-sm h-100">
                        <h6 class="fw-bold mb-3">Breakdown per Cell</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-rekap m-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th>Kode Cell</th>
                                        <th class="text-end">Batch</th>
                                        <th class="text-end">Bag</th>
                                        <th class="text-end">Kg</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelCell"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div id="dashboardEmpty" class="empty-state d-none">
            Belum ada data produksi pada rentang tanggal ini.
        </div>

    </div>

    <script>
        let chartTrend = null;

        function todayStr() { return new Date().toISOString().split('T')[0]; }

        function setQuickRange(days) {
            const sampai = new Date();
            const dari = new Date();
            dari.setDate(dari.getDate() - (days - 1));
            document.getElementById('filter_dari').value = dari.toISOString().split('T')[0];
            document.getElementById('filter_sampai').value = sampai.toISOString().split('T')[0];
            loadDashboard();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Default: 7 hari terakhir (konsisten dengan default backend)
            const sampai = new Date();
            const dari = new Date();
            dari.setDate(dari.getDate() - 6);
            document.getElementById('filter_dari').value = dari.toISOString().split('T')[0];
            document.getElementById('filter_sampai').value = sampai.toISOString().split('T')[0];
            loadDashboard();
        });

        async function loadDashboard() {
            const dari = document.getElementById('filter_dari').value;
            const sampai = document.getElementById('filter_sampai').value;

            document.getElementById('dashboardLoading').classList.remove('d-none');
            document.getElementById('dashboardContent').classList.add('d-none');
            document.getElementById('dashboardEmpty').classList.add('d-none');

            try {
                const res = await fetch(`/serah-terima/dashboard-data?dari=${encodeURIComponent(dari)}&sampai=${encodeURIComponent(sampai)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error('Gagal memuat data dashboard.');
                const json = await res.json();

                document.getElementById('rangeInfo').innerText = `Menampilkan ${json.summary.dari} s/d ${json.summary.sampai}`;

                if (json.summary.total_batch === 0) {
                    document.getElementById('dashboardEmpty').classList.remove('d-none');
                    document.getElementById('dashboardLoading').classList.add('d-none');
                    return;
                }

                renderSummary(json.summary);
                renderTrendChart(json.trend);
                renderProdukSection(json.per_produk);
                renderCellSection(json.per_cell);

                document.getElementById('dashboardLoading').classList.add('d-none');
                document.getElementById('dashboardContent').classList.remove('d-none');
            } catch (err) {
                document.getElementById('dashboardLoading').classList.add('d-none');
                alert('Gagal memuat dashboard: ' + err.message);
            }
        }

        function renderSummary(summary) {
            document.getElementById('sum_total_bag').innerHTML = `${summary.total_bag} <small>bag</small>`;
            document.getElementById('sum_total_kg').innerHTML = `${summary.total_kg.toFixed(1)} <small>kg</small>`;
            document.getElementById('sum_total_batch').innerHTML = `${summary.total_batch} <small>batch</small>`;
        }

        function renderTrendChart(trend) {
            const labels = trend.map(t => t.tanggal);
            const bagData = trend.map(t => t.total_bag);
            const kgData = trend.map(t => t.total_kg);

            if (chartTrend) chartTrend.destroy();
            chartTrend = new Chart(document.getElementById('chartTrend'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Bag',
                            data: bagData,
                            borderColor: '#0f7a3d',
                            backgroundColor: 'rgba(15,122,61,0.1)',
                            yAxisID: 'yBag',
                            tension: 0.3,
                        },
                        {
                            label: 'Total Kg',
                            data: kgData,
                            borderColor: '#3730a3',
                            backgroundColor: 'rgba(55,48,163,0.08)',
                            yAxisID: 'yKg',
                            tension: 0.3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        yBag: { type: 'linear', position: 'left', title: { display: true, text: 'Bag' } },
                        yKg: { type: 'linear', position: 'right', title: { display: true, text: 'Kg' }, grid: { drawOnChartArea: false } },
                    },
                },
            });
        }

        function renderProdukSection(perProduk) {
            const tbody = document.getElementById('tabelProduk');
            tbody.innerHTML = perProduk.map((p, i) => `
                <tr>
                    <td><span class="rank-badge">${i + 1}</span></td>
                    <td class="mono-tag">${p.kode_produk}</td>
                    <td>${p.nama_produk}</td>
                    <td class="text-end">${p.total_bag}</td>
                    <td class="text-end">${p.total_kg.toFixed(1)}</td>
                </tr>
            `).join('') || `<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>`;
        }

        function renderCellSection(perCell) {
            const tbody = document.getElementById('tabelCell');
            tbody.innerHTML = perCell.map((c, i) => `
                <tr>
                    <td><span class="rank-badge">${i + 1}</span></td>
                    <td class="mono-tag">${c.kode_cell}</td>
                    <td class="text-end">${c.total_batch}</td>
                    <td class="text-end">${c.total_bag}</td>
                    <td class="text-end">${c.total_kg.toFixed(1)}</td>
                </tr>
            `).join('') || `<tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>`;
        }
    </script>
</body>
</html>