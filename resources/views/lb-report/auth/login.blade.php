<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Report Harian Bahan Baku LB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
    font-family: 'Inter', system-ui, sans-serif;
    background:
        radial-gradient(rgba(37, 99, 235, .08) 1.2px, transparent 1.2px) 0 0 / 22px 22px,
        linear-gradient(160deg, #eff6ff 0%, #dbeafe 100%);
    background-attachment: fixed;
}

        /* Panel visual: gradien biru + pola titik (murni CSS) */
        .lb-panel {
            background:
                radial-gradient(rgba(255,255,255,.12) 1.2px, transparent 1.2px) 0 0 / 22px 22px,
                linear-gradient(150deg, #0c1f4a 0%, #1d4ed8 65%, #3b82f6 130%);
        }

        /* Satu-satunya animasi halaman: kartu fade-in saat dibuka */
        .lb-card { animation: lb-fade .5s ease-out both; }
        @keyframes lb-fade {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) { .lb-card { animation: none; } }
    </style>
</head>
<body class="text-gray-800 text-sm min-h-screen">

  <div class="flex min-h-screen items-center justify-center px-4 py-6">
    <div class="lb-card flex w-full max-w-4xl overflow-hidden rounded-2xl border bg-white shadow-xl">

      {{-- ===== Kiri: Form ===== --}}
      <div class="w-full p-7 sm:p-10 md:w-1/2">
        <div class="mb-7">
          <h2 class="text-2xl font-bold text-blue-600">
            <i class="fas fa-truck-loading mr-2"></i>Sistem Logistik LB
          </h2>
          <p class="mt-1 text-xs text-gray-500">Report Harian Bahan Baku Live Birds</p>
        </div>

        <form method="POST" action="{{ route('lbreport.login.attempt') }}" id="loginForm">
          @csrf

          <div class="mb-4">
            <label class="mb-1.5 block text-xs font-bold text-gray-500">User ID</label>
            <input
              type="text"
              name="employee_code"
              value="{{ old('employee_code') }}"
              placeholder="Contoh: APP01 / LGS01 / TLB01"
              class="w-full rounded-xl border p-3 uppercase outline-none focus:ring-2 focus:ring-blue-200"
              autocapitalize="characters"
              autocomplete="username"
              autocorrect="off"
              spellcheck="false"
              autofocus
            >
          </div>

          <div class="mb-6">
            <label class="mb-1.5 block text-xs font-bold text-gray-500">Password</label>
            <input
              type="password"
              name="password"
              placeholder="Masukkan Password"
              autocomplete="current-password"
              class="w-full rounded-xl border p-3 outline-none focus:ring-2 focus:ring-blue-200"
            >
          </div>

          <button
            type="submit"
            id="loginBtn"
            class="w-full rounded-xl bg-blue-600 py-3 font-bold text-white shadow-md transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-70"
          >
            <span id="loginBtnText">Login</span>
          </button>

          @error('employee_code')
            <div class="mt-3 text-center text-xs font-bold text-red-500">{{ $message }}</div>
          @enderror
        </form>

        {{-- Peringatan tepat di bawah tombol Login --}}
        <x-logout-warning />

        {{-- Link sekunder: satu grup --}}
        <div class="mt-5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-gray-400">
          <a href="{{ route('lbreport.dashboard') }}" class="transition hover:text-blue-600">Lihat Dashboard tanpa login</a>
          <span aria-hidden="true">&bull;</span>
          <a href="{{ route('dashboard') }}" class="transition hover:text-blue-600">Dashboard Utama</a>
        </div>
      </div>

      {{-- ===== Kanan: Panel visual (hanya tablet/desktop) ===== --}}
      <div class="lb-panel hidden flex-col items-center justify-center p-10 text-center md:flex md:w-1/2">
        <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/25">
          <i class="fas fa-truck-loading text-4xl text-blue-100"></i>
        </div>
        <h3 class="text-2xl font-bold leading-tight text-white">Report Harian<br>Bahan Baku</h3>
        <p class="mt-3 max-w-[16rem] text-xs leading-relaxed text-blue-100/80">
          Catat dan pantau kedatangan serta pengiriman bahan baku Live Birds setiap hari.
        </p>
        <div class="mt-6 rounded-full border border-blue-200/40 px-3 py-1 text-[0.68rem] tracking-widest text-blue-100">
          LIVE BIRDS LOGISTIK
        </div>
      </div>

    </div>
  </div>

  <script>
    // Cegah submit ganda & beri umpan balik saat sinyal lambat
    (function () {
      var form = document.getElementById('loginForm');
      var btn  = document.getElementById('loginBtn');
      var txt  = document.getElementById('loginBtnText');

      form.addEventListener('submit', function () {
        btn.disabled = true;
        txt.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
      });

      // Reset tombol bila user kembali ke halaman ini lewat tombol Back
      window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
          btn.disabled = false;
          txt.textContent = 'Login';
        }
      });
    })();
  </script>

</body>
</html>