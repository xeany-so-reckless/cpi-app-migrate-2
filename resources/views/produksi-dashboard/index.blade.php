<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard Yield</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <style>
    @media print {
      body { background: white; color: black; padding: 0; margin: 0; }
      .no-print { display: none !important; }
      .print-card { page-break-inside: avoid; border: 1px solid #e5e7eb !important; background: white !important; color: black !important; border-radius: 8px !important; margin-bottom: 1.5rem !important; }

      #yieldGridContainer { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; gap: 0.5rem !important; }
      #yieldGridContainer > div { padding: 0.5rem !important; }
      #yieldGridContainer .text-2xl { font-size: 1.25rem !important; }
      #yieldGridContainer .text-xs { font-size: 0.65rem !important; }

      #mainChartsGroup { display: flex !important; flex-direction: column !important; gap: 1.5rem !important; width: 100% !important; }
      #mainChartsGroup > div:first-child { width: 100% !important; max-width: 400px !important; margin: 0 auto !important; height: 320px !important; }
      #mainChartsGroup > div:last-child { width: 100% !important; height: 350px !important; }

      #yieldChartsSection { display: grid !important; grid-template-columns: 1fr !important; gap: 1.5rem !important; width: 100% !important; }
      #yieldChartsSection > div { width: 100% !important; height: 320px !important; }

      canvas { width: 100% !important; height: 100% !important; }
      .h-64, .h-48 { height: 280px !important; }
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen font-sans relative">

  <header class="bg-white border-b border-gray-200 sticky top-0 z-40 no-print shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
      <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 px-3 py-2 rounded-lg text-xs font-bold transition">← Dashboard Utama</a>
        <h1 class="text-xl font-bold tracking-tight text-blue-600">DASHBOARD YIELD PRODUKSI</h1>
        <span id="updateBadge" class="hidden bg-emerald-500 text-xs text-white font-semibold px-2 py-0.5 rounded-full animate-pulse">Data Baru Tersedia</span>
      </div>

      <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-end">
        <select id="viewMode" onchange="switchViewMode()" class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 text-gray-700">
          <option value="harian">Harian</option>
          <option value="bulanan">Bulanan</option>
          <option value="tahunan">Tahunan</option>
        </select>
        <input id="datePicker" type="date" onchange="filterAndRender()" class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 text-gray-700" />
        <button onclick="downloadPDF()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm">
          Cetak Laporan
        </button>
      </div>
    </div>
  </header>

  <nav class="max-w-7xl mx-auto px-4 mt-6 no-print">
    <div class="border-b border-gray-200 flex gap-2">
      <button id="tabGrafik" onclick="switchTab('grafik')" class="py-2 px-4 border-b-2 font-medium transition text-blue-600 border-blue-600">Dashboard Grafik</button>
      <button id="tabRekap" onclick="switchTab('rekap')" class="py-2 px-4 border-b-2 font-medium transition text-gray-500 hover:text-gray-700 border-transparent">Tabel Rekap</button>
      <button id="tabInput" onclick="switchTab('input')" class="py-2 px-4 border-b-2 font-medium transition text-gray-500 hover:text-gray-700 border-transparent">Input Data Harian</button>
    </div>
  </nav>

  <main class="max-w-7xl mx-auto px-4 py-6">
    <div id="printDateRange" class="hidden text-center text-sm font-bold text-gray-800 mb-4 block"></div>

    <section id="sectionGrafik" class="space-y-6">
      <div id="yieldGridContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4 print-card"></div>

      <div id="mainChartsGroup" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm h-80 print-card">
          <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Komposisi Hasil Produksi</h3>
          <div class="h-64 relative"><canvas id="pieChart"></canvas></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm h-80 lg:col-span-2 print-card">
          <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Monitoring Defect</h3>
          <div class="h-64 relative" id="qualityChartWrapper"><canvas id="areaChart"></canvas></div>
        </div>
      </div>

      <div id="yieldChartsSection" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm h-64 print-card">
          <h3 class="text-xs font-semibold text-teal-600 uppercase tracking-wider mb-1">Tren Yield Titik Nol</h3>
          <div class="h-48 relative"><canvas id="lineChartTitikNol"></canvas></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm h-64 print-card">
          <h3 class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Tren Yield FG + BP Others</h3>
          <div class="h-48 relative"><canvas id="lineChartFgBp"></canvas></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm h-64 print-card">
          <h3 class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-1">Tren Yield By Product</h3>
          <div class="h-48 relative"><canvas id="lineChartByProduct"></canvas></div>
        </div>
      </div>
    </section>

    <section id="sectionRekap" class="hidden bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden print-card">
      <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h2 id="tableTitle" class="font-bold text-gray-700">Data Produksi Harian</h2>
        <div class="flex gap-2 no-print">
          <button id="btnAuthEdit" onclick="requestEditAccess()" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded font-medium transition shadow-sm">Edit</button>
          <span id="editStatusBadge" class="hidden bg-emerald-100 text-emerald-700 text-xs px-2 py-1.5 rounded font-semibold border border-emerald-200">Mode Edit Aktif</span>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase font-semibold border-b border-gray-200">
              <th class="p-2.5">No PO</th>
              <th class="p-2.5">Tanggal</th>
              <th class="p-2.5">Kg DTA</th>
              <th class="p-2.5">Ekor DTA</th>
              <th class="p-2.5">Kg Netto</th>
              <th class="p-2.5">Mati (Ek)</th>
              <th class="p-2.5">ABW</th>
              <th class="p-2.5">Kg Susut</th>
              <th class="p-2.5">% Susut</th>
              <th class="p-2.5">Kg TN</th>
              <th class="p-2.5">Kg BD</th>
              <th class="p-2.5">Kg FG+BP</th>
              <th class="p-2.5">Kg BP</th>
              <th class="p-2.5">% KW2</th>
              <th class="p-2.5">% Defect</th>
              <th class="p-2.5 font-bold text-gray-700">Total Hasil</th>
              <th class="p-2.5 text-center action-column hidden">Aksi</th>
            </tr>
          </thead>
          <tbody id="rekapTableBody" class="divide-y divide-gray-200 text-gray-600"></tbody>
        </table>
      </div>
    </section>

    <section id="sectionInput" class="hidden max-w-2xl mx-auto bg-white p-6 rounded-xl border border-gray-200 shadow-md">
      <div id="inputAuthForm" class="space-y-4">
        <h2 class="text-md font-bold text-gray-700 border-b border-gray-200 pb-2 mb-4">Otentikasi Akses Input (Supervisor)</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-600 mb-1 text-xs font-semibold">ID Pengguna</label>
            <input type="text" id="inputIdUser" class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none text-sm uppercase" />
          </div>
          <div>
            <label class="block text-gray-600 mb-1 text-xs font-semibold">Kata Sandi</label>
            <input type="password" id="inputPassUser" class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none text-sm" />
          </div>
        </div>
        <button onclick="verifyInputAccess()" class="w-full bg-blue-600 hover:bg-blue-700 font-bold py-2 rounded text-white text-sm transition shadow-sm">Masuk Form Input</button>
      </div>

      <div id="inputFormContainer" class="hidden">
        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
          <h2 class="text-lg font-bold text-blue-600">Input Lap. Produksi Harian</h2>
          <span class="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded font-semibold" id="activeUserLabel"></span>
        </div>
        <form id="prodForm" onsubmit="submitForm(event)" class="space-y-4 text-sm text-gray-700">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-600 mb-1 font-medium">No PO</label>
              <select name="noPo" id="inputNoPo" required onchange="onNoPoChange(this.value)"
                class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none">
                <option value="">-- Pilih No PO --</option>
              </select>
              <div id="poSummaryWarning" class="hidden mt-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1.5"></div>
            </div>
            <div>
              <label class="block text-gray-600 mb-1 font-medium">Tanggal (otomatis dari PO)</label>
              <input type="text" id="inputTanggalInfo" disabled placeholder="-"
                class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-1.5 text-gray-500" />
            </div>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-gray-200 pt-3">
            <div><label class="block text-gray-500 mb-1">Kg DTA</label><input type="number" step="any" name="kgDta" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">Ekor DTA</label><input type="number" name="ekorDta" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">Kg Netto</label><input type="number" step="any" name="kgNetto" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">Ayam Mati (Ekor)</label><input type="number" name="ayamMati" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">Kg Titik Nol</label><input type="number" step="any" name="kgTitikNol" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">Kg Bulu Darah</label><input type="number" step="any" name="kgBuluDarah" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">Kg FG + BP Others</label><input type="number" step="any" name="kgFgBp" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">Kg By Product</label><input type="number" step="any" name="kgByProduct" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">% KW 2 / Griller PR</label><input type="number" step="any" name="pctKw2" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
            <div><label class="block text-gray-500 mb-1">% Defect Proses</label><input type="number" step="any" name="pctDefect" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5 focus:border-blue-500 focus:outline-none" /></div>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-gray-200 pt-3">
            <div><label class="block text-gray-500 mb-1">Prod Griller</label><input type="number" step="any" name="prodGriller" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5" /></div>
            <div><label class="block text-gray-500 mb-1">Prod Parting</label><input type="number" step="any" name="prodParting" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5" /></div>
            <div><label class="block text-gray-500 mb-1">Prod Marinasi</label><input type="number" step="any" name="prodMarinasi" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5" /></div>
            <div><label class="block text-gray-500 mb-1">Total Hasil</label><input type="number" step="any" name="totalHasil" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1.5" /></div>
          </div>
          <div class="pt-4"><button type="submit" id="btnSimpan" class="w-full bg-emerald-600 hover:bg-emerald-700 font-bold py-2.5 rounded text-white transition shadow-sm">Simpan Data Ke Database</button></div>
        </form>
      </div>
    </section>
  </main>

  <div id="editModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 no-print">
    <div class="bg-white rounded-xl shadow-xl border border-gray-200 max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6">
      <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-4">
        <h3 class="text-lg font-bold text-amber-600">Form Koreksi / Edit Data Baris</h3>
        <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
      </div>
      <form id="editRowForm" onsubmit="submitRowEdit(event)" class="text-sm space-y-4 text-gray-700">
        <input type="hidden" id="editOldNoPo" />
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-600 mb-1 font-semibold">No PO (Kunci Utama)</label>
            <input type="text" id="editNoPo" disabled class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-1.5 font-medium text-gray-500" />
          </div>
          <div>
            <label class="block text-gray-600 mb-1 font-semibold">Tanggal Produksi</label>
            <input type="text" id="editTanggal" disabled class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-1.5 font-medium text-gray-500" />
          </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-gray-100 pt-3">
          <div><label class="block text-gray-500 mb-1">Kg DTA</label><input type="number" step="any" id="editKgDta" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Ekor DTA</label><input type="number" id="editEkorDta" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Kg Netto</label><input type="number" step="any" id="editKgNetto" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Ayam Mati (Ek)</label><input type="number" id="editAyamMati" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Kg Titik Nol</label><input type="number" step="any" id="editKgTitikNol" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Kg Bulu Darah</label><input type="number" step="any" id="editKgBuluDarah" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Kg FG + BP Oth</label><input type="number" step="any" id="editKgFgBp" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Kg By Product</label><input type="number" step="any" id="editKgByProduct" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">% KW 2 / Pr</label><input type="number" step="any" id="editPctKw2" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">% Defect Pros</label><input type="number" step="any" id="editPctDefect" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-gray-100 pt-3">
          <div><label class="block text-gray-500 mb-1">Prod Griller</label><input type="number" step="any" id="editProdGriller" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Prod Parting</label><input type="number" step="any" id="editProdParting" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Prod Marinasi</label><input type="number" step="any" id="editProdMarinasi" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
          <div><label class="block text-gray-500 mb-1">Total Hasil</label><input type="number" step="any" id="editTotalHasil" required class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-1" /></div>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <button type="button" onclick="closeEditModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded font-medium transition">Batal</button>
          <button type="submit" id="btnSimpanEdit" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded font-bold transition shadow-sm">Simpan Pembaruan</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let rawDataGlobal = [];
    let poListGlobal = [];
    let charts = {};
    let hasEditAccess = false;
    let hasInputAccess = false;
    // Menggantikan USERS object hardcode - kredensial disimpan sesaat di
    // memori setelah tervalidasi ke server, dipakai ulang untuk tiap
    // request simpan/edit (modul ini stateless, tidak ada sesi login).
    let activeCredentials = null;

    Chart.register(ChartDataLabels);

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

    window.onload = function() {
      document.getElementById('datePicker').value = new Date().toISOString().substring(0, 10);
      loadData();
      loadPurchaseOrders('inputNoPo');
      setInterval(checkUpdates, 10000);
    };

    async function loadData() {
      try {
        rawDataGlobal = await apiFetch('{{ route('produksi-dashboard.data') }}');
        filterAndRender();
        checkUpdates();
      } catch (err) {
        console.error(err);
      }
    }

    // BARU - Load daftar No PO dari PPIC untuk dropdown form Input.
    // Dipanggil saat halaman pertama kali load, dan setiap kali tab
    // Input dibuka lagi, supaya PO baru dari PPIC ikut muncul.
    async function loadPurchaseOrders(selectId) {
      try {
        poListGlobal = await apiFetch('{{ route('produksi-dashboard.purchase-orders') }}');
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">-- Pilih No PO --</option>' +
          poListGlobal.map(po => `<option value="${po.nomorPo}">${po.nomorPo} (${po.jenisPo} - ${po.tanggal})</option>`).join('');
      } catch (err) {
        console.error(err);
      }
    }

    // DIUBAH: sekarang async - selain isi info tanggal, juga auto-fill
    // 4 field (Kg DTA, Ekor DTA, Kg Netto, Ayam Mati) dari data Report
    // Harian Bahan Baku LB terkait PO ini. Field tetap BISA diedit manual
    // kalau perlu koreksi - tidak dikunci disabled.
    async function onNoPoChange(nomorPo) {
      const po = poListGlobal.find(p => p.nomorPo === nomorPo);
      document.getElementById('inputTanggalInfo').value = po ? po.tanggal : '';

      const form = document.getElementById('prodForm');
      const warningEl = document.getElementById('poSummaryWarning');
      warningEl.classList.add('hidden');

      if (!nomorPo) {
        form.kgDta.value = '';
        form.ekorDta.value = '';
        form.kgNetto.value = '';
        form.ayamMati.value = '';
        return;
      }

      try {
        const summary = await apiFetch(`{{ route('produksi-dashboard.po-summary') }}?no_po=${encodeURIComponent(nomorPo)}`);

        form.kgDta.value = summary.kgDta;
        form.ekorDta.value = summary.ekorDta;
        form.kgNetto.value = summary.kgNetto;
        form.ayamMati.value = summary.ayamMati;

        if (summary.ritSelesai < summary.totalRit) {
          warningEl.textContent = `Data LB untuk PO ini belum lengkap - ${summary.ritSelesai} dari ${summary.totalRit} rit yang sudah Setelah Bongkar. Kg Netto & Ayam Mati kemungkinan belum final, silakan cek ulang sebelum simpan.`;
          warningEl.classList.remove('hidden');
        } else if (summary.totalRit === 0) {
          warningEl.textContent = `Belum ada data Report LB sama sekali untuk PO ini. Semua field di bawah perlu diisi manual.`;
          warningEl.classList.remove('hidden');
        }
      } catch (err) {
        console.error(err);
      }
    }

    async function checkUpdates() {
      try {
        const infoUpdate = await apiFetch('{{ route('produksi-dashboard.latest-update') }}');
        if (infoUpdate && infoUpdate.lastTimestamp) {
          const sekarang = new Date().getTime();
          const waktuData = new Date(infoUpdate.lastTimestamp).getTime();
          const selisihJam = (sekarang - waktuData) / (1000 * 60 * 60);
          document.getElementById('updateBadge').classList.toggle('hidden', !(selisihJam >= 0 && selisihJam < 24));
        } else {
          document.getElementById('updateBadge').classList.add('hidden');
        }
      } catch (err) { /* diamkan, cuma badge notifikasi */ }
    }

    function switchViewMode() {
      const mode = document.getElementById('viewMode').value;
      const picker = document.getElementById('datePicker');
      if (mode === 'harian') picker.type = 'date';
      else if (mode === 'bulanan') picker.type = 'month';
      else if (mode === 'tahunan') { picker.type = 'number'; picker.value = new Date().getFullYear(); }
      filterAndRender();
    }

    function switchTab(tab) {
      const tabs = {
        grafik: { sec: 'sectionGrafik', btn: 'tabGrafik', baseClass: 'text-blue-600 border-blue-600' },
        rekap: { sec: 'sectionRekap', btn: 'tabRekap', baseClass: 'text-blue-600 border-transparent' },
        input: { sec: 'sectionInput', btn: 'tabInput', baseClass: 'text-blue-600 border-transparent' }
      };
      Object.keys(tabs).forEach(k => {
        if (k === tab) {
          document.getElementById(tabs[k].sec).classList.remove('hidden');
          document.getElementById(tabs[k].btn).className = `py-2 px-4 border-b-2 font-medium ${tabs[k].baseClass.split(' ')[0]} border-current`;
        } else {
          document.getElementById(tabs[k].sec).classList.add('hidden');
          document.getElementById(tabs[k].btn).className = `py-2 px-4 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-300`;
        }
      });

      // BARU - Refresh daftar No PO tiap kali tab Input dibuka, biar
      // PO yang baru diinput PPIC ikut kebawa tanpa perlu reload halaman.
      if (tab === 'input') loadPurchaseOrders('inputNoPo');
    }

    async function verifyInputAccess() {
      const idInput = document.getElementById('inputIdUser').value.trim();
      const passInput = document.getElementById('inputPassUser').value;

      try {
        const res = await apiFetch('{{ route('produksi-dashboard.verify-signature') }}', {
          method: 'POST',
          body: JSON.stringify({ employee_code: idInput, password: passInput }),
        });
        activeCredentials = { employee_code: idInput, password: passInput };
        hasInputAccess = true;
        document.getElementById('inputAuthForm').classList.add('hidden');
        document.getElementById('inputFormContainer').classList.remove('hidden');
        document.getElementById('activeUserLabel').innerText = res.name;
      } catch (err) {
        alert(err.message || "ID Pengguna atau Kata Sandi salah! Hanya Supervisor yang diizinkan.");
      }
    }

    async function requestEditAccess() {
      const idInput = prompt("Masukkan ID Pengguna:");
      if (!idInput) return;
      const passInput = prompt("Masukkan Kata Sandi:");

      try {
        const res = await apiFetch('{{ route('produksi-dashboard.verify-signature') }}', {
          method: 'POST',
          body: JSON.stringify({ employee_code: idInput, password: passInput }),
        });
        activeCredentials = { employee_code: idInput, password: passInput };
        hasEditAccess = true;
        document.getElementById('btnAuthEdit').classList.add('hidden');
        document.getElementById('editStatusBadge').classList.remove('hidden');
        document.querySelectorAll('.action-column').forEach(el => el.classList.remove('hidden'));
        filterAndRender();
        alert(`Akses diberikan. Selamat bekerja, ${res.name}!`);
      } catch (err) {
        alert(err.message || "Otentikasi Gagal! Akses ditolak.");
      }
    }

    async function submitForm(e) {
      e.preventDefault();
      if (!hasInputAccess || !activeCredentials) { alert("Akses ilegal terdeteksi."); return; }

      const btn = document.getElementById('btnSimpan');
      btn.disabled = true;
      btn.innerText = "Menyimpan...";
      const form = document.getElementById('prodForm');

      const payload = {
        ...activeCredentials,
        no_po: form.noPo.value,
        kg_dta: form.kgDta.value,
        ekor_dta: form.ekorDta.value,
        kg_netto: form.kgNetto.value,
        ayam_mati: form.ayamMati.value,
        kg_titik_nol: form.kgTitikNol.value,
        kg_bulu_darah: form.kgBuluDarah.value,
        kg_fg_bp: form.kgFgBp.value,
        kg_by_product: form.kgByProduct.value,
        pct_kw2: form.pctKw2.value,
        pct_defect: form.pctDefect.value,
        prod_griller: form.prodGriller.value,
        prod_parting: form.prodParting.value,
        prod_marinasi: form.prodMarinasi.value,
        total_hasil: form.totalHasil.value,
      };

      try {
        const res = await apiFetch('{{ route('produksi-dashboard.store') }}', { method: 'POST', body: JSON.stringify(payload) });
        alert(res.message);
        form.reset();
        document.getElementById('inputTanggalInfo').value = '';
        document.getElementById('poSummaryWarning').classList.add('hidden');
        loadData();
        loadPurchaseOrders('inputNoPo');
        switchTab('grafik');
      } catch (err) {
        alert(err.message);
      } finally {
        btn.disabled = false; btn.innerText = "Simpan Data Ke Database";
      }
    }

    function openEditModal(noPo) {
      const record = rawDataGlobal.find(d => d.noPo === noPo);
      if (!record) return;

      document.getElementById('editOldNoPo').value = record.noPo;
      document.getElementById('editNoPo').value = record.noPo;
      document.getElementById('editTanggal').value = record.tanggal;
      document.getElementById('editKgDta').value = record.kgDta;
      document.getElementById('editEkorDta').value = record.ekorDta;
      document.getElementById('editKgNetto').value = record.kgNetto;
      document.getElementById('editAyamMati').value = record.ayamMati;
      document.getElementById('editKgTitikNol').value = record.kgTitikNol;
      document.getElementById('editKgBuluDarah').value = record.kgBuluDarah;
      document.getElementById('editKgFgBp').value = record.kgFgBp;
      document.getElementById('editKgByProduct').value = record.kgByProduct;
      document.getElementById('editPctKw2').value = record.pctKw2;
      document.getElementById('editPctDefect').value = record.pctDefect;
      document.getElementById('editProdGriller').value = record.prodGriller;
      document.getElementById('editProdParting').value = record.prodParting;
      document.getElementById('editProdMarinasi').value = record.prodMarinasi;
      document.getElementById('editTotalHasil').value = record.totalHasil;

      document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.add('hidden');
    }

    async function submitRowEdit(e) {
      e.preventDefault();
      if (!hasEditAccess || !activeCredentials) { alert("Tidak memiliki hak akses edit."); return; }

      const btn = document.getElementById('btnSimpanEdit');
      btn.disabled = true; btn.innerText = "Memperbarui...";

      const payload = {
        ...activeCredentials,
        no_po: document.getElementById('editOldNoPo').value,
        kg_dta: parseFloat(document.getElementById('editKgDta').value) || 0,
        ekor_dta: parseInt(document.getElementById('editEkorDta').value) || 0,
        kg_netto: parseFloat(document.getElementById('editKgNetto').value) || 0,
        ayam_mati: parseInt(document.getElementById('editAyamMati').value) || 0,
        kg_titik_nol: parseFloat(document.getElementById('editKgTitikNol').value) || 0,
        kg_bulu_darah: parseFloat(document.getElementById('editKgBuluDarah').value) || 0,
        kg_fg_bp: parseFloat(document.getElementById('editKgFgBp').value) || 0,
        kg_by_product: parseFloat(document.getElementById('editKgByProduct').value) || 0,
        pct_kw2: parseFloat(document.getElementById('editPctKw2').value) || 0,
        pct_defect: parseFloat(document.getElementById('editPctDefect').value) || 0,
        prod_griller: parseFloat(document.getElementById('editProdGriller').value) || 0,
        prod_parting: parseFloat(document.getElementById('editProdParting').value) || 0,
        prod_marinasi: parseFloat(document.getElementById('editProdMarinasi').value) || 0,
        total_hasil: parseFloat(document.getElementById('editTotalHasil').value) || 0,
      };

      try {
        await apiFetch('{{ route('produksi-dashboard.update') }}', { method: 'POST', body: JSON.stringify(payload) });
        alert("Data baris berhasil diperbarui secara menyeluruh!");
        closeEditModal();
        loadData();
      } catch (err) {
        alert("Gagal merubah data: " + err.message);
      } finally {
        btn.disabled = false; btn.innerText = "Simpan Pembaruan";
      }
    }

    function filterAndRender() {
      const mode = document.getElementById('viewMode').value;
      const filterValue = document.getElementById('datePicker').value;

      let filtered = [];
      if (filterValue) {
        if (mode === 'harian') filtered = rawDataGlobal.filter(d => d.tanggal === filterValue);
        else if (mode === 'bulanan') filtered = rawDataGlobal.filter(d => d.bulan === filterValue);
        else if (mode === 'tahunan') filtered = rawDataGlobal.filter(d => d.tahun === parseInt(filterValue) || d.tahun === filterValue);
      } else {
        filtered = rawDataGlobal;
      }

      renderGridContainer(filtered, mode);
      renderCharts(filtered, mode, filterValue);
      renderTable(filtered, mode, filterValue);
    }

    function renderGridContainer(data, mode) {
      const container = document.getElementById('yieldGridContainer');
      const chartsGroup = document.getElementById('mainChartsGroup');

      if (!data || data.length === 0) {
        container.innerHTML = `<div class="col-span-3 text-center p-8 bg-white border border-gray-200 rounded-xl text-gray-400 font-medium">Tidak ada data transaksi ter-input pada koordinat tanggal tersebut. Dashboard Kosong.</div>`;
        chartsGroup.classList.add('hidden');
        document.getElementById('yieldChartsSection').classList.add('hidden');
        return;
      }

      chartsGroup.classList.remove('hidden');
      let totalNetto = data.reduce((a, b) => a + b.kgNetto, 0);
      let totalTitikNol = data.reduce((a, b) => a + b.kgTitikNol, 0);
      let totalFg = data.reduce((a, b) => a + b.kgFgBp, 0);
      let totalBp = data.reduce((a, b) => a + b.kgByProduct, 0);
      let yTitikNol = totalNetto > 0 ? (totalTitikNol / totalNetto) * 100 : 0;
      let yFg = totalNetto > 0 ? (totalFg / totalNetto) * 100 : 0;
      let yBp = totalNetto > 0 ? (totalBp / totalNetto) * 100 : 0;

      const targetStandar = 73.00;
      let achievement = yTitikNol > 0 ? (yTitikNol / targetStandar) * 100 : 0;
      let achColor = achievement >= 100 ? 'text-emerald-600' : 'text-rose-600';
      container.innerHTML = `
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Yield Titik Nol</div>
          <div class="text-2xl font-bold text-teal-600 mt-1">${yTitikNol.toFixed(2)}%</div>
          <div class="mt-2 pt-2 border-t border-gray-100 flex justify-between items-center text-xs">
            <span class="text-gray-400">Standar: <b class="text-gray-600">${targetStandar.toFixed(2)}%</b></span>
            <span class="font-bold ${achColor}">Ach: ${achievement.toFixed(1)}%</span>
          </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
          <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Yield Finished Goods + BP Others</div>
          <div class="text-2xl font-bold text-amber-600 mt-1">${yFg.toFixed(2)}%</div>
          <div class="mt-2 pt-2 border-t border-gray-100 text-xs text-gray-400">Kumulatif Hasil / Total Netto</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
          <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Yield By Product</div>
          <div class="text-2xl font-bold text-rose-600 mt-1">${yBp.toFixed(2)}%</div>
          <div class="mt-2 pt-2 border-t border-gray-100 text-xs text-gray-400">Sampingan Proses Produksi</div>
        </div>
      `;
    }

    function getLabelConfig(color = '#374151') {
      return { display: true, align: 'top', anchor: 'end', color: color, font: { size: 9, weight: 'bold' }, formatter: (val) => val.toFixed(1) + '%' };
    }

    function renderCharts(data, mode, filterValue) {
      ['pie', 'lineTN', 'lineFG', 'lineBP', 'area'].forEach(k => { if (charts[k]) { charts[k].destroy(); charts[k] = null; } });
      if (!data || data.length === 0) return;

      const wrapper = document.getElementById('qualityChartWrapper');
      if (wrapper) { wrapper.innerHTML = '<canvas id="areaChart"></canvas>'; }

      let chartLabels = data.map(d => {
        if (mode === 'bulanan') { const parts = d.tanggal.split('-'); return parts.length === 3 ? parts[2] : d.tanggal; }
        return d.tanggal;
      });

      let griller = data.reduce((a,b) => a + b.prodGriller, 0);
      let parting = data.reduce((a,b) => a + b.prodParting, 0);
      let marinasi = data.reduce((a,b) => a + b.prodMarinasi, 0);
      let totalHasil = data.reduce((a,b) => a + b.totalHasil, 0);

      const ctxPie = document.getElementById('pieChart').getContext('2d');
      charts.pie = new Chart(ctxPie, {
        type: 'pie',
        data: {
          labels: ['Griller', 'Parting', 'Marinasi'],
          datasets: [{ data: totalHasil > 0 ? [(griller/totalHasil)*100, (parting/totalHasil)*100, (marinasi/totalHasil)*100] : [0,0,0], backgroundColor: ['#0d9488', '#d97706', '#e11d48'] }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { labels: { color: '#374151' } },
            datalabels: { color: '#fff', font: { weight: 'bold', size: 11 }, formatter: (value) => value > 0 ? value.toFixed(1) + '%' : '' }
          }
        }
      });

      const yieldSection = document.getElementById('yieldChartsSection');
      if (mode === 'bulanan' || mode === 'tahunan') {
        if (yieldSection) yieldSection.classList.remove('hidden');
        const chartOptions = (color) => ({
          responsive: true, maintainAspectRatio: false,
          scales: { x: { ticks: { color: '#4b5563', autoSkip: false, maxRotation: 0, minRotation: 0, font: { size: 9 } } }, y: { ticks: { color: '#4b5563' } } },
          plugins: { legend: { labels: { color: '#374151' } }, datalabels: getLabelConfig(color) }
        });
        charts.lineTN = new Chart(document.getElementById('lineChartTitikNol').getContext('2d'), {
          type: 'line', data: { labels: chartLabels, datasets: [{ label: 'Titik Nol', data: data.map(d => d.yieldTitikNol), borderColor: '#0d9488', tension: 0.1, fill: false }] }, options: chartOptions('#0d9488')
        });
        charts.lineFG = new Chart(document.getElementById('lineChartFgBp').getContext('2d'), {
          type: 'line', data: { labels: chartLabels, datasets: [{ label: 'FG + BP Others', data: data.map(d => d.yieldFgBp), borderColor: '#d97706', tension: 0.1, fill: false }] }, options: chartOptions('#d97706')
        });
        charts.lineBP = new Chart(document.getElementById('lineChartByProduct').getContext('2d'), {
          type: 'line', data: { labels: chartLabels, datasets: [{ label: 'By Product', data: data.map(d => d.yieldByProduct), borderColor: '#e11d48', tension: 0.1, fill: false }] }, options: chartOptions('#e11d48')
        });
      } else {
        if (yieldSection) yieldSection.classList.add('hidden');
      }

      const ctxArea = document.getElementById('areaChart').getContext('2d');
      const baseOptions = {
        responsive: true, maintainAspectRatio: false,
        scales: { x: { ticks: { color: '#4b5563', autoSkip: false, font: { size: 9 } } }, y: { beginAtZero: true, ticks: { color: '#4b5563' } } },
        plugins: { legend: { labels: { color: '#374151' } } }
      };

      if (mode === 'harian') {
        charts.area = new Chart(ctxArea, {
          type: 'bar',
          data: { labels: chartLabels, datasets: [
            { label: '% Defect Proses', data: data.map(d => d.pctDefect), backgroundColor: '#e11d48' },
            { label: '% KW 2 / Griller PR', data: data.map(d => d.pctKw2), backgroundColor: '#7c3aed' }
          ] },
          options: { ...baseOptions, plugins: { ...baseOptions.plugins, datalabels: { display: true, align: 'top', anchor: 'end', color: '#374151', font: { size: 10, weight: 'bold' }, formatter: (val) => (val > 0) ? val.toFixed(1) + '%' : '0%' } } }
        });
      } else {
        charts.area = new Chart(ctxArea, {
          type: 'line',
          data: { labels: chartLabels, datasets: [
            { label: '% Defect Proses', data: data.map(d => d.pctDefect), backgroundColor: 'rgba(225, 29, 72, 0.15)', borderColor: '#e11d48', fill: true },
            { label: '% KW 2 / Griller PR', data: data.map(d => d.pctKw2), backgroundColor: 'rgba(124, 58, 237, 0.15)', borderColor: '#7c3aed', fill: true }
          ] },
          options: { ...baseOptions, scales: { x: { stacked: true, ticks: { color: '#4b5563', autoSkip: false } }, y: { stacked: true, ticks: { color: '#4b5563' } } }, plugins: { ...baseOptions.plugins, datalabels: { display: true, align: 'center', anchor: 'center', color: '#374151', font: { size: 9, weight: 'bold' }, formatter: (val) => (val > 0) ? val.toFixed(1) + '%' : '' } } }
        });
      }
    }

    function renderTable(data, mode, filterValue) {
      const tbody = document.getElementById('rekapTableBody');
      const tableTitle = document.getElementById('tableTitle');
      tbody.innerHTML = '';

      if (mode === 'bulanan' && filterValue) {
        const dateObj = new Date(filterValue + "-01");
        const monthName = dateObj.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
        tableTitle.innerText = `Rekap Produksi - Bulan ${monthName}`;
      } else if (mode === 'harian' && filterValue) {
        tableTitle.innerText = `Data Produksi Harian`;
      } else {
        tableTitle.innerText = `Rekap Data Produksi`;
      }

      if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="17" class="text-center p-4 text-gray-400">Tidak ada baris data.</td></tr>`;
        return;
      }

      data.forEach(d => {
        const hiddenClass = hasEditAccess ? "" : "hidden";
        let displayDate = d.tanggal;
        if (mode === 'bulanan') { const parts = d.tanggal.split('-'); if (parts.length === 3) displayDate = parts[2]; }

        tbody.innerHTML += `
          <tr class="hover:bg-gray-50 transition">
            <td class="p-2.5 font-semibold text-blue-600">${d.noPo}</td>
            <td class="p-2.5 font-medium text-gray-800">${displayDate}</td>
            <td class="p-2.5">${d.kgDta.toLocaleString()}</td>
            <td class="p-2.5">${d.ekorDta.toLocaleString()}</td>
            <td class="p-2.5">${d.kgNetto.toLocaleString()}</td>
            <td class="p-2.5">${d.ayamMati.toLocaleString()}</td>
            <td class="p-2.5 text-teal-600 font-semibold">${d.abw.toFixed(2)}</td>
            <td class="p-2.5">${d.kgSusut.toLocaleString()}</td>
            <td class="p-2.5 text-rose-600 font-medium">${d.pctSusut.toFixed(2)}%</td>
            <td class="p-2.5">${d.kgTitikNol.toLocaleString()}</td>
            <td class="p-2.5">${d.kgBuluDarah.toLocaleString()}</td>
            <td class="p-2.5">${d.kgFgBp.toLocaleString()}</td>
            <td class="p-2.5">${d.kgByProduct.toLocaleString()}</td>
            <td class="p-2.5">${d.pctKw2}%</td>
            <td class="p-2.5">${d.pctDefect}%</td>
            <td class="p-2.5 font-bold text-gray-800">${d.totalHasil.toLocaleString()}</td>
            <td class="p-2.5 text-center action-column ${hiddenClass}">
              <button onclick="openEditModal('${d.noPo}')" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-2 py-1 rounded text-[10px] transition">Edit Baris</button>
            </td>
          </tr>
        `;
      });
    }

    function downloadPDF() {
      const mode = document.getElementById('viewMode').value;
      const filterVal = document.getElementById('datePicker').value;
      document.getElementById('printDateRange').innerText = `Periode Peninjauan: ${mode.toUpperCase()} (${filterVal || 'Semua Data Terekam'})`;
      window.print();
    }
  </script>
</body>
</html>