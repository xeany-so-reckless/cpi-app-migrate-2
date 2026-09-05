<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>Production Integrated System - Industrial</title> --}}
    <title> Slaughter House - Jombang </title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Chivo+Mono:wght@300;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        :root {
            --primary: #2e7d32;
            --dark-steel: #1a1c1e;
            --concrete: #f4f4f4;
            --iron: #333639;
            --glass: rgba(255, 255, 255, 0.1);
            --industrial-red: #8B2626;
            --success-green: #2ac331;
            --primary2: #FFAC1C;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { 
            background:
            radial-gradient(circle at top left,#eef7ff,transparent 35%),
            radial-gradient(circle at bottom right,#f5fff8,transparent 40%),
            #f7f9fc;
         }

        .top-ticker {
            background: var(--iron); color: #aaa; font-family: 'Chivo Mono', monospace;
            font-size: 0.75rem; padding: 8px 5%; display: flex; justify-content: space-between;
            align-items: center; border-bottom: 1px solid #444; gap: 20px;
        }
        .datetime-container { color: var(--primary2); font-weight: bold; white-space: nowrap; border-right: 1px solid #444; padding-right: 20px; }
        .marquee-container { flex-grow: 1; overflow: hidden; white-space: nowrap; }
        .marquee { display: inline-block; padding-left: 100%; animation: marquee 30s linear infinite; text-transform: uppercase; letter-spacing: 1px; }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }

        nav { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background: white; border-bottom: 3px solid var(--iron); position: sticky; top: 0; z-index: 1000; }
        .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.1rem; color: var(--iron); letter-spacing: -0.5px; }

        .hero {
            position: relative;
            height: 38vh;
            min-height: 260px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 8%;
            overflow: hidden;

            background: linear-gradient(
                135deg,
                #0a1839 0%,
                #224377 45%,
                #45658f 100%
            );

            border-radius: 0px;
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            top: -120px;
            left: -100px;
            background: rgba(59,130,246,.18);
            border-radius: 50%;
            filter: blur(70px);
}

        .hero::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -80px;
            bottom: -80px;
            background: rgba(16,185,129,.12);
            border-radius: 50%;
            filter: blur(70px);
}

        .hero-text{
            position:relative;
            z-index:2;
            max-width:550px;
}

        .hero-text h1{
            font-size:3rem;
            line-height:1.1;
            font-weight:800;
            color:#fff;
            margin-bottom:15px;
}

        .hero-text h1 span{
            color:#f59e0b;
}

        .hero-text p{
            color:rgba(255,255,255,.8);
            border-left:4px solid #10b981;
            padding-left:16px;
            font-size:.95rem;
            line-height:1.8;
}

        .hero-visual{
            position:absolute;
            right:50px;
            bottom:-10px;

            font-size:110px;
            font-weight:900;
            color:rgba(255,255,255,.05);

            letter-spacing:-5px;
            user-select:none;
}

        .content-section { padding: 60px 8%; }
        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }

        .file-card {
                background: white;
                padding: 30px;
                border: 1px solid #ddd;
                border-radius: 16px;
                transition: all 0.35s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                position: relative;
                overflow: hidden;

        }
        .file-card:hover { 
                background: linear-gradient(135deg, #ddf3f4 0%, #7b96f8 100%);
                border-color: #219ff35f;
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.418);
                                    
        }
        .file-name { font-weight: bold; font-size: 1.1rem; margin-bottom: 10px; }
        .file-info { font-size: 0.8rem; color: #777; margin-bottom: 15px; flex-grow: 1; }
        .file-link { color: var(--iron); font-weight: bold; text-decoration: none; border-bottom: 2px solid var(--success-green); padding-bottom: 2px; }

        .status-badge {
            position: absolute; top: 12px; right: 12px; font-family: 'Chivo Mono', monospace;
            font-size: 0.65rem; font-weight: bold; padding: 4px 8px; border-radius: 3px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .status-active { background-color: rgba(46, 125, 50, 0.1); color: var(--success-green); border: 1px solid var(--success-green); animation: blink-animation 1.5s steps(5, start) infinite; }
        .status-soon { background-color: #eee; color: #777; border: 1px solid #ccc; }
        @keyframes blink-animation { to { visibility: hidden; } }

        footer { background: var(--iron); color: #ffffff; padding: 30px; text-align: center; font-size: 0.8rem; }

        /* ===== WIDGET RINGKASAN DASHBOARD ===== */
        .preview-section { padding: 40px 8% 0 8%; }
        .preview-heading { font-size: 1.2rem; color: var(--iron); margin-bottom: 15px; display: flex; align-items: center; gap: 8px; font-weight: 700; }
        .preview-heading .material-symbols-outlined { color: var(--primary); font-size: 22px; }

        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 35px; }
        .stat-box { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #9ca3af; margin-bottom: 6px; }
        .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--iron); }
        .stat-box.danger .stat-value { color: #dc2626; }
        .stat-box.warning .stat-value { color: #d97706; }
        .stat-box.info .stat-value { color: #2563eb; }

        .chart-grid-top { display: grid; grid-template-columns: minmax(240px, 320px) 1fr; gap: 16px; margin-bottom: 16px; }
        .chart-grid-bottom { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .chart-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        .chart-card h4 { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #9ca3af; margin-bottom: 10px; }
        .chart-card .chart-body { height: 220px; position: relative; }

        .preview-empty { grid-column: 1 / -1; text-align: center; padding: 30px; color: #9ca3af; background: white; border: 1px dashed #e5e7eb; border-radius: 14px; font-size: 0.85rem; margin-bottom: 35px; }

        .widget-link { display: block; text-decoration: none; color: inherit; cursor: pointer; border-radius: 14px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .widget-link:hover { transform: translateY(-3px); }
        .widget-link:hover .stat-box,
        .widget-link:hover .chart-card { box-shadow: 0 10px 24px rgba(0,0,0,0.08); border-color: #d1d5db; }
        .widget-link .preview-heading { transition: color 0.2s ease; }
        .widget-link:hover .preview-heading { color: #2563eb; }
        .widget-link .preview-heading .material-symbols-outlined.link-arrow { margin-left: auto; font-size: 18px; opacity: 0; transition: opacity 0.2s ease, transform 0.2s ease; }
        .widget-link:hover .preview-heading .link-arrow { opacity: 1; transform: translateX(3px); color: #2563eb; }

        @media (max-width: 768px) {
            .chart-grid-top, .chart-grid-bottom { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="top-ticker">
        <div class="datetime-container" id="current-datetime">
            <span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;">schedule</span> Loading...
        </div>
        <div class="marquee-container">
            <div class="marquee">
                Selamat Datang di Sistem Integrasi Departemen Produksi CPI Jombang &nbsp; | &nbsp; 1 Halaman Untuk Semua Dokumen &nbsp; | &nbsp; {{ $system_phase }}
            </div>
        </div>
    </div>

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="height: 50px;">
            <span>{{ $plant_name }}</span>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-text">
            <h1 class="hero-text">
                DOCUMENT
                <br>
                <span>INTEGRATED</span>
            </h1>
            <p id="typing"></p>
        </div>
        <div class="hero-visual">{{ $version }}</div>
    </section>

    <section class="preview-section">

        <a href="{{ route('lbreport.dashboard') }}" class="widget-link">
            <h2 class="preview-heading">
                <span class="material-symbols-outlined">calendar_month</span> Summary Penerimaan LB Bulan Ini
                <span class="material-symbols-outlined link-arrow">arrow_forward</span>
            </h2>
            <div id="lbSummaryGrid" class="stat-grid">
                <div class="preview-empty">Memuat data...</div>
            </div>
            <div id="lbSummaryAreaGrid" class="stat-grid"></div>
        </a>

        <a href="{{ route('produksi-dashboard.index') }}" class="widget-link">
            <h2 class="preview-heading">
                <span class="material-symbols-outlined">monitoring</span> Grafik Produksi Bulan Ini
                <span class="material-symbols-outlined link-arrow">arrow_forward</span>
            </h2>
            <div id="monthlyChartWrapper">
                <div class="chart-grid-top">
                    <div class="chart-card">
                        <h4>Komposisi Hasil Produksi</h4>
                        <div class="chart-body"><canvas id="dashPieChart"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <h4>Monitoring Defect (% Defect vs % KW2)</h4>
                        <div class="chart-body"><canvas id="dashAreaChart"></canvas></div>
                    </div>
                </div>
                <div class="chart-grid-bottom">
                    <div class="chart-card">
                        <h4>Tren Yield Titik Nol</h4>
                        <div class="chart-body"><canvas id="dashLineTitikNol"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <h4>Tren Yield FG + BP Others</h4>
                        <div class="chart-body"><canvas id="dashLineFgBp"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <h4>Tren Yield By Product</h4>
                        <div class="chart-body"><canvas id="dashLineByProduct"></canvas></div>
                    </div>
                </div>
            </div>
            <div id="monthlyChartEmpty" class="preview-empty" style="display:none;">Belum ada data produksi bulan ini.</div>
        </a>

    </section>

    <div class="content-section">
        <div class="grid-container">
            @foreach ($production_docs as $doc)
                @php
                    $is_soon = ($doc['url'] === '#');
                    $badge_class = $is_soon ? 'status-soon' : 'status-active';
                    $badge_label = $is_soon ? 'Coming Soon' : '● Active';
                @endphp
                <div class="file-card">
                    <span class="status-badge {{ $badge_class }}">{{ $badge_label }}</span>

                    <span class="material-symbols-outlined" style="color: var(--iron); font-size: 40px; margin-bottom:15px;">
                        {{ $doc['icon'] }}
                    </span>

                    <div class="file-name">{{ $doc['name'] }}</div>
                    <p class="file-info">{{ $doc['info'] }}</p>

                    <a href="{{ $doc['url'] }}" class="file-link">SIGN IN</a>
                </div>
            @endforeach
        </div>
    </div>

    <footer>
        &copy; {{ date('Y') }} Departemen Produksi CPI Jombang | Production Integrated System 
    </footer>

    <script>
        function updateDateTime() {
            const now = new Date();
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const dateStr = now.toLocaleDateString('id-ID', options);
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false});

            document.getElementById('current-datetime').innerHTML =
                `<span class="material-symbols-outlined" style="font-size:12px; vertical-align:middle;">schedule</span> ${dateStr} | ${timeStr}`;
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>

    <script>
const text = "Digitalisasi dokumen produksi dan manajemen data departemen berbasis sistem terintegrasi.";
const typingElement = document.getElementById("typing");

let index = 0;

function typeWriter() {
    if (index < text.length) {
        typingElement.textContent += text.charAt(index);
        index++;
        setTimeout(typeWriter, 40);
    } else {
        setTimeout(() => {
            typingElement.textContent = "";
            index = 0;
            typeWriter();
        }, 2000);
    }
}

typeWriter();
</script>

    <script>
        // ===== WIDGET: RINGKASAN PENERIMAAN LB BULAN INI =====
        // Menggunakan endpoint publik yang sudah ada di modul Report LB,
        // sekarang memakai parameter `bulan` (yyyy-MM) alih-alih `tanggal`
        // supaya rekapnya mencakup satu bulan berjalan, bukan cuma hari ini.
        // Menyusun 4 stat-box tambahan untuk susut per Area (1-4), memakai
        // breakdown `area` yang sudah dihitung backend di rekap().
        function renderSusutAreaCards(area) {
            if (!area) return '';

            const areaKeys = Object.keys(area).sort();

            return areaKeys.map(key => {
                const a = area[key];
                const persen = a && typeof a.persen === 'number' ? a.persen : 0;
                const colorClass = persen >= 5 ? 'danger' : (persen >= 3 ? 'warning' : '');

                return `
                    <div class="stat-box ${colorClass}">
                        <div class="stat-label">Susut Area ${key}</div>
                        <div class="stat-value">${persen.toFixed(2)}%</div>
                    </div>
                `;
            }).join('');
        }

        async function loadLbSummaryBulanIni() {
            const grid = document.getElementById('lbSummaryGrid');
            const currentMonth = new Date().toISOString().substring(0, 7); // yyyy-MM

            try {
                const response = await fetch(`{{ route('lbreport.rekap-data') }}?bulan=${currentMonth}`);
                const res = await response.json();
                const h = res && res.harian ? res.harian : null;

                if (!h || !h.rincianRit || h.rincianRit.length === 0) {
                    grid.innerHTML = `<div class="preview-empty">Belum ada data penerimaan LB bulan ini.</div>`;
                    document.getElementById('lbSummaryAreaGrid').innerHTML = '';
                    return;
                }

                grid.innerHTML = `
                    <div class="stat-box">
                        <div class="stat-label">Total Kg Netto</div>
                        <div class="stat-value">${(h.kgNetto || 0).toLocaleString('id-ID', {minimumFractionDigits: 1})}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Total Ekor Netto</div>
                        <div class="stat-value">${(h.ekorNetto || 0).toLocaleString('id-ID')}</div>
                    </div>
                    <div class="stat-box danger">
                        <div class="stat-label">Ayam Mati</div>
                        <div class="stat-value">${(h.mati || 0).toLocaleString('id-ID')}</div>
                    </div>
                    <div class="stat-box info">
                        <div class="stat-label">Global Susut</div>
                        <div class="stat-value">${(h.persenSusut || 0).toFixed(2)}%</div>
                    </div>
                `;
                document.getElementById('lbSummaryAreaGrid').innerHTML = renderSusutAreaCards(h.area);
            } catch (err) {
                grid.innerHTML = `<div class="preview-empty">Gagal memuat ringkasan LB: ${err.message}</div>`;
                document.getElementById('lbSummaryAreaGrid').innerHTML = '';
            }
        }

        // ===== WIDGET: GRAFIK PRODUKSI BULAN INI =====
        // Menggunakan endpoint publik dari modul Dashboard Produksi Bulanan,
        // difilter ke bulan berjalan di sisi client (sama seperti mode
        // "bulanan" di halaman aslinya).
        let dashCharts = {};

        async function loadMonthlyChart() {
            const currentMonth = new Date().toISOString().substring(0, 7); // yyyy-MM

            try {
                const response = await fetch('{{ route('produksi-dashboard.data') }}');
                const allData = await response.json();
                const data = (allData || []).filter(d => d.bulan === currentMonth);

                if (data.length === 0) {
                    document.getElementById('monthlyChartWrapper').style.display = 'none';
                    document.getElementById('monthlyChartEmpty').style.display = 'block';
                    return;
                }

                document.getElementById('monthlyChartEmpty').style.display = 'none';
                renderDashboardCharts(data);
            } catch (err) {
                document.getElementById('monthlyChartWrapper').style.display = 'none';
                document.getElementById('monthlyChartEmpty').innerText = 'Gagal memuat grafik: ' + err.message;
                document.getElementById('monthlyChartEmpty').style.display = 'block';
            }
        }

        function renderDashboardCharts(data) {
            Object.values(dashCharts).forEach(c => c && c.destroy());

            const chartLabels = data.map(d => {
                const parts = d.tanggal.split('-');
                return parts.length === 3 ? parts[2] : d.tanggal;
            });

            const griller = data.reduce((a, b) => a + b.prodGriller, 0);
            const parting = data.reduce((a, b) => a + b.prodParting, 0);
            const marinasi = data.reduce((a, b) => a + b.prodMarinasi, 0);
            const totalHasil = data.reduce((a, b) => a + b.totalHasil, 0);

            dashCharts.pie = new Chart(document.getElementById('dashPieChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Griller', 'Parting', 'Marinasi'],
                    datasets: [{
                        data: totalHasil > 0 ? [(griller/totalHasil)*100, (parting/totalHasil)*100, (marinasi/totalHasil)*100] : [0,0,0],
                        backgroundColor: ['#0d9488', '#d97706', '#e11d48']
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                        datalabels: { color: '#fff', font: { weight: 'bold', size: 10 }, formatter: (v) => v > 0 ? v.toFixed(1) + '%' : '' }
                    }
                }
            });

            dashCharts.area = new Chart(document.getElementById('dashAreaChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { label: '% Defect', data: data.map(d => d.pctDefect), backgroundColor: 'rgba(225,29,72,0.15)', borderColor: '#e11d48', fill: true, tension: 0.15 },
                        { label: '% KW2', data: data.map(d => d.pctKw2), backgroundColor: 'rgba(124,58,237,0.15)', borderColor: '#7c3aed', fill: true, tension: 0.15 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { x: { ticks: { font: { size: 9 } } }, y: { beginAtZero: true, ticks: { font: { size: 9 } } } },
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }, datalabels: { display: false } }
                }
            });

            const lineOpt = (color) => ({
                responsive: true, maintainAspectRatio: false,
                scales: { x: { ticks: { font: { size: 9 } } }, y: { ticks: { font: { size: 9 } } } },
                plugins: {
                    legend: { display: false },
                    datalabels: { display: true, align: 'top', anchor: 'end', color: color, font: { size: 8, weight: 'bold' }, formatter: (v) => v.toFixed(1) + '%' }
                }
            });

            dashCharts.lineTN = new Chart(document.getElementById('dashLineTitikNol').getContext('2d'), {
                type: 'line',
                data: { labels: chartLabels, datasets: [{ data: data.map(d => d.yieldTitikNol), borderColor: '#0d9488', tension: 0.15, fill: false }] },
                options: lineOpt('#0d9488')
            });
            dashCharts.lineFG = new Chart(document.getElementById('dashLineFgBp').getContext('2d'), {
                type: 'line',
                data: { labels: chartLabels, datasets: [{ data: data.map(d => d.yieldFgBp), borderColor: '#d97706', tension: 0.15, fill: false }] },
                options: lineOpt('#d97706')
            });
            dashCharts.lineBP = new Chart(document.getElementById('dashLineByProduct').getContext('2d'), {
                type: 'line',
                data: { labels: chartLabels, datasets: [{ data: data.map(d => d.yieldByProduct), borderColor: '#e11d48', tension: 0.15, fill: false }] },
                options: lineOpt('#e11d48')
            });
        }

        if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
            Chart.register(ChartDataLabels);
        }
        loadLbSummaryBulanIni();
        loadMonthlyChart();
    </script>
</body>
</html>