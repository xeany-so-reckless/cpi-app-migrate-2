<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Export & Rekap - Uniformity Apps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
      body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
      .hanya-pdf { display: none; }
      @media print {
        .jangan-cetak { display: none !important; }
        .hanya-pdf { display: block !important; }
      }
      .pdf-mode { max-width: 790px !important; margin: 0 auto !important; padding: 10px !important; background: white;}
      .page-break { page-break-before: always; break-before: page; }
    </style>
  </head>
  <body class="text-gray-800 antialiased p-4">

    <div class="max-w-4xl mx-auto mb-4 flex justify-between items-center jangan-cetak">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 px-3 py-2 rounded-lg text-xs font-bold transition">
        ← Dashboard
      </a>
      <a id="link-kembali" href="{{ route('uniformity.index') }}" class="text-blue-600 hover:underline font-semibold text-sm flex items-center gap-1">
        <span>← Kembali ke Input Data</span>
      </a>
    </div>

    <main id="sec-rekap" class="p-6 bg-white rounded-xl shadow-lg border border-gray-200 max-w-4xl mx-auto">

      <div id="pdf-header-formal" class="hidden border-b-2 border-gray-900 pb-4 mb-4">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-xl font-bold uppercase tracking-tight text-gray-900">LAPORAN DAILY UNIFORMITY</h1>
          </div>
          <div class="text-right border-l pl-4 border-gray-300 text-[11px] text-gray-500 font-mono">
            <div>No. Dokumen : FM-PROD-008</div>
            <div>Periode: <span id="pdf-info-jenis" class="uppercase font-bold text-gray-800">-</span></div>
          </div>
        </div>
      </div>

      <div id="kontrol-rekap-area" class="border-b pb-6 mb-6 jangan-cetak">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Export & Rekap Laporan</h1>

        <div class="flex flex-wrap gap-4 items-end mb-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Periode Filter</label>
            <select id="rekap-jenis" onchange="penyesuaianKomponenFilter()" class="border rounded-lg p-2 text-sm bg-white focus:ring-2 outline-none">
              <option value="semua_hari">Semua Hari</option>
              <option value="pilih_hari">Pilih Hari</option>
              <option value="bulanan">Bulanan</option>
            </select>
          </div>
          <div>
            <input type="date" id="rekap-filter-tanggal" onchange="loadRekap()" class="hidden border rounded-lg p-2 text-sm bg-white focus:ring-2 outline-none">
          </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex flex-col md:flex-row gap-4 items-end">
          <div class="flex-1">
            <label class="block text-xs font-bold text-gray-600 mb-1">ID Otorisasi (Foreman/Forelady)</label>
            <input type="text" id="auth-id" placeholder="Contoh: APP01" class="w-full border rounded-lg p-2 text-sm outline-none uppercase">
          </div>
          <div class="flex-1">
            <label class="block text-xs font-bold text-gray-600 mb-1">Password</label>
            <input type="password" id="auth-pass" placeholder="Masukkan Password" class="w-full border rounded-lg p-2 text-sm outline-none">
          </div>
          <button id="btn-otorisasi" onclick="otorisasiTandaTangan()" class="bg-gray-800 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold text-sm transition">Validasi & Siapkan PDF</button>

          <button id="btn-download-pdf" onclick="downloadPdfLaporan()" disabled class="opacity-50 cursor-not-allowed bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition flex items-center space-x-1">
            <span>Download PDF Laporan (A4)</span>
          </button>
        </div>
        <p id="pesan-otorisasi" class="text-xs text-green-600 font-bold mt-2 hidden">Otorisasi berhasil atas nama <span id="nama-terotorisasi" class="uppercase"></span>. Laporan siap di-download.</p>

      </div>

      <div id="pdf-halaman-A" class="space-y-4">
        <div class="bg-gray-800 text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
          <h3 class="text-xs font-bold uppercase tracking-wider">LEMBAR A: RINCIAN DATA PENERIMAAN PER RIT</h3>
        </div>
        <div class="overflow-x-auto border border-gray-200 rounded-b-lg">
          <table class="min-w-full divide-y divide-gray-200 text-[10px] text-left">
            <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
              <tr>
                <th class="px-2 py-3 text-center">Tanggal</th>
                <th class="px-2 py-3 text-center">No Rit</th>
                <th class="px-3 py-3">Asal Kandang</th>
                <th class="px-2 py-3 text-center">Ekor</th>
                <th class="px-2 py-3 text-center">Kg</th>
                <th class="px-2 py-3 text-center">Sample</th>
                <th class="px-2 py-3 text-center text-red-600">Under (%)</th>
                <th class="px-2 py-3 text-center text-green-700">Masuk (%)</th>
                <th class="px-2 py-3 text-center text-yellow-600">Over (%)</th>
              </tr>
            </thead>
            <tbody id="rekap-detail-rit-body" class="bg-white divide-y divide-gray-200 text-gray-600">
              <tr><td colspan="9" class="text-center py-6 text-gray-400 text-xs">Mengkalkulasi rekap...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div id="pdf-halaman-B" class="page-break space-y-6 pt-4">

        <div class="bg-gray-800 text-white px-4 py-2 rounded-lg flex justify-between items-center">
          <h3 class="text-xs font-bold uppercase tracking-wider">LEMBAR B: GRAFIK MONITORING DISTRIBUSI</h3>
        </div>

        <div class="bg-white p-4 border border-gray-200 rounded-xl w-full">
          <div class="relative w-full h-[280px]">
            <canvas id="rekapChart"></canvas>
          </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden w-full">
          <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
            <thead class="bg-gray-100 text-gray-700 font-bold uppercase">
              <tr>
                <th id="rekap-header-periode" class="px-6 py-3">Tanggal / Periode</th>
                <th class="px-6 py-3 text-red-600">Undersize (%)</th>
                <th class="px-6 py-3 text-green-700">Size Masuk (%)</th>
                <th class="px-6 py-3 text-yellow-600">Oversize (%)</th>
              </tr>
            </thead>
            <tbody id="rekap-table-body" class="bg-white divide-y divide-gray-200 text-gray-600">
              <tr><td colspan="4" class="text-center py-6">Pilih Opsi Rekap</td></tr>
            </tbody>
          </table>
        </div>

        <div id="pdf-footer-formal" class="hidden pt-8 grid grid-cols-2 gap-8 text-center text-xs font-medium text-gray-700">
          <div>
            <p class="mb-2">Dibuat Oleh:</p>
            <div id="qr-tally-box" class="h-10 w-10 mx-auto mb-2 flex items-center justify-center relative">
               <div id="qrcode-tally"></div>
            </div>

            <div class="mx-auto border-b w-48 border-gray-400 text-gray-900 font-bold" id="tally-sign-name">
              M. DARUL CHOIRI
            </div>
            <p class="text-gray-400 text-[10px] mt-1">Tally Uniformity</p>
          </div>

          <div>
            <p class="mb-2">Diperiksa & Disetujui:</p>
            <div id="qr-signature-box" class="h-10 w-10 mx-auto mb-2 flex items-center justify-center relative">
               <div id="qrcode-container"></div>
            </div>

            <div class="mx-auto border-b w-48 border-gray-400 text-gray-900 font-bold" id="qa-sign-name">
              (Belum Diotorisasi)
            </div>
            <p class="text-gray-400 text-[10px] mt-1">Foreman/Forelady</p>
          </div>

          <div class="col-span-2 text-left text-[10px] text-gray-400 mt-6 border-t border-gray-200 pt-2 italic">
            * Dokumen dicetak pada: <span id="pdf-tanggal-cetak" class="font-mono text-gray-500">-</span>
          </div>

        </div>

      </div>
    </main>

    <script>
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      let myChart = null;
      let cacheDataDashboardGlobal = [];

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

      window.addEventListener('DOMContentLoaded', async () => {
        document.getElementById('rekap-filter-tanggal').value = new Date().toISOString().substring(0, 10);

        try {
          cacheDataDashboardGlobal = await apiFetch('{{ route('uniformity.data') }}') || [];
        } catch (err) {
          cacheDataDashboardGlobal = [];
        }
        loadRekap();

        document.getElementById('auth-pass').addEventListener('keydown', function(e) {
          if (e.key === 'Enter') { e.preventDefault(); otorisasiTandaTangan(); }
        });
      });

      async function otorisasiTandaTangan() {
        const inputId = document.getElementById('auth-id').value.trim().toUpperCase();
        const inputPass = document.getElementById('auth-pass').value;

        if (inputId === "" || inputPass === "") {
          alert("Mohon isikan ID Otorisasi dan Password dengan lengkap.");
          return;
        }

        let result;
        try {
          result = await apiFetch('{{ route('uniformity.verify-signature') }}', {
            method: 'POST',
            body: JSON.stringify({ employee_code: inputId, password: inputPass }),
          });
        } catch (err) {
          alert(err.message || "ID atau Password salah! Akses ditolak.");
          document.getElementById('auth-pass').value = '';
          document.getElementById('auth-pass').focus();
          return;
        }

        if (!result.valid) {
          alert(result.message || "ID atau Password salah! Akses ditolak.");
          document.getElementById('auth-pass').value = '';
          document.getElementById('auth-pass').focus();
          return;
        }

        const btnPdf = document.getElementById('btn-download-pdf');
        btnPdf.disabled = false;
        btnPdf.classList.remove('opacity-50', 'cursor-not-allowed');
        btnPdf.classList.add('hover:bg-red-700');

        document.getElementById('pesan-otorisasi').classList.remove('hidden');
        document.getElementById('nama-terotorisasi').innerText = result.name;
        document.getElementById('qa-sign-name').innerText = result.name.toUpperCase();

        const waktuOtorisasi = new Date().toLocaleString('id-ID');

        const qrBox = document.getElementById("qrcode-container");
        qrBox.innerHTML = "";
        const textQR = `Approved by: ${result.name} | Role: Foreman/Forelady | Date: ${waktuOtorisasi}`;
        new QRCode(qrBox, {
          text: textQR, width: 40, height: 40, colorDark: "#000000", colorLight: "#ffffff", correctLevel: QRCode.CorrectLevel.L
        });

        const qrTallyBox = document.getElementById("qrcode-tally");
        qrTallyBox.innerHTML = "";
        const textQRTally = `Prepared by: M. Darul Choiri | Role: Tally Uniformity | Date: ${waktuOtorisasi}`;
        new QRCode(qrTallyBox, {
          text: textQRTally, width: 40, height: 40, colorDark: "#000000", colorLight: "#ffffff", correctLevel: QRCode.CorrectLevel.L
        });
      }

      function penyesuaianKomponenFilter() {
        const jenis = document.getElementById('rekap-jenis').value;
        const inputTanggal = document.getElementById('rekap-filter-tanggal');
        if (jenis === 'pilih_hari') inputTanggal.classList.remove('hidden');
        else inputTanggal.classList.add('hidden');
        loadRekap();
      }

      function bersihkanDanFormatPersen(val) {
        if (val === null || val === undefined || val === '') return 0;
        let numVal = typeof val === 'number' ? val : parseFloat(String(val).replace('%', '').replace(',', '.'));
        return isNaN(numVal) ? 0 : numVal;
      }

      async function loadRekap() {
        const jenis = document.getElementById('rekap-jenis').value;
        const tglTerpilih = document.getElementById('rekap-filter-tanggal').value;
        const tbody = document.getElementById('rekap-table-body');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-400">Mengkalkulasi ulang...</td></tr>';

        let backendJenis = (jenis === "bulanan") ? "bulanan" : "harian";

        let data;
        try {
          data = await apiFetch(`{{ route('uniformity.rekap') }}?jenis=${backendJenis}`);
        } catch (err) {
          tbody.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-red-500">Gagal memuat rekap: ${err.message}</td></tr>`;
          return;
        }

        if (!data || data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6">Tidak ada rekap.</td></tr>';
          if (myChart) myChart.destroy();
          return;
        }

        let dataFinal = data;
        if (jenis === 'pilih_hari' && tglTerpilih) { dataFinal = data.filter(row => row.periode === tglTerpilih); }
        if (dataFinal.length === 0) { tbody.innerHTML = `<tr><td colspan="4" class="text-center py-6">Data tidak ditemukan.</td></tr>`; if (myChart) myChart.destroy(); return; }

        tbody.innerHTML = '';
        const labels = [], dataUnder = [], dataMasuk = [], dataOver = [];

        dataFinal.forEach(row => {
          let un = bersihkanDanFormatPersen(row.undersize);
          let ms = bersihkanDanFormatPersen(row.sizeMasuk);
          let ov = bersihkanDanFormatPersen(row.oversize);

          labels.push(row.periode); dataUnder.push(un); dataMasuk.push(ms); dataOver.push(ov);

          tbody.innerHTML += `
            <tr class="hover:bg-gray-50 border-b">
              <td class="px-6 py-3 font-medium">${row.periode}</td>
              <td class="px-6 py-3 text-red-500 font-semibold">${un.toFixed(1)}%</td>
              <td class="px-6 py-3 text-green-600 font-semibold">${ms.toFixed(1)}%</td>
              <td class="px-6 py-3 text-yellow-600 font-semibold">${ov.toFixed(1)}%</td>
            </tr>`;
        });
        renderChart(labels, dataUnder, dataMasuk, dataOver);
        renderDetailRitPadaRekap(jenis, tglTerpilih);
      }

      function renderDetailRitPadaRekap(jenisFilter, parameterTanggal) {
        const tbodyDetail = document.getElementById('rekap-detail-rit-body');
        let dataTerfilter = cacheDataDashboardGlobal || [];

        if (jenisFilter === 'pilih_hari' && parameterTanggal) {
          dataTerfilter = dataTerfilter.filter(row => row.tanggal === parameterTanggal);
        } else if (jenisFilter === 'bulanan') {
          const targetBulan = parameterTanggal ? parameterTanggal.substring(0, 7) : new Date().toISOString().substring(0, 7);
          dataTerfilter = dataTerfilter.filter(row => row.tanggal && row.tanggal.substring(0, 7) === targetBulan);
        }

        if (dataTerfilter.length === 0) {
          tbodyDetail.innerHTML = '<tr><td colspan="9" class="text-center py-4">Tidak ada rincian data.</td></tr>';
          return;
        }

        tbodyDetail.innerHTML = '';
        dataTerfilter.forEach(row => {
          let underFmt = bersihkanDanFormatPersen(row.undersize).toFixed(1) + '%';
          let masukFmt = bersihkanDanFormatPersen(row.sizeMasuk).toFixed(1) + '%';
          let overFmt = bersihkanDanFormatPersen(row.oversize).toFixed(1) + '%';

          tbodyDetail.innerHTML += `
            <tr class="hover:bg-gray-50 border-b">
              <td class="px-2 py-2 text-center">${row.tanggal}</td>
              <td class="px-2 py-2 text-center font-bold">${row.noRit}</td>
              <td class="px-3 py-2 text-left truncate max-w-[150px]">${row.asalKandang}</td>
              <td class="px-2 py-2 text-center">${row.ekorDta}</td>
              <td class="px-2 py-2 text-center">${row.kgDta}</td>
              <td class="px-2 py-2 text-center">${row.jumlahSample}</td>
              <td class="px-2 py-2 text-center font-bold text-red-500">${underFmt}</td>
              <td class="px-2 py-2 text-center font-bold text-green-600">${masukFmt}</td>
              <td class="px-2 py-2 text-center font-bold text-yellow-600">${overFmt}</td>
            </tr>`;
        });
      }

      function renderChart(labels, underData, masukData, overData) {
        const ctx = document.getElementById('rekapChart').getContext('2d');
        if (myChart) myChart.destroy();
        myChart = new Chart(ctx, { type: 'bar', data: { labels: labels, datasets: [{ label: 'Undersize (%)', data: underData, backgroundColor: 'rgba(239, 68, 68, 0.85)' }, { label: 'Size Masuk (%)', data: masukData, backgroundColor: 'rgba(22, 163, 74, 0.85)' }, { label: 'Oversize (%)', data: overData, backgroundColor: 'rgba(202, 138, 4, 0.85)' }] }, options: { responsive: true, maintainAspectRatio: false, animation: false, scales: { y: { beginAtZero: true, max: 100 } } } });
      }

      function downloadPdfLaporan() {
        const jenis = document.getElementById('rekap-jenis').value;
        const tglTerpilih = document.getElementById('rekap-filter-tanggal').value;
        const btn = document.getElementById('btn-download-pdf');
        const elemenKonten = document.getElementById('sec-rekap');
        let stringInfoPeriode = "SEMUA PERIODE";
        let penamaanFile = `Laporan_Uniformity_Semua`;
        if (jenis === 'pilih_hari') { stringInfoPeriode = `HARI TENTU (${tglTerpilih})`; penamaanFile = `Uniformity_${tglTerpilih}`; }
        else if (jenis === 'bulanan') { stringInfoPeriode = "REKAPITULASI BULANAN"; penamaanFile = `Uniformity_Bulanan`; }

        document.getElementById('pdf-tanggal-cetak').innerText = new Date().toLocaleString('id-ID') + ' WIB';
        document.getElementById('pdf-info-jenis').innerText = stringInfoPeriode;

        const originalText = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = "⏳ Menghasilkan Laporan PDF...";
        document.getElementById('pdf-header-formal').classList.remove('hidden');
        document.getElementById('pdf-footer-formal').classList.remove('hidden');
        document.getElementById('kontrol-rekap-area').classList.add('hidden');
        document.getElementById('link-kembali').classList.add('hidden');
        elemenKonten.classList.add('pdf-mode');
        if (myChart) myChart.resize();

        const opsi = { margin: [0.4, 0.4, 0.4, 0.4], filename: `${penamaanFile}.pdf`, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2, useCORS: true, logging: false }, jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }, pagebreak: { mode: ['css', 'legacy'], before: '.page-break' } };

        setTimeout(() => {
          html2pdf().set(opsi).from(elemenKonten).save().then(() => {
            document.getElementById('pdf-header-formal').classList.add('hidden');
            document.getElementById('pdf-footer-formal').classList.add('hidden');
            document.getElementById('kontrol-rekap-area').classList.remove('hidden');
            document.getElementById('link-kembali').classList.remove('hidden');
            elemenKonten.classList.remove('pdf-mode');
            if (myChart) myChart.resize();
            btn.disabled = false; btn.innerHTML = originalText;
          }).catch(err => { alert("Gagal memproses file PDF: " + err); btn.disabled = false; btn.innerHTML = originalText; });
        }, 500);
      }
    </script>
  </body>
</html>
