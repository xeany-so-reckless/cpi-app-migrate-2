<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Input Data Fresh</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bwip-js/dist/bwip-js-min.js"></script>
  <style>
    :root {
      --primary: #da0019;
      --primary-hover: #b30015;
      --bg-color: #f2f2f2;
      --text-main: #2b2b2b;
      --text-muted: #555555;
      --border: #cccccc;
      --success: #10b981;
      --error: #ef4444;
      --card-bg: #ffffff;
      --secondary: #4b5563;
    }
    * { box-sizing: border-box; font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
    body { background-color: var(--bg-color); color: var(--text-main); padding: 15px; }

    .container {
      width: 100%; max-width: 480px; margin: 0 auto; background: var(--card-bg);
      border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;
    }
    @media (min-width: 768px) {
      .container { max-width: 720px; }
    }

    .header {
      background: linear-gradient(135deg, var(--primary), #a60013); color: white; padding: 15px 20px;
      display: flex; justify-content: space-between; align-items: center; gap: 8px;
    }
    .header h2 { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
    .header-actions { display: flex; gap: 6px; }
    .btn-logout-header {
      background: rgba(255,255,255,0.2); color: white; border: none; padding: 8px 12px; border-radius: 6px;
      cursor: pointer; display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 600;
      white-space: nowrap;
    }
    .btn-logout-header:hover { background: rgba(255,255,255,0.3); }
    .btn-logout-header.active { background: rgba(255,255,255,0.4); }

    .content { padding: 25px 20px; }
    @media (min-width: 768px) { .content { padding: 30px 35px; } }

    .form-group { margin-bottom: 15px; position: relative; }
    .form-group label { display: block; font-size: 14px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; }

    input, select {
      width: 100%; padding: 14px 16px; border: 1px solid var(--border); border-radius: 6px;
      font-size: 15px; color: var(--text-main); background-color: #fafafa;
    }
    input:focus, select:focus { outline: none; border-color: var(--primary); background-color: #fff; }
    input[readonly] { background-color: #f3f4f6; border-color: transparent; color: #374151; font-weight: 600; cursor: not-allowed; }

    .input-with-icon { position: relative; display: flex; align-items: center; }
    .input-with-icon .material-icons-round { position: absolute; left: 14px; color: var(--text-muted); font-size: 20px; }
    .input-with-icon input, .input-with-icon select { padding-left: 42px; }

    .form-row { display: grid; grid-template-columns: 1fr; gap: 15px; }
    @media (min-width: 768px) {
      .form-row-2 { grid-template-columns: 1fr 1fr; }
    }

    .btn {
      width: 100%; padding: 15px; border: none; border-radius: 8px; font-size: 16px; font-weight: 700;
      cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px;
    }
    .btn-primary { background-color: var(--primary); color: white; margin-top: 10px; }
    .btn-primary:hover { background-color: var(--primary-hover); }
    .btn-secondary { background-color: var(--secondary); color: white; margin-top: 5px; }
    .btn-secondary:hover { opacity: 0.9; }
    .btn-outline { background: #fff; color: var(--text-main); border: 1px solid var(--border); }
    .btn-outline:hover { background: #f8f8f8; }
    .btn:active { transform: translateY(1px); }
    .btn:disabled { opacity: 0.6; cursor: not-allowed; }

    .error-text { color: var(--error); font-size: 13px; font-weight: 500; margin-top: 6px; text-align: center; }

    .user-info {
      background: #ffe6e8; border-left: 4px solid var(--primary); padding: 12px 16px; border-radius: 6px;
      margin-bottom: 20px; display: flex; align-items: center; gap: 12px;
    }
    .user-info-text h4 { font-size: 15px; color: var(--primary); margin-bottom: 2px; }
    .user-info-text p { font-size: 12px; color: var(--text-muted); font-weight: 600; }

    #toast {
      visibility: hidden; min-width: 250px; background-color: var(--success); color: #fff;
      text-align: center; border-radius: 8px; padding: 16px; position: fixed; z-index: 1000;
      bottom: 30px; left: 50%; transform: translateX(-50%); font-size: 14px; font-weight: 600;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15); opacity: 0; transition: opacity 0.3s, bottom 0.3s;
    }
    #toast.show { visibility: visible; opacity: 1; bottom: 50px; }
    #toast.error { background-color: var(--error); }

    .draft-container { margin-top: 25px; border-top: 2px dashed var(--border); padding-top: 20px; }
    .draft-title { font-size: 14px; font-weight: 700; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
    .draft-badge { background-color: var(--primary); color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
    .table-wrapper { overflow-x: auto; margin-bottom: 15px; border-radius: 6px; border: 1px solid var(--border); }
    table { width: 100%; border-collapse: collapse; font-size: 12px; white-space: nowrap; }
    th, td { border-bottom: 1px solid var(--border); padding: 10px 8px; text-align: left; }
    th { background-color: #f8fafc; font-weight: 700; color: var(--text-muted); }
    tbody tr:hover { background-color: #f1f5f9; }
    .action-btn { cursor: pointer; padding: 4px 6px; border: none; border-radius: 4px; color: white; display: inline-flex; align-items: center; justify-content: center; }
    .btn-edit { background-color: #f59e0b; margin-right: 4px; }
    .btn-delete { background-color: var(--error); }

    .export-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }

    .hidden { display: none !important; }

    .history-pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; gap: 10px; }
    .history-pagination span { font-size: 13px; color: var(--text-muted); font-weight: 600; white-space: nowrap; }
    .history-pagination .btn { width: auto; padding: 10px 16px; font-size: 13px; }
    .empty-row td { text-align: center; color: var(--text-muted); padding: 20px 8px; }

    /* ==================== MODAL EXPORT FORM RESMI ==================== */
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 2000;
      display: flex; align-items: center; justify-content: center; padding: 15px;
    }
    .modal-box {
      background: #fff; border-radius: 12px; padding: 25px 20px; width: 100%; max-width: 400px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto;
    }
    .modal-title { font-size: 16px; font-weight: 700; margin-bottom: 4px; color: var(--text-main); }
    .modal-subtitle { font-size: 12px; color: var(--text-muted); margin-bottom: 18px; font-weight: 500; }
    .modal-actions { display: flex; gap: 10px; margin-top: 5px; }
    .modal-actions .btn { margin-top: 0; }

    @media print {
      body { background: #fff; padding: 0; }
      .no-print { display: none !important; }
      .container { box-shadow: none; max-width: 100%; }
      .content { padding: 0; }
      #draftSection { border-top: none; padding-top: 0; }
    }
  </style>
</head>
<body>

  <div id="toast">Data berhasil disimpan!</div>

  <div class="container">
    <div class="header no-print">
      <h2>INPUT DATA FRESH</h2>
      <div class="header-actions">
        <button type="button" class="btn-logout-header active" onclick="toggleView('input')" id="btnViewInput">
          <span class="material-icons-round" style="font-size:16px;">edit_note</span> Input
        </button>
        <button type="button" class="btn-logout-header" onclick="toggleView('history')" id="btnViewHistory">
          <span class="material-icons-round" style="font-size:16px;">history</span> Riwayat
        </button>
        <form id="logoutForm" method="POST" action="{{ route('produksifresh.logout') }}">
          @csrf
          <button type="button" class="btn-logout-header" onclick="confirmLogout()">
            <span class="material-icons-round" style="font-size:16px;">logout</span>
          </button>
        </form>
      </div>
    </div>

    <!-- ==================== PANEL INPUT ==================== -->
    <div class="content" id="inputPanel">
      <div class="user-info no-print">
        <span class="material-icons-round" style="color: var(--primary); font-size: 32px;">account_circle</span>
        <div class="user-info-text">
          <h4>{{ auth()->guard('tally')->user()->name }}</h4>
          <p>{{ auth()->guard('tally')->user()->employee_code }} &bull; {{ $tipeInput === 'main' ? 'MAIN PRODUCT' : 'BY PRODUCT' }}</p>
        </div>
      </div>

      <div class="form-row form-row-2 no-print">
        <div class="form-group">
          <label>Nomor PO</label>
          <div class="input-with-icon">
            <span class="material-icons-round">receipt_long</span>
            <select id="poNumber">
              <option value="">-- Memuat daftar PO... --</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Kode Produk</label>
          <div class="input-with-icon">
            <span class="material-icons-round">qr_code_scanner</span>
            <input type="number" id="prodCode" placeholder="Ketik lalu tekan Enter ↵">
          </div>
          <div id="prodError" class="error-text" style="text-align:left;"></div>
        </div>
      </div>

      <div class="no-print">
        <div class="form-row form-row-2">
          <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" id="prodName" placeholder="Terisi otomatis..." readonly>
          </div>
          <div class="form-group">
            <label>Kode Produksi Batch (Preview)</label>
            <input type="text" id="batchCode" placeholder="Terisi otomatis..." readonly>
          </div>
        </div>

        <div class="form-group">
          <label>Kuantitas (Kg/Pcs)</label>
          <div class="input-with-icon">
            <span class="material-icons-round">scale</span>
            <input type="text" inputmode="numeric" id="qty" placeholder="0.0" autocomplete="off">
          </div>
        </div>

        <button class="btn btn-secondary" onclick="addToList()" id="btnAdd">
          <span class="material-icons-round">add_circle</span> Tambah ke Draft Sementara
        </button>
      </div>

      <div id="draftSection" class="draft-container hidden">
        <div class="draft-title">
          Data Belum Disimpan <span class="draft-badge" id="draftCount">0</span>
        </div>

        <div class="export-buttons no-print">
          <button class="btn btn-outline" onclick="cetakPdf()">
            <span class="material-icons-round" style="font-size:18px;">print</span> Cetak PDF
          </button>
          <button class="btn btn-outline" onclick="downloadExcel()">
            <span class="material-icons-round" style="font-size:18px;">download</span> Download Excel
          </button>
        </div>

        <div class="table-wrapper">
          <table id="tempTable">
            <thead>
              <tr>
                <th>No PO</th>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Kode Produksi</th>
                <th>Qty</th>
                <th class="no-print">Aksi</th>
              </tr>
            </thead>
            <tbody id="tempBody"></tbody>
          </table>
        </div>

        <button class="btn btn-primary no-print" onclick="submitData()" id="btnSubmit">
          <span class="material-icons-round">save</span> Simpan Semua ke Sistem
        </button>
      </div>
    </div>

    <!-- ==================== PANEL RIWAYAT ==================== -->
    <div class="content hidden" id="historyPanel">
      <div class="form-row form-row-2 no-print" style="margin-bottom:15px;">
        <div class="form-group">
          <label>Cari No PO</label>
          <div class="input-with-icon">
            <span class="material-icons-round">search</span>
            <input type="text" id="filterNoPo" placeholder="Ketik lalu Enter">
          </div>
        </div>
        <div class="form-group">
          <label>Tanggal</label>
          <div class="input-with-icon">
            <span class="material-icons-round">event</span>
            <input type="date" id="filterTanggal">
          </div>
        </div>
      </div>

      <!-- BARU: Cetak Form Resmi (Excel) - butuh No PO terisi di filter di atas -->
      <button class="btn btn-primary no-print" onclick="openExportModal()" id="btnOpenExportModal" style="margin-bottom:15px;">
        <span class="material-icons-round">fact_check</span> Cetak Form Resmi
      </button>

      <div class="table-wrapper">
        <table id="historyTable">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>No PO</th>
              <th>Kode</th>
              <th>Nama Produk</th>
              <th>Kode Produksi</th>
              <th>Qty</th>
              <th>Input Oleh</th>
            </tr>
          </thead>
          <tbody id="historyBody">
            <tr class="empty-row"><td colspan="7">Memuat data...</td></tr>
          </tbody>
        </table>
      </div>

      <div class="history-pagination no-print">
        <button class="btn btn-outline" onclick="changeHistoryPage(-1)" id="btnHistoryPrev">‹ Sebelumnya</button>
        <span id="historyPageInfo">-</span>
        <button class="btn btn-outline" onclick="changeHistoryPage(1)" id="btnHistoryNext">Berikutnya ›</button>
      </div>
    </div>
  </div>

  <!-- ==================== MODAL: CETAK FORM RESMI (EXCEL) ==================== -->
  <div id="exportModalOverlay" class="modal-overlay hidden no-print">
    <div class="modal-box">
      <div class="modal-title">Cetak Form Resmi (Excel)</div>
      <div class="modal-subtitle">Tanda tangani dengan ID &amp; password untuk membuat form FM-PROD-018.</div>

      <div class="form-group">
        <label>No PO</label>
        <input type="text" id="exportNoPo" readonly>
      </div>

      <div class="form-group">
        <label>Shift</label>
        <select id="exportShift">
          <option value="1">Shift 1</option>
          <option value="2">Shift 2</option>
        </select>
      </div>

      <div class="form-group">
        <label>ID Pegawai</label>
        <input type="text" id="exportEmployeeCode" placeholder="Contoh: APP01" autocomplete="off">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" id="exportPassword" placeholder="Password akun" autocomplete="off">
      </div>

      <div id="exportError" class="error-text"></div>
      <canvas id="exportQrCanvas" class="hidden"></canvas>

            <div class="modal-actions" style="flex-wrap: wrap;">
        <button class="btn btn-outline" onclick="closeExportModal()" id="btnExportCancel" style="flex:1 1 100%;">Batal</button>
        <button class="btn btn-primary" onclick="submitExportModal('xlsx')" id="btnExportSubmitXlsx" style="flex:1;">
          <span class="material-icons-round">qr_code_2</span> Unduh Excel
        </button>
        <button class="btn btn-primary" onclick="submitExportModal('pdf')" id="btnExportSubmitPdf" style="flex:1;">
          <span class="material-icons-round">picture_as_pdf</span> Unduh PDF
        </button>
      </div>
    </div>
  </div>

  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let productCache = [];
    let draftData = [];
    let editIndex = -1;
    let historyCurrentPage = 1;
    let historyLastPage = 1;
    let historyLoaded = false;

    async function apiFetch(url, options = {}) {
      const response = await fetch(url, {
        ...options,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, ...(options.headers || {}) },
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan pada server.');
      return data;
    }

    function showToast(message, isError = false) {
      const toast = document.getElementById('toast');
      toast.innerText = message;
      toast.classList.toggle('error', isError);
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function confirmLogout() {
      if (draftData.length > 0) {
        if (!confirm('Ada data yang belum disimpan ke sistem. Yakin ingin logout?')) return;
      }
      document.getElementById('logoutForm').submit();
    }

    window.addEventListener('DOMContentLoaded', () => {
      loadPurchaseOrders();
      loadProducts();
      document.getElementById('poNumber').focus();
    });

    // ==================== TAB SWITCH (Input / Riwayat) ====================
    function toggleView(target) {
      const isHistory = target === 'history';

      document.getElementById('inputPanel').classList.toggle('hidden', isHistory);
      document.getElementById('historyPanel').classList.toggle('hidden', !isHistory);

      document.getElementById('btnViewInput').classList.toggle('active', !isHistory);
      document.getElementById('btnViewHistory').classList.toggle('active', isHistory);

      if (isHistory && !historyLoaded) {
        loadHistory(1);
      }
    }

    async function loadPurchaseOrders() {
      const select = document.getElementById('poNumber');
      try {
        const list = await apiFetch('{{ route('produksifresh.purchase-orders') }}');
        if (list.length === 0) {
          select.innerHTML = `<option value="">-- Belum ada PO tercatat --</option>`;
          return;
        }
        select.innerHTML = `<option value="">-- Pilih Nomor PO --</option>` + list.map(po =>
          `<option value="${po.nomorPo}">${po.nomorPo} (${po.jenisPo} - ${po.tanggal})</option>`
        ).join('');
      } catch (err) {
        select.innerHTML = `<option value="">Gagal memuat daftar PO</option>`;
      }
    }

    async function loadProducts() {
      try {
        productCache = await apiFetch('{{ route('produksifresh.products') }}');
      } catch (err) {
        console.error(err);
      }
    }

    document.getElementById('prodCode').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); validateProduct(); }
    });

    document.getElementById('qty').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); addToList(); }
    });

    const qtyInputEl = document.getElementById('qty');
    qtyInputEl.addEventListener('input', function () {
      let rawDigits = this.value.replace(/[^0-9]/g, '');
      this.value = rawDigits.length > 0 ? (parseInt(rawDigits, 10) / 10).toFixed(1) : '';
    });

    /**
     * Preview Kode Produksi di client - HANYA untuk tampilan cepat
     * sebelum data disimpan. Nilai yang BENAR-BENAR tersimpan dihitung
     * ULANG di server saat submitData() (lihat
     * ProduksiFresh::generateKodeProduksi() di backend), tidak
     * dipercaya dari preview ini.
     */
    function previewKodeProduksi(categoryCode) {
      const d = new Date();
      const yearIndex = (d.getFullYear() - 2026 + 16) % 26;
      const yearCode = String.fromCharCode(65 + yearIndex);
      const monthCode = String.fromCharCode(d.getMonth() + 65);
      const dateCode = ('0' + d.getDate()).slice(-2);
      const day = d.getDay();
      let dayCode = 'CC';
      if ([1, 3, 5].includes(day)) dayCode = 'AA';
      else if ([2, 4, 6].includes(day)) dayCode = 'BB';
      return 'JBG' + yearCode + monthCode + dateCode + 'J' + categoryCode + dayCode + '0';
    }

    function validateProduct() {
      const code = document.getElementById('prodCode').value;
      const errDiv = document.getElementById('prodError');
      const nameInput = document.getElementById('prodName');
      const batchInput = document.getElementById('batchCode');

      errDiv.innerText = '';
      nameInput.value = '';
      batchInput.value = '';

      if (!code) return;

      const prod = productCache.find(p => p.code === String(code));
      if (!prod) {
        errDiv.innerText = '⚠ Kode tidak ditemukan atau bukan produk tipe ini!';
        return;
      }

      nameInput.value = prod.name;
      batchInput.value = previewKodeProduksi(prod.categoryCode || '00');
      document.getElementById('qty').focus();
    }

    function addToList() {
      const po = document.getElementById('poNumber').value;
      const code = document.getElementById('prodCode').value;
      const name = document.getElementById('prodName').value;
      const batch = document.getElementById('batchCode').value;
      const qtyDisplay = document.getElementById('qty').value;
      const numericQty = parseFloat(qtyDisplay);

      if (!po) { showToast('Pilih Nomor PO terlebih dahulu!', true); return; }
      if (!name || !batch || !qtyDisplay || isNaN(numericQty) || numericQty <= 0) {
        showToast('Lengkapi kode produk (Enter) dan kuantitas valid!', true);
        return;
      }

      const item = { poNumber: po, productCode: code, productName: name, productionCode: batch, qty: numericQty };

      if (editIndex > -1) {
        draftData[editIndex] = item;
        editIndex = -1;
        document.getElementById('btnAdd').innerHTML = `<span class="material-icons-round">add_circle</span> Tambah ke Draft Sementara`;
        showToast('Data sementara diperbarui.');
      } else {
        draftData.push(item);
        showToast('Ditambahkan ke draft.');
      }

      clearForm();
      renderDraft();
      document.getElementById('prodCode').focus();
    }

    function renderDraft() {
      const tbody = document.getElementById('tempBody');
      const draftSection = document.getElementById('draftSection');
      const draftCount = document.getElementById('draftCount');

      tbody.innerHTML = '';
      draftCount.innerText = draftData.length;

      if (draftData.length > 0) {
        draftSection.classList.remove('hidden');
        draftData.forEach((item, index) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${item.poNumber}</td>
            <td>${item.productCode}</td>
            <td>${item.productName}</td>
            <td>${item.productionCode}</td>
            <td><strong>${parseFloat(item.qty).toFixed(1)}</strong></td>
            <td class="no-print">
              <button class="action-btn btn-edit" onclick="editDraft(${index})" title="Edit"><span class="material-icons-round" style="font-size:16px;">edit</span></button>
              <button class="action-btn btn-delete" onclick="deleteDraft(${index})" title="Hapus"><span class="material-icons-round" style="font-size:16px;">delete</span></button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        draftSection.classList.add('hidden');
      }
    }

    function editDraft(index) {
      const item = draftData[index];
      document.getElementById('poNumber').value = item.poNumber;
      document.getElementById('prodCode').value = item.productCode;
      document.getElementById('prodName').value = item.productName;
      document.getElementById('batchCode').value = item.productionCode;
      document.getElementById('qty').value = parseFloat(item.qty).toFixed(1);
      editIndex = index;
      document.getElementById('btnAdd').innerHTML = `<span class="material-icons-round">update</span> Update Draft`;
    }

    function deleteDraft(index) {
      if (confirm('Hapus data ini dari draft?')) {
        draftData.splice(index, 1);
        if (editIndex === index) {
          editIndex = -1;
          clearForm();
          document.getElementById('btnAdd').innerHTML = `<span class="material-icons-round">add_circle</span> Tambah ke Draft Sementara`;
        }
        renderDraft();
      }
    }

    async function submitData() {
      if (draftData.length === 0) { showToast('Tidak ada data di draft untuk disimpan!', true); return; }

      const btn = document.getElementById('btnSubmit');
      btn.disabled = true;
      btn.innerHTML = `<span class="material-icons-round">sync</span> Menyimpan Semua...`;

      const payload = {
        rows: draftData.map(d => ({ no_po: d.poNumber, kode_produk: d.productCode, qty: d.qty })),
      };

      try {
        const res = await apiFetch('{{ route('produksifresh.store') }}', { method: 'POST', body: JSON.stringify(payload) });
        showToast(res.message || 'Seluruh data berhasil masuk sistem!');
        draftData = [];
        renderDraft();
        clearForm();
        document.getElementById('prodCode').focus();

        // Data baru masuk sistem - paksa reload riwayat kalau nanti dibuka lagi
        historyLoaded = false;
      } catch (err) {
        showToast('Gagal: ' + err.message, true);
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<span class="material-icons-round">save</span> Simpan Semua ke Sistem`;
      }
    }

    function clearForm() {
      document.getElementById('prodCode').value = '';
      document.getElementById('prodName').value = '';
      document.getElementById('batchCode').value = '';
      document.getElementById('qty').value = '';
      document.getElementById('prodError').innerText = '';
    }

    function cetakPdf() {
      if (draftData.length === 0) { showToast('Tidak ada data di draft untuk dicetak!', true); return; }
      window.print();
    }

    function downloadExcel() {
      if (draftData.length === 0) { showToast('Tidak ada data di draft untuk diunduh!', true); return; }

      const rows = draftData.map(d => ({
        'No PO': d.poNumber,
        'Kode Produk': d.productCode,
        'Nama Produk': d.productName,
        'Kode Produksi': d.productionCode,
        'Qty': parseFloat(d.qty).toFixed(1),
      }));

      const ws = XLSX.utils.json_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Draft Produksi Fresh');

      const tanggal = new Date().toISOString().slice(0, 10);
      XLSX.writeFile(wb, `draft-produksi-fresh-${tanggal}.xlsx`);
    }

    // ==================== RIWAYAT (DATA YANG SUDAH TERSIMPAN) ====================
    async function loadHistory(page = 1) {
      const tbody = document.getElementById('historyBody');
      tbody.innerHTML = `<tr class="empty-row"><td colspan="7">Memuat data...</td></tr>`;

      const params = new URLSearchParams({ page });
      const noPo = document.getElementById('filterNoPo').value.trim();
      const tanggal = document.getElementById('filterTanggal').value;
      if (noPo) params.set('no_po', noPo);
      if (tanggal) { params.set('tanggal_dari', tanggal); params.set('tanggal_sampai', tanggal); }

      try {
        const res = await apiFetch('{{ route('produksifresh.history') }}?' + params.toString());
        renderHistory(res.data);
        historyCurrentPage = res.meta.current_page;
        historyLastPage = res.meta.last_page;
        document.getElementById('historyPageInfo').innerText = `Hal ${historyCurrentPage}/${historyLastPage} • ${res.meta.total} data`;
        document.getElementById('btnHistoryPrev').disabled = historyCurrentPage <= 1;
        document.getElementById('btnHistoryNext').disabled = historyCurrentPage >= historyLastPage;
        historyLoaded = true;
      } catch (err) {
        tbody.innerHTML = `<tr class="empty-row"><td colspan="7">Gagal memuat riwayat.</td></tr>`;
        showToast('Gagal memuat riwayat: ' + err.message, true);
      }
    }

    function renderHistory(rows) {
      const tbody = document.getElementById('historyBody');

      if (!rows || rows.length === 0) {
        tbody.innerHTML = `<tr class="empty-row"><td colspan="7">Belum ada data tersimpan.</td></tr>`;
        return;
      }

      tbody.innerHTML = rows.map(r => `
        <tr>
          <td>${r.tanggal}</td>
          <td>${r.noPo}</td>
          <td>${r.kodeProduk}</td>
          <td>${r.namaProduk}</td>
          <td>${r.kodeProduksi}</td>
          <td><strong>${r.qty.toFixed(1)}</strong></td>
          <td>${r.inputOleh}</td>
        </tr>
      `).join('');
    }

    function changeHistoryPage(delta) {
      const target = historyCurrentPage + delta;
      if (target < 1 || target > historyLastPage) return;
      loadHistory(target);
    }

    document.getElementById('filterNoPo').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); loadHistory(1); }
    });
    document.getElementById('filterTanggal').addEventListener('change', () => loadHistory(1));

    // ==================== EXPORT FORM RESMI (EXCEL + QR TANDA TANGAN) ====================
    // Pola tanda tangan mengikuti halaman Rekap (tally-pro): verifikasi
    // ID + password ke endpoint stateless 'tally.rekap.verify-signature'
    // (tidak membuat sesi baru, tidak menyimpan histori approval),
    // lalu QR (bwip-js) dibuat di canvas dan dikirim sebagai base64 PNG
    // ke backend untuk ditempel sebagai gambar di file .xlsx
    // (PhpSpreadsheet), bersama logo perusahaan yang sudah statis di server.

    function openExportModal() {
      const noPo = document.getElementById('filterNoPo').value.trim();
      if (!noPo) {
        showToast('Isi/cari No PO dulu di kolom filter sebelum cetak form resmi!', true);
        return;
      }

      document.getElementById('exportNoPo').value = noPo;
      document.getElementById('exportShift').value = '1';
      document.getElementById('exportEmployeeCode').value = '';
      document.getElementById('exportPassword').value = '';
      document.getElementById('exportError').innerText = '';
      document.getElementById('exportModalOverlay').classList.remove('hidden');
      document.getElementById('exportEmployeeCode').focus();
    }

    function closeExportModal() {
      document.getElementById('exportModalOverlay').classList.add('hidden');
    }

        document.getElementById('exportPassword').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); submitExportModal('xlsx'); }
    });

        async function submitExportModal(format) {
      const noPo = document.getElementById('exportNoPo').value;
      const shift = document.getElementById('exportShift').value;
      const employeeCode = document.getElementById('exportEmployeeCode').value.trim();
      const password = document.getElementById('exportPassword').value;
      const errDiv = document.getElementById('exportError');
      const btn = format === 'pdf'
        ? document.getElementById('btnExportSubmitPdf')
        : document.getElementById('btnExportSubmitXlsx');
      const otherBtn = format === 'pdf'
        ? document.getElementById('btnExportSubmitXlsx')
        : document.getElementById('btnExportSubmitPdf');

      errDiv.innerText = '';

      if (!employeeCode || !password) {
        errDiv.innerText = 'ID Pegawai dan Password wajib diisi!';
        return;
      }

            btn.disabled = true;
      otherBtn.disabled = true;
      document.getElementById('btnExportCancel').disabled = true;
      btn.innerHTML = `<span class="material-icons-round">sync</span> Memverifikasi...`;

      try {
        // 1. Verifikasi ID + Password (reuse endpoint Rekap Tally Pro - stateless)
        const verifyRes = await apiFetch('{{ route('tally.rekap.verify-signature') }}', {
          method: 'POST',
          body: JSON.stringify({ employee_code: employeeCode, password: password }),
        }).catch(err => ({ valid: false, message: err.message }));

        if (!verifyRes.valid) {
          errDiv.innerText = verifyRes.message || 'ID Pengguna atau Password salah!';
          return;
        }

        // 2. Generate QR code (bwip-js) di canvas tersembunyi
        btn.innerHTML = `<span class="material-icons-round">sync</span> Membuat QR...`;
        const canvas = document.getElementById('exportQrCanvas');
        bwipjs.toCanvas(canvas, {
          bcid: 'qrcode',
          text: `PRODUKSI FRESH\nNO PO: ${noPo}\nSHIFT: ${shift}\nNAMA: ${verifyRes.name}\nID: ${employeeCode}\nDATE: ${new Date().toLocaleString('id-ID')}`,
          scale: 2,
        });
        const qrDataUrl = canvas.toDataURL('image/png');

        // 3. Kirim ke backend untuk digenerate jadi file .xlsx (PhpSpreadsheet)
        btn.innerHTML = `<span class="material-icons-round">sync</span> Menyiapkan File...`;
        const response = await fetch('{{ route('produksifresh.export-xlsx') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
                    body: JSON.stringify({
            no_po: noPo,
            shift: shift,
            signer_name: verifyRes.name,
            qr_base64: qrDataUrl,
            format: format,
          }),
        });

        const contentType = response.headers.get('Content-Type') || '';

        if (!response.ok || contentType.includes('application/json')) {
          const errData = await response.json().catch(() => ({}));
          throw new Error(errData.message || 'Gagal membuat file Excel.');
        }

        const blob = await response.blob();
        const disposition = response.headers.get('Content-Disposition') || '';
        const match = disposition.match(/filename="?([^"]+)"?/);
        const filename = match ? match[1] : `Form_Fresh_${noPo}.${format}`;

        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(downloadUrl);

        showToast('Form resmi berhasil diunduh!');
        closeExportModal();
      } catch (err) {
        errDiv.innerText = err.message || 'Terjadi kesalahan, coba lagi.';
            } finally {
        btn.disabled = false;
        otherBtn.disabled = false;
        document.getElementById('btnExportCancel').disabled = false;
        btn.innerHTML = format === 'pdf'
          ? `<span class="material-icons-round">picture_as_pdf</span> Unduh PDF`
          : `<span class="material-icons-round">qr_code_2</span> Unduh Excel`;
      }
    }
  </script>
</body>
</html>