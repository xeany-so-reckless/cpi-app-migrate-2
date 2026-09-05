<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Uniformity Apps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
      body { font-family: 'Inter', sans-serif; }
      .tab-active { border-color: #3b82f6; color: #3b82f6; border-bottom-width: 2px; }
    </style>
  </head>
  <body class="bg-gray-50 text-gray-800 antialiased">

    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
          <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 px-3 py-2 rounded-lg text-xs font-bold transition">
              ← Main Dashboard
            </a>
            <div class="bg-white border p-2 rounded-lg w-14 h-14 flex items-center justify-center">
              <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-full h-auto object-contain">
            </div>
            <span class="font-semibold text-lg tracking-wide text-gray-900">Uniformity Apps</span>
          </div>

        <nav class="flex space-x-8 h-full">
          <button id="btn-dashboard" onclick="switchTab('dashboard')" class="tab-active px-1 h-full flex items-center font-medium text-sm transition-colors">Dashboard</button>
          <button id="btn-input" onclick="switchTab('input')" class="px-1 h-full flex items-center font-medium text-sm text-gray-500 hover:text-gray-700 transition-colors">Input Data</button>
          <a id="link-export" href="{{ route('uniformity.export') }}" class="px-1 h-full flex items-center font-medium text-sm text-gray-500 hover:text-blue-600 transition-colors">Menu Rekap & PDF</a>
        </nav>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">

      <section id="sec-dashboard" class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <div class="flex items-center space-x-3">
              <h1 class="text-2xl font-bold text-gray-900">Uniformity Monitoring Dashboard</h1>
              <div id="notif-update-hari-ini" class="hidden flex items-center space-x-1.5 bg-green-50 text-green-700 px-2.5 py-1 rounded-full text-xs font-semibold border border-green-200 animate-pulse">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                <span>Ada Update Hari Ini</span>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">Data dikelompokkan otomatis berdasarkan tanggal penerimaan</p>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 items-end sm:items-center">
            <div class="flex items-center space-x-2">
              <label class="text-xs font-bold text-gray-500 uppercase">Periode Bulan:</label>
              <input type="month" id="filter-bulan" onchange="loadDashboard()" class="border border-gray-300 rounded-lg p-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50">
            </div>
             <!-- TAMBAHAN BARU -->
  <div class="flex items-center space-x-2">
    <label class="text-xs font-bold text-gray-500 uppercase">Tanggal Spesifik:</label>
    <input type="date" id="filter-tanggal-export" class="border border-gray-300 rounded-lg p-2 text-sm bg-gray-50">
  </div>
  <button onclick="exportExcelRaw()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
    <i class="fa-solid fa-file-excel"></i> Export Excel (Raw)
  </button>
  <!-- END TAMBAHAN -->
            <button onclick="loadDashboard()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">Refresh Data</button>
          </div>
        </div>

        <div id="dashboard-boxes-container" class="space-y-8">
          <div class="text-center py-8 text-gray-400 bg-white shadow rounded-xl border border-gray-200">Memuat data dashboard...</div>
        </div>
      </section>

      <section id="sec-input" class="hidden space-y-6 relative min-h-[600px]">
        <div id="input-lock-overlay" class="absolute inset-0 bg-gray-100/60 backdrop-blur-md z-40 flex flex-col items-center justify-center rounded-xl p-6 transition-all duration-300">
          <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200 max-w-md w-full text-center space-y-5">
            <div class="mx-auto w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-3xl shadow-inner">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900">Akses Lembar Input Terkunci</h2>
              <p class="text-sm text-gray-500 mt-1">Silakan masukkan kode otorisasi supervisor/admin untuk membuka form input data uniformity.</p>
            </div>
            <div class="space-y-3">
              <input type="password" id="app-password" placeholder="Masukkan Password Otorisasi" class="w-full border rounded-xl px-4 py-3 text-center text-lg tracking-widest outline-none focus:ring-2 focus:ring-blue-500 transition">
              <button onclick="verifikasiOtorisasi()" id="btn-otorisasi" class="w-full bg-gray-900 hover:bg-gray-800 text-white py-3 rounded-xl font-semibold shadow transition-all duration-150">Buka Akses Form</button>
            </div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Form Input Penerimaan & Uniformity</h1>
            <span id="edit-indicator" class="hidden inline-block mt-1 bg-yellow-100 text-yellow-800 border border-yellow-300 text-xs font-bold px-3 py-1 rounded-lg animate-pulse">Mode Edit Data Rit</span>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
          <div class="lg:col-span-2 bg-white p-6 shadow rounded-xl border border-gray-200 space-y-6">
            <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">1. Data Truk / Per Rit</h2>
            <input type="hidden" id="edit-index" value="-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Tanggal Sampling</label>
                <input type="date" id="input-tanggal" onkeydown="pindahFormDenganEnter(event, 'input-noPo')" class="w-full border rounded-lg p-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">No PO</label>
                <select id="input-noPo" onchange="toggleInputRit()"
                  class="w-full border rounded-lg p-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                  <option value="">-- Pilih No PO --</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Nomor Rit</label>
                <div class="flex gap-2">
                  <input type="text" id="input-noRit" placeholder="Contoh: RIT-01" disabled
                    onkeydown="if(event.key==='Enter'){ event.preventDefault(); cariDataDTA(); }"
                    class="w-full border rounded-lg p-2.5 bg-gray-100 text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                  <button type="button" onclick="cariDataDTA()" id="btn-cari-dta" disabled
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-4 rounded-lg text-xs font-bold whitespace-nowrap">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                  </button>
                </div>
                <p id="hint-pilih-po" class="text-[11px] text-gray-400 mt-1">Pilih No PO terlebih dahulu.</p>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Asal Kandang</label>
                <input type="text" id="input-asalKandang" onkeydown="pindahFormDenganEnter(event, 'input-sizeMin')" placeholder="Nama Kandang / Farm" class="w-full border rounded-lg p-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Size Min (Kg)</label>
                  <input type="text" inputmode="numeric" id="input-sizeMin" oninput="filterHanyaAngka(this)" onblur="formatOtomatisDuaDesimal(this)" onkeydown="pindahFormDenganEnter(event, 'input-sizeMax')" placeholder="Min" class="w-full border rounded-lg p-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                  <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Size Max (Kg)</label>
                  <input type="text" inputmode="numeric" id="input-sizeMax" oninput="filterHanyaAngka(this)" onblur="formatOtomatisDuaDesimal(this)" onkeydown="pindahFormDenganEnter(event, 'input-kgDta')" placeholder="Max" class="w-full border rounded-lg p-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Kg DTA</label>
                <input type="text" inputmode="numeric" id="input-kgDta" oninput="filterHanyaAngka(this); hitungAbw();" onblur="formatOtomatisSatuDesimal(this)" onkeydown="pindahFormDenganEnter(event, 'input-ekorDta')" placeholder="Total Berat Datang" class="w-full border rounded-lg p-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Ekor DTA</label>
                <input type="text" inputmode="numeric" id="input-ekorDta" oninput="filterHanyaAngka(this); hitungAbw();" onkeydown="pindahFormKeSamplingPertama(event)" placeholder="Total Ekor Datang" class="w-full border rounded-lg p-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
              </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 grid grid-cols-2 md:grid-cols-5 gap-4 text-center mt-4">
              <div><span class="block text-[10px] uppercase font-bold text-gray-400">Rerata ABW</span><span id="calc-rerataAbw" class="text-lg font-bold text-gray-800">-</span></div>
              <div><span class="block text-[10px] uppercase font-bold text-gray-400">Total Sample</span><span id="calc-totalSample" class="text-lg font-bold text-gray-800">0</span></div>
              <div><span class="block text-[10px] uppercase font-bold text-red-400">Undersize</span><span id="calc-undersize" class="text-lg font-bold text-red-500">0%</span></div>
              <div><span class="block text-[10px] uppercase font-bold text-green-500">Size Masuk</span><span id="calc-sizeMasuk" class="text-lg font-bold text-green-600">0%</span></div>
              <div><span class="block text-[10px] uppercase font-bold text-yellow-500">Oversize</span><span id="calc-oversize" class="text-lg font-bold text-yellow-600">0%</span></div>
            </div>

            <div class="flex space-x-3 pt-2">
              <button id="btn-batal-edit" onclick="resetFormInput()" class="hidden w-1/3 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-3 rounded-lg font-semibold transition">Batal</button>
              <button id="btn-add-temporary" onclick="tambahKeTemporer()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition cursor-pointer">Tambahkan ke Antrean Sementara</button>
            </div>
          </div>

          <div class="bg-white p-6 shadow rounded-xl border border-gray-200 flex flex-col h-[550px]">
            <div class="border-b pb-2 mb-3 flex justify-between items-center">
              <h2 class="text-lg font-semibold text-gray-800">2. Uniformity (Max 200)</h2>
            </div>
            <div class="flex-1 overflow-y-auto pr-1 space-y-2" id="sample-inputs-container"></div>
          </div>
        </div>

        <div class="bg-white shadow rounded-xl border border-gray-200 overflow-hidden mt-8">
          <div class="bg-gray-800 px-5 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-white">
            <div>
              <h3 class="font-bold text-base tracking-wide">Antrean Data Sementara</h3>
            </div>
            <button id="btn-submit-permanent" onclick="kirimPermanenKeSpreadsheet()" class="w-full sm:w-auto bg-gray-600 text-white px-6 py-2.5 rounded-lg font-bold shadow transition flex items-center justify-center" disabled>
              <span id="btn-default-label">Kirim Semua ke Database (<span id="queue-count">0</span>)</span>
              <span id="btn-loading-label" class="hidden">⏳ Memproses...</span>
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs font-medium text-gray-500 uppercase tracking-wider">
              <thead class="bg-gray-100 text-gray-700">
                <tr>
                  <th class="px-4 py-3 text-center">Aksi</th>
                  <th class="px-4 py-3 text-center">Tanggal</th>
                  <th class="px-4 py-3 text-center">No Rit</th>
                  <th class="px-4 py-3 text-left">Asal Kandang</th>
                  <th class="px-4 py-3 text-center">Sample</th>
                  <th class="px-4 py-3 text-center text-red-500">Under</th>
                  <th class="px-4 py-3 text-center text-green-600">Masuk</th>
                  <th class="px-4 py-3 text-center text-yellow-600">Over</th>
                </tr>
              </thead>
              <tbody id="temporer-table-body" class="bg-white divide-y divide-gray-200 text-gray-600 normal-case font-normal">
                <tr><td colspan="8" class="text-center py-8 text-gray-400">Belum ada data dalam antrean.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </main>

    <script>
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      let daftarDataTemporer = [];
      let isAuthorized = false;

      async function apiFetch(url, options = {}) {
        const response = await fetch(url, {
          ...options,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            ...(options.headers || {}),
          },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          throw new Error(data.message || 'Terjadi kesalahan pada server.');
        }
        return data;
      }

      window.addEventListener('DOMContentLoaded', () => {
        const today = new Date();
        const currentMonth = today.toISOString().substring(0, 7);
        document.getElementById('filter-bulan').value = currentMonth;
        document.getElementById('input-tanggal').value = today.toISOString().substring(0, 10);

        generateSampleInputs();
        loadDashboard();
        loadDaftarPO();

        document.getElementById('app-password').addEventListener('keydown', function(e) {
          if (e.key === 'Enter') { e.preventDefault(); verifikasiOtorisasi(); }
        });
      });

      function switchTab(tabName) {
        document.getElementById('sec-dashboard').classList.add('hidden');
        document.getElementById('sec-input').classList.add('hidden');
        document.getElementById('btn-dashboard').classList.remove('tab-active', 'text-blue-600');
        document.getElementById('btn-input').classList.remove('tab-active', 'text-blue-600');

        document.getElementById('sec-' + tabName).classList.remove('hidden');
        document.getElementById('btn-' + tabName).classList.add('tab-active', 'text-blue-600');
        if (tabName === 'dashboard') loadDashboard();
      }

      async function verifikasiOtorisasi() {
        const inputPass = document.getElementById('app-password');
        const overlay = document.getElementById('input-lock-overlay');

        try {
          const res = await apiFetch('{{ route('uniformity.verify-pin') }}', {
            method: 'POST',
            body: JSON.stringify({ pin: inputPass.value }),
          });

          if (res.valid) {
            isAuthorized = true;
            overlay.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => { overlay.classList.add('hidden'); }, 300);
          } else {
            alert("Kode otorisasi salah! Akses ditolak.");
            inputPass.value = ''; inputPass.focus();
          }
        } catch (err) {
          alert("Gagal menghubungi server: " + err.message);
        }
      }

      // -- FUNGSI MATH & DATA INPUT --
      function generateSampleInputs() {
        const container = document.getElementById('sample-inputs-container');
        container.innerHTML = '';
        for (let i = 1; i <= 200; i++) {
          const div = document.createElement('div');
          div.className = 'flex items-center space-x-2 bg-gray-50 p-1.5 rounded border border-gray-100';
          div.innerHTML = `
            <span class="text-[11px] font-mono font-bold text-gray-400 w-8 text-right">#${i}</span>
            <input type="text" inputmode="numeric" data-index="${i}"
              oninput="filterHanyaAngka(this)" onblur="prosesOtomatisDesimal(this)"
              onkeydown="pindahInputDenganEnter(event, ${i})" placeholder="0.00"
              class="sample-input w-full border text-xs p-1.5 rounded bg-white focus:ring-1 focus:ring-blue-500 outline-none">
          `;
          container.appendChild(div);
        }
      }

      function filterHanyaAngka(input) { input.value = input.value.replace(/[^0-9.]/g, ''); }
      function formatOtomatisDuaDesimal(input) {
        let num = parseFloat(input.value);
        if (!isNaN(num) && num > 0) { input.value = num.toFixed(2); hitungKalkulasiUniformity(); hitungAbw(); }
      }
      function formatOtomatisSatuDesimal(input) {
        let num = parseFloat(input.value);
        if (!isNaN(num) && num > 0) { input.value = num.toFixed(1); hitungAbw(); }
      }
      function prosesOtomatisDesimal(input) {
        let rawVal = input.value.trim();
        if (rawVal === '') { hitungKalkulasiUniformity(); return; }
        if (rawVal.includes('.')) {
          let num = parseFloat(rawVal); input.value = !isNaN(num) && num > 0 ? num.toFixed(2) : '';
        } else {
          let num = parseInt(rawVal, 10); if (!isNaN(num) && num > 0) { input.value = (num / 100).toFixed(2); } else { input.value = ''; }
        }
        hitungKalkulasiUniformity();
      }

      function pindahFormDenganEnter(event, nextId) {
        if (event.key === 'Enter') { event.preventDefault(); document.getElementById(nextId).focus(); }
      }
      function pindahFormKeSamplingPertama(event) {
        if (event.key === 'Enter') { event.preventDefault(); document.querySelector('.sample-input[data-index="1"]').focus(); }
      }
      function pindahInputDenganEnter(event, currentIndex) {
        if (event.key === 'Enter') {
          event.preventDefault(); prosesOtomatisDesimal(event.target);
          const nextInput = document.querySelector(`.sample-input[data-index="${currentIndex + 1}"]`);
          if (nextInput) { nextInput.focus(); nextInput.select(); }
          else { document.getElementById('btn-add-temporary').focus(); }
        }
      }

      // -- FUNGSI PO / RIT (LOOKUP DTA) --

      async function loadDaftarPO() {
        const select = document.getElementById('input-noPo');
        try {
          const list = await apiFetch('{{ route('uniformity.po-list') }}');
          select.innerHTML = '<option value="">-- Pilih No PO --</option>' +
            list.map(po => `<option value="${po}">${po}</option>`).join('');
        } catch (err) {
          console.error('Gagal ambil daftar PO:', err);
        }
      }

      function toggleInputRit() {
        const noPo = document.getElementById('input-noPo').value;
        const noRitInput = document.getElementById('input-noRit');
        const btnCari = document.getElementById('btn-cari-dta');
        const hint = document.getElementById('hint-pilih-po');

        const aktif = !!noPo;
        noRitInput.disabled = !aktif;
        btnCari.disabled = !aktif;
        noRitInput.classList.toggle('bg-gray-100', !aktif);
        noRitInput.classList.toggle('text-gray-400', !aktif);
        noRitInput.classList.toggle('bg-gray-50', aktif);
        noRitInput.classList.toggle('text-gray-800', aktif);
        hint.classList.toggle('hidden', aktif);

        if (aktif) { noRitInput.value = ''; noRitInput.focus(); }
      }

      async function cariDataDTA() {
        const noPo = document.getElementById('input-noPo').value;
        const noRitEl = document.getElementById('input-noRit');
        let noRit = noRitEl.value.trim();

        if (!noPo) return alert('Pilih No PO terlebih dahulu!');
        if (!noRit) return alert('Ketik Nomor Rit terlebih dahulu!');

        // ubah angka jadi format RIT-xx (konsisten dengan menu Hanging di workspace LB)
        if (/^\d+$/.test(noRit)) {
          noRit = 'RIT-' + noRit.padStart(2, '0');
          noRitEl.value = noRit;
        }

        const btn = document.getElementById('btn-cari-dta');
        const btnTextAsli = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
          const res = await apiFetch(`{{ route('uniformity.dta-by-rit') }}?no_po=${encodeURIComponent(noPo)}&no_rit=${encodeURIComponent(noRit)}`);

          document.getElementById('input-tanggal').value = res.tanggal || '';
          document.getElementById('input-asalKandang').value = res.farm || '';

          if (res.size) {
            const parts = res.size.split('-').map(s => s.trim());
            document.getElementById('input-sizeMin').value = parts[0] ? parseFloat(parts[0]).toFixed(2) : '';
            document.getElementById('input-sizeMax').value = parts[1] ? parseFloat(parts[1]).toFixed(2) : '';
          }

          document.getElementById('input-kgDta').value = res.kg_dta ? parseFloat(res.kg_dta).toFixed(1) : '';
          document.getElementById('input-ekorDta').value = res.ekor_dta || '';

          hitungAbw();
          hitungKalkulasiUniformity();

          const sampleFirst = document.querySelector('.sample-input[data-index="1"]');
          if (sampleFirst) sampleFirst.focus();

        } catch (err) {
          alert('Gagal ambil data: ' + err.message);
        } finally {
          btn.disabled = false;
          btn.innerHTML = btnTextAsli;
        }
      }

      function hitungAbw() {
        const kg = parseFloat(document.getElementById('input-kgDta').value) || 0;
        const ekor = parseInt(document.getElementById('input-ekorDta').value) || 0;
        document.getElementById('calc-rerataAbw').innerText = (kg > 0 && ekor > 0) ? (kg / ekor).toFixed(3) : '-';
      }

      function hitungKalkulasiUniformity() {
        const sizeMin = parseFloat(document.getElementById('input-sizeMin').value) || 0;
        const sizeMax = parseFloat(document.getElementById('input-sizeMax').value) || 0;
        const inputs = document.querySelectorAll('.sample-input');

        let totalSample = 0, underCount = 0, masukCount = 0, overCount = 0;
        inputs.forEach(input => {
          const val = parseFloat(input.value);
          if (!isNaN(val) && val > 0) {
            totalSample++;
            if (sizeMin > 0 && sizeMax > 0) {
              if (val < (sizeMin - 0.0001)) underCount++;
              else if (val >= (sizeMin - 0.0001) && val <= (sizeMax + 0.0001)) masukCount++;
              else if (val > (sizeMax + 0.0001)) overCount++;
            }
          }
        });
        document.getElementById('calc-totalSample').innerText = totalSample;

        if (totalSample > 0 && sizeMin > 0 && sizeMax > 0) {
          document.getElementById('calc-undersize').innerText = ((underCount / totalSample) * 100).toFixed(1) + '%';
          document.getElementById('calc-sizeMasuk').innerText = ((masukCount / totalSample) * 100).toFixed(1) + '%';
          document.getElementById('calc-oversize').innerText = ((overCount / totalSample) * 100).toFixed(1) + '%';
        } else {
          document.getElementById('calc-undersize').innerText = '0%';
          document.getElementById('calc-sizeMasuk').innerText = '0%';
          document.getElementById('calc-oversize').innerText = '0%';
        }
      }

      function tambahKeTemporer() {
        if (!isAuthorized) return;
        const tanggal = document.getElementById('input-tanggal').value;
        const noRit = document.getElementById('input-noRit').value.trim();
        const asalKandang = document.getElementById('input-asalKandang').value.trim();
        const sizeMin = parseFloat(document.getElementById('input-sizeMin').value) || 0;
        const sizeMax = parseFloat(document.getElementById('input-sizeMax').value) || 0;
        const kgDta = parseFloat(document.getElementById('input-kgDta').value) || 0;
        const ekorDta = parseInt(document.getElementById('input-ekorDta').value) || 0;
        const rerataAbw = parseFloat(document.getElementById('calc-rerataAbw').innerText) || 0;
        const jumlahSample = parseInt(document.getElementById('calc-totalSample').innerText) || 0;

        if (!tanggal || !noRit || !asalKandang || jumlahSample === 0) {
          alert("Lengkapi Data Rit dan isi minimal satu data berat sampling!"); return;
        }

        const dataSample = [];
        document.querySelectorAll('.sample-input').forEach(input => {
          const val = parseFloat(input.value);
          if (!isNaN(val) && val > 0) dataSample.push(val);
        });

        const dataRitObj = {
          dataRit: { tanggal, noRit, asalKandang, sizeMin, sizeMax, kgDta, ekorDta, rerataAbw, jumlahSample,
                     undersize: document.getElementById('calc-undersize').innerText,
                     sizeMasuk: document.getElementById('calc-sizeMasuk').innerText,
                     oversize: document.getElementById('calc-oversize').innerText },
          dataSample: dataSample
        };

        const editIndex = parseInt(document.getElementById('edit-index').value);
        if (editIndex > -1) { daftarDataTemporer[editIndex] = dataRitObj; } else { daftarDataTemporer.push(dataRitObj); }

        resetFormInput(); renderTabelTemporer();
      }

      function renderTabelTemporer() {
        const tbody = document.getElementById('temporer-table-body');
        const queueCount = document.getElementById('queue-count');
        const btnPermanent = document.getElementById('btn-submit-permanent');

        queueCount.innerText = daftarDataTemporer.length;
        if (daftarDataTemporer.length === 0) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-gray-400">Belum ada data dalam antrean.</td></tr>`;
          btnPermanent.disabled = true; btnPermanent.classList.remove('bg-green-600'); btnPermanent.classList.add('bg-gray-600', 'cursor-not-allowed'); return;
        }

        btnPermanent.disabled = false; btnPermanent.classList.remove('bg-gray-600', 'cursor-not-allowed'); btnPermanent.classList.add('bg-green-600', 'hover:bg-green-700');

        tbody.innerHTML = '';
        daftarDataTemporer.forEach((item, index) => {
          const rit = item.dataRit;
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="px-3 py-2 text-center">
              <button onclick="editDataTemporer(${index})" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs mr-1">Edit</button>
              <button onclick="hapusDataTemporer(${index})" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Hapus</button>
            </td>
            <td class="px-2 py-2 text-center font-mono text-[11px]">${rit.tanggal}</td>
            <td class="px-2 py-2 text-center font-semibold text-gray-900">${rit.noRit}</td>
            <td class="px-3 py-2 text-left font-medium max-w-[120px] truncate">${rit.asalKandang}</td>
            <td class="px-2 py-2 text-center font-bold">${rit.jumlahSample}</td>
            <td class="px-2 py-2 text-center text-red-500">${rit.undersize}</td>
            <td class="px-2 py-2 text-center text-green-600">${rit.sizeMasuk}</td>
            <td class="px-2 py-2 text-center text-yellow-600">${rit.oversize}</td>
          `;
          tbody.appendChild(tr);
        });
      }

      function hapusDataTemporer(index) {
        if (confirm("Hapus antrean ini?")) {
          daftarDataTemporer.splice(index, 1);
          renderTabelTemporer();
          // Jika item yang dihapus sedang dalam mode edit, batalkan mode edit
          const currentEditIndex = parseInt(document.getElementById('edit-index').value);
          if (currentEditIndex === index) {
            resetFormInput();
          } else if (currentEditIndex > index) {
            // Geser index edit karena array bergeser setelah splice
            document.getElementById('edit-index').value = currentEditIndex - 1;
          }
        }
      }

      // -- FUNGSI EDIT DATA TEMPORER --
      function editDataTemporer(index) {
        if (!isAuthorized) return;
        const item = daftarDataTemporer[index];
        if (!item) return;
        const rit = item.dataRit;

        // Tandai form sedang dalam mode edit untuk index ini
        document.getElementById('edit-index').value = index;

        // Isi ulang data rit ke form
        document.getElementById('input-tanggal').value = rit.tanggal;

        // No PO tidak disimpan di antrean sementara, jadi dikosongkan.
        // No Rit tetap diisi & field dibuat aktif supaya data lama tetap terlihat/terisi
        // tanpa memaksa user memilih ulang PO (kecuali mau cari ulang datanya).
        document.getElementById('input-noPo').value = '';
        const noRitInput = document.getElementById('input-noRit');
        noRitInput.value = rit.noRit;
        noRitInput.disabled = false;
        noRitInput.classList.remove('bg-gray-100', 'text-gray-400');
        noRitInput.classList.add('bg-gray-50', 'text-gray-800');
        document.getElementById('btn-cari-dta').disabled = false;
        document.getElementById('hint-pilih-po').classList.add('hidden');

        document.getElementById('input-asalKandang').value = rit.asalKandang;
        document.getElementById('input-sizeMin').value = rit.sizeMin > 0 ? rit.sizeMin.toFixed(2) : '';
        document.getElementById('input-sizeMax').value = rit.sizeMax > 0 ? rit.sizeMax.toFixed(2) : '';
        document.getElementById('input-kgDta').value = rit.kgDta > 0 ? rit.kgDta.toFixed(1) : '';
        document.getElementById('input-ekorDta').value = rit.ekorDta > 0 ? rit.ekorDta : '';

        // Bangun ulang 200 kolom sample lalu isi dengan data yang tersimpan
        generateSampleInputs();
        item.dataSample.forEach((val, i) => {
          const inputEl = document.querySelector(`.sample-input[data-index="${i + 1}"]`);
          if (inputEl) inputEl.value = val.toFixed(2);
        });

        // Hitung ulang ABW & uniformity berdasarkan data yang baru dimuat
        hitungAbw();
        hitungKalkulasiUniformity();

        // Tampilkan indikator mode edit & tombol batal
        document.getElementById('edit-indicator').classList.remove('hidden');
        document.getElementById('btn-batal-edit').classList.remove('hidden');

        // Bawa user ke bagian atas form supaya langsung terlihat
        document.getElementById('input-tanggal').scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      function resetFormInput() {
        document.getElementById('edit-index').value = "-1";

        document.getElementById('input-noPo').value = '';
        toggleInputRit(); // otomatis kosongkan & disable field No Rit lagi

        document.getElementById('input-asalKandang').value = '';
        document.getElementById('input-sizeMin').value = '';
        document.getElementById('input-sizeMax').value = '';
        document.getElementById('input-kgDta').value = '';
        document.getElementById('input-ekorDta').value = '';
        document.getElementById('calc-rerataAbw').innerText = '-';

        generateSampleInputs();
        hitungKalkulasiUniformity();
        document.getElementById('calc-totalSample').innerText = '0';

        // Sembunyikan kembali indikator mode edit & tombol batal
        document.getElementById('edit-indicator').classList.add('hidden');
        document.getElementById('btn-batal-edit').classList.add('hidden');
      }

      async function kirimPermanenKeSpreadsheet() {
        if (!isAuthorized || daftarDataTemporer.length === 0) return;

        if (!confirm(`Kirim total ${daftarDataTemporer.length} data Rit ke database?`)) return;

        const btn = document.getElementById('btn-submit-permanent');
        btn.disabled = true;

        let indexBerhasil = 0;
        document.getElementById('btn-default-label').classList.add('hidden');
        document.getElementById('btn-loading-label').classList.remove('hidden');

        for (let i = 0; i < daftarDataTemporer.length; i++) {
          document.getElementById('btn-loading-label').innerText = `⏳ Menyimpan (${i + 1}/${daftarDataTemporer.length})...`;
          const item = daftarDataTemporer[i];

          try {
            const res = await apiFetch('{{ route('uniformity.rits.store') }}', {
              method: 'POST',
              body: JSON.stringify({
                tanggal: item.dataRit.tanggal,
                no_rit: item.dataRit.noRit,
                asal_kandang: item.dataRit.asalKandang,
                size_min: item.dataRit.sizeMin,
                size_max: item.dataRit.sizeMax,
                kg_dta: item.dataRit.kgDta,
                ekor_dta: item.dataRit.ekorDta,
                samples: item.dataSample,
              }),
            });
            if (res.status === 'success') indexBerhasil++;
          } catch (err) {
            alert(`Gagal simpan Rit ${item.dataRit.noRit}: ${err.message}`);
          }
        }

        alert(`Sukses menyimpan ${indexBerhasil} dari ${daftarDataTemporer.length} data!`);
        daftarDataTemporer = [];
        renderTabelTemporer();
        loadDashboard();
        document.getElementById('btn-loading-label').classList.add('hidden');
        document.getElementById('btn-default-label').classList.remove('hidden');
      }

      // -- FUNGSI BANTUAN UNTUK DASHBOARD --
      function formatNilaiPersenDashboard(val) {
        if (val === null || val === undefined || val === "" || val === "-") return '0.0%';
        let num = val;
        if (typeof val === 'string') { num = parseFloat(val.replace('%', '').replace(',', '.')); }
        if (isNaN(num)) return '0.0%';
        return num.toFixed(1) + '%';
      }

      function exportExcelRaw() {
  const tanggal = document.getElementById('filter-tanggal-export').value;
  const bulan = document.getElementById('filter-bulan').value;

  let params = new URLSearchParams();
  if (tanggal) {
    params.set('tanggal', tanggal);
  } else if (bulan) {
    params.set('bulan', bulan);
  }

  window.location.href = '{{ route('uniformity.export-excel') }}?' + params.toString();
}

      async function loadDashboard() {
        const container = document.getElementById('dashboard-boxes-container');
        const notifElement = document.getElementById('notif-update-hari-ini');
        const filterBulan = document.getElementById('filter-bulan').value;

        container.innerHTML = '<div class="text-center py-8 text-gray-400 bg-white shadow rounded-xl border border-gray-200">Mengambil data terbaru...</div>';

        let data;
        try {
          data = await apiFetch('{{ route('uniformity.data') }}');
        } catch (err) {
          container.innerHTML = `<div class="text-center py-8 text-red-500 font-bold bg-white shadow rounded-xl border border-gray-200">Gagal memuat data: ${err.message}</div>`;
          return;
        }

        if (!data || data.length === 0) {
          container.innerHTML = '<div class="text-center py-8 text-gray-400 bg-white shadow rounded-xl border border-gray-200">Belum ada data rit yang tersimpan.</div>';
          notifElement.classList.add('hidden');
          return;
        }

        let filteredData = data;
        if (filterBulan) {
          filteredData = data.filter(row => row.tanggal && row.tanggal.substring(0, 7) === filterBulan);
        }

        if (filteredData.length === 0) {
          container.innerHTML = `<div class="text-center py-8 text-gray-400 bg-white shadow rounded-xl border border-gray-200">Tidak ada data rit pada bulan ${filterBulan}.</div>`;
          notifElement.classList.add('hidden');
          return;
        }

        container.innerHTML = '';
        const groupedData = {};
        let adaUpdateHariIni = false;
        const hariIniStr = new Date().toISOString().substring(0, 10);

        filteredData.forEach(row => {
          if (!groupedData[row.tanggal]) groupedData[row.tanggal] = [];
          groupedData[row.tanggal].push(row);
          if (row.tanggal === hariIniStr) adaUpdateHariIni = true;
        });

        notifElement.classList.toggle('hidden', !adaUpdateHariIni);

        const sortedDates = Object.keys(groupedData).sort((a, b) => new Date(b) - new Date(a));

        sortedDates.forEach(tanggal => {
          const rowsInDate = groupedData[tanggal];
          const dateBox = document.createElement('div');
          dateBox.className = "bg-white shadow rounded-xl border border-gray-200 overflow-hidden mb-6";

          dateBox.innerHTML = `
            <div class="bg-gray-100 px-5 py-3 border-b border-gray-200 flex justify-between items-center">
              <div class="flex items-center space-x-2">
                <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                <h2 class="text-sm font-bold text-gray-800 tracking-wide">TANGGAL PENERIMAAN: ${tanggal}</h2>
              </div>
              <span class="text-xs bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full font-semibold">${rowsInDate.length} Rit</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-xs font-medium text-gray-500 uppercase tracking-wider">
                <thead class="bg-gray-50 text-gray-600">
                  <tr>
                    <th class="px-4 py-3 text-center">No Rit</th>
                    <th class="px-4 py-3 text-left">Asal Kandang</th>
                    <th class="px-4 py-3 text-center">Size Min</th>
                    <th class="px-4 py-3 text-center">Size Max</th>
                    <th class="px-4 py-3 text-center">Ekor DTA</th>
                    <th class="px-4 py-3 text-center">Kg DTA</th>
                    <th class="px-4 py-3 text-center">Rerata ABW</th>
                    <th class="px-4 py-3 text-center">Sample</th>
                    <th class="px-4 py-3 text-center text-red-500">Under (%)</th>
                    <th class="px-4 py-3 text-center text-green-600">Masuk (%)</th>
                    <th class="px-4 py-3 text-center text-yellow-600">Over (%)</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-gray-600 normal-case font-normal"></tbody>
              </table>
            </div>
          `;
          const tbody = dateBox.querySelector('tbody');
          rowsInDate.forEach(row => {
            let sMin = parseFloat(row.sizeMin);
            let sMax = parseFloat(row.sizeMax);
            let displayMin = !isNaN(sMin) ? sMin.toFixed(2) : row.sizeMin;
            let displayMax = !isNaN(sMax) ? sMax.toFixed(2) : row.sizeMax;
            let nilaiRerata = row.rerataAbw || '-';

            const tr = document.createElement('tr');
            tr.className = "hover:bg-gray-50/80 transition-colors";
            tr.innerHTML = `
              <td class="px-4 py-3 text-center font-semibold text-gray-900">${row.noRit}</td>
              <td class="px-4 py-3 text-left font-medium">${row.asalKandang}</td>
              <td class="px-4 py-3 text-center font-mono">${displayMin}</td>
              <td class="px-4 py-3 text-center font-mono">${displayMax}</td>
              <td class="px-4 py-3 text-center">${row.ekorDta}</td>
              <td class="px-4 py-3 text-center">${row.kgDta}</td>
              <td class="px-4 py-3 text-center font-medium text-gray-700 font-mono">${nilaiRerata}</td>
              <td class="px-4 py-3 text-center">${row.jumlahSample}</td>
              <td class="px-4 py-3 text-center font-semibold text-red-500 bg-red-50/30">${formatNilaiPersenDashboard(row.undersize)}</td>
              <td class="px-4 py-3 text-center font-semibold text-green-600 bg-green-50/30">${formatNilaiPersenDashboard(row.sizeMasuk)}</td>
              <td class="px-4 py-3 text-center font-semibold text-yellow-600 bg-yellow-50/30">${formatNilaiPersenDashboard(row.oversize)}</td>
            `;
            tbody.appendChild(tr);
          });
          container.appendChild(dateBox);
        });
      }
    </script>
  </body>
</html>