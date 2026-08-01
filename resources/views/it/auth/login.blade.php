<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IT / Riwayat Log Aktivitas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; background-color: #0f172a; } </style>
</head>
<body class="text-gray-800 text-sm h-screen">

  <div class="flex justify-center items-center h-full px-4">
    <div class="bg-white p-8 rounded-xl shadow-md border w-full max-w-sm">
      <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-slate-700"><i class="fas fa-server mr-2"></i>IT Panel</h2>
        <p class="text-gray-500 text-xs mt-1">Riwayat Log Aktivitas Sistem</p>
      </div>

      <form method="POST" action="{{ route('it.login.attempt') }}">
        @csrf

        <div class="mb-4">
          <label class="block text-xs font-bold text-gray-500 mb-1.5">User ID</label>
          <input
            type="text"
            name="employee_code"
            value="{{ old('employee_code') }}"
            placeholder="Masukkan ID"
            class="w-full border rounded-xl p-3 uppercase focus:ring-2 focus:ring-slate-300 outline-none"
            autofocus
          >
        </div>
        <div class="mb-6">
          <label class="block text-xs font-bold text-gray-500 mb-1.5">Password</label>
          <input type="password" name="password" placeholder="Masukkan Password" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-slate-300 outline-none">
        </div>

        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl transition shadow-md">Login</button>

        @error('employee_code')
          <div class="text-red-500 text-xs font-bold mt-3 text-center">{{ $message }}</div>
        @enderror
      </form>

        <div class="text-center d-flex justify-content-center gap-3">
            <a href="{{ route('dashboard') }}" class="back-link">← Dashboard Produksi</a>
            <a href="{{ route('warehouse.dashboard') }}" class="back-link">← Dashboard Warehouse</a>
        </div>
    </div>
  </div>

</body>
</html>
