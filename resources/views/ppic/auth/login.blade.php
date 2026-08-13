<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPIC - Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
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
            box-shadow: 0 20px 50px rgba(30,27,46,.1);
            border: 1px solid var(--line);
        }
        .login-card { width: 50%; padding: 50px 46px; display: flex; flex-direction: column; justify-content: center; }
        .eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem; color: var(--primary);
            letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;
        }
        h3.login-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800; font-size: 1.9rem; text-transform: uppercase;
            color: var(--text); margin-bottom: 4px;
        }
        .subtitle { color: var(--muted); font-size: 0.85rem; margin-bottom: 30px; }
        label {
            font-weight: 700; font-size: 0.78rem; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; display: block;
        }
        .form-control {
            width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--line);
            padding: 0 14px; font-size: 0.92rem; color: var(--text); margin-bottom: 18px; background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
        .btn-login {
            width: 100%; height: 48px; border-radius: 10px; border: none;
            background: var(--primary); color: #fff; font-weight: 700; font-size: 0.92rem; cursor: pointer;
            transition: background .2s ease;
        }
        .btn-login:hover { background: var(--primary-dark); }
        .error-msg { color: #dc2626; font-size: 0.8rem; margin-top: -10px; margin-bottom: 14px; }
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: 0.8rem; color: var(--muted); text-decoration: none; }
        .back-link:hover { color: var(--primary); }
        .visual-side {
            width: 50%;
            background: linear-gradient(150deg, #1e1b4b 0%, #4338ca 60%, #6366f1 130%);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 40px; text-align: center; position: relative;
        }
        .visual-side .material-symbols-outlined { font-size: 64px; color: #a5b4fc; margin-bottom: 18px; }
        .visual-side h4 {
            font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1.7rem;
            text-transform: uppercase; color: #fff; letter-spacing: 0.5px; margin-bottom: 10px;
        }
        .visual-side p { color: rgba(255,255,255,.65); font-size: 0.82rem; line-height: 1.7; max-width: 260px; }
        .access-badge {
            margin-top: 24px; font-family: 'JetBrains Mono', monospace; font-size: 0.68rem; color: #a5b4fc;
            border: 1px solid rgba(165,180,252,.35); padding: 5px 12px; border-radius: 999px; letter-spacing: 1px;
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
        <h3 class="login-title">PPIC</h3>
        <p class="subtitle">CPI Jombang Plant &middot; Production Planning & Inventory Control</p>

        <form method="POST" action="{{ route('ppic.login.attempt') }}">
            @csrf
            <label>ID Pengguna</label>
            <input
                type="text"
                name="employee_code"
                value="{{ old('employee_code') }}"
                class="form-control"
                placeholder="PPIC01"
                style="text-transform: uppercase;"
                oninput="this.value = this.value.toUpperCase()"
                autofocus
            >

            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password">

            @error('employee_code')
                <div class="error-msg">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <a href="{{ route('dashboard') }}" class="back-link">← Kembali ke Dashboard Produksi</a>
    </div>

    <div class="visual-side">
        <span class="material-symbols-outlined">insights</span>
        <h4>Planning & Control</h4>
        <p>Pantau Planning vs Aktual produksi, kelola Purchase Order, dan lihat tren performa.</p>
        <div class="access-badge">PPIC ONLY</div>
    </div>
</div>

</body>
</html>
