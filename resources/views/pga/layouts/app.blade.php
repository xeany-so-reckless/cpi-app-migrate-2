<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PGA') - PGA</title>
    @include('partials.favicon')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f6fb;
            --surface: #ffffff;
            --line: #e1e3f0;
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-soft: #eef2ff;
            --text: #1e1b2e;
            --muted: #6b7280;
            --danger: #dc2626;
            --success: #16a34a;
            --sidebar-w: 250px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; }
        .material-symbols-outlined { font-family: 'Material Symbols Outlined'; line-height: 1; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: linear-gradient(170deg, #1e1b4b 0%, #312e81 100%);
            color: #fff;
            display: flex; flex-direction: column;
            z-index: 50;
            transition: transform .25s ease;
        }
        .sb-brand { padding: 22px 20px 18px; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sb-eyebrow { font-family: 'JetBrains Mono', monospace; font-size: .62rem; letter-spacing: 2px; color: #a5b4fc; text-transform: uppercase; }
        .sb-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1.7rem; letter-spacing: .5px; text-transform: uppercase; }
        .sb-sub { font-size: .7rem; color: rgba(255,255,255,.55); margin-top: 2px; }

        .sb-nav { flex: 1; padding: 16px 12px; display: flex; flex-direction: column; gap: 4px; overflow-y: auto; }
        .sb-link {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: .88rem; font-weight: 500;
            transition: background .2s ease, color .2s ease;
        }
        .sb-link .material-symbols-outlined { font-size: 22px; }
        .sb-link:hover { background: rgba(255,255,255,.1); color: #fff; }
        .sb-link.active { background: rgba(255,255,255,.16); color: #fff; font-weight: 700; box-shadow: inset 3px 0 0 #a5b4fc; }
        .sb-soon { margin-left: auto; font-size: .58rem; font-weight: 700; letter-spacing: .5px; padding: 2px 7px; border-radius: 99px; background: rgba(255,255,255,.14); color: #c7d2fe; }

        .sb-foot { padding: 14px 16px; border-top: 1px solid rgba(255,255,255,.1); }
        .sb-user { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .sb-avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,.16); display: flex; align-items: center; justify-content: center; }
        .sb-uname { font-size: .82rem; font-weight: 700; line-height: 1.2; }
        .sb-ucode { font-family: 'JetBrains Mono', monospace; font-size: .66rem; color: #a5b4fc; }
        .sb-logout {
            width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 9px; border-radius: 10px;
            background: rgba(255,255,255,.08); color: #fff;
            border: 1px solid rgba(255,255,255,.2);
            font-size: .82rem; font-weight: 600; cursor: pointer;
            transition: background .2s ease;
        }
        .sb-logout:hover { background: #dc2626; border-color: #dc2626; }
        .sb-back { display: block; text-align: center; margin-top: 10px; font-size: .72rem; color: rgba(255,255,255,.55); text-decoration: none; }
        .sb-back:hover { color: #fff; }

        /* ===== MAIN ===== */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 28px;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
        }
        .topbar h1 { font-size: 1.15rem; font-weight: 700; }
        .topbar .crumb { font-size: .78rem; color: var(--muted); }
        .menu-toggle {
            display: none;
            width: 38px; height: 38px; border-radius: 10px;
            border: 1px solid var(--line); background: #fff; color: var(--text);
            align-items: center; justify-content: center; cursor: pointer;
        }
        .content { padding: 28px; flex: 1; }

        /* ===== KOMPONEN UMUM (dipakai halaman PGA) ===== */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 22px; }
        .stat-box { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 18px 20px; box-shadow: 0 1px 4px rgba(30,27,46,.05); }
        .stat-label { font-size: .72rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
        .stat-value { font-size: 1.9rem; font-weight: 700; }
        .stat-box.danger .stat-value { color: var(--danger); }
        .stat-box.success .stat-value { color: var(--success); }
        .stat-box.info .stat-value { color: var(--primary); }

        .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 22px; box-shadow: 0 1px 4px rgba(30,27,46,.05); }
        .panel h2 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
        .panel p { color: var(--muted); font-size: .88rem; line-height: 1.7; }

        .placeholder { text-align: center; padding: 60px 20px; }
        .placeholder .material-symbols-outlined { font-size: 56px; color: #a5b4fc; margin-bottom: 12px; }
        .placeholder h2 { font-size: 1.15rem; margin-bottom: 6px; }

        .overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 40; }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            body.sb-open .sidebar { transform: none; box-shadow: 0 0 40px rgba(0,0,0,.4); }
            body.sb-open .overlay { display: block; }
            .main { margin-left: 0; }
            .menu-toggle { display: flex; }
            .topbar { padding: 12px 16px; }
            .content { padding: 18px 16px; }
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $authUser = auth('tally')->user();
@endphp

<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <div class="sb-eyebrow">CPI Jombang</div>
        <div class="sb-title">PGA</div>
        <div class="sb-sub">Dashboard &middot; APAR &middot; Buku Tamu</div>
    </div>

    <nav class="sb-nav" aria-label="Menu PGA">
        <a href="{{ route('pga.index') }}" class="sb-link {{ request()->routeIs('pga.index') ? 'active' : '' }}">
            <span class="material-symbols-outlined">dashboard</span> Dashboard
        </a>
        <a href="{{ route('pga.apar.index') }}" class="sb-link {{ request()->routeIs('pga.apar.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">fire_extinguisher</span> APAR
        </a>
        <a href="{{ route('pga.bukutamu.index') }}" class="sb-link {{ request()->routeIs('pga.bukutamu.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">menu_book</span> Buku Tamu
            <span class="sb-soon">SOON</span>
        </a>
    </nav>

    <div class="sb-foot">
        <div class="sb-user">
            <div class="sb-avatar"><span class="material-symbols-outlined">person</span></div>
            <div>
                <div class="sb-uname">{{ $authUser->name ?? '-' }}</div>
                <div class="sb-ucode">{{ $authUser->employee_code ?? '' }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('pga.logout') }}">
            @csrf
            <button type="submit" class="sb-logout">
                <span class="material-symbols-outlined" style="font-size:18px;">logout</span> Keluar
            </button>
        </form>
        <a href="{{ route('dashboard') }}" class="sb-back">← Dashboard Produksi</a>
    </div>
</aside>

<div class="overlay" id="overlay"></div>

<div class="main">
    <header class="topbar">
        <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div>
            <h1>@yield('page_title', 'PGA')</h1>
            <div class="crumb">@yield('page_sub', 'Production Department CPI - Plant Jombang')</div>
        </div>
    </header>

    <main class="content">
        @yield('content')
    </main>
</div>

<script>
    (function () {
        const body = document.body;
        document.getElementById('menuToggle').addEventListener('click', () => body.classList.toggle('sb-open'));
        document.getElementById('overlay').addEventListener('click', () => body.classList.remove('sb-open'));
    })();
</script>
@stack('scripts')
</body>
</html>
