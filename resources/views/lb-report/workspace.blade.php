<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Workspace - Report Harian LB</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    input[type="text"], input[type="number"], input[type="time"], input[type="date"], select { font-size: 16px; }

    @keyframes neon-red {
      0%, 100% { box-shadow: 0 0 5px #ef4444, 0 0 15px #ef4444; border-color: #ef4444; color: #b91c1c; }
      50% { box-shadow: 0 0 2px #f87171, 0 0 8px #f87171; border-color: #f87171; color: #dc2626; }
    }
    @keyframes neon-green {
      0%, 100% { box-shadow: 0 0 5px #10b981, 0 0 15px #10b981; border-color: #10b981; color: #047857; }
      50% { box-shadow: 0 0 2px #34d399, 0 0 8px #34d399; border-color: #34d399; color: #059669; }
    }
    .neon-kurang { animation: neon-red 1.5s ease-in-out infinite; border: 2px solid; background-color: #fef2f2; }
    .neon-overpas { animation: neon-green 1.5s ease-in-out infinite; border: 2px solid; background-color: #ecfdf5; }

    @keyframes neon-glow {
      0%, 100% { box-shadow: 0 0 5px #ef4444, 0 0 10px #ef4444; opacity: 1; }
      50% { box-shadow: 0 0 2px #ef4444, 0 0 4px #ef4444; opacity: 0.8; }
    }
    .neon-active { animation: neon-glow 1.5s ease-in-out infinite; background-color: #ef4444; color: white; border: 1px solid #f87171; }

    .sticky-thead th { position: sticky; top: 0; background-color: #f9fafb; z-index: 10; box-shadow: inset 0 -1px 0 #e5e7eb; }

    .tab-btn { padding: 0.75rem 1rem; font-weight: 700; font-size: 0.875rem; color: #6b7280; border-bottom: 2px solid transparent; }
    .tab-btn.active { color: #2563eb; border-color: #2563eb; }

    .modern-popup{ box-shadow:0 25px 60px rgba(0,0,0,.18)!important; }
    .modern-confirm-btn{ border-radius:12px!important; padding:12px 24px!important; font-size:15px!important; font-weight:600!important; }
    .modern-cancel-btn{ border-radius:12px!important; padding:12px 24px!important; font-size:15px!important; font-weight:600!important; }
  </style>
</head>
<body class="text-gray-800 text-xs sm:text-sm">

@php
    $user = auth()->guard('tally')->user();
    $roleNames = $user->roles->pluck('name');
    $canSebelum = $roleNames->intersect(['lb_penerimaan_awal', 'supervisor'])->isNotEmpty();
    $canSetelah = $roleNames->intersect(['lb_penerimaan_akhir', 'supervisor'])->isNotEmpty();
    $canHanging = $roleNames->intersect(['lb_hanging', 'supervisor'])->isNotEmpty();
    $tabCount = collect([$canSebelum, $canSetelah, $canHanging])->filter()->count();
    $defaultTab = $canSebelum ? 'sebelum' : ($canSetelah ? 'setelah' : 'hanging');
@endphp

<div class="bg-white shadow-sm border-b px-4 py-3 flex flex-wrap justify-between items-center gap-3">
  <div class="flex items-center gap-3">
    <a href="{{ route('lbreport.dashboard') }}" class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-lg text-xs font-bold transition">← Dashboard LB</a>
  </div>
  <div class="flex items-center gap-3">
    <div class="text-right">
      <p class="text-[10px] text-gray-400">Login sebagai,</p>
      <p class="font-bold text-blue-600 text-xs">{{ $user->name }} <span class="text-gray-400 font-normal">({{ $roleNames->implode(', ') }})</span></p>
    </div>
    <form id="logoutForm" method="POST" action="{{ route('lbreport.logout') }}">
      @csrf
      <button type="button" onclick="confirmLogout()" class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-md text-xs font-bold transition">
        <i class="fas fa-sign-out-alt mr-1"></i> Logout
      </button>
    </form>
  </div>
</div>

<div class="p-3 sm:p-6 max-w-7xl mx-auto">

  @if ($tabCount > 1)
    <div class="flex bg-white border rounded-lg mb-6 overflow-x-auto">
      @if ($canSebelum)
        <button id="tabbtn-sebelum" onclick="switchWorkspaceTab('sebelum')" class="tab-btn">1. Sebelum Bongkar</button>
      @endif
      @if ($canSetelah)
        <button id="tabbtn-setelah" onclick="switchWorkspaceTab('setelah')" class="tab-btn">2. Setelah Bongkar</button>
      @endif
      @if ($canHanging)
        <button id="tabbtn-hanging" onclick="switchWorkspaceTab('hanging')" class="tab-btn">3. Hanging / Counter</button>
      @endif
    </div>
  @endif

  {{-- ==================== SECTION: SEBELUM BONGKAR ==================== --}}
  @if ($canSebelum)
  <div id="tabsec-sebelum" class="bg-white p-5 md:p-6 rounded-xl shadow-sm border">
    <h2 class="text-lg font-bold text-blue-600 border-b pb-3 mb-5"><i class="fas fa-truck mr-2"></i>Data Logistik Awal (DTA)</h2>
    <form id="formSebelum" onsubmit="submitSebelum(this); return false;" class="auto-enter">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5 mb-6">
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Tanggal</label><input type="date" name="tanggal" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200 outline-none" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Nomor Rit</label><input type="text" name="no_rit" placeholder="RIT-01" class="w-full border rounded-xl p-3 uppercase focus:ring-2 focus:ring-blue-200 outline-none" required></div>
        <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 mb-1.5">Nomor PO (Production Order By SAP)</label>
<select name="no_po" id="dropdown_no_po" class="w-full border rounded-xl p-3 font-bold uppercase text-blue-600 bg-blue-50 focus:ring-2 focus:ring-blue-300 outline-none shadow-inner" required>
    <option value="">-- Memuat daftar PO... --</option>
</select>
</div>

        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Area</label>
          <select name="area" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200 outline-none" required>
            <option value="Area 1">Area 1</option><option value="Area 2">Area 2</option><option value="Area 3">Area 3</option><option value="Area 4">Area 4</option>
          </select>
        </div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Farm / Supplier</label><input type="text" name="farm" placeholder="Nama Supplier" class="w-full border rounded-xl p-3 uppercase focus:ring-2 focus:ring-blue-200 outline-none" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Size</label><input type="text" name="size" placeholder="1.10 - 1.20" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200 outline-none" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Jam Datang</label><input type="time" name="jam_kedatangan" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200 outline-none" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Ekspedisi</label><input type="text" name="ekspedisi" class="w-full border rounded-xl p-3 uppercase focus:ring-2 focus:ring-blue-200 outline-none"></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">No Polisi</label><input type="text" name="no_polisi" class="w-full border rounded-xl p-3 uppercase focus:ring-2 focus:ring-blue-200 outline-none"></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Kg DTA</label><input type="number" step="0.1" name="kg_dta" placeholder="0.0" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200 outline-none" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Ekor DTA</label><input type="number" name="ekor_dta" placeholder="0" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-200 outline-none" required></div>
      </div>
      <button type="submit" id="btnSimpanSebelum" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition uppercase tracking-wide text-base shadow-md">Simpan Data Awal</button>
    </form>
  </div>
  @endif

  {{-- ==================== SECTION: SETELAH BONGKAR ==================== --}}
  @if ($canSetelah)
  <div id="tabsec-setelah" class="bg-white p-5 md:p-6 rounded-xl shadow-sm border">
     <h2 class="text-lg font-bold text-emerald-600 border-b pb-3 mb-5"><i class="fas fa-balance-scale mr-2"></i>Data Hasil Timbang</h2>

    <div class="mb-6 bg-gray-50 p-4 rounded-xl border">
      <label class="block text-xs font-bold text-gray-500 mb-2"><i class="fas fa-search mr-1"></i> PENELUSURAN NOMOR PO</label>

      <div class="flex flex-col sm:flex-row gap-2 mb-3">
        <input type="text" id="search_po_setelah" placeholder="Masukkan No PO (Ex: PO-123)" class="flex-1 border rounded-xl p-3 uppercase focus:ring-2 focus:ring-emerald-200 outline-none" onkeydown="if(event.key==='Enter') cariDaftarPO()">
        <button type="button" onclick="cariDaftarPO()" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-3 rounded-xl font-bold transition shadow-sm whitespace-nowrap"><i class="fas fa-list"></i> Tampilkan Rit</button>
      </div>

      <div id="listRitByPo" class="hidden mb-3 grid grid-cols-1 sm:grid-cols-2 gap-2 bg-white p-3 rounded border max-h-48 overflow-y-auto shadow-inner"></div>

      <div class="flex items-center gap-2 mb-3 mt-4"><div class="h-px bg-gray-300 flex-1"></div><span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Pencarian Klasik</span><div class="h-px bg-gray-300 flex-1"></div></div>

      <div class="flex flex-col sm:flex-row gap-3">
        <input type="date" id="search_tanggal" class="border rounded-xl p-3 focus:ring-2 focus:ring-emerald-200 outline-none sm:w-1/3 text-gray-600" title="Tanggal Pencarian">
        <input type="text" id="search_no_rit" placeholder="Nomor Rit (Ex: RIT-01)" class="flex-1 border rounded-xl p-3 uppercase focus:ring-2 focus:ring-emerald-200 outline-none" onkeydown="if(event.key==='Enter') cariDataAwalRit()">
        <button type="button" id="btnCariRit" onclick="cariDataAwalRit()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-3 rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-sm"><i class="fas fa-search"></i> Proses Rit</button>
      </div>
    </div>

    <form id="formSetelah" onsubmit="submitSetelah(this); return false;" class="hidden auto-enter">
      <input type="hidden" name="tanggal_update" id="tanggal_update">
      <input type="hidden" name="no_rit_update" id="no_rit_update">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5 mb-6">
        <div>
          <div class="flex justify-between items-center mb-1.5">
            <label class="block text-xs font-bold text-gray-500">Ekor Netto (Otomatis dari Hanging)</label>
            <button type="button" id="btnLihatDetailHanging" onclick="bukaPopupDetailHanging()" class="hidden text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1 bg-blue-50 px-2 py-0.5 rounded transition shadow-sm border border-blue-100">
              <i class="fas fa-eye"></i> Lihat Rincian Hanging
            </button>
          </div>
          <input type="number" name="ekor_netto" id="ekor_netto" class="w-full border border-gray-200 bg-gray-100 rounded-xl p-3 text-gray-500 cursor-not-allowed" readonly required>
        </div>

        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">No SPPA</label><input type="text" name="no_sppa" class="w-full border rounded-xl p-3 uppercase focus:ring-2 focus:ring-emerald-200 outline-none" required></div>

        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Kg Netto (Timbang)</label><input type="number" step="0.1" name="kg_netto" id="kg_netto" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-emerald-200 outline-none" oninput="hitungUndersizeKg()" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Kg Basah</label><input type="number" step="0.1" name="kg_basah" id="kg_basah" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-emerald-200 outline-none" required></div>

        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Ayam Mati (Ekor)</label><input type="number" name="ayam_mati" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-emerald-200 outline-none" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Kg Bruto</label><input type="number" step="0.1" name="kg_rphu" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-emerald-200 outline-none" required></div>

        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Undersize (Ekor)</label><input type="number" name="ekor_undersize" id="ekor_undersize" class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-emerald-200 outline-none" oninput="hitungUndersizeKg()" required></div>
        <div><label class="block text-xs font-bold text-gray-500 mb-1.5">Undersize (Kg) - Auto Kalkulasi</label><input type="number" step="0.01" name="kg_undersize" id="kg_undersize" class="w-full border border-gray-200 bg-gray-100 rounded-xl p-3 text-emerald-600 font-bold cursor-not-allowed" readonly required></div>

        <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 mb-1.5">Keterangan Tambahan (Opsional)</label><input type="text" name="keterangan" placeholder="Ketik jika ada keterangan tambahan..." class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-emerald-200 outline-none"></div>
      </div>
      <button type="submit" id="btnSimpanSetelah" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl transition uppercase tracking-wide text-base shadow-md">Finalisasi Update Rit</button>
    </form>
  </div>
  @endif

  {{-- ==================== SECTION: HANGING / COUNTER ==================== --}}
  @if ($canHanging)
  <div id="tabsec-hanging">
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start gap-2">
      <div class="flex items-center gap-2 mt-1">
        <h1 class="text-lg sm:text-2xl font-bold text-gray-800">Form Hanging</h1>
        <span id="status-indicator" class="bg-gray-300 text-gray-600 text-[10px] px-2 py-0.5 rounded-full font-bold transition-all duration-300">STANDBY</span>
      </div>
    </div>

    <form id="form-hanging-tally" onsubmit="event.preventDefault();">
      <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-md p-4 mb-4 text-white">
        <label class="block text-[11px] font-bold tracking-widest text-blue-200 mb-1"><i class="fas fa-barcode mr-1"></i> NOMOR PO (ISI JIKA DATA BEDA HARI)</label>
        <input type="text" id="input-no-po" placeholder="Opsional, Cth: PO-123" class="w-full border-0 rounded-lg px-3 py-2 font-bold text-gray-800 bg-white/90 shadow-inner outline-none focus:ring-2 focus:ring-yellow-400 text-sm uppercase mb-3">

        <label class="block text-[11px] font-bold tracking-widest text-blue-200 mb-1"><i class="fas fa-truck-loading mr-1"></i> MASUKKAN NOMOR RITASE</label>
        <div class="flex gap-2 max-w-md">
          <input type="text" id="input-no-rit" placeholder="Contoh: RIT-01" onkeydown="if(event.key==='Enter') cariNoRit()" class="w-full border-0 rounded-lg px-3 py-2 font-bold text-gray-800 shadow-inner outline-none focus:ring-2 focus:ring-yellow-400 text-sm uppercase">
          <button type="button" id="btn-cari-rit" onclick="cariNoRit()" class="bg-yellow-400 text-gray-900 font-bold px-4 rounded-lg text-sm hover:bg-yellow-300 active:scale-95 transition flex items-center gap-2 shadow"><i class="fas fa-search" id="icon-cari"></i> <span>Cari</span></button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:col-span-3 flex flex-col justify-between gap-3">
          <div>
            <h2 class="text-[11px] font-bold text-red-400 mb-2 flex items-center gap-2"><i class="far fa-clock"></i> PROSES WAKTU & PIC</h2>
            <div class="grid grid-cols-2 gap-2 mb-3">
              <div><label class="block text-[10px] text-gray-400 mb-0.5">Jam Bongkar</label><input type="time" id="jam-bongkar" class="w-full border rounded-md px-2 py-1 text-gray-700 bg-gray-50 text-xs outline-none"></div>
              <div><label class="block text-[10px] text-gray-400 mb-0.5">Jam Selesai</label><input type="time" id="jam-selesai" class="w-full border rounded-md px-2 py-1 text-gray-700 text-xs outline-none focus:border-blue-400"></div>
            </div>

            <div class="border-t border-gray-100 pt-2 grid grid-cols-1 gap-2">
              <div>
                <label class="block text-[10px] text-gray-400 mb-0.5">Petugas Tally</label>
                <input type="text" id="input-nama-tally" value="{{ $user->name }}" class="w-full border border-gray-200 rounded-md px-2 py-1 text-gray-500 bg-gray-100 text-xs font-bold outline-none cursor-not-allowed" readonly>
              </div>
              <div>
                <label class="block text-[10px] text-emerald-600 font-bold mb-0.5">Disetujui Oleh (Foreman/SPV)</label>
                <select id="input-nama-foreman" class="w-full border rounded-md px-2 py-1 text-gray-700 text-xs uppercase outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200">
                  <option value="">-- PILIH FOREMAN/SPV BERTUGAS --</option>
                  <option value="ANDI SETIAWAN">Andi Setiawan</option>
                  <option value="M. LUTFI ALFIANSYAH">M. Lutfi Alfiansyah</option>
                  <option value="RINA PRATIWI">Rina Pratiwi</option>
                  <option value="YUNI SRI LESTARI">Yuni Sri Lestari</option>
                  <option value="M. FAISAL HANAFI">M. Faisal Hanafi</option>
                  <option value="BOBBY ANDI">Bobby Andi</option>
                </select>
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-2 mt-2">
            <button type="button" onclick="simpanDraftLokal()" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-md transition text-xs flex items-center justify-center gap-2 shadow-sm border border-blue-600">
              <i class="fas fa-save"></i> Simpan Sementara
            </button>
            <button type="button" id="btn-submit-grup" onclick="eksekusiSimpanKeSpreadsheet()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2 rounded-md transition text-xs flex items-center justify-center gap-2 shadow-sm border border-emerald-600">
              <i class="fas fa-paper-plane" id="icon-btn"></i> <span id="text-btn">Simpan Final</span>
            </button>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:col-span-6">
          <h2 class="text-[11px] font-bold text-red-400 mb-2 flex items-center gap-2"><i class="far fa-file-alt"></i> INFORMASI PROSES</h2>
          <div class="grid grid-cols-2 gap-y-3 gap-x-2 text-[11px] mt-1">
            <div><div class="text-gray-400 font-medium">Asal Kandang (Farm)</div><div id="info-farm" class="font-bold text-gray-700 text-xs truncate">-</div></div>
            <div><div class="text-gray-400 font-medium">Size / Area</div><div id="info-size" class="font-bold text-gray-700 text-xs">-</div></div>
            <div class="col-span-2 border-t border-gray-100 pt-2">
              <div class="text-gray-400 font-medium mb-0.5">Total Target Surat Jalan (DTA)</div>
              <div class="font-bold text-gray-800 text-sm"><span id="info-sj" class="text-blue-600 text-base">0</span> Ekor / <span id="info-kg" class="text-indigo-600">0</span> Kg</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:col-span-3 flex flex-col gap-1.5 text-[11px]">
          <div class="flex justify-between items-center bg-orange-50 p-1.5 rounded border border-orange-100 text-xs">
            <span class="text-gray-700 font-semibold">Diterima</span><span id="summary-diterima" class="font-bold text-orange-500 text-sm">0 Ekor</span>
          </div>
          <div class="flex justify-between items-center px-1"><span class="text-gray-400">Total Kosong</span><span id="summary-kosong" class="font-bold text-gray-400">0 Pcs</span></div>
          <div class="flex justify-between items-center px-1 border-t border-gray-50 pt-1.5"><span class="text-gray-500 font-medium">Selisih</span><span id="summary-selisih" class="font-bold text-red-500 text-xs">0 Ekor</span></div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
          <h2 class="text-[11px] font-bold text-emerald-600 flex items-center gap-1"><i class="fas fa-th-list"></i> INPUT JUMLAH AYAM (0-50/BLOK)</h2>
        </div>
        <div class="overflow-x-auto max-h-[500px]">
          <table class="w-full text-center text-xs border-collapse">
            <thead class="text-gray-500 font-bold sticky-thead text-[10px] sm:text-xs">
              <tr>
                <th rowspan="2" class="p-2 border-r border-b border-gray-200 w-8">NO</th><th rowspan="2" class="p-2 border-r border-b border-gray-200 w-16">BLOK</th>
                <th colspan="2" class="p-1 border-r border-b border-gray-200 bg-blue-50/30">KOLOM A</th>
                <th colspan="2" class="p-1 border-r border-b border-gray-200 bg-orange-50/30">KOLOM B</th>
                <th colspan="2" class="p-1 border-r border-b border-gray-200 bg-purple-50/30">KOLOM C</th>
                <th colspan="2" class="p-1 border-b border-gray-200 bg-pink-50/30">KOLOM D</th>
              </tr>
              <tr class="bg-gray-50/70 text-[9px] sm:text-[10px]">
                <th class="p-1.5 border-r border-b border-gray-200 text-emerald-600 font-bold">AYM</th><th class="p-1.5 border-r border-b border-gray-200 text-gray-400">KSG</th>
                <th class="p-1.5 border-r border-b border-gray-200 text-emerald-600 font-bold">AYM</th><th class="p-1.5 border-r border-b border-gray-200 text-gray-400">KSG</th>
                <th class="p-1.5 border-r border-b border-gray-200 text-emerald-600 font-bold">AYM</th><th class="p-1.5 border-r border-b border-gray-200 text-gray-400">KSG</th>
                <th class="p-1.5 border-r border-b border-gray-200 text-emerald-600 font-bold">AYM</th><th class="p-1.5 border-b border-gray-200 text-gray-400">KSG</th>
              </tr>
            </thead>
            <tbody id="hanging-mobile-rows"></tbody>
          </table>
        </div>
      </div>
    </form>
  </div>
  @endif

</div>

{{-- ==================== MODAL: DETAIL HANGING (dipakai di section Setelah) ==================== --}}
@if ($canSetelah)
<div id="modalHanging" class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex justify-center items-center hidden p-4 overflow-y-auto">
  <div class="bg-white rounded-2xl shadow-2xl border max-w-2xl w-full max-h-[90vh] flex flex-col">
    <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white flex justify-between items-center rounded-t-2xl shadow-sm">
      <h3 class="text-base font-bold flex items-center gap-2"><i class="fas fa-file-invoice"></i> Hasil Timbang Lembar Hanging</h3>
      <button onclick="tutupPopupDetailHanging()" class="text-white hover:text-gray-200 text-xl focus:outline-none transition"><i class="fas fa-times"></i></button>
    </div>

    <div id="printTargetHanging" class="p-6 overflow-y-auto flex-1 text-gray-800 bg-white leading-relaxed">
      <div class="text-center border-b-2 border-gray-200 pb-4 mb-5">
        <h2 class="text-xl font-extrabold text-gray-900 tracking-wide uppercase">Laporan Hasil Penimbangan (Hanging)</h2>
        <p class="text-xs text-gray-500 mt-1 font-medium">CPI Jombang - Slaughterhouse</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-5 bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs shadow-inner">
        <div><p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Nomor PO (SAP)</p><p id="pop_no_po" class="text-sm font-bold text-blue-600">-</p></div>
        <div><p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Nomor Ritase</p><p id="pop_no_rit" class="text-sm font-bold text-emerald-600">-</p></div>
        <div><p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Asal Kandang</p><p id="pop_farm" class="text-sm font-bold text-orange-600 uppercase">-</p></div>
        <div><p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Tanggal Bongkar</p><p id="pop_tanggal" class="font-semibold text-gray-700">-</p></div>
        <div><p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Waktu Bongkar</p><p id="pop_waktu" class="font-semibold text-gray-700">-</p></div>
        <div><p class="text-gray-400 font-bold uppercase tracking-wider text-[10px]">Ekspedisi</p><p id="pop_ekspedisi" class="font-semibold text-purple-600 uppercase">-</p></div>
      </div>

      <div class="grid grid-cols-3 gap-3 text-center mb-6">
        <div class="border rounded-xl p-3 bg-blue-50 border-blue-100 shadow-sm flex flex-col justify-center items-center">
          <span class="block text-[10px] text-blue-600 font-bold uppercase tracking-wider mb-1">Ekor DTA</span>
          <span id="pop_ekor_dta" class="text-lg font-extrabold text-blue-700">0</span>
        </div>
        <div class="border rounded-xl p-3 bg-emerald-50 border-emerald-100 shadow-sm flex flex-col justify-center items-center">
          <span class="block text-[10px] text-emerald-600 font-bold uppercase tracking-wider mb-1">Netto Ekor</span>
          <span id="pop_total_diterima" class="text-lg font-extrabold text-emerald-700">0</span>
        </div>
        <div id="pop_box_selisih" class="rounded-xl p-3 shadow-sm flex flex-col justify-center items-center transition-all duration-300">
          <span class="block text-[10px] font-bold uppercase tracking-wider mb-1 opacity-90">Selisih Ekor</span>
          <span id="pop_selisih_angka" class="text-xl font-extrabold mb-1 tracking-tighter">0</span>
          <span id="pop_selisih_status" class="text-[9px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-widest bg-white bg-opacity-30 border border-current">PAS</span>
        </div>
      </div>

      <div class="mb-6">
        <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider"><i class="fas fa-list-ol mr-1"></i> Rincian Grid Timbang Keranjang</p>
        <div class="overflow-x-auto border rounded-xl border-gray-200 shadow-sm">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-gray-100 text-gray-600 border-b font-bold">
                <th class="p-2.5 text-center w-12">No</th><th class="p-2.5">Jumlah Keranjang</th><th class="p-2.5 text-right">Jumlah Ekor</th><th class="p-2.5 text-right">Berat Timbang (Kg)</th>
              </tr>
            </thead>
            <tbody id="pop_table_body" class="divide-y divide-gray-100"></tbody>
          </table>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-6 border-t border-gray-200 pt-5 mt-6 text-center text-xs">
        <div class="flex flex-col items-center">
          <p class="text-gray-400 font-bold mb-2 uppercase tracking-wider text-[10px]">Dibuat Oleh (Tally Counter LB)</p>
          <div class="p-1 border border-gray-200 rounded-xl bg-white shadow-sm mb-2"><img id="pop_qr_qc" src="" alt="QR Code TLB" class="w-24 h-24 object-contain"></div>
          <p id="pop_nama_qc" class="font-bold text-gray-800 uppercase">-</p>
        </div>
        <div class="flex flex-col items-center">
          <p class="text-gray-400 font-bold mb-2 uppercase tracking-wider text-[10px]">Disetujui Oleh (Foreman/Forelady)</p>
          <div class="p-1 border border-gray-200 rounded-xl bg-white shadow-sm mb-2"><img id="pop_qr_foreman" src="" alt="QR Code Logistik" class="w-24 h-24 object-contain"></div>
          <p id="pop_nama_foreman" class="font-bold text-gray-800 uppercase">-</p>
        </div>
      </div>
    </div>

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl">
      <button onclick="tutupPopupDetailHanging()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl text-xs transition">Tutup</button>
    </div>
  </div>
</div>
@endif

<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

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

  function confirmLogout() {
    Swal.fire({
      title: '<span style="font-size:24px;font-weight:700;">Keluar dari Sistem?</span>',
      html: `<div style="font-size:15px;color:#6b7280;margin-top:8px;">Anda akan keluar dari <b style="color:#2563eb;">Workspace Report LB</b>.</div>`,
      icon: 'question', width: 430, showCancelButton: true, reverseButtons: true,
      confirmButtonText: 'Ya, Keluar', cancelButtonText: 'Batal',
      confirmButtonColor: '#dc3545', cancelButtonColor: '#64748b',
      customClass: { popup: 'modern-popup', confirmButton: 'modern-confirm-btn', cancelButton: 'modern-cancel-btn' },
    }).then((result) => {
      if (result.isConfirmed) document.getElementById('logoutForm').submit();
    });
  }

  const accessibleTabs = [
    @if($canSebelum) 'sebelum', @endif
    @if($canSetelah) 'setelah', @endif
    @if($canHanging) 'hanging',
    @endif
  ];

  function switchWorkspaceTab(tab) {
    accessibleTabs.forEach(t => {
      const sec = document.getElementById('tabsec-' + t);
      const btn = document.getElementById('tabbtn-' + t);
      if (sec) sec.classList.toggle('hidden', t !== tab);
      if (btn) btn.classList.toggle('active', t === tab);
    });
  }

  window.addEventListener('DOMContentLoaded', () => {
    if (accessibleTabs.length > 1) {
      switchWorkspaceTab('{{ $defaultTab }}');
    }

    const today = new Date().toISOString().split('T')[0];
    const tglSebelum = document.querySelector('#formSebelum [name="tanggal"]');
    if (tglSebelum) tglSebelum.value = today;
    const tglSearch = document.getElementById('search_tanggal');
    if (tglSearch) tglSearch.value = today;

    if (document.getElementById('formSebelum')) initEnterKey(); loadDaftarPO();
    if (document.getElementById('hanging-mobile-rows')) { renderTableKosong(); initVerticalNavigation(); }
  });

  document.addEventListener('input', function(e) {
    if (e.target.tagName === 'INPUT' && e.target.type === 'text' && !e.target.classList.contains('hanging-input')) {
      let start = e.target.selectionStart;
      let end = e.target.selectionEnd;
      e.target.value = e.target.value.toUpperCase();
      e.target.setSelectionRange(start, end);
    }
  });

  function initEnterKey() {
    document.querySelectorAll('.auto-enter input:not([readonly]), .auto-enter select').forEach((el, index, array) => {
      el.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); if (index < array.length - 1) array[index + 1].focus(); }
      });
    });
  }

  {{-- ============ SECTION: SEBELUM BONGKAR ============ --}}
  @if ($canSebelum)
  async function loadDaftarPO() {
    const select = document.getElementById('dropdown_no_po');
    if (!select) return;
    try {
        const list = await apiFetch('{{ route("lbreport.purchase-orders") }}');
        if (list.length === 0) {
            select.innerHTML = `<option value="">-- Belum ada PO tercatat di PPIC --</option>`;
            return;
        }
        select.innerHTML = `<option value="">-- Pilih Nomor PO --</option>` + list.map(po =>
            `<option value="${po.nomorPo}">${po.nomorPo} (${po.jenisPo} - ${po.tanggal})</option>`
        ).join('');
    } catch (err) {
        select.innerHTML = `<option value="">Gagal memuat daftar PO</option>`;
    }
}


  async function submitSebelum(form) {
    let btn = document.getElementById('btnSimpanSebelum');
    btn.disabled = true; btn.innerText = "PROCESSING...";

    const fd = new FormData(form);
    const payload = Object.fromEntries(fd.entries());

    try {
      const res = await apiFetch('{{ route("lbreport.sebelum.store") }}', { method: 'POST', body: JSON.stringify(payload) });
      alert(res.message);
      form.reset();
      document.querySelector('#formSebelum [name="tanggal"]').value = new Date().toISOString().split('T')[0];
    } catch (err) {
      alert("Error: " + err.message);
    } finally {
      btn.disabled = false; btn.innerText = "Simpan Data Awal";
    }
  }
  @endif

  {{-- ============ SECTION: SETELAH BONGKAR ============ --}}
  @if ($canSetelah)
  function hitungUndersizeKg() {
    let kgNetto = parseFloat(document.getElementById('kg_netto').value) || 0;
    let ekorNetto = parseFloat(document.getElementById('ekor_netto').value) || 0;
    let ekorUndersize = parseFloat(document.getElementById('ekor_undersize').value) || 0;
    if (ekorNetto > 0 && kgNetto > 0 && ekorUndersize > 0) {
      let abw = kgNetto / ekorNetto;
      document.getElementById('kg_undersize').value = (ekorUndersize * abw).toFixed(2);
    } else { document.getElementById('kg_undersize').value = ""; }
  }

  async function cariDaftarPO() {
    let po = document.getElementById('search_po_setelah').value.trim();
    if (!po) return alert("Masukkan Nomor PO terlebih dahulu!");
    let divList = document.getElementById('listRitByPo');
    divList.innerHTML = `<span class="text-blue-500 font-bold text-xs"><i class="fas fa-spinner fa-spin mr-1"></i> Mencari PO...</span>`;
    divList.classList.remove('hidden');

    try {
      const res = await apiFetch(`{{ route('lbreport.daftar-rit-po') }}?no_po=${encodeURIComponent(po)}`);
      if (res.list.length === 0) { divList.innerHTML = `<span class="text-gray-500 text-xs">Tidak ada Rit ditemukan untuk PO ini.</span>`; return; }
      let html = "";
      res.list.forEach(r => {
        let stColor = r.status === "Baru" ? "text-blue-500" : (r.status === "Lama" ? "text-emerald-500" : "text-gray-400");
        html += `<button type="button" onclick="pilihRitDariPO('${r.tanggal}', '${r.noRit}')" class="text-left bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 border p-2.5 rounded-lg flex justify-between items-center transition">
                  <span class="font-bold text-emerald-700">${r.noRit}</span>
                  <span class="text-[10px] text-gray-500 flex flex-col items-end"><span>${r.tanggal}</span><span class="font-bold ${stColor}">${r.status}</span></span>
                 </button>`;
      });
      divList.innerHTML = html;
    } catch (err) {
      divList.innerHTML = `<span class="text-red-500 text-xs">${err.message}</span>`;
    }
  }

  function pilihRitDariPO(tgl, rit) {
    document.getElementById('search_tanggal').value = tgl;
    document.getElementById('search_no_rit').value = rit;
    cariDataAwalRit();
  }

  async function cariDataAwalRit() {
    let tanggalCari = document.getElementById('search_tanggal').value;
    let noRit = document.getElementById('search_no_rit').value.trim();
    if (!tanggalCari) return alert('Pilih tanggal pencarian!');
    if (!noRit) return alert('Ketik nomor Ritase!');

    let btnCari = document.getElementById('btnCariRit');
    let textAsli = btnCari.innerHTML;
    btnCari.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...'; btnCari.disabled = true;

    try {
      const res = await apiFetch(`{{ route('lbreport.ekor-netto-hanging') }}?tanggal=${tanggalCari}&no_rit=${encodeURIComponent(noRit)}`);
      document.getElementById('tanggal_update').value = tanggalCari;
      document.getElementById('no_rit_update').value = noRit;
      document.getElementById('ekor_netto').value = res.ekorNetto;
      document.getElementById('formSetelah').classList.remove('hidden');
      document.getElementById('kg_netto').focus();
      hitungUndersizeKg();
      const btnDetail = document.getElementById('btnLihatDetailHanging');
      if (btnDetail) btnDetail.classList.remove('hidden');
    } catch (err) {
      alert(err.message);
    } finally {
      btnCari.innerHTML = textAsli; btnCari.disabled = false;
    }
  }

  async function bukaPopupDetailHanging() {
    let tanggalCari = document.getElementById('tanggal_update').value;
    let noRit = document.getElementById('no_rit_update').value;

    if (!tanggalCari || !noRit) return alert("Silakan proses nomor ritase terlebih dahulu!");

    const btnDetail = document.getElementById('btnLihatDetailHanging');
    const textAsli = btnDetail.innerHTML;
    btnDetail.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menarik Data...'; btnDetail.disabled = true;

    try {
      const res = await apiFetch(`{{ route('lbreport.detail-hanging') }}?tanggal=${tanggalCari}&no_rit=${encodeURIComponent(noRit)}`);

      document.getElementById('pop_no_po').innerText = res.noPo || '-';
      document.getElementById('pop_no_rit').innerText = res.noRit || noRit;
      document.getElementById('pop_tanggal').innerText = res.tanggal || tanggalCari;
      document.getElementById('pop_waktu').innerText = (res.jamBongkar || "-") + " s/d " + (res.jamSelesai || "-");
      document.getElementById('pop_farm').innerText = res.farm || "-";
      document.getElementById('pop_ekspedisi').innerText = res.ekspedisi || "-";

      let ekorDTA = parseFloat(res.ekorDTA) || 0;
      let ekorNettoNum = parseFloat(res.totalDiterima) || parseFloat(document.getElementById('ekor_netto').value) || 0;
      document.getElementById('pop_ekor_dta').innerText = ekorDTA;
      document.getElementById('pop_total_diterima').innerText = ekorNettoNum;

      let selisih = ekorDTA - ekorNettoNum;
      let boxSelisih = document.getElementById('pop_box_selisih');
      document.getElementById('pop_selisih_angka').innerText = Math.abs(selisih);
      let txtSelisihStatus = document.getElementById('pop_selisih_status');

      if (selisih > 0) {
        txtSelisihStatus.innerText = "KURANG";
        boxSelisih.className = "rounded-xl p-3 shadow-sm flex flex-col justify-center items-center neon-kurang";
      } else {
        txtSelisihStatus.innerText = selisih === 0 ? "PAS" : "OVER";
        boxSelisih.className = "rounded-xl p-3 shadow-sm flex flex-col justify-center items-center neon-overpas";
      }

      let tbody = document.getElementById('pop_table_body'); tbody.innerHTML = "";
      if (res.gridData && res.gridData.length > 0) {
        res.gridData.forEach((item, idx) => {
          tbody.innerHTML += `<tr class="border-b hover:bg-gray-50 transition">
              <td class="p-2.5 text-center font-medium text-gray-500">${item.no || (idx + 1)}</td>
              <td class="p-2.5 text-gray-700 font-medium">${item.keranjang || item.JumlahKeranjang || '-'} Plt</td>
              <td class="p-2.5 text-right font-semibold text-gray-900">${item.ekor || item.JumlahEkor || '0'} Pcs</td>
              <td class="p-2.5 text-right font-bold text-blue-600">${item.berat || item.Kg || '0'} Kg</td>
            </tr>`;
        });
      }

      let rawTally = res.namaTally || "-";
      document.getElementById('pop_nama_qc').innerText = rawTally;
      let qrDataTLB = "CREATED_BY_" + rawTally.replace(/\s+/g, '_') + "_" + (res.noPo || '') + "_" + noRit;
      document.getElementById('pop_qr_qc').src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(qrDataTLB);

      let rawForeman = res.namaForeman || "-";
      document.getElementById('pop_nama_foreman').innerText = rawForeman;
      let qrDataAPP = "APPROVED_BY_" + rawForeman.replace(/\s+/g, '_') + "_" + (res.noPo || '') + "_" + noRit;
      document.getElementById('pop_qr_foreman').src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(qrDataAPP);

      document.getElementById('modalHanging').classList.remove('hidden');
    } catch (err) {
      alert("Peringatan Sistem: " + err.message);
    } finally {
      btnDetail.innerHTML = textAsli; btnDetail.disabled = false;
    }
  }

  function tutupPopupDetailHanging() { document.getElementById('modalHanging').classList.add('hidden'); }

  async function submitSetelah(form) {
    let btn = document.getElementById('btnSimpanSetelah');
    btn.disabled = true; btn.innerText = "PROCESSING...";

    const fd = new FormData(form);
    const payload = Object.fromEntries(fd.entries());

    try {
      const res = await apiFetch('{{ route("lbreport.setelah.store") }}', { method: 'POST', body: JSON.stringify(payload) });
      alert(res.message);
      form.reset();
      document.getElementById('formSetelah').classList.add('hidden');
      const btnDetail = document.getElementById('btnLihatDetailHanging');
      if (btnDetail) btnDetail.classList.add('hidden');
    } catch (err) {
      alert("Error: " + err.message);
    } finally {
      btn.disabled = false; btn.innerText = "Finalisasi Update Rit";
    }
  }
  @endif

  {{-- ============ SECTION: HANGING / COUNTER ============ --}}
  @if ($canHanging)
  let isEditMode = false;
  let stateTanggalPenerimaan = "";
  let stateNoPo = "";

  function renderTableKosong() {
    const tbody = document.getElementById("hanging-mobile-rows");
    let html = "";
    for (let i = 1; i <= 19; i++) {
      let start = (i - 1) * 50;
      let end = i * 50;
      html += `<tr class="hover:bg-gray-50/80 border-b border-gray-100 transition text-[11px] sm:text-xs"><td class="p-2 border-r border-gray-100 text-gray-400 font-mono">${i}</td><td class="p-1 border-r border-gray-100 text-left pl-2"><div class="font-bold text-gray-700">B-${i}</div><div class="text-[9px] text-gray-400 font-mono">${start}-${end}</div></td>`;
      for (let col = 1; col <= 4; col++) {
        let tabIdx = (col - 1) * 19 + i;
        let bgInput = col === 1 ? 'bg-blue-50/10' : (col === 2 ? 'bg-orange-50/10' : (col === 3 ? 'bg-purple-50/10' : 'bg-pink-50/10'));
        let fInput = col === 1 ? 'focus:border-blue-500 focus:bg-blue-50' : (col === 2 ? 'focus:border-orange-500 focus:bg-orange-50' : (col === 3 ? 'focus:border-purple-500 focus:bg-purple-50' : 'focus:border-pink-500 focus:bg-pink-50'));
        html += `
          <td class="p-1 border-r border-gray-100 ${bgInput}">
              <div class="flex items-center justify-center gap-0.5">
                  <button type="button" onclick="adjust('a${col}_${i}', -1, ${i}, ${col})" class="btn-counter w-6 h-6 rounded bg-gray-100 text-gray-600 active:bg-red-200 font-bold">-</button>
                  <input type="number" id="a${col}_${i}" data-row="${i}" data-col="${col}" placeholder="0" min="0" max="50" inputmode="numeric" tabindex="${tabIdx}" oninput="calc(${i}, ${col})" class="hanging-input w-9 sm:w-11 text-center border rounded py-1 font-bold text-emerald-600 outline-none ${fInput}">
                  <button type="button" onclick="adjust('a${col}_${i}', 1, ${i}, ${col})" class="btn-counter w-6 h-6 rounded bg-gray-100 text-gray-600 active:bg-emerald-200 font-bold">+</button>
              </div>
          </td>
          <td id="k${col}_${i}" class="p-2 border-r border-gray-100 font-bold text-gray-400 bg-gray-50/30">0</td>`;
      }
      html += `</tr>`;
    }
    tbody.innerHTML = html;
    updateGlobalSummary();
  }

  function initVerticalNavigation() {
    document.addEventListener('keydown', function(e) {
      if ((e.key === 'Enter' || e.key === 'Tab') && e.target.classList.contains('hanging-input')) {
        e.preventDefault();
        let r = parseInt(e.target.getAttribute('data-row')), c = parseInt(e.target.getAttribute('data-col'));
        if (r < 19) r++; else { r = 1; c = c < 4 ? c + 1 : 1; }
        const next = document.getElementById(`a${c}_${r}`);
        if (next) { next.focus(); next.select(); next.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
      }
    });
    document.addEventListener('focusin', function(e) { if (e.target.classList.contains('hanging-input')) setTimeout(() => e.target.select(), 50); });
  }

  function adjust(id, change, r, c) {
    const input = document.getElementById(id);
    if (input && !input.disabled) {
      let v = parseInt(input.value) || 0;
      v += change;
      if (v < 0) v = 0; if (v > 50) v = 50;
      input.value = v; calc(r, c);
    }
  }

  function calc(r, c) {
    const inputAym = document.getElementById(`a${c}_${r}`);
    const cellKsg = document.getElementById(`k${c}_${r}`);
    if (inputAym && cellKsg) {
      let aymVal = parseInt(inputAym.value);
      if (isNaN(aymVal) || aymVal === 0) { cellKsg.innerText = 0; cellKsg.className = "p-2 border-r border-gray-100 font-bold text-gray-400 bg-gray-50/30"; updateGlobalSummary(); return; }
      if (aymVal > 50) { aymVal = 50; inputAym.value = 50; }
      let ksgVal = 50 - aymVal;
      cellKsg.innerText = ksgVal;
      cellKsg.className = ksgVal > 0 ? "p-2 border-r border-gray-100 font-bold text-red-500 bg-red-50" : "p-2 border-r border-gray-100 font-bold text-gray-400 bg-gray-50/30";
      updateGlobalSummary();
    }
  }

  function updateGlobalSummary() {
    let totalAyam = 0, totalKosong = 0;
    const totalSuratJalan = parseInt(document.getElementById('info-sj').innerText) || 0;
    for (let i = 1; i <= 19; i++) {
      for (let col = 1; col <= 4; col++) {
        const aym = document.getElementById(`a${col}_${i}`), ksg = document.getElementById(`k${col}_${i}`);
        if (aym) totalAyam += (parseInt(aym.value) || 0);
        if (ksg) totalKosong += (parseInt(ksg.innerText) || 0);
      }
    }
    document.getElementById('summary-diterima').innerText = totalAyam.toLocaleString('id-ID') + " Ekor";
    document.getElementById('summary-kosong').innerText = totalKosong.toLocaleString('id-ID') + " Pcs";

    let selisih = totalSuratJalan - totalAyam;
    const selisihEl = document.getElementById('summary-selisih');
    selisihEl.innerText = (selisih <= 0 ? "+" : "") + Math.abs(selisih).toLocaleString('id-ID') + " Ekor";
    selisihEl.className = selisih === 0 ? "font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded" : (selisih < 0 ? "font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded" : "font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded");
  }

  function simpanDraftLokal() {
    const noRit = document.getElementById('input-no-rit').value.trim().toUpperCase();
    const noPo = document.getElementById('input-no-po').value.trim().toUpperCase();
    if (!noRit) return alert("Cari Nomor Ritase terlebih dahulu sebelum menyimpan draft!");
    let draftData = { jamBongkar: document.getElementById('jam-bongkar').value, jamSelesai: document.getElementById('jam-selesai').value, grid: {} };
    for (let i = 1; i <= 19; i++) {
      for (let col = 1; col <= 4; col++) {
        const input = document.getElementById(`a${col}_${i}`);
        if (input && input.value) draftData.grid[`a${col}_${i}`] = input.value;
      }
    }
    localStorage.setItem('HangingDraft_' + noRit + '_' + noPo, JSON.stringify(draftData));
    alert("Draft berhasil disimpan di perangkat ini!");
  }

  function muatDraftLokal(noRit, noPo) {
    renderTableKosong();
    const saved = localStorage.getItem('HangingDraft_' + noRit + '_' + noPo);
    if (saved) {
      try {
        const draftData = JSON.parse(saved);
        document.getElementById('jam-bongkar').value = draftData.jamBongkar || "";
        document.getElementById('jam-selesai').value = draftData.jamSelesai || "";
        for (let key in draftData.grid) {
          const input = document.getElementById(key);
          if (input) { input.value = draftData.grid[key]; let parts = key.split('_'); calc(parseInt(parts[1]), parseInt(parts[0].replace('a', ''))); }
        }
        updateGlobalSummary();
      } catch (e) {}
    }
  }

  function resetFormState(isLogout = false) {
    if (!isLogout) { document.getElementById('input-no-rit').value = ""; document.getElementById('input-no-po').value = ""; }
    document.getElementById('jam-bongkar').value = "";
    document.getElementById('jam-selesai').value = "";
    document.getElementById('input-nama-foreman').value = "";
    document.getElementById('info-farm').innerText = "-";
    document.getElementById('info-size').innerText = "-";
    document.getElementById('info-sj').innerText = "0";
    document.getElementById('info-kg').innerText = "0";
    const statusIndicator = document.getElementById('status-indicator');
    statusIndicator.innerText = "STANDBY";
    statusIndicator.className = "bg-gray-300 text-gray-600 text-[10px] px-2 py-0.5 rounded-full font-bold transition-all duration-300";
    isEditMode = false; stateTanggalPenerimaan = ""; stateNoPo = "";
    bukaKunciTombol();
    renderTableKosong();
  }

  async function cariNoRit() {
    const noRit = document.getElementById('input-no-rit').value.trim();
    const noPo = document.getElementById('input-no-po').value.trim();
    if (!noRit) return alert("Masukkan nomor Ritase!");

    document.getElementById('btn-cari-rit').disabled = true;
    document.getElementById('icon-cari').className = "fas fa-spinner fa-spin";

    try {
      const res = await apiFetch(`{{ route('lbreport.ritase') }}?no_rit=${encodeURIComponent(noRit)}&no_po=${encodeURIComponent(noPo)}`);

      if (res && res.status === "SUCCESS") {
        document.getElementById('info-farm').innerText = res.farm;
        document.getElementById('info-size').innerText = res.size;
        document.getElementById('info-sj').innerText = res.ekorSJ;
        document.getElementById('info-kg').innerText = res.kgSJ.toLocaleString('id-ID');

        stateTanggalPenerimaan = res.tanggalPenerimaan;
        stateNoPo = res.noPo;

        const statusIndicator = document.getElementById('status-indicator');

        if (res.sudahAdaData === true || res.isEdit === true) {
          isEditMode = true;
          statusIndicator.innerText = "EDIT MODE";
          statusIndicator.className = "text-[10px] px-2 py-0.5 rounded-full font-bold transition-all duration-300 bg-amber-500 text-white shadow-md";
          document.getElementById('text-btn').innerText = "Simpan Perubahan";
          if (res.dataHanging) {
            document.getElementById('jam-bongkar').value = res.dataHanging.jamBongkar || "";
            document.getElementById('jam-selesai').value = res.dataHanging.jamSelesai || "";
            document.getElementById('input-nama-foreman').value = res.dataHanging.namaForeman || "";
            for (let key in res.dataHanging.grid) {
              const input = document.getElementById(key);
              if (input) { input.value = res.dataHanging.grid[key]; let parts = key.split('_'); calc(parseInt(parts[1]), parseInt(parts[0].replace('a', ''))); }
            }
          }
        } else {
          isEditMode = false;
          statusIndicator.innerText = "LIVE INPUT";
          statusIndicator.className = "text-[10px] px-2 py-0.5 rounded-full font-bold transition-all duration-300 neon-active shadow-md";
          document.getElementById('text-btn').innerText = "Simpan Final";
          document.getElementById('input-nama-foreman').value = "";
          muatDraftLokal(noRit.toUpperCase(), noPo.toUpperCase());
        }
      } else {
        alert(res ? res.message : "Data tidak ditemukan.");
        resetFormState();
        document.getElementById('input-no-rit').value = noRit;
        document.getElementById('input-no-po').value = noPo;
      }
    } catch (err) {
      alert("Koneksi ke server gagal: " + err.message);
    } finally {
      document.getElementById('btn-cari-rit').disabled = false;
      document.getElementById('icon-cari').className = "fas fa-search";
    }
  }

  async function eksekusiSimpanKeSpreadsheet() {
    const farm = document.getElementById('info-farm').innerText;
    const noRitInput = document.getElementById('input-no-rit').value.trim();
    const jamBongkar = document.getElementById('jam-bongkar').value;
    const jamSelesai = document.getElementById('jam-selesai').value;
    const namaTally = document.getElementById('input-nama-tally').value;
    const namaForeman = document.getElementById('input-nama-foreman').value.trim().toUpperCase();
    if (farm === "-" || !noRitInput) return alert("Cari Ritase valid dahulu sebelum menyimpan!");
    if (!jamBongkar || !jamSelesai) return alert("Silakan isi Jam Bongkar dan Jam Selesai terlebih dahulu!");
    if (!namaForeman) return alert("Mohon lengkapi 'Disetujui Oleh (Foreman/SPV)' sebelum menyimpan final!");

    document.getElementById('btn-submit-grup').disabled = true;
    document.getElementById('text-btn').innerText = "Menyimpan...";

    let totalDiterimaText = document.getElementById('summary-diterima').innerText.replace(/\D/g, "");
    let totalKosongText = document.getElementById('summary-kosong').innerText.replace(/\D/g, "");
    let gridData = {};
    for (let i = 1; i <= 19; i++) {
      for (let col = 1; col <= 4; col++) {
        const input = document.getElementById(`a${col}_${i}`);
        if (input && input.value) gridData[`a${col}_${i}`] = input.value;
      }
    }

    const payload = {
      no_rit: noRitInput.toUpperCase(),
      jam_bongkar: jamBongkar,
      jam_selesai: jamSelesai,
      total_sj: parseInt(document.getElementById('info-sj').innerText) || 0,
      total_diterima: parseInt(totalDiterimaText) || 0,
      total_kosong: parseInt(totalKosongText) || 0,
      grid: gridData,
      tanggal_penerimaan: stateTanggalPenerimaan,
      no_po: stateNoPo,
      nama_tally: namaTally,
      nama_foreman: namaForeman,
    };

    try {
      const res = await apiFetch('{{ route("lbreport.hanging.store") }}', { method: 'POST', body: JSON.stringify(payload) });
      alert(res.message || "Data berhasil disimpan!");
      localStorage.removeItem('HangingDraft_' + noRitInput.toUpperCase() + '_' + (document.getElementById('input-no-po').value.trim().toUpperCase()));
      resetFormState();
    } catch (err) {
      alert("Gagal menyimpan: " + err.message);
    } finally {
      bukaKunciTombol();
    }
  }

  function bukaKunciTombol() {
    const btn = document.getElementById('btn-submit-grup');
    if (btn) { btn.disabled = false; document.getElementById('text-btn').innerText = isEditMode ? "Simpan Perubahan" : "Simpan Final"; }
  }
  @endif
</script>
</body>
</html>