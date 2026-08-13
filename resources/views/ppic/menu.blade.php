<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PPIC - Menu Utama</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f6fb; --surface: #ffffff; --surface-hover: #f8f9ff; --line: #e1e3f0;
            --primary: #4f46e5; --primary-dark: #4338ca; --primary-soft: #eef2ff;
            --text: #1e1b2e; --muted: #6b7280; --muted-dim: #a3a6b8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); }
        nav {
            display: flex; justify-content: space-between; align-items: center; padding: 14px 5%;
            background: var(--surface); border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; color: var(--text); }
        .logo img { height: 38px; }
        .back-link { color: var(--muted); text-decoration: none; font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        .back-link:hover { color: var(--primary); }
        .btn-logout {
            background: transparent; border: 1px solid var(--line); color: var(--muted);
            border-radius: 8px; padding: 8px 14px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
        }
        .btn-logout:hover { color: #dc2626; border-color: #dc2626; }

        .hero { padding: 46px 5% 20px; }
        .eyebrow {
            font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--primary);
            letter-spacing: 3px; text-transform: uppercase; margin-bottom: 10px;
        }
        h1 {
            font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 2.6rem;
            text-transform: uppercase; letter-spacing: -0.3px; color: var(--text);
        }

        .grid-container {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px; padding: 30px 5% 60px;
        }
        .menu-card {
            background: var(--surface); border: 1px solid var(--line); border-radius: 14px;
            padding: 30px 26px; text-decoration: none; color: inherit;
            transition: all .2s ease; display: flex; flex-direction: column; gap: 14px;
        }
        .menu-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(30,27,46,.08); border-color: var(--primary); }
        .menu-icon {
            width: 52px; height: 52px; border-radius: 12px; background: var(--primary-soft);
            display: flex; align-items: center; justify-content: center; color: var(--primary);
        }
        .menu-icon .material-symbols-outlined { font-size: 28px; }
        .menu-name { font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 1.4rem; text-transform: uppercase; }
        .menu-desc { font-size: 0.85rem; color: var(--muted); line-height: 1.5; }
        .menu-arrow { color: var(--primary); font-weight: 700; font-size: 0.82rem; display: flex; align-items: center; gap: 4px; }
    </style>
</head>
<body>

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
            <span>PPIC</span>
        </div>
        <div style="display:flex; align-items:center; gap:16px;">
            <span style="font-size:0.8rem; color: var(--muted); font-family:'JetBrains Mono',monospace;">{{ auth()->guard('tally')->user()->name }}</span>
            <form id="logoutForm" method="POST" action="{{ route('ppic.logout') }}">
                @csrf
                <button type="button" class="btn-logout" onclick="confirmLogout()">Keluar</button>
            </form>
        </div>
    </nav>

    <div class="hero">
        <div class="eyebrow">Production Planning & Inventory Control</div>
        <h1>Menu Utama</h1>
    </div>

    <div class="grid-container">
        <a href="{{ route('ppic.planning.index') }}" class="menu-card">
            <div class="menu-icon"><span class="material-symbols-outlined">monitoring</span></div>
            <div class="menu-name">Planning vs Aktual</div>
            <p class="menu-desc">Bandingkan Plan vs Aktual Ekor & KG harian, lihat selisih dan persentase otomatis.</p>
            <span class="menu-arrow">Buka <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span></span>
        </a>

        <a href="{{ route('ppic.purchase-order.index') }}" class="menu-card">
            <div class="menu-icon"><span class="material-symbols-outlined">receipt_long</span></div>
            <div class="menu-name">Input PO</div>
            <p class="menu-desc">Catat Purchase Order baru - jenis PO, nomor PO, dan tanggal.</p>
            <span class="menu-arrow">Buka <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span></span>
        </a>

        <a href="{{ route('ppic.dashboard.index') }}" class="menu-card">
            <div class="menu-icon"><span class="material-symbols-outlined">bar_chart</span></div>
            <div class="menu-name">Dashboard</div>
            <p class="menu-desc">Grafik tren Planning vs Aktual dan ringkasan PO bulan berjalan.</p>
            <span class="menu-arrow">Buka <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span></span>
        </a>
    </div>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Keluar dari Sistem?',
                html: '<div style="font-size:14px;color:#6b7280;">Anda akan keluar dari <b>PPIC</b>.</div>',
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }
    </script>
</body>
</html>
