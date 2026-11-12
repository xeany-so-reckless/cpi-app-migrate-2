
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPIC - Login</title>

    @include('partials.favicon')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #3157c8;
            --primary-dark: #2445a8;
            --primary-soft: #edf2ff;
            --text: #18243a;
            --muted: #8290a5;
            --line: #e7ebf2;
            --bg: #f4f7fc;
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
            min-height: 100vh;
            min-height: 100dvh;
            background:
                radial-gradient(circle at 10% 10%, rgba(49, 87, 200, .07), transparent 28%),
                radial-gradient(circle at 90% 90%, rgba(80, 140, 220, .08), transparent 30%),
                var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* MAIN CARD */

        .login-wrapper {
            width: 1050px;
            max-width: 100%;
            height: auto;
            min-height: 0;

            display: grid;
            grid-template-columns: .92fr 1.08fr;

            background: #fff;
            border: 1px solid rgba(222, 229, 241, .9);
            border-radius: 24px;
            overflow: hidden;

            box-shadow:
                0 30px 80px rgba(34, 57, 105, .09),
                0 5px 20px rgba(34, 57, 105, .03);
        }

        /* LOGIN SECTION */

        .login-card {
            padding: 38px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
            position: relative;
            z-index: 2;
        }

        /* BRAND */

        .brand {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 30px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border: none;
            background: transparent;
        }

        .brand-name {
            font-size: .9rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: .4px;
        }

        .brand-caption {
            font-size: .65rem;
            color: var(--muted);
            margin-top: 4px;
            letter-spacing: .6px;
        }

        .eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .eyebrow::before {
            content: '';
            width: 22px;
            height: 2px;
            background: var(--primary);
            border-radius: 10px;
        }

        h3.login-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 3.1rem;
            line-height: 1;
            font-weight: 800;
            letter-spacing: .5px;
            color: var(--text);
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: .81rem;
            line-height: 1.7;
            color: var(--muted);
            margin-bottom: 27px;
        }

        /* FORM */

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: .73rem;
            font-weight: 700;
            color: #46546b;
            margin-bottom: 8px;
            letter-spacing: .2px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .material-symbols-outlined {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #9aa7ba;
            pointer-events: none;
            transition: color .2s ease;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 15px 0 46px;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: #fbfcfe;
            color: var(--text);
            font-size: .86rem;
            font-weight: 500;
            outline: none;
            transition: all .25s ease;
        }

        .form-control::placeholder {
            color: #b3bdcc;
            font-weight: 400;
        }

        .form-control:hover {
            border-color: #c8d3e8;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(49, 87, 200, .09);
        }

        .input-wrapper:focus-within .material-symbols-outlined {
            color: var(--primary);
        }

        .error-msg {
            color: #dc4242;
            font-size: .76rem;
            margin-top: 7px;
            font-weight: 500;
        }

        /* LOGIN BUTTON */

        .btn-login {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 11px;
            background: linear-gradient(110deg, #3157c8, #426de0);
            color: #fff;
            font-size: .87rem;
            font-weight: 700;
            letter-spacing: .2px;
            cursor: pointer;
            margin-top: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 7px 17px rgba(49, 87, 200, .2);
            transition: all .25s ease;
        }

        .btn-login:hover {
            background: linear-gradient(110deg, #2445a8, #3157c8);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(49, 87, 200, .25);
        }

        .btn-login .material-symbols-outlined {
            font-size: 19px;
        }

        /* BACK TO DASHBOARD */

        .back-link {
            width: 100%;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-top: 13px;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid #dfe5ef;
            border-radius: 11px;
            color: #66758c;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            transition: all .25s ease;
        }

        .back-link:hover {
            color: var(--primary);
            background: var(--primary-soft);
            border-color: #cbd8fa;
        }

        .back-link .material-symbols-outlined {
            font-size: 19px;
        }

        /* VISUAL SECTION */

        .visual-side {
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #eaf0ff 0%, #f3f6ff 55%, #e5edff 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .visual-side::before {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            border: 1px solid rgba(49, 87, 200, .10);
            border-radius: 50%;
            top: -160px;
            right: -170px;
        }

        .visual-side::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border: 1px solid rgba(49, 87, 200, .10);
            border-radius: 50%;
            bottom: -150px;
            left: -120px;
        }

        .visual-content {
            width: 100%;
            max-width: 390px;
            position: relative;
            z-index: 1;
        }

        .visual-heading {
            margin-bottom: 23px;
        }

        .visual-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            background: rgba(49, 87, 200, .08);
            color: var(--primary);
            border-radius: 7px;
            font-family: 'JetBrains Mono', monospace;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .8px;
            margin-bottom: 14px;
        }

        .visual-label .material-symbols-outlined {
            font-size: 15px;
        }

        .visual-heading h4 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2.55rem;
            line-height: 1.05;
            font-weight: 800;
            color: #1c2c50;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .visual-heading h4 span {
            color: var(--primary);
        }

        .visual-heading p {
            font-size: .8rem;
            line-height: 1.7;
            color: #71809b;
            max-width: 330px;
        }

        /* DASHBOARD PREVIEW */

        .dashboard-preview {
            width: 100%;
            background: rgba(255, 255, 255, .88);
            border: 1px solid rgba(255, 255, 255, .95);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 15px 40px rgba(45, 74, 145, .10);
            backdrop-filter: blur(10px);
        }

        .preview-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .preview-title {
            font-size: .74rem;
            font-weight: 800;
            color: #34425b;
        }

        .preview-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .6rem;
            font-weight: 700;
            color: #26966a;
            background: #eaf8f1;
            padding: 6px 9px;
            border-radius: 6px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #26966a;
        }

        .preview-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stat-box {
            padding: 11px;
            background: #f7f9fe;
            border: 1px solid #edf0f7;
            border-radius: 10px;
        }

        .stat-icon {
            width: 27px;
            height: 27px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #e9efff;
            color: #4165cb;
            margin-bottom: 8px;
        }

        .stat-icon .material-symbols-outlined {
            font-size: 17px;
        }

        .stat-box small {
            display: block;
            font-size: .6rem;
            color: #91a0b5;
            margin-bottom: 4px;
        }

        .stat-box strong {
            font-size: .95rem;
            color: #263650;
            font-weight: 800;
        }

        .preview-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 13px;
            border-top: 1px solid #edf0f6;
        }

        .preview-bottom span {
            font-size: .61rem;
            color: #8997ac;
        }

        .preview-bottom strong {
            font-family: 'JetBrains Mono', monospace;
            font-size: .65rem;
            color: #4165cb;
        }

        .access-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 23px;
            padding-top: 17px;
            border-top: 1px solid rgba(49, 87, 200, .12);
        }

        .access-badge span {
            font-family: 'JetBrains Mono', monospace;
            font-size: .61rem;
            font-weight: 600;
            letter-spacing: .8px;
            color: #8190ad;
        }

        .access-badge strong {
            font-family: 'JetBrains Mono', monospace;
            font-size: .63rem;
            color: var(--primary);
        }

        /* SHORT DESKTOP */

        @media (max-height: 750px) and (min-width: 701px) {
            body {
                padding: 12px;
            }

            .login-card {
                padding: 24px 36px;
            }

            .brand {
                margin-bottom: 20px;
            }

            .subtitle {
                margin-bottom: 18px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .visual-side {
                padding: 25px;
            }

            .visual-heading {
                margin-bottom: 15px;
            }

            .visual-heading h4 {
                font-size: 2.2rem;
            }

            .dashboard-preview {
                padding: 12px;
            }

            .stat-box {
                padding: 8px;
            }

            .access-badge {
                margin-top: 14px;
                padding-top: 12px;
            }

            .back-link {
                margin-top: 9px;
                min-height: 40px;
            }
        }

        /* MOBILE */

        @media (max-width: 700px) {
            body {
                padding: 15px;
                align-items: flex-start;
            }

            .login-wrapper {
                grid-template-columns: 1fr;
                width: 100%;
                max-width: 460px;
                height: auto;
                margin: auto;
            }

            .visual-side {
                padding: 28px 25px;
            }

            .visual-heading {
                margin-bottom: 18px;
            }

            .visual-heading h4 {
                font-size: 2rem;
            }

            .dashboard-preview {
                padding: 15px;
            }

            .preview-stats {
                gap: 8px;
            }

            .login-card {
                padding: 30px 28px;
            }

            .brand {
                margin-bottom: 25px;
            }

            h3.login-title {
                font-size: 2.8rem;
            }

            .subtitle {
                margin-bottom: 23px;
            }
        }

        @media (max-width: 380px) {
            .login-card {
                padding: 26px 21px;
            }

            .visual-side {
                padding: 24px 20px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">

    <!-- LOGIN SECTION -->
    <div class="login-card">

        <div class="brand">
            <div class="brand-icon">
                <img src="{{ asset('images/logo.png') }}" alt="Company Logo">
            </div>

            <div>
                <div class="brand-name">PRODUCTION SYSTEM</div>
                <div class="brand-caption">CPI JOMBANG PLANT</div>
            </div>
        </div>

        <div class="eyebrow">Restricted Access</div>

        <h3 class="login-title">PPIC PORTAL</h3>

        <p class="subtitle">
            Sign in to access your planning and inventory control workspace.
        </p>

        <form method="POST" action="{{ route('ppic.login.attempt') }}">
            @csrf

            <div class="form-group">
                <label for="employee_code">ID Pengguna</label>

                <div class="input-wrapper">
                    <span class="material-symbols-outlined">badge</span>

                    <input
                        type="text"
                        id="employee_code"
                        name="employee_code"
                        value="{{ old('employee_code') }}"
                        class="form-control"
                        placeholder="Masukkan ID Pengguna"
                        style="text-transform: uppercase;"
                        oninput="this.value = this.value.toUpperCase()"
                        autocomplete="username"
                        autofocus
                    >
                </div>

                @error('employee_code')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="input-wrapper">
                    <span class="material-symbols-outlined">lock</span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <button type="submit" class="btn-login">
                Sign In
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <x-logout-warning />

        <a href="{{ route('dashboard') }}" class="back-link">
            <span class="material-symbols-outlined">arrow_back</span>
            Kembali ke Dashboard Produksi
        </a>

    </div>

    <!-- VISUAL SECTION -->
    <div class="visual-side">

        <div class="visual-content">

            <div class="visual-heading">

                <div class="visual-label">
                    <span class="material-symbols-outlined">verified_user</span>
                    ENTERPRISE WORKSPACE
                </div>

                <h4>
                    Planning <span>&</span><br>
                    Production Control
                </h4>

                <p>
                    Integrated production planning and inventory monitoring
                    for smarter operational decisions.
                </p>

            </div>

            <div class="dashboard-preview">

                <div class="preview-top">
                    <div class="preview-title">PPIC OVERVIEW</div>

                    <div class="preview-status">
                        <span class="status-dot"></span>
                        SYSTEM ACTIVE
                    </div>
                </div>

                <div class="preview-stats">

                    <div class="stat-box">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">calendar_month</span>
                        </div>
                        <small>Production Planning</small>
                        <strong>PLANNING</strong>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">inventory_2</span>
                        </div>
                        <small>Inventory Control</small>
                        <strong>MONITORING</strong>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">shopping_cart</span>
                        </div>
                        <small>Purchase Order</small>
                        <strong>MANAGEMENT</strong>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">query_stats</span>
                        </div>
                        <small>Production Performance</small>
                        <strong>ANALYTICS</strong>
                    </div>

                </div>

                <div class="preview-bottom">
                    <span>Production Integrated System</span>
                    <strong>PPIC / 2026</strong>
                </div>

            </div>

            <div class="access-badge">
                <span>AUTHORIZED PERSONNEL ONLY</span>
                <strong>PPIC ACCESS</strong>
            </div>

        </div>

    </div>

</div>

</body>
</html>
