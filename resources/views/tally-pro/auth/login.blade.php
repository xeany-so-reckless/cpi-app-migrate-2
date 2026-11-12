<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TALLY PRO 2026 - Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        :root {
            --primary: #27ae60;
            --primary-dark: #219653;
            --primary-light: #eaf8ef;
            --bg: #f5f8f6;
            --white: #ffffff;
            --text: #172b21;
            --muted: #84918a;
            --border: #e7eee9;
            --danger: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI',
                Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden;
        }

        body {
            background: var(--bg);
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            height: 100dvh;
            min-height: 0;
        }

        /* LEFT SECTION */
        .login-left {
            width: 46%;
            min-width: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px;
            position: relative;
            background:
                radial-gradient(circle at 10% 10%, rgba(39,174,96,.07), transparent 32%),
                var(--bg);
        }

        .login-container {
            width: 100%;
            max-width: 410px;
        }

        /* BRAND */
        .brand {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 48px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 21px;
        }

        .brand-info h3 {
            color: var(--text);
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .brand-info span {
            display: block;
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
            letter-spacing: .7px;
        }

        /* HEADER */
        .welcome {
            margin-bottom: 32px;
        }

        .welcome .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-dark);
            background: var(--primary-light);
            padding: 7px 12px;
            border-radius: 30px;
            margin-bottom: 17px;
            letter-spacing: .5px;
        }

        .welcome h1 {
            font-size: 34px;
            line-height: 1.2;
            letter-spacing: -1.3px;
            color: var(--text);
            font-weight: 800;
        }

        .welcome p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 11px;
            line-height: 1.7;
        }

        /* FORM */
        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: #405248;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper > i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3b0a8;
            font-size: 15px;
            transition: .2s;
        }

        .form-group input {
            width: 100%;
            height: 52px;
            padding: 0 48px;
            border: 1.5px solid var(--border);
            border-radius: 11px;
            outline: none;
            background: var(--white);
            color: var(--text);
            font-size: 13px;
            font-family: inherit;
            transition: all .25s ease;
        }

        .form-group input::placeholder {
            color: #b5c0b9;
        }

        .form-group input:hover {
            border-color: #c8ddce;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(39,174,96,.10);
        }

        .input-wrapper:focus-within > i {
            color: var(--primary);
        }

        .password-input input {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #a3b0a8;
            cursor: pointer;
            font-size: 15px;
            transition: .2s;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        /* BUTTON */
        .btn-login {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 11px;
            background: var(--primary);
            color: white;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .3px;
            cursor: pointer;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 5px 14px rgba(39,174,96,.20);
            transition: all .25s ease;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(39,174,96,.25);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* ERROR */
        .error-msg {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 9px;
            background: #fff1f2;
            border: 1px solid #ffe0e3;
            color: var(--danger);
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 9px;
            line-height: 1.5;
        }

        /* FOOTER */
        .card-footer {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 22px;
            border-top: 1px solid var(--border);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #718078;
            text-decoration: none;
            transition: .2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .version {
            font-size: 10px;
            color: #a6b1aa;
            font-weight: 600;
        }

        .copyright {
            margin-top: 45px;
            font-size: 10px;
            color: #a3afa7;
            letter-spacing: .3px;
        }

        /* RIGHT SECTION */
        .login-right {
            flex: 1;
            position: relative;
            overflow: hidden;
            background-image:
                linear-gradient(
                    135deg,
                    rgba(12, 42, 26, .25),
                    rgba(10, 35, 22, .65)
                ),
                url('/images/tally-login-gambar.jpg');
            background-position: center;
            background-size: cover;
            display: flex;
            align-items: center;
            padding: 65px;
        }

        .visual-content {
            position: relative;
            z-index: 2;
            max-width: 550px;
            color: white;
            animation: fadeUp .8s ease;
            transform: translateY(-35px);
        }

        .visual-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #c7f4d6;
            margin-bottom: 20px;
        }

        .visual-label::before {
            content: "";
            width: 25px;
            height: 2px;
            background: #52d889;
        }

        .visual-content h2 {
            font-size: clamp(32px, 3.5vw, 48px);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1.5px;
            margin-bottom: 18px;
        }

        .visual-content h2 span {
            color: #72e29c;
        }

        .visual-content p {
            font-size: 14px;
            line-height: 1.9;
            color: rgba(255,255,255,.78);
            max-width: 430px;
        }

        .visual-bottom {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 38px;
            font-size: 11px;
            color: rgba(255,255,255,.7);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #63e58f;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(99,229,143,.7);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */
        @media (max-width: 1000px) {
            .login-left {
                width: 50%;
                min-width: 380px;
                padding: 35px;
            }

            .login-right {
                padding: 40px;
            }

            .visual-content h2 {
                font-size: 34px;
            }
        }

        @media (max-width: 760px) {
            html, body {
                overflow: auto;
            }

            .login-wrapper {
                min-height: 100dvh;
                height: auto;
                flex-direction: column;
            }

            .login-left {
                width: 100%;
                min-width: 0;
                min-height: 100dvh;
                padding: 35px 24px;
            }

            .login-container {
                max-width: 430px;
            }

            .brand {
                margin-bottom: 38px;
            }

            .welcome h1 {
                font-size: 30px;
            }

            .login-right {
                display: none;
            }

            .copyright {
                margin-top: 35px;
            }
        }

        @media (max-height: 700px) and (min-width: 761px) {
            .login-left {
                padding-top: 20px;
                padding-bottom: 20px;
            }

            .brand {
                margin-bottom: 24px;
            }

            .welcome {
                margin-bottom: 22px;
            }

            .welcome h1 {
                font-size: 29px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            .copyright {
                margin-top: 25px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">

    <!-- LEFT: LOGIN -->
    <section class="login-left">

        <div class="login-container">

            <!-- BRAND -->
            <div class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>

                <div class="brand-info">
                    <h3>TALLY PRO 2026</h3>
                    <span>INTEGRATED MANAGEMENT SYSTEM</span>
                </div>
            </div>

            <!-- HEADER -->
            <div class="welcome">
                <div class="eyebrow">
                    <i class="fa-solid fa-shield-halved"></i>
                    SECURE ACCESS
                </div>

                <h1>Welcome<br>Back.</h1>

                <p>
                    Silakan masuk menggunakan akun perusahaan Anda
                    untuk mengakses sistem TALLY PRO.
                </p>
            </div>

            <!-- LOGIN FORM -->
            <form method="POST" action="{{ route('tally.login.attempt') }}">
                @csrf

                <div class="form-group">
                    <label for="employee_code">ID Pengguna</label>

                    <div class="input-wrapper">
                        <i class="fa-regular fa-user"></i>

                        <input
                            type="text"
                            id="employee_code"
                            name="employee_code"
                            value="{{ old('employee_code') }}"
                            placeholder="Masukkan ID pengguna"
                            autocomplete="username"
                            autofocus
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>

                    <div class="input-wrapper password-input">
                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                            aria-label="Tampilkan kata sandi"
                        >
                            <i id="eyeIcon" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Masuk ke Sistem
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                @error('employee_code')
                    <div class="error-msg">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        {{ $message }}
                    </div>
                @enderror

            </form>

            <!-- FOOTER -->
            <div class="card-footer">
                <a href="{{ route('dashboard') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Dashboard Utama
                </a>

                <span class="version">V.2.0</span>
            </div>

            <div class="copyright">
                &copy; 2026 TALLY PRO. All rights reserved.
            </div>

        </div>
    </section>

    <!-- RIGHT: VISUAL -->
    <section class="login-right">

        <div class="visual-content">

            <div class="visual-label">
                PRODUCTION MANAGEMENT
            </div>

            <h2>
                Smarter<br>
                Workflow.<br>
                <span>Better Control.</span>
            </h2>

            <p>
                An integrated platform designed to streamline
                operational processes, improve productivity,
                and provide better visibility across your
                production environment.
            </p>

            <div class="visual-bottom">
                <span class="status-dot"></span>
                Integrated Dashboard System
            </div>

        </div>

    </section>

</div>

<script>
    function togglePassword() {
        const password = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");
        const toggleButton = document.querySelector(".toggle-password");

        if (password.type === "password") {
            password.type = "text";
            eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
            toggleButton.setAttribute("aria-label", "Sembunyikan kata sandi");
        } else {
            password.type = "password";
            eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
            toggleButton.setAttribute("aria-label", "Tampilkan kata sandi");
        }
    }
</script>

</body>
</html>
