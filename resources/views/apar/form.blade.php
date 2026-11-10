<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspeksi APAR</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --red: #d9232a;
            --red-dark: #b81c22;
            --red-soft: #fdecec;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --slate: #0f172a;
            --slate-3: #475569;
            --muted: #64748b;
            --line: #e2e8f0;
            --bg: #f1f5f9;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif; }
        body { background: var(--bg); color: var(--slate); min-height: 100vh; -webkit-text-size-adjust: 100%; }

        .head { background: var(--red); color: #fff; padding: 22px 18px 26px; text-align: center; }
        .head .eyebrow { font-size: .68rem; letter-spacing: 1.6px; text-transform: uppercase; opacity: .85; }
        .head h1 { font-size: 1.45rem; font-weight: 700; margin-top: 4px; }
        .head p { font-size: .8rem; opacity: .9; margin-top: 4px; }

        .wrap { max-width: 560px; margin: -14px auto 40px; padding: 0 14px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 18px 16px; margin-bottom: 14px; box-shadow: 0 2px 10px rgba(15,23,42,.05); }
        .card h2 { font-size: .78rem; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--red); margin-bottom: 14px; }

        .field { margin-bottom: 16px; }
        .field:last-child { margin-bottom: 0; }
        .field[hidden] { display: none; }
        label.lbl, .lbl { display: block; font-size: .82rem; font-weight: 600; color: var(--slate-3); margin-bottom: 6px; }
        .req { color: var(--red); }
        .hint { font-size: .72rem; color: var(--muted); margin-top: 4px; }

        input[type=text], input[type=date], input[type=time], select, textarea {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff;
            padding: 0 12px; height: 48px; font-size: 16px; color: var(--slate);
        }
        textarea { height: auto; min-height: 90px; padding: 12px; resize: vertical; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--red); box-shadow: 0 0 0 3px var(--red-soft); }
        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        /* Pilihan Baik / Rusak */
        .seg { display: flex; gap: 8px; }
        .seg label { flex: 1; position: relative; cursor: pointer; }
        .seg input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; cursor: pointer; }
        .pill {
            display: flex; align-items: center; justify-content: center;
            height: 46px; border-radius: 10px; border: 2px solid var(--line);
            background: #f8fafc; color: var(--slate-3);
            font-size: .9rem; font-weight: 600; transition: all .15s ease;
        }
        .seg input:checked + .pill.ok  { background: var(--green-soft); border-color: var(--green); color: #166534; }
        .seg input:checked + .pill.bad { background: var(--red-soft); border-color: var(--red); color: var(--red-dark); }
        .seg input:checked + .pill.na  { background: #e2e8f0; border-color: #94a3b8; color: var(--slate); }
        .seg input:focus-visible + .pill { outline: 3px solid #93c5fd; outline-offset: 1px; }

        /* Foto */
        .foto-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
            width: 100%; min-height: 120px; border: 2px dashed #cbd5e1; border-radius: 12px;
            background: #f8fafc; color: var(--slate-3); font-size: .85rem; font-weight: 600;
            cursor: pointer; text-align: center; padding: 14px;
        }
        .foto-btn svg { width: 34px; height: 34px; stroke: var(--red); }
        .foto-btn.has-file { border-style: solid; border-color: var(--green); }
        #foto { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .foto-wrap { position: relative; }
        #fotoPreview { display: none; width: 100%; max-height: 300px; object-fit: contain; border-radius: 10px; margin-top: 10px; border: 1px solid var(--line); background: #000; }
        #fotoInfo { font-size: .74rem; color: var(--muted); margin-top: 6px; }

        .err { color: #dc2626; font-size: .78rem; margin-top: 5px; font-weight: 500; }
        .banner { background: var(--red-soft); border: 1px solid #fca5a5; color: #991b1b; border-radius: 12px; padding: 12px 14px; font-size: .84rem; margin-bottom: 14px; line-height: 1.5; }

        .btn-submit {
            width: 100%; height: 54px; border: none; border-radius: 12px;
            background: var(--red); color: #fff; font-size: 1rem; font-weight: 700; cursor: pointer;
            box-shadow: 0 6px 16px rgba(217,35,42,.3);
        }
        .btn-submit:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; }
        .foot { text-align: center; font-size: .72rem; color: var(--muted); margin-top: 16px; }
    </style>
</head>
<body>

<header class="head">
    <div class="eyebrow">CPI Jombang &middot; Personalia & General Affairs</div>
    <h1>Inspeksi APAR</h1>
    <p>Isi form di bawah sesuai kondisi APAR di lokasi</p>
</header>

<div class="wrap">

    @if ($errors->any())
        <div class="banner">
            <strong>Form belum bisa dikirim.</strong> Periksa isian yang bertanda merah di bawah.
            @if ($errors->has('foto'))
                Foto perlu dipilih ulang.
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('apar.store') }}" enctype="multipart/form-data" id="aparForm" novalidate>
        @csrf

        {{-- ============ IDENTITAS ============ --}}
        <section class="card">
            <h2>Data Inspeksi</h2>

            <div class="field">
                <label class="lbl" for="nama_inspektor">Nama inspektor <span class="req">*</span></label>
                <input type="text" id="nama_inspektor" name="nama_inspektor" value="{{ old('nama_inspektor', $prefillNama) }}" maxlength="100" autocomplete="name" required>
                @error('nama_inspektor') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="jabatan">Jabatan <span class="req">*</span></label>
                <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $prefillJabatan) }}" maxlength="100" required>
                @error('jabatan') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <div class="row2">
                    <div>
                        <label class="lbl" for="tanggal_inspeksi">Tanggal <span class="req">*</span></label>
                        <input type="date" id="tanggal_inspeksi" name="tanggal_inspeksi" value="{{ old('tanggal_inspeksi', $defaultTanggal) }}" max="{{ $defaultTanggal }}" required>
                    </div>
                    <div>
                        <label class="lbl" for="jam_inspeksi">Jam <span class="req">*</span></label>
                        <input type="time" id="jam_inspeksi" name="jam_inspeksi" value="{{ old('jam_inspeksi', $defaultJam) }}" required>
                    </div>
                </div>
                @error('tanggal_inspeksi') <div class="err">{{ $message }}</div> @enderror
                @error('jam_inspeksi') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="nomor_apar">Nomor APAR <span class="req">*</span></label>
                <input type="text" id="nomor_apar" name="nomor_apar" value="{{ old('nomor_apar') }}" maxlength="50" autocapitalize="characters" required>
                @error('nomor_apar') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="jenis_apar">Jenis APAR <span class="req">*</span></label>
                <select id="jenis_apar" name="jenis_apar" required>
                    <option value="">-- Pilih jenis --</option>
                    @foreach ($jenisList as $kode => $label)
                        <option value="{{ $kode }}" @selected(old('jenis_apar') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis_apar') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="lokasi_apar">Lokasi APAR <span class="req">*</span></label>
                <input type="text" id="lokasi_apar" name="lokasi_apar" value="{{ old('lokasi_apar') }}" maxlength="150" placeholder="Contoh: Gudang Frozen, dekat pintu 2" required>
                @error('lokasi_apar') <div class="err">{{ $message }}</div> @enderror
            </div>
        </section>

        {{-- ============ KONDISI KOMPONEN ============ --}}
        <section class="card">
            <h2>Kondisi Komponen</h2>

            @foreach (\App\Models\AparInspection::KOMPONEN as $field => $label)
                @php
                    $isGauge = $field === 'pressure_gauge';
                    $isRoda  = $field === 'roda';
                    $nilai   = old($field, $isRoda ? 'tidak_ada' : null);
                @endphp

                <div class="field" @if ($isGauge) id="gaugeField" hidden @endif>
                    <span class="lbl">{{ $label }} <span class="req">*</span></span>

                    <div class="seg">
                        <label>
                            <input type="radio" name="{{ $field }}" value="baik" @checked($nilai === 'baik') required>
                            <span class="pill ok">Baik</span>
                        </label>
                        <label>
                            <input type="radio" name="{{ $field }}" value="rusak" @checked($nilai === 'rusak')>
                            <span class="pill bad">Rusak</span>
                        </label>
                        @if ($isRoda)
                            <label>
                                <input type="radio" name="{{ $field }}" value="tidak_ada" @checked($nilai === 'tidak_ada')>
                                <span class="pill na">Tidak Ada</span>
                            </label>
                        @endif
                    </div>

                    @if ($isGauge)
                        <div class="hint">Khusus APAR jenis Dry Chemical Powder.</div>
                    @elseif ($isRoda)
                        <div class="hint">Khusus APAB (alat pemadam api berat). Pilih "Tidak Ada" bila bukan APAB.</div>
                    @endif

                    @error($field) <div class="err">{{ $message }}</div> @enderror
                </div>
            @endforeach
        </section>

        {{-- ============ KETERANGAN & FOTO ============ --}}
        <section class="card">
            <h2>Keterangan &amp; Dokumentasi</h2>

            <div class="field">
                <label class="lbl" for="keterangan">Keterangan kondisi lainnya</label>
                <textarea id="keterangan" name="keterangan" maxlength="1000" placeholder="Tulis temuan atau catatan lain (opsional)">{{ old('keterangan') }}</textarea>
                @error('keterangan') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <span class="lbl">Foto dokumentasi <span class="req">*</span></span>
                <div class="foto-wrap">
                    <input type="file" id="foto" name="foto" accept="image/*" required>
                    <label for="foto" class="foto-btn" id="fotoBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <span id="fotoText">Ambil / pilih foto</span>
                    </label>
                </div>
                <img id="fotoPreview" alt="Pratinjau foto">
                <div id="fotoInfo"></div>
                @error('foto') <div class="err">{{ $message }}</div> @enderror
            </div>
        </section>

        <button type="submit" class="btn-submit" id="btnSubmit">Kirim Inspeksi</button>
        <div class="foot">Data langsung masuk ke PGA.</div>
    </form>
</div>

<script>
(function () {
    // ---- Pressure gauge: hanya untuk Dry Chemical Powder ----
    const jenis = document.getElementById('jenis_apar');
    const gaugeField = document.getElementById('gaugeField');
    const gaugeInputs = gaugeField.querySelectorAll('input');

    function toggleGauge() {
        const show = jenis.value === 'dry_powder';
        gaugeField.hidden = !show;
        gaugeInputs.forEach(i => { i.disabled = !show; i.required = show; });
    }
    jenis.addEventListener('change', toggleGauge);
    toggleGauge();

    // ---- Foto: pratinjau + perkecil di browser sebelum diunggah ----
    const MAX_SISI = 1600;
    const foto = document.getElementById('foto');
    const fotoBtn = document.getElementById('fotoBtn');
    const fotoText = document.getElementById('fotoText');
    const preview = document.getElementById('fotoPreview');
    const info = document.getElementById('fotoInfo');
    const btnSubmit = document.getElementById('btnSubmit');
    const form = document.getElementById('aparForm');

    const kb = n => n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.round(n / 1024) + ' KB';

    async function perkecil(file) {
        const bmp = await createImageBitmap(file, { imageOrientation: 'from-image' });
        const skala = Math.min(1, MAX_SISI / Math.max(bmp.width, bmp.height));
        const w = Math.round(bmp.width * skala);
        const h = Math.round(bmp.height * skala);

        const c = document.createElement('canvas');
        c.width = w; c.height = h;
        const ctx = c.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(bmp, 0, 0, w, h);

        const blob = await new Promise(r => c.toBlob(r, 'image/jpeg', 0.8));
        if (!blob || blob.size >= file.size) return file;
        return new File([blob], 'foto.jpg', { type: 'image/jpeg' });
    }

    foto.addEventListener('change', async function () {
        let file = foto.files[0];
        if (!file) return;

        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Memproses foto...';

        try {
            const kecil = await perkecil(file);
            if (kecil !== file) {
                const dt = new DataTransfer();
                dt.items.add(kecil);
                foto.files = dt.files;
                file = kecil;
            }
        } catch (e) {
            // Browser tidak mendukung / format tidak bisa dibaca: pakai file asli
        }

        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
        fotoBtn.classList.add('has-file');
        fotoText.textContent = 'Ganti foto';
        info.textContent = 'Ukuran foto: ' + kb(file.size);

        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Kirim Inspeksi';
    });

    // ---- Cegah kirim ganda ----
    form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Mengirim...';
    });

    // Validasi bawaan browser (novalidate dimatikan agar pesan field kosong tampil)
    form.removeAttribute('novalidate');
})();
</script>
</body>
</html>
