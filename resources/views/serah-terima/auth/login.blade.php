<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Serah Terima - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            margin: 0;
            min-height: 100vh;
            background: #eef3f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container { max-width: 400px; margin-top: 100px; }

        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-20deg);
            animation: shimmer-animation 2.5s infinite;
        }
        @keyframes shimmer-animation {
            0% { left: -100%; }
            50% { left: 200%; }
            100% { left: 200%; }
        }

        .back-link {
            display: inline-block; margin-top: 16px; font-size: 13px;
            color: #64748b; text-decoration: none;
        }
        .back-link:hover { color: #1e293b; text-decoration: underline; }

        .login-wrapper {
            width: 1000px;
            max-width: 95%;
            display: flex;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
        }

        .login-card {
            width: 45%;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .image-side {
            width: 55%;
            background: url("{{ asset('images/serah-terima-login.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
        }

        .btn {
            height: 50px;
            border-radius: 12px;
        }

        @media(max-width: 900px) {
            .login-wrapper {
                flex-direction: column;
            }
            .login-card {
                width: 100%;
            }
            .image-side {
                width: 100%;
                min-height: 280px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <h3 class="text-center mb-1 fw-bold text-dark">E-SERAH TERIMA</h3>
        <p class="text-center text-muted small mb-4">CPI Jombang Plant</p>

        <form method="POST" action="{{ route('serahterima.login.attempt') }}">
            @csrf
            <div class="mb-3">
                <label class="fw-bold text-secondary mb-1">ID Pengguna</label>
                <input
                    type="text"
                    name="employee_code"
                    value="{{ old('employee_code') }}"
                    class="form-control text-uppercase"
                    placeholder="TPR / TWH / SPV / SPVG / ADMG"
                    oninput="this.value = this.value.toUpperCase()"
                    autofocus
                >
            </div>

            <div class="mb-3">
                <label class="fw-bold text-secondary mb-1">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Masukkan password"
                >
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold btn-shimmer">
                Masuk
            </button>

            @error('employee_code')
                <div class="text-danger small mt-2">
                    {{ $message }}
                </div>
            @enderror
        </form>

        <div class="text-center">
            <a href="{{ route('dashboard') }}" class="back-link">← Kembali ke Dashboard</a>
        </div>
    </div>

    <!-- Sisi kanan otomatis penuh dan diam -->
    <div class="image-side"></div>
</div>

</body>
</html>