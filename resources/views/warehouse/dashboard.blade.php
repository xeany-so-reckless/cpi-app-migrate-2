<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> CPI App Migrate - Warehouse </title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #eef4f8;
            --surface: #ffffff;
            --surface-hover: #f3f9fc;
            --line: #d9e4ed;
            --ice: #0891b2;
            --ice-soft: #e0f6fb;
            --ice-text: #0e7490;
            --hazard: #f59e0b;
            --text: #16232e;
            --muted: #64798c;
            --muted-dim: #9bacba;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: var(--bg);
            color: var(--text);
            background-image:
                radial-gradient(circle at 10% 0%, rgba(8,145,178,.06), transparent 40%),
                radial-gradient(circle at 90% 100%, rgba(8,145,178,.05), transparent 45%);
        }

        .display { font-family: 'Barlow Condensed', sans-serif; }
        .mono { font-family: 'JetBrains Mono', monospace; }

        /* ---------- TOP STATUS BAR (tetap gelap - nuansa console) ---------- */
        .top-ticker {
            background: #0a1219;
            color: #7d8ea1;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            padding: 8px 5%;
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px;
        }
        .datetime-container { color: #22d3ee; font-weight: 600; white-space: nowrap; border-right: 1px solid #253140; padding-right: 20px; }
        .marquee-container { flex-grow: 1; overflow: hidden; white-space: nowrap; }
        .marquee { display: inline-block; padding-left: 100%; animation: marquee 32s linear infinite; text-transform: uppercase; letter-spacing: 1.5px; }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }

        /* ---------- NAV ---------- */
        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 5%;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; color: var(--text); letter-spacing: -0.3px; }
        .logo img { height: 42px; }

        /* ---------- HERO ---------- */
        .hero {
            position: relative;
            min-height: 260px;
            display: flex;
            align-items: center;
            padding: 0 8%;
            overflow: hidden;
            background:
                repeating-linear-gradient(90deg, rgba(8,145,178,.05) 0px, rgba(8,145,178,.05) 1px, transparent 1px, transparent 48px),
                repeating-linear-gradient(0deg, rgba(8,145,178,.05) 0px, rgba(8,145,178,.05) 1px, transparent 1px, transparent 48px),
                linear-gradient(135deg, #eaf6fb 0%, #e3f0f6 100%);
            border-bottom: 1px solid var(--line);
        }

        .hero-text { position: relative; z-index: 2; max-width: 620px; padding: 46px 0; }
        .hero-eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: var(--ice-text);
            letter-spacing: 3px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }
        .hero-eyebrow::before {
            content: '';
            width: 8px; height: 8px;
            background: var(--ice);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(8,145,178,.5);
            animation: pulse-dot 1.6s ease-in-out infinite;
        }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }

        .hero-text h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 4rem;
            line-height: 0.92;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            color: var(--text);
            margin-bottom: 16px;
        }
        .hero-text h1 span { color: var(--ice); }

        .hero-text p {
            color: var(--muted);
            border-left: 3px solid var(--hazard);
            padding-left: 16px;
            font-size: 0.92rem;
            line-height: 1.75;
        }

        /* ---------- HAZARD DIVIDER ---------- */
        .hazard-divider {
            height: 6px;
            background: repeating-linear-gradient(-45deg, var(--hazard) 0px, var(--hazard) 14px, var(--bg) 14px, var(--bg) 28px);
            opacity: 0.9;
        }

        /* ---------- CONTENT ---------- */
        .content-section { padding: 50px 8% 70px; }
        .section-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--line);
        }

        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }

        .file-card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 26px 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all .25s ease;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(22,35,46,.04);
        }
        .file-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--line);
            transition: background .25s ease;
        }
        .file-card.is-active::before { background: var(--ice); }
        .file-card:hover {
            background: var(--surface-hover);
            border-color: var(--ice);
            transform: translateY(-4px);
            box-shadow: 0 14px 28px rgba(22,35,46,.1);
        }

        .card-top-row { display: flex; justify-content: space-between; align-items: flex-start; }
        .card-icon {
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            background: var(--ice-soft);
            border: 1px solid #bfe8f2;
            border-radius: 8px;
            color: var(--ice-text);
        }
        .card-icon .material-symbols-outlined { font-size: 24px; }

        .file-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: 0.2px;
            color: var(--text);
            text-transform: uppercase;
        }
        .file-info { font-size: 0.82rem; color: var(--muted); line-height: 1.55; flex-grow: 1; }

        .card-bottom-row { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; }
        .status-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.62rem; font-weight: 700;
            letter-spacing: .5px; text-transform: uppercase;
            display: flex; align-items: center; gap: 6px;
        }
        .status-tag.active { color: var(--ice-text); }
        .status-tag.soon { color: var(--muted-dim); }
        .status-tag .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .status-tag.active .dot { box-shadow: 0 0 6px rgba(8,145,178,.5); animation: pulse-dot 1.6s ease-in-out infinite; }

        .file-link {
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.82rem;
            text-decoration: none;
            display: flex; align-items: center; gap: 4px;
        }
        .file-card.is-active .file-link { color: var(--ice-text); }
        .file-card:not(.is-active) .file-link { color: var(--muted-dim); pointer-events: none; }

        /* ---------- STATUS BADGE (pojok kanan atas, blink hijau/ice seperti Produksi) ---------- */
        .status-badge {
            position: absolute; top: 14px; right: 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem; font-weight: bold; padding: 4px 8px; border-radius: 3px;
            text-transform: uppercase; letter-spacing: 0.5px;
            z-index: 2;
        }
        .status-badge.status-active {
            background-color: rgba(8, 145, 178, 0.1);
            color: var(--ice-text);
            border: 1px solid var(--ice);
            animation: blink-animation 1.5s steps(5, start) infinite;
        }
        .status-badge.status-soon {
            background-color: #eee;
            color: #9bacba;
            border: 1px solid #ccc;
        }
        @keyframes blink-animation { to { visibility: hidden; } }

        footer {
            background: #0a1219;
            color: #64798c;
            padding: 26px;
            text-align: center;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
        }

        @media (max-width: 768px) {
            .hero { padding: 40px 6%; }
            .hero-text h1 { font-size: 3rem; }
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
                WAREHOUSE OPERATIONS CONSOLE &nbsp; // &nbsp; CPI JOMBANG COLD STORAGE &nbsp; // &nbsp; {{ $system_phase }}
            </div>
        </div>
    </div>

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
            <span>{{ $plant_name }}</span>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-text">
            <div class="hero-eyebrow">Warehouse Console &middot; {{ $version }}</div>
            <h1>WAREHOUSE<br><span>OPERATIONS</span></h1>
            <p>Satu pintu untuk seluruh alur gudang - dari barang masuk, penempatan cell, hingga barang keluar. Dibangun mengikuti struktur cell cold storage yang sebenarnya.</p>
        </div>
    </section>

    <div class="hazard-divider"></div>

    <div class="content-section">
        <div class="section-label">Menu Operasional</div>
        <div class="grid-container">
            @foreach ($warehouse_menus as $menu)
                @php
                    $is_active = ($menu['url'] !== '#');
                    $badge_class = $is_active ? 'status-active' : 'status-soon';
                    $badge_label = $is_active ? '● Active' : 'Coming Soon';
                @endphp
                <div class="file-card {{ $is_active ? 'is-active' : '' }}">
                    <span class="status-badge {{ $badge_class }}">{{ $badge_label }}</span>

                    <div class="card-top-row">
                        <div class="card-icon">
                            <span class="material-symbols-outlined">{{ $menu['icon'] }}</span>
                        </div>
                    </div>

                    <div class="file-name">{{ $menu['name'] }}</div>
                    <p class="file-info">{{ $menu['info'] }}</p>

                    <div class="card-bottom-row">
                        <span class="status-tag {{ $is_active ? 'active' : 'soon' }}">
                            <span class="dot"></span>
                            {{ $is_active ? 'Online' : 'Coming Soon' }}
                        </span>
                        <a href="{{ $menu['url'] }}" class="file-link">
                            Buka <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <footer>
        &copy; {{ date('Y') }} DEPARTEMEN WAREHOUSE CPI JOMBANG // WAREHOUSE OPERATIONS CONSOLE
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
</body>
</html>