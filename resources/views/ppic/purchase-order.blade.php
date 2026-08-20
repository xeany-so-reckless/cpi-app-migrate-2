<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PPIC - Input PO</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f6fb; --surface: #ffffff; --surface-hover: #f8f9ff; --line: #e1e3f0;
            --primary: #4f46e5; --primary-dark: #4338ca; --primary-soft: #eef2ff;
            --text: #1e1b2e; --muted: #6b7280; --danger: #dc2626;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); }
        .mono { font-family: 'JetBrains Mono', monospace; }
        nav {
            display: flex; justify-content: space-between; align-items: center; padding: 14px 5%;
            background: var(--surface); border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 1000;
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.05rem; }
        .logo img { height: 38px; }
        .back-link { color: var(--muted); text-decoration: none; font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        .back-link:hover { color: var(--primary); }

        .page-header { padding: 30px 5% 16px; }
        .page-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 2.2rem; text-transform: uppercase; }

        .layout { display: grid; grid-template-columns: 340px 1fr; gap: 20px; padding: 0 5% 60px; align-items: start; }
        @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } }

        .card { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 22px; }
        .card h5 { font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 1.2rem; text-transform: uppercase; margin-bottom: 16px; }

        label { font-weight: 700; font-size: 0.72rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; display: block; }
        .form-control, select.form-control {
            width: 100%; height: 42px; border-radius: 8px; border: 1px solid var(--line);
            padding: 0 12px; font-size: 0.85rem; color: var(--text); margin-bottom: 14px; background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }

        .btn-primary {
            width: 100%; height: 44px; border-radius: 8px; border: none; background: var(--primary);
            color: #fff; font-weight: 700; font-size: 0.85rem; cursor: pointer;
        }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-primary:disabled { background: var(--muted); cursor: not-allowed; opacity: 0.7; }

        .search-bar { padding: 0 0 14px; }
        .search-bar input {
            width: 100%; height: 42px; border-radius: 8px; border: 1px solid var(--line); padding: 0 14px; font-size: 0.85rem;
        }

        .table-wrapper { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        thead th {
            background: #fafaff; color: var(--muted); font-family: 'JetBrains Mono', monospace; font-size: 0.62rem;
            letter-spacing: 1px; text-transform: uppercase; text-align: left; padding: 12px 14px; border-bottom: 1px solid var(--line);
        }
        tbody td { padding: 12px 14px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--surface-hover); }
        .po-chip {
            font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 0.78rem; color: var(--primary);
            background: var(--primary-soft); padding: 3px 8px; border-radius: 5px;
        }

                .status-badge {
            font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 0.7rem;
            padding: 3px 8px; border-radius: 5px; white-space: nowrap;
        }
        .status-aktif { color: #16a34a; background: #f0fdf4; }
        .status-teco { color: var(--danger); background: #fef2f2; }
        .btn-teco {
            border: 1px solid var(--line); background: #fff; border-radius: 6px;
            padding: 4px 10px; font-size: 0.72rem; font-weight: 700; cursor: pointer; white-space: nowrap;
        }
        .btn-teco:hover { background: var(--surface-hover); }
        .empty-state, .loading-state { text-align: center; padding: 50px 20px; color: var(--muted); font-size: 0.85rem; }
        .btn-icon { background: none; border: none; cursor: pointer; color: var(--muted); padding: 4px; border-radius: 6px; }
        .btn-icon:hover { color: var(--danger); background: #fef2f2; }
        .btn-icon:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
            <span>PPIC - Input PO</span>
        </div>
        <a href="{{ route('ppic.index') }}" class="back-link">
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span> Menu Utama
        </a>
    </nav>

    <div class="page-header">
        <div class="page-title">Input PO</div>
    </div>

        <div class="layout">
        <div class="card">
            <h5>PO Baru</h5>
            <label>Jenis PO</label>
            <select id="f_jenis_po" class="form-control" onchange="toggleProdukField()">
                <option value="">-- Pilih Jenis --</option>
                <option value="FEH0">FEH0</option>
                <option value="FEH1">FEH1</option>
                <option value="FEH2">FEH2</option>
                <option value="FEHM">FEHM</option>
            </select>

            <label>Nomor PO</label>
            <input type="text" id="f_nomor_po" class="form-control" placeholder="Contoh: PO-2026-001">

            <div id="jumlahRitWrapper">
                <label>Jumlah Rit</label>
                <input type="number" id="f_jumlah_rit" class="form-control" placeholder="Contoh: 3" min="1">
            </div>

            <div id="produkWrapper" style="display:none;">
                <label>Nama Produk</label>
                <select id="f_produk" class="form-control">
                    <option value="">-- Pilih Produk --</option>
                    <option value="54">YM PART 9 @1.0 KG FZ DBESTO ORI</option>
                    <option value="55">YM PART 9 @1.0 KG FZ LAZATTO</option>
                    <option value="56">YM PART 9 @1.1 KG FZ LAZATTO</option>
                    <option value="57">YM PART 10 @0.9 KG FZ M3 FC</option>
                </select>
            </div>

            <label>Tanggal</label>
            <input type="date" id="f_tanggal" class="form-control">

            <button class="btn-primary" id="btnSimpan" onclick="submitPo()">Simpan PO</button>
        </div>

        <div>
            <div class="search-bar" style="display:flex; gap:10px; align-items:center;">
                <input type="text" id="searchInput" placeholder="Cari nomor PO atau jenis..." style="flex:1;">
                <button type="button" onclick="toggleTrashedView()" id="btnToggleTrashed" style="white-space:nowrap; height:42px; border-radius:8px; border:1px solid var(--line); background:#fff; padding:0 14px; font-size:0.8rem; font-weight:700; color:var(--muted); cursor:pointer;">Riwayat Terhapus</button>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nomor PO</th>
                            <th>Jenis PO</th>
                            <th>Produk</th>
                            <th>Jumlah Rit</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Diinput Oleh</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr><td colspan="8" class="loading-state">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <div id="trashedWrapper" class="table-wrapper" style="display:none; margin-top:16px;">
                <table>
                    <thead>
                        <tr>
                            <th>Nomor PO</th>
                            <th>Jenis PO</th>
                            <th>Produk</th>
                            <th>Tanggal</th>
                            <th>Dihapus Pada</th>
                            <th>Diinput Oleh</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="trashedTableBody">
                        <tr><td colspan="7" class="loading-state">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        document.getElementById('f_tanggal').valueAsDate = new Date();

        async function apiFetch(url, options = {}) {
            const response = await fetch(url, {
                ...options,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, ...(options.headers || {}) },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan pada server.');
            return data;
        }

                function toggleProdukField() {
    const jenis = document.getElementById('f_jenis_po').value;
    const wrapper = document.getElementById('produkWrapper');
    const jumlahRitWrapper = document.getElementById('jumlahRitWrapper');

    wrapper.style.display = jenis === 'FEHM' ? 'block' : 'none';
    if (jenis !== 'FEHM') document.getElementById('f_produk').value = '';

    jumlahRitWrapper.style.display = jenis === 'FEH0' ? 'block' : 'none';
    if (jenis !== 'FEH0') document.getElementById('f_jumlah_rit').value = '';
}

        async function submitPo() {
            const jenisPo = document.getElementById('f_jenis_po').value;
            const produkId = document.getElementById('f_produk').value;
            const jumlahRit = document.getElementById('f_jumlah_rit').value;

            const payload = {
                jenis_po: jenisPo,
                nomor_po: document.getElementById('f_nomor_po').value.trim(),
                tanggal: document.getElementById('f_tanggal').value,
                jumlah_rit: jumlahRit ? parseInt(jumlahRit, 10) : null,
                produk_id: jenisPo === 'FEHM' ? produkId : null,
            };

            if (!payload.jenis_po || !payload.nomor_po || !payload.tanggal) {
                Swal.fire({ title: 'Lengkapi Data', text: 'Semua field wajib diisi!', icon: 'warning', confirmButtonColor: '#4f46e5' });
                return;
            }

                        if (jenisPo === 'FEH0' && (!payload.jumlah_rit || payload.jumlah_rit < 1)) {
                Swal.fire({ title: 'Lengkapi Data', text: 'Jumlah Rit wajib diisi, minimal 1!', icon: 'warning', confirmButtonColor: '#4f46e5' });
                return;
            }

            if (jenisPo === 'FEHM' && !produkId) {
                Swal.fire({ title: 'Lengkapi Data', text: 'Nama produk wajib dipilih untuk jenis PO FEHM!', icon: 'warning', confirmButtonColor: '#4f46e5' });
                return;
            }

            const btn = document.getElementById('btnSimpan');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            try {
                const res = await apiFetch(`{{ route('ppic.purchase-order.store') }}`, { method: 'POST', body: JSON.stringify(payload) });
                Swal.fire({ title: 'Tersimpan!', text: res.message, icon: 'success', confirmButtonColor: '#4f46e5' });
                document.getElementById('f_nomor_po').value = '';
                document.getElementById('f_jumlah_rit').value = '';
                document.getElementById('f_produk').value = '';
                loadData();
            } catch (err) {
                Swal.fire({ title: 'Gagal', text: err.message, icon: 'error' });
            } finally {
                btn.disabled = false;
                btn.textContent = 'Simpan PO';
            }
        }

        async function loadData() {
            const tbody = document.getElementById('tableBody');
            const search = document.getElementById('searchInput').value.trim();
            try {
                const qs = search ? `?search=${encodeURIComponent(search)}` : '';
                const data = await apiFetch(`{{ route('ppic.purchase-order.data') }}${qs}`);
                if (data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Belum ada PO tercatat.</td></tr>`;
                    return;
                }
                tbody.innerHTML = data.map(d => `
                    <tr>
                        <td><span class="po-chip">${d.nomorPo}</span></td>
                        <td>${d.jenisPo}</td>
                        <td>${d.namaProduk ?? '-'}</td>
                        <td>${d.jumlahRit ?? '-'}</td>
                        <td>${d.tanggalLabel}</td>
                        <td>
                            <span class="status-badge ${d.isTeco ? 'status-teco' : 'status-aktif'}">${d.isTeco ? 'TECO' : 'AKTIF'}</span>
                            <button class="btn-teco" onclick="toggleTeco(${d.id})">${d.isTeco ? 'Buka Lagi' : 'Tandai TECO'}</button>
                        </td>
                        <td style="font-size:0.78rem; color:var(--muted);">${d.namaUser}</td>
                                                <td>${d.isTeco ? '' : `<button class="btn-icon" onclick="hapusPo(${d.id})"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>`}</td>
                    </tr>
                `).join('');
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Gagal memuat: ${err.message}</td></tr>`;
            }
        }

        async function hapusPo(id) {
            const result = await Swal.fire({
                title: 'Hapus PO?', text: 'Data yang dihapus tidak bisa dikembalikan.', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
            });
            if (!result.isConfirmed) return;

            try {
                await apiFetch(`{{ url('ppic/purchase-order') }}/${id}`, { method: 'DELETE' });
                loadData();
            } catch (err) {
                Swal.fire({ title: 'Gagal Menghapus', text: err.message, icon: 'error' });
            }
        }

                async function toggleTeco(id) {
            try {
                const res = await apiFetch(`{{ url('ppic/purchase-order') }}/${id}/toggle-teco`, { method: 'POST' });
                Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', confirmButtonColor: '#4f46e5' });
                loadData();
            } catch (err) {
                Swal.fire({ title: 'Gagal', text: err.message, icon: 'error' });
            }
        }

                let trashedVisible = false;

        function toggleTrashedView() {
            trashedVisible = !trashedVisible;
            document.getElementById('trashedWrapper').style.display = trashedVisible ? 'block' : 'none';
            document.getElementById('btnToggleTrashed').textContent = trashedVisible ? 'Tutup Riwayat Terhapus' : 'Riwayat Terhapus';
            if (trashedVisible) loadTrashed();
        }

        async function loadTrashed() {
            const tbody = document.getElementById('trashedTableBody');
            tbody.innerHTML = `<tr><td colspan="7" class="loading-state">Memuat data...</td></tr>`;
            try {
                const data = await apiFetch(`{{ route('ppic.purchase-order.trashed') }}`);
                if (data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Tidak ada PO yang terhapus.</td></tr>`;
                    return;
                }
                tbody.innerHTML = data.map(d => `
                    <tr>
                        <td><span class="po-chip">${d.nomorPo}</span></td>
                        <td>${d.jenisPo}</td>
                        <td>${d.namaProduk ?? '-'}</td>
                        <td>${d.tanggalLabel}</td>
                        <td style="font-size:0.78rem; color:var(--muted);">${d.deletedAtLabel ?? '-'}</td>
                        <td style="font-size:0.78rem; color:var(--muted);">${d.namaUser}</td>
                        <td><button class="btn-teco" onclick="restorePo(${d.id})">Restore</button></td>
                    </tr>
                `).join('');
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Gagal memuat: ${err.message}</td></tr>`;
            }
        }

        async function restorePo(id) {
            try {
                const res = await apiFetch(`{{ url('ppic/purchase-order') }}/${id}/restore`, { method: 'POST' });
                Swal.fire({ title: 'Dipulihkan!', text: res.message, icon: 'success', confirmButtonColor: '#4f46e5' });
                loadTrashed();
                loadData();
            } catch (err) {
                Swal.fire({ title: 'Gagal', text: err.message, icon: 'error' });
            }
        }

        let searchTimer;
        document.getElementById('searchInput').addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadData, 400);
        });

        loadData();
    </script>
</body>
</html>