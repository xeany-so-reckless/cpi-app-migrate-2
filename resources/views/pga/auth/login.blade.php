
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#101827">

    <title>PGA - Login</title>

    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800;900&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">

    <style>
        :root {
            --bg: #101827;
            --primary: #dc2626;
            --primary-dark: #b91c1c;
            --text: #f8fafc;
            --muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        html, body {
            width: 100%;
            min-height: 100%;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .page {
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;
            height: 100vh;
            height: 100svh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            isolation: isolate;
            background:
                radial-gradient(ellipse at 12% 85%, rgba(30,64,100,.25), transparent 45%),
                radial-gradient(ellipse at 85% 15%, rgba(220,38,38,.10), transparent 35%),
                #101827;
        }

        .grid-bg {
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 45px 45px;
            mask-image: linear-gradient(to bottom, black, transparent 95%);
            pointer-events: none;
        }

        .panel-icon img {
    width: 26px;
    height: 26px;
    object-fit: contain;
    display: block;
}

        .glow {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            border: 1px solid rgba(220,38,38,.09);
            right: -200px;
            top: 10%;
            z-index: -1;
            box-shadow:
                0 0 0 60px rgba(220,38,38,.02),
                0 0 0 120px rgba(220,38,38,.012);
            pointer-events: none;
        }

        /* HEADER */

        .header {
            flex-shrink: 0;
            height: 75px;
            padding: 0 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(220,38,38,.12);
            border: 1px solid rgba(220,38,38,.25);
            color: #f87171;
        }

        .brand-icon span {
            font-size: 22px;
        }

        .brand-name {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .6px;
        }

        .brand-sub {
            font-family: 'JetBrains Mono', monospace;
            color: #718096;
            font-size: 8px;
            letter-spacing: 1.2px;
            margin-top: 3px;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            letter-spacing: 1px;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 10px rgba(74,222,128,.6);
        }

        /* MAIN */

        .main {
            flex: 1;
            min-height: 0;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 9%;
            display: grid;
            grid-template-columns: 1fr 400px;
            align-items: center;
            gap: 60px;
        }

        /* INTRO */

        .intro {
            max-width: 600px;
        }

        .intro-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            color: #f87171;
            letter-spacing: 2.5px;
            margin-bottom: 18px;
        }

        .intro-label::before {
            content: "";
            width: 28px;
            height: 2px;
            background: var(--primary);
        }

        .intro-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: clamp(58px, 7vw, 90px);
            font-weight: 900;
            line-height: .87;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .intro-title span {
            color: var(--primary);
        }

        .intro-description {
            max-width: 390px;
            margin-top: 22px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.9;
        }

        /* LOGIN PANEL */

        .login-panel {
            width: 100%;
            padding: 32px;
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 14px;
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }

        .panel-icon {
            width: 43px;
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            background: rgba(220,38,38,.12);
            color: #f87171;
            border: 1px solid rgba(220,38,38,.22);
            margin-bottom: 19px;
        }

        .panel-icon span {
            font-size: 23px;
        }

        .panel-eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            letter-spacing: 2px;
            color: #f87171;
            margin-bottom: 7px;
        }

        .panel-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 32px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .panel-description {
            color: #94a3b8;
            font-size: 11px;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #cbd5e1;
            letter-spacing: .8px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            height: 47px;
            padding: 0 13px;
            gap: 10px;
            background: rgba(8,15,28,.55);
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 8px;
            transition: .2s ease;
        }

        .input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(220,38,38,.10);
        }

        .input-icon {
            color: #718096;
            font-size: 18px;
        }

        .form-control {
            flex: 1;
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            background: transparent;
            color: white;
            font-size: 12px;
            font-weight: 500;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .toggle-password {
            border: none;
            background: transparent;
            color: #718096;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .toggle-password:hover {
            color: #f87171;
        }

        .error-msg {
            font-size: 10px;
            color: #f87171;
            margin-top: 7px;
        }

        .btn-login {
            width: 100%;
            height: 47px;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 9px;
            transition: .25s ease;
            margin-top: 7px;
        }

        .btn-login:hover {
            background: #ef4444;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220,38,38,.2);
        }

        .btn-login span {
            font-size: 17px;
        }

        .back-link {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 19px;
            color: #8492a6;
            text-decoration: none;
            font-size: 10px;
            transition: .2s;
        }

        .back-link:hover {
            color: #f87171;
        }

        /* FOOTER */

        .footer {
            flex-shrink: 0;
            height: 48px;
            padding: 0 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,.07);
        }

        .footer-text {
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
            font-size: 8px;
            letter-spacing: .8px;
        }

        .footer-version {
            color: #f87171;
            font-family: 'JetBrains Mono', monospace;
            font-size: 8px;
        }

        /* SHORT DESKTOP SCREENS */

        @media (max-height: 700px) and (min-width: 701px) {
            .header {
                height: 60px;
            }

            .footer {
                height: 38px;
            }

            .main {
                padding-top: 15px;
                padding-bottom: 15px;
                gap: 40px;
            }

            .intro-title {
                font-size: 65px;
            }

            .intro-label {
                margin-bottom: 12px;
            }

            .intro-description {
                margin-top: 15px;
            }

            .login-panel {
                padding: 23px 28px;
            }

            .panel-icon {
                width: 36px;
                height: 36px;
                margin-bottom: 12px;
            }

            .panel-icon span {
                font-size: 20px;
            }

            .panel-description {
                margin-bottom: 16px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .input-wrapper,
            .btn-login {
                height: 43px;
            }

            .back-link {
                margin-top: 13px;
            }
        }

        /* MOBILE */

        @media (max-width: 700px) {
            .page {
                height: auto;
                min-height: 100svh;
                overflow: visible;
            }

            .header {
                height: 65px;
                padding: 0 6%;
            }

            .header-status {
                font-size: 8px;
            }

            .main {
                flex: 1;
                min-height: auto;
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 40px 7%;
                max-width: 480px;
            }

            .intro {
                text-align: center;
                margin: auto;
            }

            .intro-label {
                justify-content: center;
                margin-bottom: 13px;
            }

            .intro-label::before {
                display: none;
            }

            .intro-title {
                font-size: 66px;
            }

            .intro-description {
                margin: 15px auto 0;
                font-size: 11px;
            }

            .login-panel {
                padding: 27px;
            }

            .footer {
                height: 43px;
                padding: 0 6%;
            }
        }

        @media (max-width: 400px) {
            .header-status {
                display: none;
            }

            .intro-title {
                font-size: 57px;
            }

            .login-panel {
                padding: 23px;
            }

            .footer-text,
            .footer-version {
                font-size: 7px;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <div class="grid-bg"></div>
    <div class="glow"></div>

    <!-- HEADER -->
    <header class="header">

        <div class="brand">
            <div class="panel-icon">
                <img src="{{ asset('images/logo.png') }}" alt="PGA Icon">
            </div>

            <div>
                <div class="brand-name">CPI JOMBANG</div>
                <div class="brand-sub">PLANT MANAGEMENT SYSTEM</div>
            </div>
        </div>

        <div class="header-status">
            <span class="status-dot"></span>
            SECURE ACCESS PORTAL
        </div>

    </header>

    <!-- MAIN -->
    <main class="main">

        <section class="intro">

            <div class="intro-label">
                PAPERLESS & DIGITALIZED
            </div>

            <h1 class="intro-title">
                PERSONALIA & <br>
                <span>GENERAL AFFAIRS .</span>
            </h1>

            <p class="intro-description">
                PGA Management Portal untuk pengelolaan keselamatan,
                inspeksi APAR, administrasi, dan aktivitas kunjungan
                di CPI Jombang Plant.
            </p>

        </section>

        <!-- LOGIN -->
        <section class="login-panel">

            <div class="panel-icon">
                <img src="{{ asset('images/logo.png') }}" alt="PGA Icon">
            </div>

            <div class="panel-eyebrow">
                RESTRICTED ACCESS
            </div>

            <h2 class="panel-title">Welcome Back.</h2>

            <p class="panel-description">
                Masuk menggunakan akun PGA untuk melanjutkan ke sistem.
            </p>

            <form method="POST" action="{{ route('pga.login.attempt') }}">
                @csrf

                <div class="form-group">

                    <label class="form-label" for="employee_code">
                        ID Pengguna
                    </label>

                    <div class="input-wrapper">

                        <span class="material-symbols-outlined input-icon">
                            badge
                        </span>

                        <input
                            type="text"
                            id="employee_code"
                            name="employee_code"
                            value="{{ old('employee_code') }}"
                            class="form-control"
                            placeholder="Contoh: PGA01"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase()"
                            autocomplete="username"
                            autofocus
                            required
                        >

                    </div>

                    @error('employee_code')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror

                </div>

                <div class="form-group">

                    <label class="form-label" for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <span class="material-symbols-outlined input-icon">
                            lock
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                            aria-label="Tampilkan password"
                        >
                            <span
                                class="material-symbols-outlined"
                                id="password-icon"
                            >visibility</span>
                        </button>

                    </div>

                </div>

                <button type="submit" class="btn-login">
                    MASUK KE SISTEM
                    <span class="material-symbols-outlined">
                        arrow_forward
                    </span>
                </button>

            </form>

            <x-logout-warning />

            <a href="{{ route('dashboard') }}" class="back-link">
                <span class="material-symbols-outlined" style="font-size:15px;">
                    arrow_back
                </span>
                Kembali ke Dashboard Produksi
            </a>

        </section>

    </main>

    <!-- FOOTER -->
    <footer class="footer">

        <div class="footer-text">
            CHAROEN POKPHAND INDONESIA · JOMBANG PLANT
        </div>

        <div class="footer-version">
            PGA PORTAL / 2026
        </div>

    </footer>

</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('password-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            passwordIcon.textContent = 'visibility';
        }
    }
</script>

</body>
</html>
