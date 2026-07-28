<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Riwayat Log Aktivitas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .badge-module { padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; }
    .badge-action { padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 text-sm">

@php $user = auth()->guard('tally')->user(); @endphp

<header class="bg-slate-800 text-white sticky top-0 z-40 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap justify-between items-center gap-3">
    <div class="flex items-center gap-3">
      {{-- <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-xs font-bold transition">← Dashboard Utama</a> --}}
      <h1 class="text-lg font-bold tracking-tight"><i class="fas fa-server mr-2"></i>Riwayat Log Aktivitas</h1>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-xs bg-white/10 px-3 py-1.5 rounded-full font-semibold">{{ $user->name }}</span>
      <form id="logoutForm" method="POST" action="{{ route('it.logout') }}">
        @csrf
        <button type="button" onclick="confirmLogout()" class="bg-red-500/20 hover:bg-red-500/30 text-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition">
          <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </button>
      </form>
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-6">

  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5">
    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Filter</h2>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
      <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Modul</label>
        <select id="filterModule" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:border-slate-500">
          <option value="">Semua Modul</option>
        </select>
      </div>
      <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Aksi</label>
        <select id="filterAction" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:border-slate-500">
          <option value="">Semua Aksi</option>
        </select>
      </div>
      <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Employee Code</label>
        <input type="text" id="filterEmployeeCode" placeholder="Contoh: TLY01" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-xs uppercase focus:outline-none focus:border-slate-500">
      </div>
      <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Dari Tanggal</label>
        <input type="date" id="filterTanggalDari" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:border-slate-500">
      </div>
      <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Sampai Tanggal</label>
        <input type="date" id="filterTanggalSampai" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:border-slate-500">
      </div>
    </div>
    <div class="flex gap-2 mt-3">
      <button onclick="loadLogs()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-xs font-bold transition"><i class="fas fa-filter mr-1"></i> Terapkan Filter</button>
      <button onclick="resetFilter()" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-xs font-bold transition">Reset</button>
      <button onclick="loadLogs()" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-xs font-bold transition ml-auto"><i class="fas fa-sync-alt mr-1"></i> Refresh</button>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
      <h2 class="font-bold text-gray-700 text-sm">Log Aktivitas <span id="logCount" class="text-gray-400 font-normal"></span></h2>
      <span class="text-[10px] text-gray-400">Maks. 500 baris terbaru</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-100 text-gray-600 uppercase font-semibold border-b border-gray-200">
          <tr>
            <th class="p-2.5">Waktu</th>
            <th class="p-2.5">Employee Code</th>
            <th class="p-2.5">Nama</th>
            <th class="p-2.5">Modul</th>
            <th class="p-2.5">Aksi</th>
            <th class="p-2.5">Deskripsi</th>
            <th class="p-2.5">IP Address</th>
          </tr>
        </thead>
        <tbody id="logTableBody" class="divide-y divide-gray-100">
          <tr><td colspan="7" class="text-center p-6 text-gray-400">Memuat data...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  const moduleColors = {
    tally_pro: 'bg-emerald-100 text-emerald-700',
    serah_terima: 'bg-blue-100 text-blue-700',
    uniformity: 'bg-purple-100 text-purple-700',
    report_lb: 'bg-amber-100 text-amber-700',
    produksi_dashboard: 'bg-teal-100 text-teal-700',
    auth: 'bg-gray-100 text-gray-700',
  };
  const actionColors = {
    login: 'bg-emerald-50 text-emerald-600',
    logout: 'bg-gray-50 text-gray-500',
    create: 'bg-blue-50 text-blue-600',
    update: 'bg-amber-50 text-amber-600',
    delete: 'bg-red-50 text-red-600',
    approve: 'bg-teal-50 text-teal-600',
    verify: 'bg-indigo-50 text-indigo-600',
    sign: 'bg-purple-50 text-purple-600',
  };

  async function apiFetch(url, options = {}) {
    const response = await fetch(url, {
      ...options,
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, ...(options.headers || {}) },
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan pada server.');
    return data;
  }

  function confirmLogout() {
    Swal.fire({
      title: '<span style="font-size:22px;font-weight:700;">Keluar dari Sistem?</span>',
      html: `<div style="font-size:14px;color:#6b7280;margin-top:6px;">Anda akan keluar dari <b>Panel IT</b>.</div>`,
      icon: 'question', width: 400, showCancelButton: true, reverseButtons: true,
      confirmButtonText: 'Ya, Keluar', cancelButtonText: 'Batal',
      confirmButtonColor: '#dc3545', cancelButtonColor: '#64748b',
    }).then((result) => { if (result.isConfirmed) document.getElementById('logoutForm').submit(); });
  }

  window.addEventListener('DOMContentLoaded', async () => {
    await loadFilterOptions();
    loadLogs();
  });

  async function loadFilterOptions() {
    try {
      const res = await apiFetch('{{ route('it.filter-options') }}');
      const moduleSelect = document.getElementById('filterModule');
      res.modules.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m; opt.innerText = m;
        moduleSelect.appendChild(opt);
      });
      const actionSelect = document.getElementById('filterAction');
      res.actions.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a; opt.innerText = a;
        actionSelect.appendChild(opt);
      });
    } catch (err) { /* diamkan, dropdown tetap "Semua" */ }
  }

  function resetFilter() {
    document.getElementById('filterModule').value = '';
    document.getElementById('filterAction').value = '';
    document.getElementById('filterEmployeeCode').value = '';
    document.getElementById('filterTanggalDari').value = '';
    document.getElementById('filterTanggalSampai').value = '';
    loadLogs();
  }

  async function loadLogs() {
    const tbody = document.getElementById('logTableBody');
    tbody.innerHTML = `<tr><td colspan="7" class="text-center p-6 text-gray-400">Memuat data...</td></tr>`;

    const params = new URLSearchParams();
    const module = document.getElementById('filterModule').value;
    const action = document.getElementById('filterAction').value;
    const employeeCode = document.getElementById('filterEmployeeCode').value.trim();
    const tanggalDari = document.getElementById('filterTanggalDari').value;
    const tanggalSampai = document.getElementById('filterTanggalSampai').value;

    if (module) params.set('module', module);
    if (action) params.set('action', action);
    if (employeeCode) params.set('employee_code', employeeCode);
    if (tanggalDari) params.set('tanggal_dari', tanggalDari);
    if (tanggalSampai) params.set('tanggal_sampai', tanggalSampai);

    try {
      const logs = await apiFetch(`{{ route('it.data') }}?${params.toString()}`);
      document.getElementById('logCount').innerText = `(${logs.length} baris)`;

      if (logs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center p-6 text-gray-400">Tidak ada log yang cocok dengan filter ini.</td></tr>`;
        return;
      }

      tbody.innerHTML = '';
      logs.forEach(log => {
        const moduleClass = moduleColors[log.module] || 'bg-gray-100 text-gray-700';
        const actionClass = actionColors[log.action] || 'bg-gray-50 text-gray-600';
        tbody.innerHTML += `
          <tr class="hover:bg-gray-50 transition">
            <td class="p-2.5 font-mono text-[11px] text-gray-500 whitespace-nowrap">${log.waktu}</td>
            <td class="p-2.5 font-bold text-gray-800">${log.employeeCode}</td>
            <td class="p-2.5">${log.userName}</td>
            <td class="p-2.5"><span class="badge-module ${moduleClass}">${log.module}</span></td>
            <td class="p-2.5"><span class="badge-action ${actionClass}">${log.action}</span></td>
            <td class="p-2.5 text-gray-600">${log.description}</td>
            <td class="p-2.5 font-mono text-[11px] text-gray-400">${log.ipAddress}</td>
          </tr>
        `;
      });
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center p-6 text-red-500 font-bold">Gagal memuat data: ${err.message}</td></tr>`;
    }
  }
</script>

</body>
</html>
