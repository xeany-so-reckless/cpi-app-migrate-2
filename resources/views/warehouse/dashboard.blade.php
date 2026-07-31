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
            --bg: #0a0f14;
            --surface: #131a22;
            --surface-hover: #182029;
            --line: #232e3a;
            --ice: #22d3ee;
            --ice-dim: #0e7490;
            --hazard: #fbbf24;
            --text: #e7edf3;
            --muted: #7d8ea1;
            --muted-dim: #4b5a6b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: var(--bg);
            color: var(--text);
            background-image:
                radial-gradient(circle at 15% 0%, rgba(34,211,238,.07), transparent 40%),
                radial-gradient(circle at 85% 100%, rgba(34,211,238,.05), transparent 45%);
        }

        .display { font-family: 'Barlow Condensed', sans-serif; }
        .mono { font-family: 'JetBrains Mono', monospace; }

        .top-ticker {
            background: #05080b;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            padding: 8px 5%;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--line);
            gap: 20px;
        }
        .datetime-container { color: var(--ice); font-weight: 600; white-space: nowrap; border-right: 1px solid var(--line); padding-right: 20px; }
        .marquee-container { flex-grow: 1; overflow: hidden; white-space: nowrap; }
        .marquee { display: inline-block; padding-left: 100%; animation: marquee 32s linear infinite; text-transform: uppercase; letter-spacing: 1.5px; }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 5%;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; color: var(--text); letter-spacing: -0.3px; }
        .logo img { height: 42px; background: #fff; border-radius: 6px; padding: 3px 6px; }

        .hero {
            position: relative;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8%;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(10,15,20,.4), rgba(10,15,20,.92)),
                repeating-linear-gradient(90deg, rgba(34,211,238,.045) 0px, rgba(34,211,238,.045) 1px, transparent 1px, transparent 48px),
                repeating-linear-gradient(0deg, rgba(34,211,238,.045) 0px, rgba(34,211,238,.045) 1px, transparent 1px, transparent 48px),
                var(--bg);
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
            gap: 30px;
        }

        .hero-text { position: relative; z-index: 2; max-width: 600px; padding: 50px 0; }
        .hero-eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: var(--ice);
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
            box-shadow: 0 0 8px var(--ice);
            animation: pulse-dot 1.6s ease-in-out infinite;
        }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }

        .hero-text h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 4.2rem;
            line-height: 0.92;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            color: #fff;
            margin-bottom: 18px;
        }
        .hero-text h1 span { color: var(--ice); }

        .hero-text p {
            color: var(--muted);
            border-left: 3px solid var(--hazard);
            padding-left: 16px;
            font-size: 0.92rem;
            line-height: 1.75;
            min-height: 3.5em;
        }

        .cell-grid-visual {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(6, 26px);
            grid-auto-rows: 26px;
            gap: 5px;
            padding: 18px;
            background: rgba(19, 26, 34, .7);
            border: 1px solid var(--line);
            border-radius: 4px;
            backdrop-filter: blur(4px);
        }
        .cg-cell {
            background: #1b232d;
            border: 1px solid #253140;
            border-radius: 2px;
        }
        .cg-cell.lit {
            background: rgba(34,211,238,.18);
            border-color: var(--ice);
            box-shadow: 0 0 6px rgba(34,211,238,.5);
            animation: cell-blink 3s ease-in-out infinite;
        }
        @keyframes cell-blink { 0%, 100% { opacity: 1; } 50% { opacity: .45; } }
        .cell-grid-label {
            grid-column: 1 / -1;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.6rem;
            color: var(--muted-dim);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
            text-align: center;
        }

        .hazard-divider {
            height: 6px;
            background: repeating-linear-gradient(-45deg, var(--hazard) 0px, var(--hazard) 14px, #0a0f14 14px, #0a0f14 28px);
            opacity: 0.85;
        }

        .content-section { padding: 56px 8% 70px; }
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
            border-color: var(--ice-dim);
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(0,0,0,.4);
        }

        .card-top-row { display: flex; justify-content: space-between; align-items: flex-start; }
        .card-icon {
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            background: #0e1620;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--ice);
        }
        .card-icon .material-symbols-outlined { font-size: 24px; }

        .area-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid var(--line);
            color: var(--muted);
        }
        .area-code.is-active { color: var(--ice); border-color: var(--ice-dim); }

        .file-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: 0.2px;
            color: #fff;
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
        .status-tag.active { color: var(--ice); }
        .status-tag.soon { color: var(--muted-dim); }
        .status-tag .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .status-tag.active .dot { box-shadow: 0 0 6px var(--ice); animation: pulse-dot 1.6s ease-in-out infinite; }

        .file-link {
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.82rem;
            text-decoration: none;
            display: flex; align-items: center; gap: 4px;
        }
        .file-card.is-active .file-link { color: var(--ice); }
        .file-card:not(.is-active) .file-link { color: var(--muted-dim); pointer-events: none; }

        footer {
            background: #05080b;
            color: white;
            padding: 26px;
            text-align: center;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            border-top: 1px solid var(--line);
        }

        @media (max-width: 768px) {
            .hero { flex-direction: column; align-items: flex-start; padding: 40px 6%; }
            .hero-text h1 { font-size: 3rem; }
            .cell-grid-visual { grid-template-columns: repeat(6, 22px); grid-auto-rows: 22px; }
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

        {{-- <div class="cell-grid-visual" aria-hidden="true">
            <div class="cell-grid-label">CS COLD STORAGE</div>
            @php
                $litIndexes = collect(range(0, 35))->shuffle()->take(7)->toArray();
            @endphp
            @for ($i = 0; $i < 36; $i++)
                <div class="cg-cell {{ in_array($i, $litIndexes) ? 'lit' : '' }}"></div>
            @endfor
        </div> --}}
    </section>

    <div class="hazard-divider"></div>

    <div class="content-section">
        <div class="section-label">Menu Operasional</div>
        <div class="grid-container">
            @php
                $area_codes = [
                    'Inbound' => 'INB',
                    'Stock Warehouse' => 'STK',
                    'Outbound' => 'OUT',
                    'Inbound STRSTO' => 'STR',
                    'B2B' => 'B2B',
                    'Transfer Cell' => 'TRF',
                ];
            @endphp
            @foreach ($warehouse_menus as $menu)
                @php
                    $is_active = ($menu['url'] !== '#');
                    $kode = $area_codes[$menu['name']] ?? '---';
                @endphp
                <div class="file-card {{ $is_active ? 'is-active' : '' }}">
                    <div class="card-top-row">
                        <div class="card-icon">
                            <span class="material-symbols-outlined">{{ $menu['icon'] }}</span>
                        </div>
                        <span class="area-code {{ $is_active ? 'is-active' : '' }}">{{ $kode }}</span>
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