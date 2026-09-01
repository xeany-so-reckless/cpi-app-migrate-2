<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Outbound - CPI App Migrate</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8f4ee;
            --surface: #ffffff;
            --surface-hover: #fbf6ee;
            --line: #ede2d3;
            --amber: #c2701a;
            --amber-dim: #9a5813;
            --amber-soft: #fbead6;
            --hazard: #d97706;
            --danger: #dc2626;
            --success: #16a34a;
            --text: #16232e;
            --muted: #7a6f60;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); }
        .mono { font-family: 'JetBrains Mono', monospace; }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 5%;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; color: var(--text); }
        .logo img { height: 38px; border-radius: 6px; padding: 3px 6px; }

        .page-header { padding: 30px 5% 18px; }
        .page-eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: var(--amber-dim);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .page-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 2.4rem;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            color: var(--text);
        }

        .btn {
            border: none; border-radius: 9px; padding: 10px 18px;
            font-size: 0.85rem; font-weight: 700; cursor: pointer;
            transition: all .15s ease; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-amber { background: var(--amber); color: #fff; }
        .btn-amber:hover { background: var(--amber-dim); }
        .btn-outline { background: #fff; color: var(--muted); border: 1px solid var(--line); }
        .btn-outline:hover { border-color: var(--amber); color: var(--amber-dim); }
        .btn-danger-outline { background: #fff; color: var(--danger); border: 1px solid #f3caca; }
        .btn-danger-outline:hover { background: #fef2f2; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }

        .container { padding: 0 5% 60px; max-width: 1100px; margin: 0 auto; }
        .card {
            background: var(--surface); border: 1px solid var(--line); border-radius: 14px;
            padding: 24px; margin-bottom: 22px;
        }
        .card-title {
            font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1.15rem;
            text-transform: uppercase; letter-spacing: .3px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px; color: var(--text);
        }
        .card-title .material-symbols-outlined { color: var(--amber); font-size: 22px; }

        label {
            font-weight: 700; font-size: 0.75rem; color: var(--muted);
            text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; display: block;
        }
        .form-control, select.form-control {
            width: 100%; height: 46px; border-radius: 9px; border: 1px solid var(--line);
            padding: 0 13px; font-size: 0.9rem; color: var(--text); background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--amber); box-shadow: 0 0 0 3px var(--amber-soft); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 6px; }

        .cell-picker-row { display: flex; gap: 10px; align-items: flex-end; margin-bottom: 16px; }
        .cell-picker-row > div:first-child { flex: 1; }

        .bag-list { border: 1px solid var(--line); border-radius: 10px; overflow: hidden; margin-bottom: 14px; }
        .bag-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 14px;
            border-bottom: 1px solid var(--line); font-size: 0.85rem;
        }
        .bag-item:last-child { border-bottom: none; }
        .bag-item:hover { background: var(--surface-hover); }
        .bag-item input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--amber); cursor: pointer; }
        .bag-chip {
            font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 0.75rem;
            color: var(--amber-dim); background: var(--amber-soft); padding: 2px 8px; border-radius: 5px;
        }
        .bag-meta { color: var(--muted); font-size: 0.78rem; }
        .bag-kg { margin-left: auto; font-family: 'JetBrains Mono', monospace; font-weight: 700; }
        .bag-generic-badge {
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
            color: var(--hazard); background: #fef3e2; padding: 2px 7px; border-radius: 5px;
        }
        .bag-empty { padding: 20px; text-align: center; color: var(--muted); font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        thead th {
            text-align: left; font-size: 0.68rem; color: var(--muted); text-transform: uppercase;
            letter-spacing: .5px; padding: 10px 12px; border-bottom: 1px solid var(--line);
        }
        tbody td { padding: 12px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        .num { font-family: 'JetBrains Mono', monospace; text-align: right; }
        .empty-state { text-align: center; padding: 30px; color: var(--muted); font-size: 0.85rem; }

        .summary-total { display: flex; gap: 24px; padding: 14px 4px 4px; font-size: 0.85rem; color: var(--muted); }
        .summary-total b { color: var(--text); font-family: 'JetBrains Mono', monospace; }

        .save-bar {
            position: sticky; bottom: 0; background: var(--surface); border-top: 1px solid var(--line);
            padding: 16px 5%; display: flex; justify-content: flex-end; gap: 10px; margin: 0 -5%;
        }
    </style>
</head>
<body>

@php
    $currentUser = auth()->guard('tally')->user();
@endphp

<nav>
    <div class="logo">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="height:38px; background:#fff;">
        <span>OUTBOUND</span>
    </div>
    <div style="display:flex; align-items:center; gap:16px;">
    <a href="{{ route('warehouse.outbound.history') }}" style="color: var(--muted); text-decoration:none; font-size:0.82rem; font-weight:600;">Riwayat</a>
    <span class="mono" style="font-size:0.78rem; color: var(--muted);">{{ $currentUser->name }}</span>
    <form id="logoutForm" method="POST" action="{{ route('warehouse.outbound.logout') }}">
            @csrf
            <button type="button" class="btn btn-danger-outline" onclick="confirmLogout()">Keluar</button>
        </form>
    </div>
</nav>

<div class="page-header">
    <div class="page-eyebrow">Checker &middot; Barang Keluar</div>
    <div class="page-title">Input Outbound</div>
</div>

<div class="container">

    {{-- ==================== FORM HEADER DO ==================== --}}
    <div class="card">
        <div class="card-title"><span class="material-symbols-outlined">assignment</span> Data Pengiriman (DO)</div>
        <div class="form-grid">
            <div><label>Tanggal</label><input type="date" id="f_tanggal" class="form-control"></div>
            <div><label>No DO</label><input type="text" id="f_no_do" class="form-control" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()"></div>
            <div><label>Nama Customer</label><input type="text" id="f_nama_customer" class="form-control"></div>
            <div><label>Jam Muat</label><input type="time" id="f_jam_muat" class="form-control"></div>
            <div><label>No Polisi</label><input type="text" id="f_no_pol" class="form-control" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()"></div>
            <div><label>Nama Driver</label><input type="text" id="f_nama_driver" class="form-control"></div>
        </div>
    </div>

    {{-- ==================== PILIH CELL + CHECKLIST BAG ==================== --}}
    <div class="card">
        <div class="card-title"><span class="material-symbols-outlined">grid_view</span> Tambah Cell yang Dimuat</div>

        <div class="cell-picker-row">
            <div>
                <label>Kode Cell</label>
                <select id="cellPicker" class="form-control" onchange="onCellPicked(this.value)">
                    <option value="">-- Pilih Kode Cell --</option>
                </select>
            </div>
            <button class="btn btn-outline" onclick="loadCellOptions()">
                <span class="material-symbols-outlined" style="font-size:16px;">refresh</span> Muat Ulang
            </button>
        </div>

        <div id="cellDetailPanel" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div class="mono" style="font-size:0.85rem;">
                    Isi Cell <span id="detailKodeCell" class="bag-chip"></span>
                </div>
                <button class="btn btn-outline" onclick="okAllBags()">
                    <span class="material-symbols-outlined" style="font-size:16px;">done_all</span> OK ALL
                </button>
            </div>

            <div class="bag-list" id="bagListContainer">
                <div class="bag-empty">Memuat isi cell...</div>
            </div>

            <div style="text-align:right;">
                <button class="btn btn-amber" id="btnAddCellToDo" onclick="addCellToDo()">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span> Tambahkan Cell Ini ke DO
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== DATA TIR (OPSIONAL - KHUSUS TRUK BESAR) ==================== --}}
    <div class="card">
        <div class="card-title"><span class="material-symbols-outlined">local_shipping</span> Data Tir (Opsional)</div>
        <p style="font-size:0.82rem; color:var(--muted); margin-bottom:14px;">
            Isi kalau truk besar (misal tronton) - kosongkan kalau customer pakai mobil kecil.
        </p>

        <div class="bag-list" id="tirListContainer">
            <div class="bag-empty">Belum ada Tir ditambahkan.</div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
            <button class="btn btn-outline" id="btnAddTir" onclick="addTirRow()">
                <span class="material-symbols-outlined" style="font-size:16px;">add</span> Tambah Tir
            </button>
            <div class="mono" style="font-size:0.85rem; color:var(--muted);">
                Total Bag (Tir): <b id="tirTotalBag" style="color:var(--text);">0</b>
            </div>
        </div>
    </div>

    {{-- ==================== DAFTAR CELL DALAM DO INI ==================== --}}
    <div class="card">
        <div class="card-title"><span class="material-symbols-outlined">checklist</span> Cell dalam DO Ini</div>
        <table>
            <thead>
                <tr>
                    <th>Kode Cell</th>
                    <th class="num">Jumlah Bag</th>
                    <th class="num">Total Kg</th>
                    <th style="width:60px;"></th>
                </tr>
            </thead>
            <tbody id="doCellTableBody">
                <tr><td colspan="4" class="empty-state">Belum ada Cell yang ditambahkan.</td></tr>
            </tbody>
        </table>
        <div class="summary-total">
            <div>Total Bag: <b id="grandTotalBag">0</b></div>
            <div>Total Kg: <b id="grandTotalKg">0</b></div>
        </div>
    </div>

</div>

<div class="save-bar">
    <button class="btn btn-outline" onclick="resetForm()">Reset</button>
    <button class="btn btn-amber" id="btnSave" onclick="submitOutbound()">
        <span class="material-symbols-outlined" style="font-size:18px;">save</span> Simpan (Selesai Muat)
    </button>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // State DO yang sedang dibangun di sisi client
    let doCells = []; // [{ cellId, kodeCell, bags: [...], totalBag, totalKg }]
    let tirRows = []; // [{ jumlahBag: number|null }] - urutan array = label Tir 1, Tir 2, dst
    let currentCell = null; // cell yang sedang dibuka detailnya
    let currentBags = []; // hasil availableBags() untuk currentCell
    let cellOptionsCache = [];

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
            title: 'Keluar dari Sistem?',
            html: '<div style="font-size:14px;color:#7a6f60;">Anda akan keluar dari <b>Outbound</b>.</div>',
            icon: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#7a6f60',
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('logoutForm').submit();
        });
    }

    // ==================== LOAD DROPDOWN CELL ====================
    async function loadCellOptions() {
        const select = document.getElementById('cellPicker');
        select.innerHTML = `<option value="">-- Memuat daftar cell... --</option>`;
        try {
            const list = await apiFetch('{{ route("warehouse.outbound.cells") }}');
            cellOptionsCache = list;

            const dipakai = new Set(doCells.map(c => c.cellId));
            const tersedia = list.filter(c => !dipakai.has(c.id));

            if (tersedia.length === 0) {
                select.innerHTML = `<option value="">-- Tidak ada cell tersedia --</option>`;
                return;
            }

            select.innerHTML = `<option value="">-- Pilih Kode Cell --</option>` + tersedia.map(c =>
                `<option value="${c.id}">${c.kodeCell} (${c.stockBag} Bag / ${c.stockKg} Kg)</option>`
            ).join('');
        } catch (err) {
            select.innerHTML = `<option value="">Gagal memuat: ${err.message}</option>`;
        }
    }

    // ==================== KLIK / PILIH 1 CELL -> TAMPILKAN ISI ====================
    async function onCellPicked(cellId) {
        const panel = document.getElementById('cellDetailPanel');
        const container = document.getElementById('bagListContainer');

        if (!cellId) {
            panel.style.display = 'none';
            currentCell = null;
            currentBags = [];
            return;
        }

        panel.style.display = 'block';
        container.innerHTML = `<div class="bag-empty">Memuat isi cell...</div>`;

        try {
            const res = await apiFetch(`{{ url('warehouse/outbound/cells') }}/${cellId}`);
            currentCell = res.cell;
            currentBags = res.bags.map((b, idx) => ({ ...b, _key: bagKey(b), _checked: false }));

            document.getElementById('detailKodeCell').innerText = currentCell.kodeCell;
            renderBagList();
        } catch (err) {
            container.innerHTML = `<div class="bag-empty">Gagal memuat isi cell: ${err.message}</div>`;
        }
    }

    function bagKey(b) {
        return b.type === 'generic' ? 'generic' : `${b.batch_id}-${b.nomor_bag}`;
    }

    function renderBagList() {
        const container = document.getElementById('bagListContainer');

        if (currentBags.length === 0) {
            container.innerHTML = `<div class="bag-empty">Cell ini tidak memiliki bag yang tersedia untuk dikeluarkan.</div>`;
            return;
        }

        container.innerHTML = currentBags.map(b => {
            if (b.type === 'generic') {
                return `
                    <label class="bag-item" style="cursor:pointer;">
                        <input type="checkbox" ${b._checked ? 'checked' : ''} onchange="toggleBag('${b._key}', this.checked)">
                        <span class="bag-generic-badge">Stock Adjustment</span>
                        <span class="bag-meta">${b.jumlah_bag} bag tanpa identitas batch (hasil penyesuaian manual)</span>
                        <span class="bag-kg">${Number(b.kg).toLocaleString('id-ID', {maximumFractionDigits:1})} Kg</span>
                    </label>
                `;
            }

            return `
                <label class="bag-item" style="cursor:pointer;">
                    <input type="checkbox" ${b._checked ? 'checked' : ''} onchange="toggleBag('${b._key}', this.checked)">
                    <span class="bag-chip">Bag #${b.nomor_bag}</span>
                    <span class="bag-meta">${b.kode_produksi ?? '-'} &middot; ${b.produk ?? '-'}</span>
                    <span class="bag-kg">${Number(b.kg).toLocaleString('id-ID', {maximumFractionDigits:1})} Kg</span>
                </label>
            `;
        }).join('');
    }

    function toggleBag(key, checked) {
        const bag = currentBags.find(b => b._key === key);
        if (bag) bag._checked = checked;
    }

    function okAllBags() {
        currentBags.forEach(b => b._checked = true);
        renderBagList();
    }

    // ==================== TAMBAH CELL (YANG SUDAH DICENTANG) KE DO ====================
    function addCellToDo() {
        if (!currentCell) return;

        const checked = currentBags.filter(b => b._checked);
        if (checked.length === 0) {
            Swal.fire({ title: 'Belum ada bag dicentang', text: 'Centang minimal 1 bag atau klik OK ALL.', icon: 'warning' });
            return;
        }

        const totalBag = checked.reduce((sum, b) => sum + (b.type === 'generic' ? b.jumlah_bag : 1), 0);
        const totalKg = checked.reduce((sum, b) => sum + Number(b.kg), 0);

        doCells.push({
            cellId: currentCell.id,
            kodeCell: currentCell.kodeCell,
            bags: checked.map(b => b.type === 'generic'
                ? { type: 'generic' }
                : { type: 'batch', batch_id: b.batch_id, nomor_bag: b.nomor_bag }
            ),
            totalBag,
            totalKg,
        });

        // Reset panel & dropdown
        document.getElementById('cellPicker').value = '';
        document.getElementById('cellDetailPanel').style.display = 'none';
        currentCell = null;
        currentBags = [];

        renderDoCellTable();
        loadCellOptions(); // cell yang baru ditambahkan hilang dari pilihan
    }

    function removeCellFromDo(index) {
        doCells.splice(index, 1);
        renderDoCellTable();
        loadCellOptions();
    }

    function renderDoCellTable() {
        const tbody = document.getElementById('doCellTableBody');

        if (doCells.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="empty-state">Belum ada Cell yang ditambahkan.</td></tr>`;
        } else {
            tbody.innerHTML = doCells.map((c, idx) => `
                <tr>
                    <td><span class="bag-chip">${c.kodeCell}</span></td>
                    <td class="num">${c.totalBag}</td>
                    <td class="num">${c.totalKg.toLocaleString('id-ID', {maximumFractionDigits:1})}</td>
                    <td>
                        <button class="btn btn-danger-outline" style="padding:6px 10px;" onclick="removeCellFromDo(${idx})">
                            <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        const grandBag = doCells.reduce((a, b) => a + b.totalBag, 0);
        const grandKg = doCells.reduce((a, b) => a + b.totalKg, 0);
        document.getElementById('grandTotalBag').innerText = grandBag.toLocaleString('id-ID');
        document.getElementById('grandTotalKg').innerText = grandKg.toLocaleString('id-ID', {maximumFractionDigits:1});
    }

    // ==================== DATA TIR (OPSIONAL) ====================
    const MAX_TIR = 20;

    function addTirRow() {
        if (tirRows.length >= MAX_TIR) {
            Swal.fire({ title: 'Maksimal 20 Tir', icon: 'warning' });
            return;
        }
        tirRows.push({ jumlahBag: null });
        renderTirList();
        // fokus otomatis ke input yang baru muncul
        const inputs = document.querySelectorAll('.tir-jumlah-input');
        if (inputs.length > 0) inputs[inputs.length - 1].focus();
    }

    function removeTirRow(index) {
        tirRows.splice(index, 1);
        renderTirList();
    }

    function updateTirJumlah(index, value) {
        tirRows[index].jumlahBag = value === '' ? null : parseInt(value, 10);
        updateTirTotal();
    }

    function renderTirList() {
        const container = document.getElementById('tirListContainer');
        const btnAdd = document.getElementById('btnAddTir');
        btnAdd.disabled = tirRows.length >= MAX_TIR;

        if (tirRows.length === 0) {
            container.innerHTML = `<div class="bag-empty">Belum ada Tir ditambahkan.</div>`;
            updateTirTotal();
            return;
        }

        container.innerHTML = tirRows.map((row, idx) => `
            <div class="bag-item">
                <span class="bag-chip">Tir ${idx + 1}</span>
                <input type="number" min="1" class="form-control tir-jumlah-input"
                    style="max-width:140px; height:38px;"
                    placeholder="Jumlah Bag"
                    value="${row.jumlahBag ?? ''}"
                    oninput="updateTirJumlah(${idx}, this.value)">
                <button class="btn btn-danger-outline" style="margin-left:auto; padding:6px 10px;" onclick="removeTirRow(${idx})">
                    <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                </button>
            </div>
        `).join('');

        updateTirTotal();
    }

    function updateTirTotal() {
        const total = tirRows.reduce((sum, r) => sum + (Number(r.jumlahBag) || 0), 0);
        document.getElementById('tirTotalBag').innerText = total.toLocaleString('id-ID');
    }

    // Hanya baris yang benar-benar terisi angka valid yang dikirim ke
    // server - baris kosong dilewati begitu saja (tidak mengganggu
    // penomoran Tir 1, Tir 2, dst yang terkirim, karena backend
    // menomori ulang berdasarkan urutan array yang dikirim).
    function collectTirPayload() {
        return tirRows
            .filter(r => Number.isInteger(r.jumlahBag) && r.jumlahBag > 0)
            .map(r => r.jumlahBag);
    }

    // ==================== SAVE ====================
    async function submitOutbound() {
        const payload = {
            tanggal: document.getElementById('f_tanggal').value,
            no_do: document.getElementById('f_no_do').value.trim(),
            nama_customer: document.getElementById('f_nama_customer').value.trim(),
            jam_muat: document.getElementById('f_jam_muat').value,
            no_pol: document.getElementById('f_no_pol').value.trim(),
            nama_driver: document.getElementById('f_nama_driver').value.trim(),
            cells: doCells.map(c => ({ cell_id: c.cellId, bags: c.bags })),
            tirs: collectTirPayload(),
        };

        if (!payload.tanggal || !payload.no_do || !payload.nama_customer || !payload.jam_muat || !payload.no_pol || !payload.nama_driver) {
            Swal.fire({ title: 'Data belum lengkap', text: 'Mohon lengkapi seluruh data pengiriman (DO).', icon: 'warning' });
            return;
        }
        if (payload.cells.length === 0) {
            Swal.fire({ title: 'Belum ada Cell', text: 'Tambahkan minimal 1 Cell sebelum menyimpan.', icon: 'warning' });
            return;
        }

        const btn = document.getElementById('btnSave');
        btn.disabled = true;

        try {
            const res = await apiFetch('{{ route("warehouse.outbound.store") }}', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            await Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success' });
            resetForm();
        } catch (err) {
            Swal.fire({ title: 'Gagal Menyimpan', text: err.message, icon: 'error' });
        } finally {
            btn.disabled = false;
        }
    }

    function resetForm() {
        document.getElementById('f_tanggal').value = new Date().toISOString().split('T')[0];
        document.getElementById('f_no_do').value = '';
        document.getElementById('f_nama_customer').value = '';
        document.getElementById('f_jam_muat').value = '';
        document.getElementById('f_no_pol').value = '';
        document.getElementById('f_nama_driver').value = '';
        doCells = [];
        tirRows = [];
        currentCell = null;
        currentBags = [];
        document.getElementById('cellDetailPanel').style.display = 'none';
        renderDoCellTable();
        renderTirList();
        loadCellOptions();
    }

    window.addEventListener('DOMContentLoaded', () => {
        document.getElementById('f_tanggal').value = new Date().toISOString().split('T')[0];
        loadCellOptions();
        renderDoCellTable();
        renderTirList();
    });
</script>
</body>
</html>