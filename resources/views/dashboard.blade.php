<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaughter House Jombang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <style>
        :root {
            --red: #d9232a;
            --red-soft: #fdecec;
            --slate: #0f172a;
            --slate-2: #1e293b;
            --slate-3: #475569;
            --muted: #94a3b8;
            --line: #e2e8f0;
            --green: #16a34a;
            --amber: #d97706;
            --blue: #1d4ed8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { height: 100%; }

        body {
    min-height: 100%;
    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif;
    color: #fff;
    background-color: var(--slate);
    background-image:
            linear-gradient(rgba(15,23,42,.25), rgba(15,23,42,.25)),
            url('{{ asset('images/dashboard_pict.jpg') }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

        .material-symbols-outlined { font-family: 'Material Symbols Outlined'; line-height: 1; }

        a:focus-visible, button:focus-visible {
            outline: 3px solid #fff;
            outline-offset: 2px;
            border-radius: 8px;
        }

        .app { display: flex; flex-direction: column; min-height: 100vh; }

        /* ===================== TICKER (ATAS) ===================== */
        .top-ticker {
    flex: 0 0 auto;
    height: 34px;
    display: flex;
    align-items: center;
    background: rgba(15,23,42,.20);          /* sebelumnya .85 */
    backdrop-filter: blur(8px);              /* baris baru */
    -webkit-backdrop-filter: blur(8px);      /* baris baru */
    border-bottom: 1px solid rgba(255,255,255,.15);
    font-size: 12px;
}
        .datetime-container {
            display: flex; align-items: center; gap: 6px;
            height: 100%;
            padding: 0 14px;
            background: rgba(217,35,42,.60); 
            font-size: 11px; font-weight: 600; letter-spacing: .3px;
            white-space: nowrap;
        }
        .datetime-container .material-symbols-outlined { font-size: 14px; }
        .marquee-container { flex: 1; overflow: hidden; white-space: nowrap; }
        .marquee {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 35s linear infinite;
            color: rgba(255,255,255,.9);
            font-weight: 500;
            letter-spacing: .4px;
        }
        .marquee:hover { animation-play-state: paused; }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }

        /* ===================== HEADER ===================== */
        .hero-header {
            flex: 0 0 auto;
            position: relative;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 4%;
        }
        .logo-container {
            display: flex; align-items: center; gap: 12px;
            font-weight: 700; font-size: 15px; letter-spacing: .4px;
            min-width: 0;
        }
        /* Logo PNG transparan. Kalau logo berwarna gelap dan kurang terbaca,
           tambahkan: background:#fff; padding:4px 8px; border-radius:8px; */
        .logo-img {
            height: 46px; width: auto; object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,.5));
        }
        .logo-fallback {
            display: none;
            background: var(--red); color: #fff;
            padding: 6px 10px; border-radius: 8px;
            font-size: 15px; font-style: italic; font-weight: 800;
        }
        .logo-container span.company { text-shadow: 0 2px 6px rgba(0,0,0,.6); }

        /* ===================== DOCK MENU LAINNYA (IKON BULAT) ===================== */
        .dock-wrap {
            display: flex; flex-direction: column; align-items: center;
            gap: 8px;
            margin-top: 2px;
        }
        .dock-label {
            font-size: 11px; font-weight: 500; letter-spacing: .4px;
            color: rgba(255,255,255,.72);
            text-shadow: 0 1px 4px rgba(0,0,0,.6);
        }
        .dock {
            display: flex; flex-wrap: wrap; justify-content: center;
            gap: 10px;
            padding: 7px 14px;
            background: rgba(0,0,0,.22);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 28px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .dock-item { position: relative; }

        .dock-link {
            position: relative;
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.30);
            color: #fff;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0,0,0,.18);
            transition: background .25s ease, transform .25s cubic-bezier(.16,1,.3,1), border-color .25s ease;
        }
        .dock-link .material-symbols-outlined { font-size: 21px; }
        a.dock-link:hover, a.dock-link:focus-visible {
            background: rgba(217,35,42,.85);
            border-color: rgba(255,255,255,.75);
            transform: translateY(-3px) scale(1.1);
        }
        .dock-link.is-disabled { opacity: .5; cursor: not-allowed; }

        .dock-dot {
            position: absolute; top: -2px; right: -2px;
            width: 10px; height: 10px;
            border-radius: 50%;
            border: 1.5px solid #0f172a;
        }
        .dock-dot.dot-active { background: #10b981; box-shadow: 0 0 8px #10b981; }
        .dock-dot.dot-soon { background: #64748b; }

        /* Tooltip di ATAS ikon supaya tidak terpotong dasar layar */
        .dock-tip {
            position: absolute;
            bottom: calc(100% + 12px);
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            padding: 6px 12px;
            background: rgba(15,23,42,.96);
            color: #fff;
            font-size: 12px; font-weight: 500;
            white-space: nowrap;
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,.4);
            opacity: 0; visibility: hidden;
            pointer-events: none;
            transition: opacity .18s ease, transform .18s ease, visibility .18s;
            z-index: 30;
        }
        .dock-tip::after {
            content: '';
            position: absolute; bottom: -5px; left: 50%;
            width: 8px; height: 8px;
            background: rgba(15,23,42,.96);
            border-right: 1px solid rgba(255,255,255,.15);
            border-bottom: 1px solid rgba(255,255,255,.15);
            transform: translateX(-50%) rotate(45deg);
        }
        .dock-item:hover .dock-tip,
        .dock-item:focus-within .dock-tip {
            opacity: 1; visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        /* ===================== HERO ===================== */
        .hero-content {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 0 4% 24px;
            text-align: center;
        }

        .hero-badge {
            position: relative; overflow: hidden;
            display: inline-block;
            background: rgba(217,35,42,.9);
            border: 1px solid rgba(255,255,255,.4);
            border-radius: 50px;
            padding: 8px 24px;
            font-size: 11px; font-weight: 700; letter-spacing: 1.8px;
            text-transform: uppercase;
            box-shadow: 0 0 14px rgba(217,35,42,.6), 0 0 28px rgba(217,35,42,.3), inset 0 0 10px rgba(255,255,255,.18);
        }
        .hero-badge::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.5), transparent);
            transform: skewX(-25deg);
            animation: sweep 3.5s infinite ease-in-out;
        }
        @keyframes sweep { 0% { left: -100%; } 50%, 100% { left: 200%; } }

        .hero-title {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
            font-size: clamp(30px, 4.4vw, 46px);
            font-weight: 800;
            letter-spacing: 3px;
            line-height: 1.1;
            text-shadow: 0 2px 4px rgba(0,0,0,.6), 0 6px 20px rgba(0,0,0,.45);
        }

        .hero-subtitle {
            max-width: 680px;
            padding: 11px 26px;
            border-radius: 16px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.35);
            box-shadow: 0 8px 32px rgba(0,0,0,.2);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            font-size: 14px; font-weight: 500; line-height: 1.6; letter-spacing: .3px;
            text-shadow: 0 2px 8px rgba(0,0,0,.6);
        }

        .fade-in { opacity: 0; animation: fadeInUp .8s cubic-bezier(.16,1,.3,1) forwards; }
        .delay-1 { animation-delay: .1s; }
        .delay-2 { animation-delay: .25s; }
        .delay-3 { animation-delay: .4s; }
        .delay-4 { animation-delay: .55s; }
        .delay-5 { animation-delay: .7s; }
        .delay-6 { animation-delay: .85s; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ===================== 3 KARTU UTAMA ===================== */
        .cards-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            width: 100%;
            max-width: 960px;
            margin-top: 4px;
        }

        .file-card {
            position: relative;
            display: flex; flex-direction: column; align-items: center; text-align: center;
            gap: 8px;
            padding: 22px 18px 16px;
            background: rgba(15,23,42,.55);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 24px rgba(0,0,0,.25);
            transition: transform .3s cubic-bezier(.16,1,.3,1), background .3s ease, border-color .3s ease, box-shadow .3s ease;
        }
        .file-card.is-active:hover {
            transform: translateY(-5px);
            background: rgba(217,35,42,.78);
            border-color: rgba(255,255,255,.6);
            box-shadow: 0 12px 32px rgba(217,35,42,.4), 0 0 20px rgba(255,255,255,.15);
        }
        .file-card.is-soon .file-icon, .file-card.is-soon .file-name, .file-card.is-soon .file-info { opacity: .55; }

        .file-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            display: flex; align-items: center; justify-content: center;
            transition: background .3s ease, color .3s ease;
        }
        .file-icon .material-symbols-outlined { font-size: 26px; }
        .file-card.is-active:hover .file-icon { background: #fff; color: var(--red); }

        .file-name { font-weight: 700; font-size: 15px; line-height: 1.3; text-shadow: 0 2px 4px rgba(0,0,0,.5); }
        .file-info { font-size: 12px; line-height: 1.5; color: rgba(255,255,255,.82); flex-grow: 1; }

        .file-link {
            margin-top: 4px;
            font-size: 12px; font-weight: 700; letter-spacing: .6px;
            color: #fff; text-decoration: none;
            border-bottom: 2px solid rgba(255,255,255,.75); padding-bottom: 2px;
        }
        a.file-link::after { content: ''; position: absolute; inset: 0; border-radius: 16px; }
        .file-link.is-disabled { color: rgba(255,255,255,.5); border-bottom-color: rgba(255,255,255,.25); cursor: not-allowed; }

        .status-badge {
            position: absolute; top: 10px; right: 10px;
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 10px; font-weight: 600;
            padding: 2px 9px; border-radius: 20px;
        }
        .status-active { background: rgba(16,185,129,.18); color: #6ee7b7; border: 1px solid rgba(110,231,183,.4); }
        .status-active::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #34d399; }
        .status-soon { background: rgba(255,255,255,.1); color: #cbd5e1; border: 1px solid rgba(255,255,255,.2); }

        /* ===================== TOMBOL SUMMARY ===================== */
        .summary-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 28px;
    background: rgba(255,255,255,.12);
    color: #fff;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 999px;
    font: 700 14px 'Inter', system-ui, sans-serif;
    cursor: pointer;
    box-shadow: 0 8px 32px rgba(0,0,0,.2);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    text-shadow: 0 2px 8px rgba(0,0,0,.6);
    transition: background .25s ease, border-color .25s ease, transform .2s ease;
}
.summary-btn .material-symbols-outlined { color: #fff; font-size: 22px; }
.summary-btn:hover {
    background: rgba(217,35,42,.75);
    border-color: rgba(255,255,255,.6);
    transform: translateY(-2px);
}

        /* ===================== POPUP SUMMARY ===================== */
        html.modal-open, html.modal-open body { overflow: hidden; }

        .modal {
            position: fixed; inset: 0; z-index: 100;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal[hidden] { display: none; }
        .modal-backdrop {
            position: absolute; inset: 0;
            background: rgba(15,23,42,.72);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .modal-card {
            position: relative;
            width: min(1180px, 100%);
            max-height: 92vh;
            max-height: 92dvh;
            display: flex; flex-direction: column;
            background: #f4f6f9;
            color: var(--slate-2);
            border-radius: 16px;
            border-top: 4px solid var(--red);
            box-shadow: 0 30px 80px rgba(0,0,0,.5);
            overflow: hidden;
            animation: popIn .25s cubic-bezier(.16,1,.3,1);
        }
        @keyframes popIn { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: none; } }

        .modal-head {
            flex: 0 0 auto;
            display: flex; align-items: center; gap: 12px;
            padding: 14px 20px;
            background: #fff;
            border-bottom: 1px solid var(--line);
        }
        .modal-head h2 { font-size: 1.05rem; font-weight: 700; color: var(--slate); }
        .modal-sub { font-size: .8rem; color: var(--slate-3); }
        .modal-close {
            margin-left: auto;
            width: 36px; height: 36px;
            border: 1px solid var(--line);
            border-radius: 50%;
            background: #fff; color: var(--slate-3);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background .2s ease, color .2s ease;
        }
        .modal-close:hover { background: var(--red); color: #fff; border-color: var(--red); }
        .modal-close:focus-visible { outline-color: var(--red); }

        .modal-body { flex: 1 1 auto; overflow-y: auto; padding: 20px; }

        .sum-heading {
            display: flex; align-items: center; gap: 8px;
            font-size: .95rem; font-weight: 700; color: var(--slate);
            margin-bottom: 12px;
        }
        .sum-heading .material-symbols-outlined { color: var(--red); font-size: 22px; }
        .sum-heading a {
            margin-left: auto;
            display: inline-flex; align-items: center; gap: 4px;
            font-size: .78rem; font-weight: 600;
            color: var(--red); text-decoration: none;
        }
        .sum-heading a:hover { text-decoration: underline; }
        .sum-heading a:focus-visible { outline-color: var(--red); }
        .sum-heading a .material-symbols-outlined { font-size: 16px; color: var(--red); }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        .stat-box {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 1px 4px rgba(15,23,42,.05);
        }
        .stat-label { font-size: .7rem; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
        .stat-value { font-size: 1.45rem; font-weight: 700; color: var(--slate); }
        .stat-box.danger  .stat-value { color: var(--red); }
        .stat-box.warning .stat-value { color: var(--amber); }
        .stat-box.info    .stat-value { color: var(--blue); }

        .sum-block { margin-bottom: 26px; }
        .sum-block:last-child { margin-bottom: 0; }

        .chart-grid-top { display: grid; grid-template-columns: minmax(240px, 320px) 1fr; gap: 12px; margin-bottom: 12px; }
        .chart-grid-bottom { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .chart-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 1px 4px rgba(15,23,42,.05);
            min-width: 0;
        }
        .chart-card h4 { font-size: .74rem; font-weight: 600; color: var(--slate-3); margin-bottom: 8px; }
        .chart-body { height: 210px; position: relative; }

        .preview-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 26px;
            color: var(--muted);
            background: #fff;
            border: 1px dashed var(--line);
            border-radius: 12px;
            font-size: .85rem;
        }

        /* ===================== DESKTOP: HERO SATU LAYAR TANPA SCROLL ===================== */
        @media (min-width: 1024px) and (min-height: 600px) {
            html, body { height: 100%; overflow: hidden; }
            .app { height: 100vh; height: 100dvh; min-height: 0; }
        }

        /* Laptop layar pendek: rapatkan sedikit supaya tetap muat tanpa scroll */
        @media (min-width: 1024px) and (min-height: 600px) and (max-height: 760px) {
            .hero-content { gap: 10px; padding-bottom: 16px; }
            .hero-title { font-size: 34px; }
            .hero-subtitle { padding: 8px 22px; font-size: 13px; }
            .file-card { padding: 16px 16px 12px; gap: 6px; }
            .file-icon { width: 42px; height: 42px; }
            .file-info { font-size: 11.5px; line-height: 1.4; }
            .summary-btn { padding: 10px 24px; }
            .dock-link { width: 38px; height: 38px; }
        }

        /* ===================== LAYAR KECIL ===================== */
        @media (max-width: 1023px), (max-height: 599px) {
            body { overflow: auto; }
            .hero-content { padding-top: 10px; padding-bottom: 40px; }
        }
        @media (max-width: 860px) {
            .cards-row { grid-template-columns: 1fr; max-width: 420px; }
            .chart-grid-top, .chart-grid-bottom { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .logo-container span.company { display: none; }
            .hero-title { letter-spacing: 1.5px; }
            .hero-subtitle { padding: 10px 16px; font-size: 13px; }
            .hero-badge { letter-spacing: 1px; font-size: 10px; padding: 8px 16px; }
            .modal { padding: 10px; }
            .modal-body { padding: 14px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .fade-in { animation: none; opacity: 1; }
            .marquee, .hero-badge::before { animation: none; }
            .marquee { padding-left: 0; }
            .modal-card { animation: none; }
        }

        /* Kartu melayang pelan */
@keyframes float {
    0%, 100% { translate: 0 0; }
    50%      { translate: 0 -8px; }
}

.file-card {
    animation: float 5s ease-in-out infinite;
}

/* Tiap kartu beda fase & kecepatan supaya geraknya tidak serempak */
.file-card:nth-child(1) { animation-duration: 5s;   animation-delay: 0s; }
.file-card:nth-child(2) { animation-duration: 5.6s; animation-delay: -1.8s; }
.file-card:nth-child(3) { animation-duration: 6.2s; animation-delay: -3.4s; }

/* Saat di-hover, berhenti melayang supaya efek naik tidak bertabrakan */
.file-card:hover { animation-play-state: paused; }

        @keyframes wiggle {
    0%, 100% { rotate: 0deg; }
    25%      { rotate: -14deg; }
    75%      { rotate: 14deg; }
}
a.dock-link:hover .material-symbols-outlined {
    animation: wiggle .5s ease-in-out;
}
    </style>
</head>
<body>

@php
    // Nama harus persis sama dengan 'name' di DashboardController.
    // Urutan di sini = urutan tampil kartu.
    $featured_names = ['Report Harian Bahan Baku Live Birds', 'PPIC', 'Warehouse'];

    $all_docs   = collect($production_docs);
    $main_docs  = collect($featured_names)->map(fn ($n) => $all_docs->firstWhere('name', $n))->filter();
    $other_docs = $all_docs->reject(fn ($d) => in_array($d['name'], $featured_names))->values();
@endphp

<div class="app">

    {{-- ===================== TICKER (ATAS) ===================== --}}
    <div class="top-ticker">
        <div class="datetime-container">
            <span class="material-symbols-outlined">schedule</span>
            <span id="datetime-text">Memuat...</span>
        </div>
        <div class="marquee-container">
            <div class="marquee">
                Selamat Datang di Sistem Integrasi Departemen Produksi CPI Jombang &nbsp; | &nbsp; 1 Halaman Untuk Semua Dokumen &nbsp; | &nbsp; {{ $system_phase }} &nbsp; | &nbsp; {{ $version }} &nbsp; | &nbsp; &copy; {{ date('Y') }} Departemen Produksi CPI Jombang
            </div>
        </div>
    </div>

    {{-- ===================== HEADER ===================== --}}
    <header class="hero-header">
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo CP" class="logo-img"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div class="logo-fallback">CP</div>
            <span class="company">PT. CHAROEN POKPHAND INDONESIA</span>
        </div>

    </header>

    {{-- ===================== HERO ===================== --}}
    <main class="hero-content">
        <div class="hero-badge fade-in delay-1">{{ $plant_name }}</div>
        <h1 class="hero-title fade-in delay-2">INTEGRATED DOCUMENT</h1>
        <p class="hero-subtitle fade-in delay-3">Digitalisasi dokumen produksi dan manajemen data departemen berbasis sistem terintegrasi</p>

        <div class="cards-row fade-in delay-4">
            @foreach ($main_docs as $doc)
                @php
                    $is_soon = ($doc['url'] === '#');
                    $badge_class = $is_soon ? 'status-soon' : 'status-active';
                    $badge_label = $is_soon ? 'Coming Soon' : 'Active';
                @endphp
                <div class="file-card {{ $is_soon ? 'is-soon' : 'is-active' }}">
                    <span class="status-badge {{ $badge_class }}">{{ $badge_label }}</span>

                    <div class="file-icon">
                        <span class="material-symbols-outlined">{{ strtolower($doc['icon']) }}</span>
                    </div>
                    <div class="file-name">{{ $doc['name'] }}</div>
                    <p class="file-info">{{ $doc['info'] }}</p>

                    @if ($is_soon)
                        <span class="file-link is-disabled" aria-disabled="true">SIGN IN</span>
                    @else
                        <a href="{{ $doc['url'] }}" class="file-link">SIGN IN</a>
                    @endif
                </div>
            @endforeach
        </div>

        <button type="button" class="summary-btn fade-in delay-5" id="summaryOpen" aria-haspopup="dialog" aria-controls="summaryModal">
            <span class="material-symbols-outlined">monitoring</span>
            Lihat Summary Bulan Ini
        </button>

        {{-- Menu lainnya: ikon bulat kecil, nama tampil lewat tooltip --}}
        <div class="dock-wrap fade-in delay-6">
            <span class="dock-label">Others Menu</span>
            <nav class="dock" aria-label="Menu lainnya">
                @foreach ($other_docs as $doc)
                    @php $is_soon = ($doc['url'] === '#'); @endphp
                    <div class="dock-item">
                        @if ($is_soon)
                            <span class="dock-link is-disabled" tabindex="0" role="link" aria-disabled="true" aria-label="{{ $doc['name'] }} (Coming Soon)">
                                <span class="material-symbols-outlined">{{ strtolower($doc['icon']) }}</span>
                                <span class="dock-dot dot-soon"></span>
                            </span>
                            <span class="dock-tip" role="tooltip">{{ $doc['name'] }} - Coming Soon</span>
                        @else
                            <a href="{{ $doc['url'] }}" class="dock-link" aria-label="{{ $doc['name'] }}">
                                <span class="material-symbols-outlined">{{ strtolower($doc['icon']) }}</span>
                                <span class="dock-dot dot-active"></span>
                            </a>
                            <span class="dock-tip" role="tooltip">{{ $doc['name'] }}</span>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>
    </main>
</div>

{{-- ===================== POPUP SUMMARY ===================== --}}
<div class="modal" id="summaryModal" hidden role="dialog" aria-modal="true" aria-labelledby="summaryTitle">
    <div class="modal-backdrop" data-close="1"></div>
    <div class="modal-card">
        <div class="modal-head">
            <h2 id="summaryTitle">Summary Bulan Ini</h2>
            <span class="modal-sub" id="summaryMonth"></span>
            <button type="button" class="modal-close" id="summaryClose" aria-label="Tutup summary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="modal-body">

            <section class="sum-block">
                <h3 class="sum-heading">
                    <span class="material-symbols-outlined">calendar_month</span> Penerimaan LB
                    <a href="{{ route('lbreport.dashboard') }}">Buka Report LB <span class="material-symbols-outlined">arrow_forward</span></a>
                </h3>
                <div id="lbSummaryGrid" class="stat-grid">
                    <div class="preview-empty">Memuat data...</div>
                </div>
                <div id="lbSummaryAreaGrid" class="stat-grid"></div>
            </section>

            <section class="sum-block">
                <h3 class="sum-heading">
                    <span class="material-symbols-outlined">monitoring</span> Grafik Produksi
                    <a href="{{ route('produksi-dashboard.index') }}">Buka Dashboard Produksi <span class="material-symbols-outlined">arrow_forward</span></a>
                </h3>
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
            </section>

        </div>
    </div>
</div>

{{-- ===================== SCRIPT ===================== --}}
<script>
    // Jam real-time (WIB)
    function updateClock() {
        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', {
            weekday: 'short', day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta'
        });
        const timeStr = now.toLocaleTimeString('id-ID', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: 'Asia/Jakarta'
        }).replace(/\./g, ':');
        document.getElementById('datetime-text').textContent = dateStr + ' | ' + timeStr + ' WIB';
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<script>
    // Bulan berjalan diambil dari server (WIB), bukan dari browser,
    // supaya tidak salah bulan saat dini hari tanggal 1.
    const currentMonth = "{{ now('Asia/Jakarta')->format('Y-m') }}"; // yyyy-MM

    if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // ===== POPUP SUMMARY =====
    const summaryModal = document.getElementById('summaryModal');
    const summaryOpen = document.getElementById('summaryOpen');
    const summaryClose = document.getElementById('summaryClose');
    let lastFocus = null;
    let lastSummaryLoad = 0;

    function openSummary() {
        lastFocus = document.activeElement;
        summaryModal.hidden = false;
        document.documentElement.classList.add('modal-open');
        summaryClose.focus();

        // Ambil data saat pertama dibuka, lalu segarkan bila sudah lebih dari 1 menit.
        if (Date.now() - lastSummaryLoad > 60000) {
            lastSummaryLoad = Date.now();
            const [y, m] = currentMonth.split('-').map(Number);
            document.getElementById('summaryMonth').textContent =
                new Date(y, m - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            loadLbSummaryBulanIni();
            loadMonthlyChart();
        }
    }

    function closeSummary() {
        summaryModal.hidden = true;
        document.documentElement.classList.remove('modal-open');
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    summaryOpen.addEventListener('click', openSummary);
    summaryClose.addEventListener('click', closeSummary);
    summaryModal.addEventListener('click', function (e) {
        if (e.target.dataset && e.target.dataset.close) closeSummary();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !summaryModal.hidden) closeSummary();
    });

    // ===== SUMMARY: PENERIMAAN LB BULAN INI =====
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

    // ===== SUMMARY: GRAFIK PRODUKSI BULAN INI =====
    let dashCharts = {};

    function showChartMessage(msg) {
        document.getElementById('monthlyChartWrapper').style.display = 'none';
        const empty = document.getElementById('monthlyChartEmpty');
        empty.innerText = msg;
        empty.style.display = 'block';
    }

    async function loadMonthlyChart() {
        if (typeof Chart === 'undefined') {
            showChartMessage('Gagal memuat grafik: library Chart.js tidak tersedia.');
            return;
        }

        try {
            const response = await fetch('{{ route('produksi-dashboard.data') }}');
            const allData = await response.json();
            const data = (allData || []).filter(d => d.bulan === currentMonth);

            if (data.length === 0) {
                showChartMessage('Belum ada data produksi bulan ini.');
                return;
            }

            document.getElementById('monthlyChartEmpty').style.display = 'none';
            document.getElementById('monthlyChartWrapper').style.display = '';
            renderDashboardCharts(data);
        } catch (err) {
            showChartMessage('Gagal memuat grafik: ' + err.message);
        }
    }

    function renderDashboardCharts(data) {
        Object.values(dashCharts).forEach(c => c && c.destroy());

        const C_RED = '#d9232a';
        const C_SLATE = '#334155';
        const C_AMBER = '#d97706';
        const C_TEAL = '#0f766e';

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
                    backgroundColor: [C_RED, C_SLATE, C_AMBER]
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
                    { label: '% Defect', data: data.map(d => d.pctDefect), backgroundColor: 'rgba(217,35,42,0.15)', borderColor: C_RED, fill: true, tension: 0.15 },
                    { label: '% KW2', data: data.map(d => d.pctKw2), backgroundColor: 'rgba(51,65,85,0.15)', borderColor: C_SLATE, fill: true, tension: 0.15 }
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
            data: { labels: chartLabels, datasets: [{ data: data.map(d => d.yieldTitikNol), borderColor: C_TEAL, tension: 0.15, fill: false }] },
            options: lineOpt(C_TEAL)
        });
        dashCharts.lineFG = new Chart(document.getElementById('dashLineFgBp').getContext('2d'), {
            type: 'line',
            data: { labels: chartLabels, datasets: [{ data: data.map(d => d.yieldFgBp), borderColor: C_AMBER, tension: 0.15, fill: false }] },
            options: lineOpt(C_AMBER)
        });
        dashCharts.lineBP = new Chart(document.getElementById('dashLineByProduct').getContext('2d'), {
            type: 'line',
            data: { labels: chartLabels, datasets: [{ data: data.map(d => d.yieldByProduct), borderColor: C_RED, tension: 0.15, fill: false }] },
            options: lineOpt(C_RED)
        });
    }
</script>
</body>
</html>