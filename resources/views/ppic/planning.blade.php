<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PPIC - Planning vs Aktual</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f6fb; --surface: #ffffff; --surface-hover: #f8f9ff; --line: #e1e3f0;
            --primary: #4f46e5; --primary-dark: #4338ca; --primary-soft: #eef2ff;
            --text: #1e1b2e; --muted: #6b7280; --muted-dim: #a3a6b8;
            --success: #16a34a; --danger: #dc2626;
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
        .page-title {
            font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 2.2rem;
            text-transform: uppercase; color: var(--text);
        }

        .layout { display: grid; grid-template-columns: 340px 1fr; gap: 20px; padding: 0 5% 60px; align-items: start; }
        @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } }

        .card { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 22px; }
        .card h5 { font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 1.2rem; text-transform: uppercase; margin-bottom: 16px; }
        .card-note { font-size: 0.72rem; color: var(--muted-dim); margin: -10px 0 16px; line-height: 1.4; }

        label { font-weight: 700; font-size: 0.72rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; display: block; }
        .form-control {
            width: 100%; height: 42px; border-radius: 8px; border: 1px solid var(--line);
            padding: 0 12px; font-size: 0.85rem; color: var(--text); margin-bottom: 14px; background: #fff;
        }
        textarea.form-control { height: auto; padding: 10px 12px; min-height: 70px; resize: vertical; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .btn-primary {
            width: 100%; height: 44px; border-radius: 8px; border: none; background: var(--primary);
            color: #fff; font-weight: 700; font-size: 0.85rem; cursor: pointer;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        .table-wrapper { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; overflow: hidden; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        thead th {
            background: #fafaff; color: var(--muted); font-family: 'JetBrains Mono', monospace; font-size: 0.62rem;
            letter-spacing: 1px; text-transform: uppercase; text-align: left; padding: 12px 14px; border-bottom: 1px solid var(--line); white-space: nowrap;
        }
        tbody td { padding: 12px 14px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--surface-hover); }
        .num { font-family: 'JetBrains Mono', monospace; text-align: right; }
        .positif { color: var(--success); font-weight: 700; }
        .negatif { color: var(--danger); font-weight: 700; }
        .empty-state, .loading-state { text-align: center; padding: 50px 20px; color: var(--muted); font-size: 0.85rem; }
        .btn-icon { background: none; border: none; cursor: pointer; color: var(--muted); padding: 4px; border-radius: 6px; }
        .btn-icon:hover { color: var(--danger); background: #fef2f2; }
    </style>
</head>
<body>

    <nav>
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
            <span>PPIC - Planning vs Aktual</span>
        </div>
        <a href="{{ route('ppic.index') }}" class="back-link">
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span> Menu Utama
        </a>
    </nav>

    <div class="page-header">
        <div class="page-title">Planning vs Aktual</div>
    </div>

    <div class="layout">
        <div class="card">
            <h5>Input Data Harian</h5>
            <label>Tanggal</label>
            <input type="date" id="f_tanggal" class="form-control">

            <div class="form-row">
                <div>
                    <label>Plan Ekor</label>
                    <input type="number" id="f_plan_ekor" class="form-control" min="0">
                </div>
                <div>
                    <label>Plan KG</label>
                    <input type="number" step="0.01" id="f_plan_kg" class="form-control" min="0">
                </div>
            </div>

            <label>Keterangan (opsional)</label>
            <textarea id="f_keterangan" class="form-control" placeholder="Catatan tambahan..."></textarea>

            <button class="btn-primary" onclick="submitPlan()">Simpan</button>
            <div class="card-note">Aktual Ekor & Aktual Kg terisi otomatis dari Report Harian Bahan Baku LB setelah data Setelah Bongkar diinput - tidak diinput manual di sini.</div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="num">Plan Ekor</th>
                        <th class="num">Aktual Ekor</th>
                        <th class="num">Selisih</th>
                        <th class="num">% Selisih</th>
                        <th class="num">Plan Kg</th>
                        <th class="num">Aktual Kg</th>
                        <th class="num">Selisih Kg</th>
                        <th class="num">% Selisih Kg</th>
                        <th>Keterangan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="11" class="loading-state">Memuat data...</td></tr>
                </tbody>
            </table>
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

        // DIUBAH: aktual_ekor & aktual_kg dihapus dari payload - sekarang
        // otomatis terisi di backend dari Report Harian Bahan Baku LB,
        // bukan input manual PPIC lagi.
        async function submitPlan() {
            const payload = {
                tanggal: document.getElementById('f_tanggal').value,
                plan_ekor: parseInt(document.getElementById('f_plan_ekor').value) || 0,
                plan_kg: parseFloat(document.getElementById('f_plan_kg').value) || 0,
                keterangan: document.getElementById('f_keterangan').value || null,
            };

            if (!payload.tanggal) { alert('Pilih tanggal terlebih dahulu!'); return; }

            try {
                const res = await apiFetch(`{{ route('ppic.planning.store') }}`, { method: 'POST', body: JSON.stringify(payload) });
                Swal.fire({ title: 'Tersimpan!', text: res.message, icon: 'success', confirmButtonColor: '#4f46e5' });
                loadData();
            } catch (err) {
                Swal.fire({ title: 'Gagal', text: err.message, icon: 'error' });
            }
        }

        function fmt(v) { return Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }); }

        async function loadData() {
            const tbody = document.getElementById('tableBody');
            try {
                const data = await apiFetch(`{{ route('ppic.planning.data') }}`);
                if (data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="11" class="empty-state">Belum ada data.</td></tr>`;
                    return;
                }
                tbody.innerHTML = data.map(d => `
                    <tr>
                        <td>${d.tanggalLabel}</td>
                        <td class="num">${fmt(d.planEkor)}</td>
                        <td class="num">${fmt(d.aktualEkor)}</td>
                        <td class="num ${d.selisihEkor >= 0 ? 'positif' : 'negatif'}">${d.selisihEkor >= 0 ? '+' : ''}${fmt(d.selisihEkor)}</td>
                        <td class="num ${d.persenSelisihEkor >= 0 ? 'positif' : 'negatif'}">${d.persenSelisihEkor >= 0 ? '+' : ''}${fmt(d.persenSelisihEkor)}%</td>
                        <td class="num">${fmt(d.planKg)}</td>
                        <td class="num">${fmt(d.aktualKg)}</td>
                        <td class="num ${d.selisihKg >= 0 ? 'positif' : 'negatif'}">${d.selisihKg >= 0 ? '+' : ''}${fmt(d.selisihKg)}</td>
                        <td class="num ${d.persenSelisihKg >= 0 ? 'positif' : 'negatif'}">${d.persenSelisihKg >= 0 ? '+' : ''}${fmt(d.persenSelisihKg)}%</td>
                        <td style="max-width:180px; font-size:0.75rem; color:var(--muted);">${d.keterangan || '-'}</td>
                        <td><button class="btn-icon" onclick="hapusPlan(${d.id})"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button></td>
                    </tr>
                `).join('');
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="11" class="empty-state">Gagal memuat: ${err.message}</td></tr>`;
            }
        }

        async function hapusPlan(id) {
            const result = await Swal.fire({
                title: 'Hapus Data?', text: 'Data yang dihapus tidak bisa dikembalikan.', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
            });
            if (!result.isConfirmed) return;

            try {
                await apiFetch(`{{ url('ppic/planning') }}/${id}`, { method: 'DELETE' });
                loadData();
            } catch (err) {
                Swal.fire({ title: 'Gagal Menghapus', text: err.message, icon: 'error' });
            }
        }

        loadData();
    </script>
</body>
</html>