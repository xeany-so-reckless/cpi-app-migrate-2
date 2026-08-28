<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Warehouse - Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #eef4f8;
            --surface: #ffffff;
            --line: #d9e4ed;
            --ice: #0891b2;
            --ice-soft: #e0f6fb;
            --ice-text: #0e7490;
            --hazard: #f59e0b;
            --text: #16232e;
            --muted: #64798c;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            background: var(--bg);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 900px;
            max-width: 100%;
            display: flex;
            background: var(--surface);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(22,35,46,.1);
            border: 1px solid var(--line);
        }

        .login-card {
            width: 50%;
            padding: 50px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: var(--ice-text);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        h3.login-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.9rem;
            text-transform: uppercase;
            color: var(--text);
            margin-bottom: 4px;
        }

        .subtitle { color: var(--muted); font-size: 0.85rem; margin-bottom: 30px; }

        label {
            font-weight: 700;
            font-size: 0.78rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
            display: block;
        }

        .form-control {
            width: 100%;
            height: 48px;
            border-radius: 10px;
            border: 1px solid var(--line);
            padding: 0 14px;
            font-size: 0.92rem;
            color: var(--text);
            margin-bottom: 18px;
            background: #fff;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--ice);
            box-shadow: 0 0 0 3px var(--ice-soft);
        }

        .btn-login {
            width: 100%;
            height: 48px;
            border-radius: 10px;
            border: none;
            background: var(--ice);
            color: #fff;
            font-weight: 700;
            font-size: 0.92rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background .2s ease;
        }
        .btn-login:hover { background: var(--ice-text); }

        .error-msg {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: -10px;
            margin-bottom: 14px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.8rem;
            color: var(--muted);
            text-decoration: none;
        }
        .back-link:hover { color: var(--ice-text); }

        .visual-side {
            width: 50%;
            background:
                repeating-linear-gradient(90deg, rgba(255,255,255,.06) 0px, rgba(255,255,255,.06) 1px, transparent 1px, transparent 40px),
                repeating-linear-gradient(0deg, rgba(255,255,255,.06) 0px, rgba(255,255,255,.06) 1px, transparent 1px, transparent 40px),
                linear-gradient(150deg, #0a1219 0%, #0e2430 60%, #0e7490 130%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        .visual-side .material-symbols-outlined {
            font-size: 64px;
            color: #22d3ee;
            margin-bottom: 18px;
        }
        .visual-side h4 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.7rem;
            text-transform: uppercase;
            color: #fff;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .visual-side p {
            color: rgba(255,255,255,.65);
            font-size: 0.82rem;
            line-height: 1.7;
            max-width: 260px;
        }
        .access-badge {
            margin-top: 24px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
            color: #22d3ee;
            border: 1px solid rgba(34,211,238,.35);
            padding: 5px 12px;
            border-radius: 999px;
            letter-spacing: 1px;
        }

        @media (max-width: 800px) {
            .login-wrapper { flex-direction: column-reverse; }
            .login-card, .visual-side { width: 100%; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="eyebrow">Restricted Access</div>
        <h3 class="login-title">Stock Warehouse</h3>
        <p class="subtitle">CPI Jombang Plant &middot; Khusus Admin/Supervisor Gudang</p>

        <form method="POST" action="{{ route('warehouse.stock.login.attempt') }}">
            @csrf
            <label>ID Pengguna</label>
            <input
                type="text"
                name="employee_code"
                value="{{ old('employee_code') }}"
                class="form-control"
                placeholder="SPVG / ADMG01"
                style="text-transform: uppercase;"
                oninput="this.value = this.value.toUpperCase()"
                autofocus
            >

            <label>Password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Masukkan password"
            >

            @error('employee_code')
                <div class="error-msg">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <a href="{{ route('warehouse.dashboard') }}" class="back-link">← Kembali ke Warehouse Console</a>

        <a href="{{ route('dashboard') }}" class="back-link">← Kembali ke Dashboard Utama</a>
    </div>

    <div class="visual-side">
        <span class="material-symbols-outlined">inventory_2</span>
        <h4>Stock Monitoring</h4>
        <p>Pantau kapasitas &amp; isi setiap Cell Cold Storage secara real-time.</p>
        <div class="access-badge">ADMIN / SPV GUDANG ONLY</div>
    </div>
</div>

</body>
</html>
