<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Serah Terima Hasil Produksi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --ink: #0b1220;
            --canvas: #eef2f6;
            --surface: #ffffff;
            --line: #e1e7ef;
            --muted: #64748b;
            --accent: #0f7a3d;
            --accent-dark: #0a5129;
            --accent-soft: #e3f5ea;
            --amber: #b45309;
            --amber-soft: #fef3c7;
            --red: #b91c1c;
            --red-soft: #fee2e2;
            --indigo: #3730a3;
            --indigo-soft: #e6e4fb;
        }

        body {
            background-color: var(--canvas);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
        }

        .mono-tag {
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.02em;
        }

        /* ---------- NAVBAR ---------- */
        .navbar-custom {
            background-color: var(--ink);
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .navbar-custom .navbar-brand {
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .navbar-custom .navbar-brand small {
            display: block;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 10px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .role-pill {
            background: var(--accent-soft);
            color: var(--accent-dark);
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 12px;
            padding: 6px 14px;
            border-radius: 999px;
            letter-spacing: 0.01em;
        }
        .btn-ghost-logout {
            background-color: red;
            border: 1px solid rgba(255,255,255,.25);
            color: #f1f5f9;
            font-weight: 600;
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 13px;
            transition: .2s;
        }
        .btn-ghost-logout:hover { background: var(--red); border-color: var(--red); color: #fff; }

        /* ---------- CARDS & BUTTONS ---------- */
        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
        }
        h5.fw-bold, .card h5 {
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
            color: var(--ink);
        }
        label {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 12.5px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .form-control {
            border-radius: 9px;
            border: 1px solid var(--line);
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 .2rem var(--accent-soft);
        }

        .btn { border-radius: 9px; font-family: 'Inter', sans-serif; font-weight: 600; }
        .btn-success { background: var(--accent); border-color: var(--accent); }
        .btn-success:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
        .btn-primary { background: var(--indigo); border-color: var(--indigo); }
        .btn-primary:hover { background: #2a2680; border-color: #2a2680; }
        .btn-dark { background: var(--ink); border-color: var(--ink); }
        .btn-outline-primary { color: var(--indigo); border-color: var(--indigo); }
        .btn-outline-primary:hover { background: var(--indigo); border-color: var(--indigo); }
        .btn-outline-warning { color: var(--amber); border-color: var(--amber); }
        .btn-outline-warning:hover { background: var(--amber); border-color: var(--amber); color: #fff; }
        .btn-outline-danger { color: var(--red); border-color: var(--red); }
        .btn-outline-danger:hover { background: var(--red); border-color: var(--red); }

        /* ---------- MANIFEST TABLE ---------- */
        .manifest-head {
            display: flex; align-items: center; gap: 14px;
            padding-bottom: 14px; margin-bottom: 4px;
            border-bottom: 1px solid var(--line);
        }
        .manifest-title { font-family: 'Manrope', sans-serif; font-weight: 800; font-size: 12pt; color: var(--ink); }
        .manifest-sub { font-size: 8pt; color: var(--muted); letter-spacing: .04em; text-transform: uppercase; }

        .kode-chip {
            display: inline-block;
            background: var(--ink);
            color: #6ee7a8;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            font-size: 6.6pt;
            letter-spacing: 0.03em;
            padding: 3px 6px;
            border-radius: 5px;
        }
        .cell-chip {
            display: inline-block;
            background: var(--accent-soft);
            color: var(--accent-dark);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            font-size: 7pt;
            padding: 2px 7px;
            border-radius: 5px;
        }

        .status-pill {
            display: inline-block;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 5.6pt;
            letter-spacing: .03em;
            padding: 1.5px 6px;
            border-radius: 999px;
        }
        .status-pill.ok { background: var(--accent-soft); color: var(--accent-dark); }
        .status-pill.reject { background: var(--red-soft); color: var(--red); }
        .status-pill.pending { background: var(--amber-soft); color: var(--amber); }

        .card-neon-verified {
            background-color: var(--accent-soft) !important;
            border: 1px solid var(--accent) !important;
            color: var(--accent-dark) !important;
            font-weight: bold;
        }
        .btn-xs { padding: 1px 4px; font-size: 6.5pt; line-height: 1.2; border-radius: 4px; }

        .empty-state {
            border: 1px dashed var(--line);
            border-radius: 14px;
            background: var(--surface);
            padding: 48px 20px;
            text-align: center;
            color: var(--muted);
            font-family: 'Inter', sans-serif;
        }

        /* SweetAlert Modern */
        .modern-popup{ box-shadow:0 25px 60px rgba(0,0,0,.18)!important; font-family:'Inter',sans-serif; }
        .modern-confirm-btn{ border-radius:12px!important; padding:12px 24px!important; font-size:15px!important; font-weight:600!important; transition:.25s; }
        .modern-confirm-btn:hover{ transform:translateY(-2px); box-shadow:0 12px 30px rgba(220,53,69,.35); }
        .modern-cancel-btn{ border-radius:12px!important; padding:12px 24px!important; font-size:15px!important; font-weight:600!important; transition:.25s; }
        .modern-cancel-btn:hover{ transform:translateY(-2px); box-shadow:0 12px 30px rgba(100,116,139,.25); }

        @media (prefers-reduced-motion: no-preference) {
            .page-block { animation: fadeIn .35s ease both; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-block {
            page-break-after: always;
            break-after: page;
            page-break-inside: avoid; break-inside: avoid;
        }
        .page-block:last-child { page-break-after: avoid; break-after: avoid; }

        @media (min-width: 768px) {
            #leftInputPanel {
                position: -webkit-sticky;
                position: sticky;
                top: 20px;
                z-index: 100;
                height: max-content;
            }
        }

        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; padding: 0; margin: 0; }
            .no-print { display: none !important; }

            .page-block {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin-bottom: 0 !important; padding: 10px !important; background: white !important;
                animation: none !important;
            }
            .table { page-break-inside: avoid !important; width: auto !important; }
            tr { page-break-inside: avoid !important; break-inside: avoid !important; }

            .card { border: none !important; box-shadow: none !important; background: transparent !important; }
            @page { size: A4 landscape; margin: 6mm 5mm; }
        }
    </style>
</head>
<body>

    @php
        $currentUser = auth()->guard('tally')->user();
        $roleLabels = [
            'tally_produksi' => 'Tally Produksi',
            'tally_gudang'   => 'Tally Gudang',
            'supervisor'     => 'Supervisor',
        ];
        // Ambil role aktif user untuk modul ini (dari relasi roles yang baru),
        // dipakai untuk label tampilan & dikirim ke JS di bawah.
        $activeRole = collect(array_keys($roleLabels))->first(fn ($r) => $currentUser->hasRole($r));
        $roleLabel = $roleLabels[$activeRole] ?? $activeRole;
    @endphp

    <nav class="navbar navbar-custom px-4 py-3 d-flex justify-content-between no-print">
        <span class="navbar-brand text-white mb-0">
            Serah Terima Hasil Produksi
            <small>CPI Jombang Plant &middot; Traceability System</small>
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="role-pill">{{ $currentUser->name }} &middot; {{ $roleLabel }}</span>
            <form id="logoutForm" method="POST" action="{{ route('serahterima.logout') }}" class="d-inline">
                @csrf
                <button type="button" class="btn btn-ghost-logout" onclick="confirmLogout()">Keluar</button>
            </form>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">

            <div id="leftInputPanel" class="col-md-3 no-print @if(! $currentUser->hasAnyRole(['tally_produksi', 'supervisor'])) d-none @endif">
                <div class="card p-3 shadow-sm mb-3">
                    <h5 class="fw-bold" id="formTitle">Input Tally Produksi</h5>
                    <input type="hidden" id="edit_col_index" value="">

                    <div class="mb-2">
                        <label>Tanggal Produksi</label>
                        <input type="date" id="p_tanggal_produksi" class="form-control" onchange="handleDateChange(this)" onkeydown="handleMainFormEnter(event, 'p_trolly')">
                    </div>

                    <div class="mb-2">
                        <label>No. Trolly</label>
                        <input type="text" id="p_trolly" class="form-control" onkeydown="handleMainFormEnter(event, 'p_kode_item')">
                    </div>

                    <div class="mb-2">
                        <label>Kode Item</label>
                        <input type="text" id="p_kode_item" class="form-control" placeholder="Ketik kode angka produk" oninput="handleKodeItemInput()" onkeydown="handleKodeItemEnter(event)">
                        <div id="item_name_preview" class="text-success small fw-bold mt-1" style="min-height: 18px;"></div>
                    </div>

                    <div class="mb-2">
                        <label>Jumlah Bag (Max 10)</label>
                        <input type="number" id="p_qty" class="form-control" max="10" min="1" onchange="generateKgInputs()" onkeydown="handleQtyEnter(event)">
                    </div>

                    <div id="kgInputsContainer" class="mt-2 p-2 bg-light rounded border">
                        <small class="text-muted d-block mb-2">
                            <i class="fa-solid fa-lightbulb me-1 text-warning"></i>
                            Tips: Ketik tanpa desimal (cth: 265 → 26.5) & Tekan Enter untuk lanjut.
                        </small>
                    </div>

                    <button id="btnSubmitRole1" class="btn btn-success w-100 mt-3 fw-bold" onclick="submitRole1()">Simpan & Geser Kolom</button>
                    <button id="btnCancelEdit" class="btn btn-secondary w-100 mt-2 d-none" onclick="cancelEditMode()">Batal Koreksi</button>
                </div>
            </div>

            <div class="col-md-{{ $currentUser->hasAnyRole(['tally_produksi', 'supervisor']) ? '9' : '12' }}" id="mainTablePanel">
                <div class="no-print d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded shadow-sm flex-wrap gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="fw-bold text-secondary text-uppercase small" style="font-size: 8.5pt;">
                            <i class="fa-solid fa-calendar-days me-1"></i> Filter Tanggal:
                        </span>
                        <input type="date" id="global_filter_tanggal" class="form-control form-control-sm d-inline-block w-auto shadow-sm" onchange="syncAndFilterDate(this.value)">
                        <button class="btn btn-outline-primary btn-sm fw-bold shadow-sm" onclick="clearDateFilter()">Semua Tanggal</button>
                        <span id="filter_kode_prefix" class="mono-tag badge bg-dark p-2 shadow-sm" style="font-size: 8.5pt; display: none;"></span>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-dark btn-sm shadow-sm fw-bold" onclick="refreshData()">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
                        </button>
                        @if ($currentUser->hasAnyRole(['supervisor']))
                            <button class="btn btn-success btn-sm shadow-sm fw-bold" onclick="exportToExcel()">
                                <i class="fa-solid fa-file-excel me-1"></i> Download Excel
                            </button>

                            <button class="btn btn-danger btn-sm shadow-sm fw-bold" onclick="window.print()">
                                <i class="fa-solid fa-print me-1"></i> Cetak / PDF (A4 Landscape)
                            </button>
                        @endif
                    </div>
                </div>

                <div id="printArea">
                    <div id="tablesContainer"></div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function confirmLogout() {
            Swal.fire({
                title: '<span style="font-size:24px;font-weight:700;">Keluar dari Sistem?</span>',
                html: `
                    <div style="font-size:15px;color:#6b7280;margin-top:8px;">
                        Anda akan keluar dari
                        <b style="color:#000000;">Serah Terima Hasil Produksi</b>.
                    </div>
                `,
                icon: 'question',
                width: 430,
                background: '#fff',
                color: '#1f2937',
                padding: '2em',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#64748b',
                customClass: {
                    popup: 'modern-popup',
                    confirmButton: 'modern-confirm-btn',
                    cancelButton: 'modern-cancel-btn',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }

        const currentUser = {
            nama: @json($currentUser->name),
            role: @json($activeRole),
        };

        // Master produk dari database (menggantikan MASTER_ITEM_LOCAL hardcode)
        const MASTER_ITEM_LOCAL = @json($products->mapWithKeys(fn ($p) => [$p['code'] => $p['name']]));

        let globalDataList = [];
        let currentDisplayedData = [];
        let verifiedNamaItem = "";

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

        document.addEventListener('DOMContentLoaded', function () {
            const todayStr = new Date().toISOString().split('T')[0];
            const pTgl = document.getElementById('p_tanggal_produksi');
            if (pTgl) pTgl.value = todayStr;
            document.getElementById('global_filter_tanggal').value = todayStr;
            syncAndFilterDate(todayStr);

            if (currentUser.role === 'tally_produksi') {
                setTimeout(() => { document.getElementById('p_trolly')?.focus(); }, 300);
            }
        });

        function handleMainFormEnter(event, nextElementId) {
            if (event.key === "Enter") {
                event.preventDefault();
                document.getElementById(nextElementId).focus();
            }
        }

        function handleKodeItemInput() {
            const kode = document.getElementById('p_kode_item').value.trim();
            const previewDiv = document.getElementById('item_name_preview');

            if (!kode) { verifiedNamaItem = ""; previewDiv.innerText = ""; return; }

            if (MASTER_ITEM_LOCAL[kode]) {
                verifiedNamaItem = MASTER_ITEM_LOCAL[kode];
                previewDiv.innerText = "✓ " + MASTER_ITEM_LOCAL[kode];
                previewDiv.style.color = "#27ae60";
            } else {
                verifiedNamaItem = "";
                previewDiv.innerText = "❌ Kode belum terdaftar...";
                previewDiv.style.color = "#c0392b";
            }
        }

        function handleKodeItemEnter(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                if (verifiedNamaItem) { document.getElementById('p_qty').focus(); }
                else { alert("Kode item tidak valid!"); document.getElementById('p_kode_item').select(); }
            }
        }

        function handleQtyEnter(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                generateKgInputs();
                setTimeout(() => {
                    const firstBagInput = document.getElementById('input_bag_1');
                    if (firstBagInput) firstBagInput.focus();
                }, 100);
            }
        }

        function generateKgInputs() {
            let qty = parseInt(document.getElementById('p_qty').value) || 0;
            if (qty > 10) { alert("Maksimal 10 Bag!"); qty = 10; document.getElementById('p_qty').value = 10; }
            const container = document.getElementById('kgInputsContainer');
            container.innerHTML = `<small class="text-muted d-block mb-2">💡 Tips: Ketik tanpa desimal (cth: 265 → 26.5) & Tekan Enter untuk lanjut.</small>`;
            for (let i = 1; i <= qty; i++) {
                container.innerHTML += `
                    <div class="mb-1 row align-items-center">
                        <label class="col-sm-4 col-form-label col-form-label-sm fw-bold">Bag ${i}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm kg-bag-input"
                                   id="input_bag_${i}" data-index="${i}"
                                   onblur="formatAutoDecimal(this)"
                                   onkeydown="handleEnterNavigation(event, ${i}, ${qty})">
                        </div>
                    </div>`;
            }
        }

        function formatAutoDecimal(inputElement) {
            const val = inputElement.value.trim();
            if (!val) return;
            if (!isNaN(val) && !val.includes('.') && !val.includes(',')) {
                inputElement.value = (parseFloat(val) / 10).toFixed(1);
            }
        }

        function handleEnterNavigation(event, currentIdx, totalQty) {
            if (event.key === "Enter") {
                event.preventDefault();
                formatAutoDecimal(document.getElementById(`input_bag_${currentIdx}`));
                if (currentIdx < totalQty) { document.getElementById(`input_bag_${currentIdx + 1}`).focus(); }
                else { document.getElementById('btnSubmitRole1').focus(); }
            }
        }

        function handleGudangEnter(event, nextId, isSubmit, colIdx) {
            if (event.key === "Enter") {
                event.preventDefault();
                if (isSubmit) { submitFinalGudang(colIdx); }
                else if (nextId) { document.getElementById(nextId).focus(); }
            }
        }

        async function submitRole1() {
            const tglProd = document.getElementById('p_tanggal_produksi').value;
            const kodeItemAngka = document.getElementById('p_kode_item').value.trim();
            const trolleyInput = document.getElementById('p_trolly').value.trim();

            if (!tglProd) { alert("Pilih Tanggal Produksi terlebih dahulu!"); return; }
            if (!trolleyInput) { alert("Masukkan Nomor Trolly terlebih dahulu!"); return; }
            if (!verifiedNamaItem) { alert("Masukkan Kode Item yang valid!"); return; }

            const editColIdx = document.getElementById('edit_col_index').value;
            const targetPrefix = getKodeDatePrefix(tglProd);
            const isDuplicate = globalDataList.some(d => {
                if (editColIdx && String(d.colIndex) === String(editColIdx)) return false;
                return d.id && d.id.startsWith(targetPrefix) &&
                       d.noTrolly && String(d.noTrolly).trim().toUpperCase() === String(trolleyInput).trim().toUpperCase();
            });
            if (isDuplicate) {
                alert(`⚠️ Peringatan: Nomor Trolly "${trolleyInput}" sudah pernah dimasukkan pada kode produksi tanggal ini! Silakan periksa kembali.`);
                document.getElementById('p_trolly').focus();
                document.getElementById('p_trolly').select();
                return;
            }

            const kgInputs = document.getElementsByClassName('kg-bag-input');
            const kgBags = [];
            for (let input of kgInputs) {
                formatAutoDecimal(input);
                kgBags.push(parseFloat(input.value) || 0);
            }

            const payload = {
                tanggal_produksi: tglProd,
                kode_item: kodeItemAngka,
                no_trolly: trolleyInput,
                jumlah_bag: parseInt(document.getElementById('p_qty').value),
                kg_bags: kgBags,
            };

            try {
                if (editColIdx) {
                    await apiFetch(`/serah-terima/batches/${editColIdx}`, { method: 'PUT', body: JSON.stringify(payload) });
                    alert("Koreksi Berhasil Diperbarui!");
                    cancelEditMode();
                    refreshData();
                } else {
                    const res = await apiFetch(`/serah-terima/batches`, { method: 'POST', body: JSON.stringify(payload) });
                    alert(res.message);
                    const tglTerpilih = document.getElementById('p_tanggal_produksi').value;
                    clearForm();
                    document.getElementById('p_tanggal_produksi').value = tglTerpilih;
                    document.getElementById('global_filter_tanggal').value = tglTerpilih;
                    syncAndFilterDate(tglTerpilih);
                    document.getElementById('p_trolly').focus();
                }
            } catch (err) {
                alert(err.message);
            }
        }

        async function verifikasiSatuBag(colIndex, bagRowIndex, statusBaru) {
            try {
                await apiFetch(`/serah-terima/batches/${colIndex}/bag/${bagRowIndex}`, {
                    method: 'POST',
                    body: JSON.stringify({ status: statusBaru }),
                });
                refreshData();
            } catch (err) {
                alert("Gagal verifikasi: " + err.message);
            }
        }

        async function submitFinalGudang(colIndex) {
            const cell = document.getElementById(`cell_${colIndex}`).value;
            if (!cell) { alert("Isi Lokasi Kode Cell Cold Storage!"); return; }

            try {
                await apiFetch(`/serah-terima/batches/${colIndex}/finalize`, {
                    method: 'POST',
                    body: JSON.stringify({ kode_cell: cell }),
                });
                alert("Atribut Cell Gudang Berhasil Disimpan!");
                refreshData();
            } catch (err) {
                alert(err.message);
            }
        }

        async function submitRole3(colIndex) {
            try {
                await apiFetch(`/serah-terima/batches/${colIndex}/approve`, { method: 'POST', body: JSON.stringify({}) });
                alert("Dokumen Sah! QR Code Otorisasi Terbit.");
                refreshData();
            } catch (err) {
                alert(err.message);
            }
        }

        function handleDateChange(inputElement) {
            const tglBaru = inputElement.value;
            const isEditMode = document.getElementById('edit_col_index').value !== "";
            if (!isEditMode) {
                clearForm();
            }

            inputElement.value = tglBaru;
            document.getElementById('global_filter_tanggal').value = tglBaru;
            syncAndFilterDate(tglBaru);
        }

        function syncAndFilterDate(tglValue) {
            const pTgl = document.getElementById('p_tanggal_produksi');
            if (pTgl) pTgl.value = tglValue;

            const prefixDiv = document.getElementById('filter_kode_prefix');
            if (tglValue) {
                const prefix = getKodeDatePrefix(tglValue);
                if (prefixDiv) {
                    prefixDiv.innerText = "Prefix: " + prefix;
                    prefixDiv.style.display = "inline-block";
                }
            } else {
                if (prefixDiv) prefixDiv.style.display = "none";
            }
            refreshData();
        }

        function clearDateFilter() {
            document.getElementById('global_filter_tanggal').value = "";
            const pTgl = document.getElementById('p_tanggal_produksi');
            if (pTgl) pTgl.value = "";
            const prefixDiv = document.getElementById('filter_kode_prefix');
            if (prefixDiv) prefixDiv.style.display = "none";
            refreshData();
        }

        function clearForm() {
            document.getElementById('p_trolly').value = "";
            document.getElementById('p_kode_item').value = "";
            document.getElementById('item_name_preview').innerText = "";
            document.getElementById('p_qty').value = "";
            document.getElementById('kgInputsContainer').innerHTML = `<small class="text-muted d-block mb-2">💡 Tips: Ketik tanpa desimal (cth: 265 -> 26.5) & Tekan Enter untuk lanjut.</small>`;
            document.getElementById('edit_col_index').value = "";
            verifiedNamaItem = "";
        }

        function sethatToEditMode(colIndex) {
            const data = globalDataList.find(d => d.colIndex === colIndex);
            if (!data) return;

            // Jaga-jaga di sisi klien: kalau tally_produksi mencoba masuk mode edit
            // padahal gudang sudah memproses batch ini, tolak di sini juga
            // (server tetap jadi penjaga utama lewat guard di controller).
            if (currentUser.role === 'tally_produksi' && data.isLockedByGudang) {
                alert("Data ini sudah tidak bisa diedit karena Tally Gudang sudah memproses batch ini.");
                return;
            }

            document.getElementById('formTitle').innerText = "✏️ Koreksi Tally Produksi";
            document.getElementById('edit_col_index').value = colIndex;
            document.getElementById('p_trolly').value = data.noTrolly;
            verifiedNamaItem = data.namaItem;

            document.getElementById('p_kode_item').value = data.kodeItem || "";
            document.getElementById('item_name_preview').innerText = "✓ " + data.namaItem;
            document.getElementById('p_qty').value = data.jumlahBag;

            generateKgInputs();
            for (let i = 1; i <= data.jumlahBag; i++) {
                document.getElementById(`input_bag_${i}`).value = data.kgBags[i - 1];
            }
            document.getElementById('btnSubmitRole1').innerText = "Simpan Perubahan Koreksi";
            document.getElementById('btnCancelEdit').classList.remove('d-none');
        }

        function cancelEditMode() {
            document.getElementById('formTitle').innerText = "Input Tally Produksi";
            document.getElementById('btnSubmitRole1').innerText = "Simpan & Geser Kolom";
            document.getElementById('btnCancelEdit').classList.add('d-none');
            clearForm();
            const todayStr = new Date().toISOString().split('T')[0];
            document.getElementById('p_tanggal_produksi').value = todayStr;
            document.getElementById('global_filter_tanggal').value = todayStr;
            syncAndFilterDate(todayStr);
        }

        async function refreshData() {
            const tglPilih = document.getElementById('global_filter_tanggal').value;
            document.body.style.cursor = 'wait';
            try {
                const dataList = await apiFetch(`/serah-terima/data?tanggal=${encodeURIComponent(tglPilih)}`);
                globalDataList = dataList;
                currentDisplayedData = dataList;
                renderTable(dataList);
            } catch (err) {
                alert("Gagal memuat data: " + err.message);
            } finally {
                document.body.style.cursor = 'default';
            }
        }

        function getKodeDatePrefix(tanggalInputStr) {
            if (!tanggalInputStr) return "";
            const dateParts = tanggalInputStr.split("-");
            const tahun = parseInt(dateParts[0]);
            const bulan = parseInt(dateParts[1]);
            const tanggal = parseInt(dateParts[2]);

            const baseYear = 2026;
            const baseCharIndex = 16;
            const yearDiff = tahun - baseYear;
            let targetYearIndex = (baseCharIndex + yearDiff) % 26;
            if (targetYearIndex < 0) targetYearIndex += 26;
            const kodeTahun = String.fromCharCode(65 + targetYearIndex);
            const kodeBulan = String.fromCharCode(65 + (bulan - 1));
            const kodeTanggal = tanggal < 10 ? "0" + tanggal : tanggal.toString();
            return "JBG" + kodeTahun + kodeBulan + kodeTanggal;
        }

        function renderTable(dataList) {
            const container = document.getElementById('tablesContainer');

            const currentScrollY = window.scrollY;
            container.style.minHeight = container.offsetHeight + 'px';

            container.innerHTML = "";
            if (dataList.length === 0) {
                container.innerHTML = `<div class="empty-state">Belum ada data tersedia pada kriteria penyaringan tanggal ini.<br><span style="font-size:12px;">Input data baru akan langsung muncul di sini.</span></div>`;
                container.style.minHeight = '';
                return;
            }

            const chunks = [];
            let tempChunk = [];
            for (let i = 0; i < dataList.length; i++) {
                tempChunk.push(dataList[i]);
                if (tempChunk.length === 10 || i === dataList.length - 1) {
                    chunks.push(tempChunk);
                    tempChunk = [];
                }
            }

            chunks.forEach((targetData, pageIndex) => {
                const htmlBlock = document.createElement('div');
                htmlBlock.className = "card p-3 shadow-sm mb-4 page-block bg-white";

                const headerText = `
                    <div class="manifest-head">
                        <img src="{{ asset('images/logo.jpg') }}"
                             alt="Logo CPI"
                             style="height: 40px; max-width: 110px; object-fit: contain;">
                        <div>
                            <div class="manifest-title">FORM SERAH TERIMA HASIL PRODUKSI</div>
                            <div class="manifest-sub">CPI Jombang Plant &middot; Traceability Manifest</div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered text-center align-middle m-0" style="font-size: 7.5pt; table-layout: fixed; width: auto; min-width: auto;">
                `;

                let tableContent = `<thead><tr style="background: var(--ink);">
                            <th style="width: 140px; min-width: 140px; text-align: left; padding-left: 6px; color: #fff; font-family: 'Manrope', sans-serif;">Kode Produksi</th>`;
                targetData.forEach(d => {
                    tableContent += `<th style="width: 85px; min-width: 85px; max-width: 85px;"><span class="kode-chip">${d.id}</span></th>`;
                });
                tableContent += `</tr></thead><tbody>`;

                tableContent += `<tr class="table-light text-start fw-bold"><td class="text-dark">Tgl/Jam</td>` + targetData.map(d => `<td style="font-size: 6.5pt; font-weight: normal;">${d.timestamp}</td>`).join('') + `</tr>`;
                tableContent += `<tr><td class="text-start fw-bold">No. Trolly</td>` + targetData.map(d => `<td>${d.noTrolly}</td>`).join('') + `</tr>`;
                tableContent += `<tr><td class="text-start fw-bold">Nama Item</td>` + targetData.map(d => `<td style="font-size: 7pt; font-weight:bold; color:#2c3e50; line-height: 1.1; word-break: break-word;">${d.namaItem}</td>`).join('') + `</tr>`;

                tableContent += `<tr class="table-light"><td class="text-start fw-bold">Jumlah Bag</td>`;
                targetData.forEach(d => {
                    let btnSemua = "";
                    let hasPending = d.statusBags && d.statusBags.some((s, idx) => idx < d.jumlahBag && s === "PENDING");

                    if (currentUser.role === 'tally_gudang' && hasPending) {
                        btnSemua = `<br><button class="btn btn-primary btn-xs mt-1 no-print w-100 shadow-sm fw-bold" onclick="verifikasiSemuaBag(${d.colIndex}, ${d.jumlahBag}, 'OK VERIFIED')">✓ Lolos Semua</button>`;
                    }
                    tableContent += `<td>${d.jumlahBag} Bag ${btnSemua}</td>`;
                });
                tableContent += `</tr>`;

                for (let i = 0; i < 10; i++) {
                    tableContent += `<tr><td class="text-start fw-bold text-secondary">Bag ${i + 1}</td>`;
                    targetData.forEach(d => {
                        if (i < d.jumlahBag) {
                            let currentStatus = d.statusBags[i];
                            let cellContent = `${d.kgBags[i].toFixed(1)} Kg`;

                            if (currentUser.role === 'tally_gudang') {
                                if (currentStatus === "PENDING") {
                                    cellContent += `<br><div class="mt-1 no-print">
                                            <button class="btn btn-success btn-xs py-0" onclick="verifikasiSatuBag(${d.colIndex}, ${i}, 'OK VERIFIED')">Lolos</button>
                                            <button class="btn btn-danger btn-xs py-0" onclick="verifikasiSatuBag(${d.colIndex}, ${i}, 'TOLAK (REJECT)')">Tolak</button>
                                        </div>`;
                                } else if (currentStatus === "OK VERIFIED") {
                                    cellContent += `<br><span class="status-pill ok no-print">✓ OK</span>`;
                                } else {
                                    cellContent += `<br><span class="status-pill reject no-print">❌ REJECT</span>`;
                                }
                            } else {
                                if (currentStatus === "OK VERIFIED") cellContent += `<br><span class="status-pill ok no-print">✓ OK</span>`;
                                else if (currentStatus === "TOLAK (REJECT)") cellContent += `<br><span class="status-pill reject no-print">❌ REJECT</span>`;
                                else cellContent += `<br><span class="status-pill pending no-print">PENDING</span>`;
                            }
                            tableContent += `<td>${cellContent}</td>`;
                        } else {
                            tableContent += `<td>-</td>`;
                        }
                    });
                    tableContent += `</tr>`;
                }

                tableContent += `<tr class="table-secondary fw-bold"><td class="text-start">Total Bersih</td>` + targetData.map(d => `<td>${d.totalKg.toFixed(1)} Kg</td>`).join('') + `</tr>`;
                tableContent += `<tr style="background-color: #f8fafc;"><td class="text-start fw-bold" style="color: var(--muted);">QR Tally Prod</td>`;
                targetData.forEach(d => {
                    if (d.qrProdUrl) {
                        let namaTallyProd = d.namaTallyProd || "PROD TEAM";
                        tableContent += `<td><img src="${d.qrProdUrl}" width="28" height="28"><br><span style="font-size:5.5pt; color:var(--ink); font-weight:bold; display:block; text-transform:uppercase; max-width:75px; margin:0 auto; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${namaTallyProd}</span></td>`;
                    } else { tableContent += `<td>-</td>`; }
                });
                tableContent += `</tr>`;

                tableContent += `<tr style="background-color: var(--accent-soft);"><td class="text-start fw-bold" style="color: var(--accent-dark);">Kode Cell</td>`;
                targetData.forEach(d => {
                    if (!d.kodeCell && currentUser.role === 'tally_gudang') {
                        tableContent += `<td>
                                            <input type="text" id="cell_${d.colIndex}" class="form-control form-control-sm p-0 text-center mb-1" style="font-size:7pt;" placeholder="Cell" onkeydown="handleGudangEnter(event, null, true, ${d.colIndex})">
                                            <button class="btn btn-primary btn-xs w-100 py-0 no-print" onclick="submitFinalGudang(${d.colIndex})">Sahkan Cell</button>
                                         </td>`;
                    } else { tableContent += `<td>${d.kodeCell ? `<span class="cell-chip">${d.kodeCell}</span>` : '-'}</td>`; }
                });
                tableContent += `</tr>`;

                tableContent += `<tr style="background-color: var(--accent-soft);"><td class="text-start fw-bold" style="color: var(--accent-dark);">QR Tally Gudang</td>`;
                targetData.forEach(d => {
                    if (d.kodeCell && d.kodeCell !== "-") {
                        let namaTallyWh = d.namaTallyWh || "WH TEAM";
                        let qrGudangUrl = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(d.id + '|CELL:' + d.kodeCell + '|BY:' + namaTallyWh)}`;
                        tableContent += `<td><img src="${qrGudangUrl}" width="28" height="28"><br><span style="font-size:5.5pt; color:var(--accent-dark); font-weight:bold; display:block; text-transform:uppercase; max-width:75px; margin:0 auto; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${namaTallyWh}</span></td>`;
                    } else {
                        tableContent += `<td><span class="text-muted small" style="font-size:6pt;">Belum Sah</span></td>`;
                    }
                });
                tableContent += `</tr>`;

                tableContent += `<tr style="background-color: #fffaf0;"><td class="text-start fw-bold" style="color: var(--accent-dark);">Supervisor</td>`;
                targetData.forEach(d => {
                    if (d.statusApprove === "VERIFIED & APPROVED") {
                        tableContent += `<td class="card-neon-verified" style="padding: 2px 0;">
                                            <span style="font-size:6pt; display:block;">✓ APPROVED</span>
                                            <img src="${d.barcodeUrl}" class="mt-1" width="28" height="28">
                                         </td>`;
                    } else {
                        // Lock hanya berlaku untuk tally_produksi. Gudang dianggap sudah
                        // mulai proses kalau ada bag yang bukan PENDING lagi atau kode
                        // cell sudah disahkan (sudah dihitung backend: d.isLockedByGudang).
                        let gudangSudahProses = !!d.isLockedByGudang;

                        let actionContent = `<span class="text-muted d-block" style="font-size:6.5pt;">Menunggu</span>`;
                                if (currentUser.role === 'tally_produksi') {
                                    if (!gudangSudahProses) {
                                        actionContent += `<button class="btn btn-outline-warning btn-xs mt-1 no-print font-weight-bold" onclick="sethatToEditMode(${d.colIndex})">Edit</button>`;
                                    } else {
                                        actionContent += `<span class="d-block text-muted mt-1" style="font-size:6pt;">🔒 Terkunci (WH proses)</span>`;
                                    }
                                } else if (currentUser.role === 'supervisor') {
                            // Supervisor tetap boleh edit & hapus kapan saja, terlepas
                            // dari status gudang (hak override).
                            actionContent = `
                                <div class="d-flex flex-column gap-1">
                                    <button class="btn btn-success btn-xs shadow py-1 px-1 no-print fw-bold" onclick="submitRole3(${d.colIndex})">Approve</button>
                                    <button class="btn btn-outline-warning btn-xs no-print fw-bold" onclick="sethatToEditMode(${d.colIndex})">✏️ Edit</button>
                                    <button class="btn btn-outline-danger btn-xs no-print fw-bold" onclick="hapusTrolly(${d.colIndex})">🗑️ Hapus</button>
                                </div>
                            `;
                        }
                        tableContent += `<td>${actionContent}</td>`;
                    }
                });
                tableContent += `</tr></tbody>`;

                const footerText = `</table>
                        <div class="text-end mt-1 text-dark" style="font-size: 8pt; font-weight: bold; font-style: italic;">
                            (Lembar ${pageIndex + 1})
                        </div>
                    </div>`;
                htmlBlock.innerHTML = headerText + tableContent + footerText;
                container.appendChild(htmlBlock);
            });

            window.scrollTo(0, currentScrollY);
            setTimeout(() => { container.style.minHeight = ''; }, 100);
        }

        async function verifikasiSemuaBag(colIndex, jumlahBag, statusBaru) {
            if (!confirm("Anda yakin ingin meloloskan SEMUA bag pada kolom ini?")) return;
            document.body.style.cursor = 'wait';
            try {
                await apiFetch(`/serah-terima/batches/${colIndex}/bag/verify-all`, {
                    method: 'POST',
                    body: JSON.stringify({ status: statusBaru }),
                });
                alert("Semua bag dalam kolom ini berhasil diverifikasi!");
                refreshData();
            } catch (err) {
                alert("Terjadi kesalahan saat memverifikasi massal: " + err.message);
            } finally {
                document.body.style.cursor = 'default';
            }
        }

        async function hapusTrolly(colIndex) {
            const result = await Swal.fire({
                title: '<span style="font-size:24px;font-weight:700;">Hapus Data Troli?</span>',
                html: `
                    <div style="font-size:15px;color:#6b7280;margin-top:8px;">
                        Data ini akan <b style="color:#b91c1c;">dihapus permanen</b> dan tidak bisa dikembalikan.
                    </div>
                `,
                icon: 'warning',
                width: 430,
                background: '#fff',
                color: '#1f2937',
                padding: '2em',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#64748b',
                customClass: {
                    popup: 'modern-popup',
                    confirmButton: 'modern-confirm-btn',
                    cancelButton: 'modern-cancel-btn',
                },
            });

            if (!result.isConfirmed) return;

            document.body.style.cursor = 'wait';
            try {
                await apiFetch(`/serah-terima/batches/${colIndex}`, { method: 'DELETE' });
                await Swal.fire({
                    title: 'Terhapus!',
                    html: '<div style="font-size:15px;color:#6b7280;">Data troli berhasil dihapus.</div>',
                    icon: 'success',
                    confirmButtonColor: '#0f7a3d',
                    customClass: { popup: 'modern-popup', confirmButton: 'modern-confirm-btn' },
                });
                refreshData();
            } catch (err) {
                Swal.fire({
                    title: 'Gagal Menghapus',
                    html: `<div style="font-size:15px;color:#6b7280;">${err.message}</div>`,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    customClass: { popup: 'modern-popup', confirmButton: 'modern-confirm-btn' },
                });
            } finally {
                document.body.style.cursor = 'default';
            }
        }

        function exportToExcel() {
            if (!currentDisplayedData || currentDisplayedData.length === 0) {
                alert("Tidak ada data untuk didownload!");
                return;
            }

            let tableHtml = `<table border="1">
                <thead>
                    <tr>
                        <th style="background-color: #2c3e50; color: white;">Tanggal</th>
                        <th style="background-color: #2c3e50; color: white;">Kode</th>
                        <th style="background-color: #2c3e50; color: white;">Nama Produk</th>
                        <th style="background-color: #2c3e50; color: white;">Nomor Trolly</th>
                        <th style="background-color: #2c3e50; color: white;">Jumlah Bag</th>
                        <th style="background-color: #2c3e50; color: white;">Kg</th>
                        <th style="background-color: #2c3e50; color: white;">Kode Cell</th>
                    </tr>
                </thead>
                <tbody>`;
            currentDisplayedData.forEach(d => {
            let tgl = d.timestamp ? d.timestamp.split(" ")[0] : "-";
            let kode = d.id || "-";
            let nama = d.namaItem || "-";
            let trolly = d.noTrolly || "-";
            let jmlBag = d.jumlahBag || "0"; 
            let kg = d.totalKg ? d.totalKg.toFixed(1) : "0";
            let kodeCell = d.kodeCell || "-";
            
            tableHtml += `<tr>
                <td>${tgl}</td>
                <td>${kode}</td>
                <td>${nama}</td>
                <td>${trolly}</td>
                <td>${jmlBag}</td>
                <td>${kg}</td>
                <td>${kodeCell}</td>
            </tr>`;
            });
            tableHtml += `</tbody></table>`;

            const blob = new Blob([tableHtml], { type: 'application/vnd.ms-excel' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = 'Laporan_Tally_Produksi.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Auto-refresh tiap 30 detik, jeda kalau sedang mode edit
        setInterval(() => {
            const editColIndex = document.getElementById('edit_col_index').value;
            if (!editColIndex) {
                refreshData();
            }
        }, 30000);
    </script>
</body>
</html>