<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Report Harian Bahan Baku LB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; } </style>
</head>
<body class="text-gray-800 text-sm h-screen">

  <div class="flex justify-center items-center h-full px-4">
    <div class="bg-white p-8 rounded-xl shadow-md border w-full max-w-sm">
      <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-blue-600"><i class="fas fa-truck-loading mr-2"></i>Sistem Logistik LB</h2>
        <p class="text-gray-500 text-xs mt-1">Report Harian Bahan Baku Live Birds</p>
      </div>

      <form method="POST" action="{{ route('lbreport.login.attempt') }}">
        @csrf

        <div class="mb-4">
          <label class="block text-xs font-bold text-gray-500 mb-1.5">User ID</label>
          <input
            type="text"
            name="employee_code"
            value="{{ old('employee_code') }}"
            placeholder="Contoh: APP01 / LGS01 / TLB01"
            class="w-full border rounded-xl p-3 uppercase focus:ring-2 focus:ring-blue-200 outline-none"
            autofocus
          >
        </div>
        <div class="mb-6">
          <label class="block text-xs font-bold text-gray-500 mb-1.5">Password</label>
          <input type="password" name="password" placeholder="Masukkan Password" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200 outline-none">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-md">Login</button>

        @error('employee_code')
          <div class="text-red-500 text-xs font-bold mt-3 text-center">{{ $message }}</div>
        @enderror
      </form>

      <div class="text-center mt-4">
        <a href="{{ route('lbreport.dashboard') }}" class="text-xs text-gray-400 hover:text-blue-600 transition">← Lihat Dashboard tanpa login</a>
      </div>
      <div class="text-center mt-2">
        <a href="{{ route('dashboard') }}" class="text-xs text-gray-400 hover:text-blue-600 transition">← Kembali ke Dashboard Utama</a>
      </div>
    </div>
  </div>

</body>
</html>
