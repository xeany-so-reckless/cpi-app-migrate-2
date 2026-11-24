<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluasi Penerapan Training</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #4f46e5;
            --brand-dark: #4338ca;
            --brand-soft: #eef2ff;
            --red: #dc2626;
            --red-soft: #fee2e2;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --blue: #2563eb;
            --blue-soft: #dbeafe;
            --amber: #d97706;
            --amber-soft: #fef3c7;
            --slate: #0f172a;
            --slate-3: #475569;
            --muted: #64748b;
            --line: #e2e8f0;
            --bg: #f1f5f9;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif; }
        body { background: var(--bg); color: var(--slate); min-height: 100vh; -webkit-text-size-adjust: 100%; }

        .head { background: var(--brand); color: #fff; padding: 22px 18px 26px; text-align: center; }
        .head .eyebrow { font-size: .68rem; letter-spacing: 1.6px; text-transform: uppercase; opacity: .85; }
        .head h1 { font-size: 1.4rem; font-weight: 700; margin-top: 4px; }
        .head p { font-size: .8rem; opacity: .9; margin-top: 4px; }

        .wrap { max-width: 560px; margin: -14px auto 40px; padding: 0 14px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 18px 16px; margin-bottom: 14px; box-shadow: 0 2px 10px rgba(15,23,42,.05); }
        .card[hidden] { display: none; }
        .card h2 { font-size: .78rem; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--brand); margin-bottom: 14px; }

        .field { margin-bottom: 16px; }
        .field:last-child { margin-bottom: 0; }
        .lbl { display: block; font-size: .82rem; font-weight: 600; color: var(--slate-3); margin-bottom: 6px; }
        .req { color: var(--red); }
        .hint { font-size: .78rem; color: var(--muted); line-height: 1.5; margin-bottom: 12px; }

        input[type=text], input[type=date], select {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff;
            padding: 0 12px; height: 48px; font-size: 16px; color: var(--slate);
        }
        input:focus, select:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-soft); }

        /* Checkbox materi */
        .chk { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 8px; cursor: pointer; background: #fff; position: relative; }
        .chk:last-child { margin-bottom: 0; }
        .chk input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; cursor: pointer; }
        .chk .box { width: 22px; height: 22px; border: 2px solid #cbd5e1; border-radius: 6px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: #fff; }
        .chk .txt { font-size: .88rem; font-weight: 500; line-height: 1.35; flex: 1; }
        .chk input:checked ~ .box { background: var(--brand); border-color: var(--brand); }
        .chk input:checked ~ .box::after { content: ''; width: 5px; height: 10px; border: solid #fff; border-width: 0 2.5px 2.5px 0; transform: rotate(45deg) translate(-1px, -1px); }
        .chk:has(input:checked) { border-color: var(--brand); background: var(--brand-soft); }
        .chk.locked { cursor: default; }
        .chk.locked input { cursor: default; }
        .chk input:focus-visible ~ .box { outline: 3px solid #93c5fd; outline-offset: 1px; }
        .tag { font-size: .62rem; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; padding: 3px 8px; border-radius: 99px; background: var(--brand); color: #fff; flex-shrink: 0; }

        /* Petunjuk */
        .instr { background: var(--brand-soft); border-color: #c7d2fe; }
        .instr h2 { margin-bottom: 8px; }
        .instr p { font-size: .82rem; color: var(--slate-3); line-height: 1.6; }

        /* Materi & pertanyaan */
        .materi h2 { display: flex; gap: 8px; align-items: baseline; }
        .materi h2 .kode { background: var(--brand); color: #fff; border-radius: 6px; padding: 1px 8px; font-size: .74rem; }
        .q { padding: 14px 0; border-top: 1px solid var(--line); }
        .q:first-of-type { border-top: none; padding-top: 0; }
        .qt { display: flex; gap: 10px; font-size: .88rem; line-height: 1.5; margin-bottom: 10px; }
        .qt .no { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--bg); color: var(--slate-3); font-size: .72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-top: 1px; }

        .opts { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .opt { position: relative; cursor: pointer; }
        .opt input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; cursor: pointer; }
        .pill {
            display: flex; align-items: center; justify-content: center; text-align: center;
            min-height: 44px; padding: 6px 8px; border-radius: 10px; border: 2px solid var(--line);
            background: #f8fafc; color: var(--slate-3); font-size: .84rem; font-weight: 600; transition: all .15s ease;
        }
        .opt input:checked + .pill.o4 { background: var(--green-soft); border-color: var(--green); color: #166534; }
        .opt input:checked + .pill.o3 { background: var(--blue-soft); border-color: var(--blue); color: #1e40af; }
        .opt input:checked + .pill.o2 { background: var(--amber-soft); border-color: var(--amber); color: #92400e; }
        .opt input:checked + .pill.o1 { background: var(--red-soft); border-color: var(--red); color: #991b1b; }
        .opt input:focus-visible + .pill { outline: 3px solid #93c5fd; outline-offset: 1px; }

        .err { color: #dc2626; font-size: .78rem; margin-top: 5px; font-weight: 500; }
        .banner { background: var(--red-soft); border: 1px solid #fca5a5; color: #991b1b; border-radius: 12px; padding: 12px 14px; font-size: .84rem; margin-bottom: 14px; line-height: 1.5; }

        .btn-submit {
            width: 100%; height: 54px; border: none; border-radius: 12px;
            background: var(--brand); color: #fff; font-size: 1rem; font-weight: 700; cursor: pointer;
            box-shadow: 0 6px 16px rgba(79,70,229,.3);
        }
        .btn-submit:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; }
        .foot { text-align: center; font-size: .72rem; color: var(--muted); margin-top: 16px; }
    </style>
</head>
<body>

@php
    $terpilih = array_values(array_unique(array_merge($wajib, array_map('strval', (array) old('materi', [])))));
    $jumlahBelumDijawab = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'jawaban.'))->count();
@endphp

<header class="head">
    <div class="eyebrow">CPI Jombang &middot; PGA</div>
    <h1>Evaluasi Penerapan Training</h1>
    <p>Isi sesuai kondisi penerapan di tempat kerja Anda</p>
</header>

<div class="wrap">

    @if ($errors->any())
        <div class="banner">
            <strong>Form belum bisa dikirim.</strong>
            @if ($errors->has('umum'))
                {{ $errors->first('umum') }}
            @elseif ($jumlahBelumDijawab > 0)
                Masih ada {{ $jumlahBelumDijawab }} pertanyaan yang belum dijawab. Periksa isian yang bertanda merah.
            @else
                Periksa isian yang bertanda merah di bawah.
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('training.store') }}" id="trainingForm">
        @csrf

        {{-- ============ DATA DIRI ============ --}}
        <section class="card">
            <h2>Data Diri</h2>

            <div class="field">
                <label class="lbl" for="nama">Nama <span class="req">*</span></label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $prefillNama) }}" maxlength="100" autocomplete="name" required>
                @error('nama') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="nik">Nomor Induk Karyawan <span class="req">*</span></label>
                <input type="text" id="nik" name="nik" value="{{ old('nik', $prefillNik) }}" maxlength="30" autocapitalize="characters" autocomplete="off" required>
                @error('nik') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="tanggal_pengisian">Tanggal pengisian <span class="req">*</span></label>
                <input type="date" id="tanggal_pengisian" name="tanggal_pengisian" value="{{ old('tanggal_pengisian', $defaultTanggal) }}" max="{{ $defaultTanggal }}" required>
                @error('tanggal_pengisian') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="bagian">Bagian <span class="req">*</span></label>
                <select id="bagian" name="bagian" required>
                    <option value="">-- Pilih bagian --</option>
                    @foreach ($bagianList as $kode => $label)
                        <option value="{{ $kode }}" @selected(old('bagian', $prefillBagian) === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('bagian') <div class="err">{{ $message }}</div> @enderror
            </div>
        </section>

        {{-- ============ PILIH TRAINING ============ --}}
        <section class="card">
            <h2>Training yang Sudah Diikuti</h2>
            <p class="hint">Boleh pilih lebih dari satu. Materi bertanda <strong>Wajib</strong> sudah otomatis terpilih. Pertanyaan akan muncul sesuai materi yang dipilih.</p>

            @foreach ($materi as $kode => $m)
                @php $isWajib = in_array($kode, $wajib, true); @endphp
                <label class="chk {{ $isWajib ? 'locked' : '' }}">
                    <input type="checkbox" name="materi[]" value="{{ $kode }}"
                        @checked(in_array($kode, $terpilih, true))
                        @if ($isWajib) onclick="return false" tabindex="-1" aria-disabled="true" @endif>
                    <span class="box"></span>
                    <span class="txt">{{ $m['judul'] }}</span>
                    @if ($isWajib) <span class="tag">Wajib</span> @endif
                </label>
            @endforeach
        </section>

        {{-- ============ PETUNJUK ============ --}}
        <section class="card instr">
            <h2>Petunjuk Pengisian</h2>
            <p>Pilih jawaban yang sesuai dengan kondisi penerapan di tempat kerja Anda:
                <strong>Selalu</strong>, <strong>Sering</strong>, <strong>Kadang-Kadang</strong>, atau <strong>Tidak Pernah</strong>.
                Semua pertanyaan pada materi yang dipilih wajib dijawab.</p>
        </section>

        {{-- ============ PERTANYAAN PER MATERI ============ --}}
        @foreach ($materi as $kode => $m)
            <section class="card materi" data-materi="{{ $kode }}" @if (! in_array($kode, $terpilih, true)) hidden @endif>
                <h2><span class="kode">{{ $m['kode'] }}</span> {{ $m['judul'] }}</h2>

                @foreach ($m['pertanyaan'] as $no => $teks)
                    <div class="q">
                        <div class="qt"><span class="no">{{ $loop->iteration }}</span><span>{{ $teks }}</span></div>

                        <div class="opts">
                            @foreach ($skala as $nilai => $label)
                                <label class="opt">
                                    <input type="radio" name="jawaban[{{ $kode }}][{{ $no }}]" value="{{ $nilai }}"
                                        @checked((string) old("jawaban.{$kode}.{$no}") === (string) $nilai)>
                                    <span class="pill o{{ $nilai }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error("jawaban.{$kode}.{$no}") <div class="err">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </section>
        @endforeach

        <button type="submit" class="btn-submit" id="btnSubmit">Kirim Evaluasi</button>
        <div class="foot">Data langsung masuk ke PGA.</div>
    </form>
</div>

<script>
(function () {
    const form = document.getElementById('trainingForm');
    const btn  = document.getElementById('btnSubmit');

    // Tampilkan pertanyaan hanya untuk materi yang dicentang.
    // Pertanyaan yang disembunyikan dinonaktifkan supaya tidak ikut dikirim / diwajibkan.
    function sinkron() {
        document.querySelectorAll('section.materi').forEach(function (sec) {
            const cb = form.querySelector('input[name="materi[]"][value="' + sec.dataset.materi + '"]');
            const tampil = !!(cb && cb.checked);
            sec.hidden = !tampil;
            sec.querySelectorAll('input[type=radio]').forEach(function (r) {
                r.disabled = !tampil;
                r.required = tampil;
            });
        });
    }

    form.querySelectorAll('input[name="materi[]"]').forEach(function (cb) {
        cb.addEventListener('change', sinkron);
    });
    sinkron();

    // Cegah kirim ganda
    form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        btn.disabled = true;
        btn.textContent = 'Mengirim...';
    });
})();
</script>
</body>
</html>
