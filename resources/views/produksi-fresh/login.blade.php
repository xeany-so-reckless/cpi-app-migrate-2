<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Produksi Fresh</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #da0019;
      --primary-hover: #b30015;
      --bg-color: #f2f2f2;
      --text-main: #2b2b2b;
      --text-muted: #555555;
      --border: #cccccc;
      --error: #ef4444;
      --card-bg: #ffffff;
    }
    * { box-sizing: border-box; font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    body {
      background-color: var(--bg-color);
      color: var(--text-main);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px 15px;
    }
    .container {
      width: 100%;
      max-width: 420px;
      background: var(--card-bg);
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border-top: 8px solid var(--primary);
      padding: 35px 25px 30px;
    }
    @media (min-width: 768px) {
      .container { max-width: 460px; padding: 45px 40px 35px; }
    }
    .logo-img { width: 80px; height: auto; margin: 0 auto 10px; display: block; }
    .login-title { text-align: center; font-size: 18px; font-weight: 800; margin-bottom: 10px; }
    .subtitle { text-align: center; font-size: 14px; color: var(--text-muted); margin-bottom: 20px; }
    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block; font-size: 14px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;
    }
    input, select {
      width: 100%; padding: 14px 16px; border: 1px solid var(--border); border-radius: 6px;
      font-size: 15px; color: var(--text-main); background-color: #fafafa;
    }
    input:focus, select:focus { outline: none; border-color: var(--primary); background-color: #fff; }
    .btn {
      width: 100%; padding: 15px; border: none; border-radius: 8px; font-size: 16px; font-weight: 700;
      cursor: pointer; background-color: var(--primary); color: #fff; margin-top: 8px;
    }
    .btn:hover { background-color: var(--primary-hover); }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      font-size: 13px;
      color: var(--text-muted);
      text-decoration: none;
      font-weight: 600;
    }
    .back-link:hover { color: var(--primary); }
    .error-box {
      background: #fef2f2; border-left: 4px solid var(--error); color: var(--error);
      padding: 10px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; margin-bottom: 16px;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="logo-img" onerror="this.style.display='none'">
    <div class="login-title">PT. CHAROEN POKHPAND INDONESIA - JOMBANG PLANT</div>
    <div class="subtitle">Form Input Hasil Produksi Fresh</div>

    @if ($errors->any())
      <div class="error-box">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('produksifresh.login.attempt') }}">
      @csrf
      <div class="form-group">
        <label>Tipe Otorisasi</label>
        <select name="tipe_input" required>
          <option value="main" @selected(old('tipe_input') === 'main')>Main Product Fresh</option>
          <option value="byproduct" @selected(old('tipe_input') === 'byproduct')>By Product</option>
        </select>
      </div>

      <div class="form-group">
        <label>ID Pengguna</label>
        <input type="text" name="employee_code" value="{{ old('employee_code') }}" style="text-transform:uppercase" placeholder="Contoh: APP01" required autofocus>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan Password" required>
      </div>

      <button class="btn" type="submit">MASUK</button>
    </form>
    <a href="{{ route('dashboard') }}" class="back-link">← Kembali ke Dashboard Produksi</a>
  </div>
</body>
</html>
