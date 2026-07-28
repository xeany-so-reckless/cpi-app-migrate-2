<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TALLY PRO 2026 - Login</title>
    <style>
        :root {
            /* Tema warna Anda */
            --active-color: #27ae60;
            --active-color-dark: #219653;
            /* Warna tambahan untuk aksen */
            --bg-body: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden; /* Mencegah scroll double */
        }

        /* Container utama split screen */
        .login-wrapper {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* --- Bagian Kiri: Form Login --- */
        .login-left {
            flex: 1; /* Ambil 50% lebar atau lebih pada mobile */
            background-color: var(--bg-body);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            box-sizing: border-box;
            z-index: 2; /* Agar shadow card terlihat di atas bagian kanan */
        }

        /* Modern Card untuk form */
        .login-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            /* Shadow yang lebih halus dan modern */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px; /* Sedikit lebih lebar agar lega */
            box-sizing: border-box;
            border: 1px solid #f0f0f0;
        }

        .card-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .card-header h2 {
            margin: 0;
            color: var(--text-main);
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .card-header small {
            color: var(--text-muted);
            display: block;
            margin-top: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Styling Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            transition: all 0.2s ease;
            background-color: #fbfbfb;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--active-color);
            background-color: #ffffff;
            /* Efek ring fokus yang modern */
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.15);
        }

        /* Styling Tombol Login */
        .btn-login {
            width: 100%;
            padding: 13px;
            background-color: var(--active-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
            transition: all 0.2s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            background-color: var(--active-color-dark);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Styling Link Kembali */
        .card-footer {
            text-align: center;
            margin-top: 25px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .back-link {
            display: inline-block;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--text-main);
        }

        /* Styling Pesan Error */
        .error-msg {
            color: #e11d48;
            font-size: 13px;
            margin-top: 15px;
            background-color: #fff1f2;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ffe4e6;
            text-align: center;
            font-weight: 500;
        }


        /* --- Bagian Kanan: Visual/Gambar --- */
        .login-right {
            flex: 1.2; /* Sedikit lebih lebar dari form di desktop */
            /* Background Gradient & Pattern */
            background-color: #1a1a1a;
            background-image:
                url('/images/tally-login-gambar.jpg'); /* Pola SVG geometris samar */
            background-size: cover, auto;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            box-sizing: border-box;
            color: white;
            text-align: center;
        }

        .visual-content h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -1.5px;
        }

        .visual-content p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.85;
            max-width: 400px;
            margin: 0 auto;
        }

        /* --- Media Query untuk Responsivitas Mobile --- */
        @media (max-width: 850px) {
            .login-wrapper {
                flex-direction: column; /* Stack vertikal di mobile */
            }

            .login-right {
                flex: none;
                height: 30vh; /* Tampilkan sedikit visual di atas */
                padding: 30px;
            }

            .visual-content h1 {
                font-size: 28px;
            }

            .visual-content p {
                font-size: 14px;
                display: none; /* Sembunyikan deskripsi di mobile agar hemat tempat */
            }

            .login-left {
                flex: 1;
                padding: 20px;
            }

            .login-card {
                padding: 30px 20px;
                border-radius: 12px;
            }
        }

        .password-input {
    position: relative;
}

        .password-input input {
            width: 100%;
            padding: 14px 50px 14px 16px; /* ruang untuk ikon mata */
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            border: none;
            background: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 18px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color .2s;
        }

        .toggle-password:hover {
            color: #27ae60;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        <!-- Sisi Kiri: Form Login -->
        <div class="login-left">
            <div class="login-card">
                <div class="card-header">
                    <h2>TALLY PRO 2026</h2>
                    <small>Integrated Dashboard V.2</small>
                </div>

                <form method="POST" action="{{ route('tally.login.attempt') }}">
                    @csrf

                    <div class="form-group">
                        <label for="employee_code">ID Pengguna</label>
                        <input
                            type="text"
                            id="employee_code"
                            name="employee_code"
                            value="{{ old('employee_code') }}"
                            placeholder="TLY/APP"
                            autocomplete="off"
                            autofocus
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Kata Sandi</label>

                        <div class="password-input">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan kata sandi Anda"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i id="eyeIcon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">Masuk</button>

                    @error('employee_code')
                        <div class="error-msg"> {{ $message }}</div>
                    @enderror
                </form>

                <div class="card-footer">
                    <a href="{{ route('dashboard') }}" class="back-link">← Kembali ke Dashboard Utama</a>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan: Visual/Gambar -->
        <div class="login-right">
        </div>
    </div>
    
        <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");

            if (password.type === "password") {
                password.type = "text";
                eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                password.type = "password";
                eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
        </script>
</body>
</html>