<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Penerimaan LB</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
 
    @keyframes neon-glow-blue {
      0%, 100% { box-shadow: 0 0 5px #2563eb, 0 0 12px #3b82f6; opacity: 1; }
      50% { box-shadow: 0 0 2px #2563eb, 0 0 4px #3b82f6; opacity: 0.8; }
    }
    .neon-update-active { animation: neon-glow-blue 1.2s ease-in-out infinite; background-color: #2563eb; color: white; border: 1px solid #60a5fa; }
 
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  </style>
</head>
<body class="text-gray-800 text-sm">
 
  <div class="bg-white shadow-sm border-b px-4 py-3 flex flex-wrap justify-center items-center gap-4 font-medium text-gray-500 mb-6">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 px-3 py-2 rounded-lg text-xs font-bold transition">
        ← Dashboard Utama
    </a>
    <span class="text-gray-300">|</span>
    <a href="{{ route('lbreport.dashboard') }}" class="text-blue-600 bg-blue-50 px-3 py-1.5 rounded-md text-xs font-bold">Dashboard LB</a>
    <a href="{{ route('lbreport.workspace') }}" class="hover:text-blue-600 text-xs">Input / Hanging (Login)</a>
  </div>
 
  <div class="p-4 max-w-7xl mx-auto">
 
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5 flex flex-wrap justify-between items-center gap-4">
      <div class="flex items-center gap-4">
        <div class="bg-white border p-2 rounded-lg w-14 h-14 flex items-center justify-center shrink-0">
          <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-full h-auto object-contain">
        </div>
        <div>
          <div class="flex items-center flex-wrap gap-2.5">
            <h1 class="text-xl font-bold text-gray-800">MONITORING PENERIMAAN LIVE BIRDS</h1>
            <span id="mainNeonBadge" class="text-[10px] px-2.5 py-0.5 rounded-full font-bold transition-all duration-300 hidden neon-update-active shadow-md uppercase tracking-wider">
              <i class="fas fa-sync-alt fa-spin mr-1"></i> Update Baru
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-0.5">Sistem Terintegrasi Live Birds</p>
        </div>
      </div>
 
      <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <input type="text" id="filterPO" placeholder="Filter by No PO" class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 flex-1 md:flex-none uppercase" onchange="resetAndLoadData()" title="Jika diisi, filter tanggal akan diabaikan">
        <input type="date" id="filterTanggal" class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 flex-1 md:flex-none" onchange="resetAndLoadData()">
 
        <button onclick="loadRekapData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition">
          <i class="fas fa-sync-alt mr-1"></i> Refresh
        </button>
        <button onclick="downloadExcelHarian()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition">
          <i class="fas fa-file-excel mr-1"></i> Excel
        </button>
      </div>
    </div>
 
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
      <div class="flex justify-between items-center mb-4 border-b pb-3">
        <div class="flex items-center gap-3">
          <h2 class="text-lg font-bold text-gray-700" id="titleHarianText">Rekapitulasi Data</h2>
          <span id="headerBadgeUpdate" class="text-xs font-bold px-2 py-1 rounded bg-green-100 text-green-700 hidden animate-pulse border border-green-300">
            <i class="fas fa-bell mr-1"></i> DATA BARU MASUK!
          </span>
        </div>
      </div>
 
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 border p-4 rounded-lg"><div class="text-xs text-gray-500 font-bold uppercase mb-1">Total Kg Netto</div><div class="text-2xl font-black text-gray-800" id="hKgNetto">0.00</div></div>
        <div class="bg-gray-50 border p-4 rounded-lg"><div class="text-xs text-gray-500 font-bold uppercase mb-1">Total Ekor Netto</div><div class="text-2xl font-black text-gray-800" id="hEkorNetto">0</div></div>
        <div class="bg-red-50 border border-red-100 p-4 rounded-lg"><div class="text-xs text-red-500 font-bold uppercase mb-1">Ayam Mati</div><div class="text-2xl font-black text-red-600" id="hMati">0</div></div>
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg"><div class="text-xs text-blue-500 font-bold uppercase mb-1">Global Susut</div><div class="text-2xl font-black text-blue-600" id="hSusut">0.00%</div></div>
      </div>
 
      <h3 class="text-sm font-bold text-gray-600 mb-2 uppercase">Rincian Ritase</h3>
      <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 text-gray-600 border-b">
            <tr>
              <th class="p-3">Tanggal / Jam</th>
              <th class="p-3">No PO / No Rit</th>
              <th class="p-3">Asal / Area</th>
              <th class="p-3">Kg DTA</th>
              <th class="p-3">Ek DTA</th>
              <th class="p-3">Kg Net</th>
              <th class="p-3">Ek Net</th>
              <th class="p-3">Mati</th>
              <th class="p-3">Susut</th>
              <th class="p-3">Aksi</th>
            </tr>
          </thead>
          <tbody id="tabelRincianBody" class="divide-y">
            <tr><td colspan="10" class="text-center p-4 text-gray-500">Memuat data...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
 
  <div id="modalDetail" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-2 md:p-6 transition-all duration-300">
    <div class="bg-slate-100 w-full max-w-6xl max-h-[95vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-white/20">
 
      <div class="bg-white px-6 py-4 border-b flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
            <i class="fas fa-circle text-blue-500 text-[10px]"></i> Data Summary Per Truk
          </h2>
          <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-1 font-bold uppercase tracking-wider">
            <span>Dashboard</span> <i class="fas fa-chevron-right text-[8px]"></i>
            <span>Penerimaan</span> <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-blue-500" id="mdBreadcrumbRit">...</span>
          </div>
        </div>
      </div>
 
      <div class="bg-white px-6 py-3 border-b flex flex-wrap justify-between items-center gap-3">
        <div class="flex items-center gap-4">
          <div class="bg-slate-100 p-2.5 rounded-xl shadow-inner border border-slate-200"><i class="fas fa-truck-moving text-slate-500 text-lg"></i></div>
          <div>
            <div class="text-xs font-black text-slate-800 uppercase tracking-wide" id="mdNoSppaHeader">...</div>
            <div class="flex flex-wrap items-center gap-2 mt-0.5">
              <span class="text-[10px] text-slate-500 font-semibold uppercase">PO: <b class="text-blue-600" id="mdPoHeader">...</b></span>
              <span class="w-1 h-1 rounded-full bg-slate-300"></span>
              <span class="text-[10px] text-slate-500 font-semibold uppercase">Ritase: <b class="text-blue-600" id="mdRitLabel">...</b></span>
              <span class="w-1 h-1 rounded-full bg-slate-300"></span>
              <span class="text-[10px] text-slate-500 font-semibold uppercase">Polisi: <b class="text-slate-700" id="mdNoPolisiHeader">...</b></span>
            </div>
          </div>
        </div>
 
        <div class="flex items-center gap-2">
          <button onclick="downloadPDFModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-md shadow-red-200">
            <i class="fas fa-file-pdf"></i> Export PDF
          </button>
          <button onclick="tutupDetail()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
            <i class="fas fa-times"></i> Tutup
          </button>
        </div>
      </div>
 
      <div id="modalContentToPrint" class="overflow-y-auto p-4 md:p-6">
        <div class="grid grid-cols-12 gap-6">
 
          <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">
              <div class="bg-slate-50 px-5 py-3 border-b flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest flex items-center gap-2">
                  <i class="fas fa-file-invoice text-blue-500"></i> DTA (Surat Jalan)
                </span>
              </div>
 
              <div class="p-5 grid grid-cols-2 gap-y-5 gap-x-4">
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tanggal</div>
                  <div class="text-xs font-bold text-slate-800" id="mdTanggal">...</div>
                </div>
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">No Ritase</div>
                  <div class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded inline-block border border-blue-100" id="mdNoRit">...</div>
                </div>
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Ekspedisi</div>
                  <div class="text-xs font-bold text-slate-800" id="mdEkspedisi">...</div>
                </div>
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">No Polisi</div>
                  <div class="text-xs font-bold text-slate-800" id="mdNoPolisi">...</div>
                </div>
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Farm / Area</div>
                  <div class="text-xs font-black text-slate-700" id="mdArea">...</div>
                </div>
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Size</div>
                  <div class="text-xs font-bold text-slate-800" id="mdSize">...</div>
                </div>
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">No SPPA</div>
                  <div class="text-xs font-bold text-slate-800" id="mdNoSppa">...</div>
                </div>
                <div>
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Kg Basah</div>
                  <div class="text-xs font-bold text-slate-800" id="mdKgBasah">...</div>
                </div>
                <div class="col-span-2">
                  <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Jam Truk Datang</div>
                  <div class="text-xs font-bold text-slate-800 flex items-center gap-1"><i class="far fa-clock text-slate-400"></i> <span id="mdJamDatang">...</span></div>
                </div>
              </div>
 
              <div class="px-5 pb-5 mt-auto">
                <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                   <div class="text-center border-r border-slate-200">
                      <div class="text-[15px] font-black text-slate-700" id="mdTotalEkor">0</div>
                      <div class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">Tot. Ekor</div>
                   </div>
                   <div class="text-center border-r border-slate-200">
                      <div class="text-[15px] font-black text-slate-700" id="mdTotalKg">0</div>
                      <div class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">Tot. Kg</div>
                   </div>
                   <div class="text-center">
                      <div class="text-[15px] font-black text-blue-600" id="mdAbw">0</div>
                      <div class="text-[8px] font-bold text-blue-400 uppercase mt-0.5">ABW</div>
                   </div>
                </div>
              </div>
 
              <div class="bg-blue-50/50 px-5 py-3 border-t border-slate-100 flex justify-between items-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Keterangan</span>
                <span class="text-xs font-medium text-slate-800 tracking-wide italic" id="mdKeterangan">...</span>
              </div>
            </div>
 
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <div class="bg-slate-50 px-5 py-3 border-b flex items-center gap-2">
                <i class="fas fa-stopwatch text-orange-500"></i>
                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Waktu Proses / Hanging</span>
              </div>
              <div class="p-5">
                 <div class="relative flex justify-between items-center bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2 w-16 border-t-2 border-dashed border-slate-300"></div>
                    <div class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2 bg-white text-slate-400 rounded-full p-1 border border-slate-200 z-10">
                      <i class="fas fa-chevron-right text-[8px]"></i>
                    </div>
                    <div class="w-1/2 pr-6 relative z-20">
                       <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center mb-1.5">Mulai Bongkar</div>
                       <div class="bg-white text-orange-600 text-center py-2.5 rounded-lg border border-orange-200 font-black text-sm shadow-sm" id="mdJamBongkar">...</div>
                    </div>
                    <div class="w-1/2 pl-6 relative z-20">
                       <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center mb-1.5">Proses Selesai</div>
                       <div class="bg-white text-emerald-600 text-center py-2.5 rounded-lg border border-emerald-200 font-black text-sm shadow-sm" id="mdJamSelesai">...</div>
                    </div>
                 </div>
              </div>
            </div>
          </div>
 
          <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
              <div class="absolute top-0 right-0 p-4 opacity-5"><i class="fas fa-calculator text-8xl"></i></div>
              <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="fas fa-list-ol text-blue-500"></i> Ringkasan Perhitungan Ayam
              </h3>
 
              <div class="space-y-4 mb-8">
                  <div class="flex justify-between items-center text-slate-600 border-b border-slate-50 pb-2">
                    <span class="font-medium text-xs">Target Total Ekor (DTA)</span>
                    <span class="font-black text-slate-800 text-lg" id="mdEkorDta">0</span>
                 </div>
                 <div class="flex justify-between items-center text-slate-400 italic">
                    <span class="text-[11px]">Catatan: Data berdasarkan input surat jalan awal.</span>
                 </div>
              </div>
 
               <div class="bg-emerald-500 rounded-2xl p-8 text-white flex flex-col md:flex-row justify-between items-center gap-6 shadow-xl shadow-emerald-100/50">
                 <div class="text-center md:text-left">
                    <div class="text-[10px] font-bold opacity-80 uppercase tracking-widest mb-1">Jumlah Ayam Diterima</div>
                    <div class="text-6xl font-black tracking-tight" id="mdAyamDiterima">0</div>
                 </div>
                 <div class="flex flex-col items-center md:items-end gap-2">
                    <div id="mdBadgeStatusHanging" class="bg-white/20 backdrop-blur-md px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/30">
                       <i class="fas fa-check-circle mr-1"></i> <span id="mdStatusHanging">...</span>
                     </div>
                    <div class="text-right mt-1">
                       <div class="text-[10px] font-bold opacity-70 uppercase tracking-widest">Selisih Real</div>
                       <div class="text-2xl font-black" id="mdSelisih">0 <span class="text-xs opacity-60">Ekor</span></div>
                     </div>
                 </div>
              </div>
            </div>
 
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div class="bg-white p-6 rounded-2xl border border-rose-100 shadow-sm relative overflow-hidden transition hover:shadow-md">
                   <div class="absolute -right-4 -bottom-4 opacity-[0.03] rotate-12 text-rose-500"><i class="fas fa-skull text-9xl"></i></div>
                   <h3 class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-4">Ayam Mati</h3>
                  <div class="flex items-center gap-5">
                     <div class="w-16 h-16 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 text-2xl font-black border border-rose-100 shadow-inner" id="mdMati">0</div>
                     <div>
                        <div class="text-lg font-black text-slate-800">Ayam Mati</div>
                        <div class="text-[9px] font-bold text-rose-400 uppercase tracking-wider mt-0.5">Terdeteksi Saat Bongkar</div>
                      </div>
                  </div>
               </div>
 
               <div class="bg-white p-6 rounded-2xl border border-blue-100 shadow-sm relative overflow-hidden transition hover:shadow-md">
                  <div class="absolute -right-4 -bottom-4 opacity-[0.03] -rotate-12 text-blue-500"><i class="fas fa-weight-hanging text-9xl"></i></div>
                  <h3 class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-4">Ayam Retur / Undersize</h3>
                  <div class="flex items-center gap-5">
                     <div class="min-w-[80px] h-16 px-4 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 text-2xl font-black border border-blue-100 shadow-inner" id="mdUndersizeKg">0.00</div>
                     <div>
                        <div class="text-lg font-black text-slate-800">Total Reject</div>
                        <div class="text-[9px] font-bold text-blue-400 uppercase tracking-wider mt-0.5">Dalam Satuan Kilogram</div>
                     </div>
                  </div>
                </div>
            </div>
 
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
               <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                 <i class="fas fa-balance-scale text-emerald-500"></i> Persentase Uniformity
               </h3>
               <div class="grid grid-cols-3 gap-4">
                   <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 text-center">
                     <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Undersize</div>
                     <div class="text-xl font-black text-slate-700" id="uUndersize">-</div>
                  </div>
                  <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-center shadow-inner">
                     <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-2">Size Masuk</div>
                     <div class="text-2xl font-black text-emerald-600" id="uSizeMasuk">-</div>
                  </div>
                  <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 text-center">
                     <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Oversize</div>
                     <div class="text-xl font-black text-slate-700" id="uOversize">-</div>
                  </div>
               </div>
               <div class="mt-6 pt-5 border-t border-slate-100 flex justify-between items-center px-2">
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Berat Reject (Analisa Susut)</span>
                  <span class="text-sm font-black text-rose-500 bg-rose-50 px-3 py-1 rounded-lg border border-rose-100" id="mdBeratReject">0.00 Kg</span>
               </div>
            </div>
 
          </div>
        </div>
      </div>
    </div>
  </div>
 
  <script>
    let currentDataState = "";
    window.addEventListener('load', () => {
      document.getElementById('filterTanggal').value = new Date().toISOString().split('T')[0];
      loadRekapData();
      setInterval(loadRekapData, 30000);
    });
    document.addEventListener('input', function(e) {
      if (e.target.tagName === 'INPUT' && (e.target.type === 'text' || e.target.type === 'search') && e.target.id !== 'filterTanggal') {
        let start = e.target.selectionStart;
        let end = e.target.selectionEnd;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(start, end);
      }
    });
    function resetAndLoadData() {
      currentDataState = "";
      document.getElementById('headerBadgeUpdate').classList.add('hidden');
      document.getElementById('mainNeonBadge').classList.add('hidden');
      loadRekapData();
    }
    function bersihkanFormatJam(waktuStr) {
      if (!waktuStr) return ["-", "-"];
      let str = String(waktuStr);
      if (str === "-" || str === "Belum diproses") return ["-", "-"];
      let polaWaktu = str.match(/\d{2}:\d{2}(:\d{2})?/g);
      if (polaWaktu && polaWaktu.length >= 2) return [polaWaktu[0], polaWaktu[1]];
      else if (polaWaktu && polaWaktu.length === 1) return [polaWaktu[0], "-"];
      return ["-", "-"];
    }
 
    async function loadRekapData() {
      let tgl = document.getElementById('filterTanggal').value;
      let po = document.getElementById('filterPO').value.trim();
 
      if (!tgl && !po) return;
 
      if (currentDataState === "") {
        document.getElementById('tabelRincianBody').innerHTML = '<tr><td colspan="10" class="text-center p-4">Memuat data...</td></tr>';
      }
 
      let res;
      try {
        const params = new URLSearchParams();
        if (po) { params.set('po', po); } else { params.set('tanggal', tgl); }
        const response = await fetch(`{{ route('lbreport.rekap-data') }}?${params.toString()}`);
        res = await response.json();
      } catch (err) {
        document.getElementById('tabelRincianBody').innerHTML = `<tr><td colspan="10" class="text-center p-4 text-red-500 font-bold">Error Server: ${err.message}</td></tr>`;
        return;
      }
 
      if (!res || !res.harian) {
        document.getElementById('tabelRincianBody').innerHTML = '<tr><td colspan="10" class="text-center p-4 text-red-500 font-bold">Gagal memuat data. Pastikan filter tanggal/PO sudah benar.</td></tr>';
        return;
      }
 
      let newDataState = JSON.stringify(res.harian.rincianRit);
      if (currentDataState !== "" && newDataState !== currentDataState) {
        document.getElementById('headerBadgeUpdate').classList.remove('hidden');
        document.getElementById('mainNeonBadge').classList.remove('hidden');
        setTimeout(() => {
          document.getElementById('headerBadgeUpdate').classList.add('hidden');
          document.getElementById('mainNeonBadge').classList.add('hidden');
        }, 15000);
      }
      currentDataState = newDataState;
 
      document.getElementById('hKgNetto').innerText = (res.harian.kgNetto || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
      document.getElementById('hEkorNetto').innerText = (res.harian.ekorNetto || 0).toLocaleString('id-ID');
      document.getElementById('hMati').innerText = (res.harian.mati || 0).toLocaleString('id-ID');
      document.getElementById('hSusut').innerText = (res.harian.persenSusut || 0).toFixed(2) + "%";
 
      let tbody = document.getElementById('tabelRincianBody');
      tbody.innerHTML = "";
 
      if (res.harian.rincianRit.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center p-4">Tidak ada data untuk tanggal/PO ini.</td></tr>';
      } else {
        res.harian.rincianRit.forEach(rit => {
          let stdSusut = 4.0;
          if (rit.area === "Area 1") stdSusut = 2.5;
          else if (rit.area === "Area 2") stdSusut = 3.0;
          else if (rit.area === "Area 3") stdSusut = 3.5;
          else if (rit.area === "Area 4") stdSusut = 4.0;
 
          let colorClass = (rit.susutPercent || 0) > stdSusut ? 'text-red-500' : 'text-emerald-500';
 
          tbody.innerHTML += `
            <tr class="hover:bg-gray-50 transition border-b">
              <td class="p-3"><div class="font-bold">${rit.tanggal}</div><div class="text-[10px] text-gray-500">${rit.jam || "-"}</div></td>
              <td class="p-3 font-bold text-blue-600"><div class="text-[10px] text-gray-400 font-normal uppercase">${rit.po || "-"}</div>${rit.noRit || "-"}</td>
              <td class="p-3"><div class="font-bold">${rit.asal || "-"}</div><div class="text-[10px] text-gray-500">${rit.area || "-"}</div></td>
              <td class="p-3">${(rit.kgDta || 0).toLocaleString()}</td>
              <td class="p-3">${(rit.ekorDta || 0).toLocaleString()}</td>
              <td class="p-3 font-bold text-slate-800">${(rit.kgNetto || 0).toLocaleString()}</td>
              <td class="p-3 font-bold">${(rit.ekorNetto || 0).toLocaleString()}</td>
              <td class="p-3 text-rose-500 font-bold bg-rose-50/30">${rit.mati || 0}</td>
              <td class="p-3 font-bold ${colorClass}">${(rit.susutPercent || 0).toFixed(2)}%</td>
              <td class="p-3"><button onclick="bukaDetail('${rit.noRit}')" class="bg-white text-blue-600 hover:bg-blue-50 px-3 py-1 rounded-lg text-xs font-bold border border-blue-200 shadow-sm transition">Detail</button></td>
            </tr>`;
        });
      }
    }
 
    async function bukaDetail(noRit) {
      document.getElementById('modalDetail').classList.remove('hidden');
      document.getElementById('modalDetail').classList.add('flex');
 
      const elementsToReset = ['mdBreadcrumbRit', 'mdRitLabel', 'mdTanggal', 'mdNoRit', 'mdSize', 'mdEkspedisi', 'mdNoPolisi', 'mdArea', 'mdJamDatang', 'mdTotalEkor', 'mdTotalKg', 'mdAbw', 'mdNoSppa', 'mdKgBasah', 'mdKeterangan', 'mdAyamDiterima', 'mdEkorDta', 'mdSelisih', 'mdStatusHanging', 'mdMati', 'mdUndersizeKg', 'mdBeratReject', 'uUndersize', 'uSizeMasuk', 'uOversize', 'mdJamBongkar', 'mdJamSelesai', 'mdNoSppaHeader', 'mdNoPolisiHeader', 'mdPoHeader'];
      elementsToReset.forEach(id => {
        let el = document.getElementById(id);
        if (el) el.innerText = "...";
      });
      let tglActive = document.getElementById('filterTanggal').value;
      let poActive = document.getElementById('filterPO').value.trim();
 
      let data;
      try {
        const params = new URLSearchParams({ no_rit: noRit, tanggal: tglActive, po: poActive });
        const response = await fetch(`{{ route('lbreport.detail') }}?${params.toString()}`);
        data = await response.json();
      } catch (err) {
        alert("Gagal: " + err.message); tutupDetail(); return;
      }
 
      if (!data || data.error) {
        alert(data && data.error ? data.error : "Data detail tidak ditemukan.");
        tutupDetail();
        return;
      }
 
      document.getElementById('mdBreadcrumbRit').innerText = data.noRit || "-";
      document.getElementById('mdRitLabel').innerText = data.noRit || "-";
      document.getElementById('mdNoSppaHeader').innerText = data.noSppa || "-";
      document.getElementById('mdNoPolisiHeader').innerText = data.noPolisi || "-";
      document.getElementById('mdPoHeader').innerText = data.po || "-";
 
      document.getElementById('mdTanggal').innerText = data.tanggal || "-";
      document.getElementById('mdNoRit').innerText = data.noRit || "-";
      document.getElementById('mdSize').innerText = data.size || "-";
      document.getElementById('mdEkspedisi').innerText = data.ekspedisi || "-";
      document.getElementById('mdNoPolisi').innerText = data.noPolisi || "-";
      document.getElementById('mdArea').innerText = data.area || "-";
      document.getElementById('mdNoSppa').innerText = data.noSppa || "-";
      document.getElementById('mdKgBasah').innerText = data.kgBasah ? parseFloat(data.kgBasah).toLocaleString('id-ID', {minimumFractionDigits: 1}) + " Kg" : "0.0 Kg";
      document.getElementById('mdJamDatang').innerText = data.jamDatang || "-";
      document.getElementById('mdKeterangan').innerText = data.keterangan || "-";
 
      document.getElementById('mdTotalEkor').innerText = (data.totalEkorDta || 0).toLocaleString('id-ID');
      document.getElementById('mdTotalKg').innerText = (data.totalKgDta || 0).toLocaleString('id-ID');
      document.getElementById('mdAbw').innerText = data.abw || "0.00";
 
      let jamSplit = bersihkanFormatJam(data.jamHanging);
      document.getElementById('mdJamBongkar').innerText = jamSplit[0];
      document.getElementById('mdJamSelesai').innerText = jamSplit[1];
 
      document.getElementById('mdAyamDiterima').innerText = (data.ayamDiterima || 0).toLocaleString('id-ID');
      document.getElementById('mdEkorDta').innerText = (data.totalEkorDta || 0).toLocaleString('id-ID');
 
      let selisihVal = data.selisihEkor || 0;
      document.getElementById('mdSelisih').innerText = Math.abs(selisihVal) + (selisihVal < 0 ? " (Lebih)" : " (Kurang)");
 
      let stHanging = data.statusHanging || "-";
      document.getElementById('mdStatusHanging').innerText = stHanging;
      let badge = document.getElementById('mdBadgeStatusHanging');
      badge.className = stHanging === "KURANG" ? "bg-rose-500 rounded-full px-4 py-1.5 text-[10px] font-black tracking-widest border border-white/20 shadow-md" : "bg-emerald-600 rounded-full px-4 py-1.5 text-[10px] font-black tracking-widest border border-white/20 shadow-md";
      document.getElementById('mdMati').innerText = (data.mati || 0);
      document.getElementById('mdUndersizeKg').innerText = (data.undersizeKg || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
      document.getElementById('mdBeratReject').innerText = (data.beratRejectTotal || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + " Kg";
 
      document.getElementById('uUndersize').innerText = data.uniUndersize || "0%";
      document.getElementById('uSizeMasuk').innerText = data.uniSizeMasuk || "0%";
      document.getElementById('uOversize').innerText = data.uniOversize || "0%";
    }
 
    function tutupDetail() {
      document.getElementById('modalDetail').classList.add('hidden');
      document.getElementById('modalDetail').classList.remove('flex');
    }
 
    function downloadPDFModal() {
      const element = document.getElementById('modalContentToPrint');
      const rit = document.getElementById('mdRitLabel').innerText;
      const opt = {
        margin: 10, filename: 'Summary_Rit_'+rit+'.pdf', image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };
      html2pdf().set(opt).from(element).save();
    }
 
    async function downloadExcelHarian() {
      let tgl = document.getElementById('filterTanggal').value;
      try {
        const response = await fetch(`{{ route('lbreport.raw-data') }}?tanggal=${tgl}`);
        const dataAoa = await response.json();
        const ws = XLSX.utils.aoa_to_sheet(dataAoa);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Rekap_Harian");
        XLSX.writeFile(wb, "Data_Penerimaan_" + tgl + ".xlsx");
      } catch (err) {
        alert("Gagal mengunduh Excel: " + err.message);
      }
    }
  </script>
</body>
</html>