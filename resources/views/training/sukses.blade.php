<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluasi Terkirim</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --brand: #4f46e5; --brand-soft: #eef2ff; --green: #16a34a; --green-soft: #dcfce7; --slate: #0f172a; --slate-3: #475569; --muted: #64748b; --line: #e2e8f0; --bg: #f1f5f9; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif; }
        body { background: var(--bg); color: var(--slate); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px 14px; }
        .box { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 28px 20px; width: 100%; max-width: 460px; text-align: center; box-shadow: 0 2px 10px rgba(15,23,42,.06); }
        .ico { width: 64px; height: 64px; border-radius: 50%; background: var(--green-soft); color: var(--green); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
        .ico svg { width: 34px; height: 34px; }
        h1 { font-size: 1.3rem; font-weight: 700; }
        .sub { font-size: .86rem; color: var(--slate-3); margin-top: 6px; line-height: 1.6; }
        .info { text-align: left; background: #f8fafc; border: 1px solid var(--line); border-radius: 12px; padding: 14px; margin: 18px 0; font-size: .84rem; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 5px 0; }
        .row span:first-child { color: var(--muted); flex-shrink: 0; }
        .row span:last-child { font-weight: 600; text-align: right; }
        .materi { margin-top: 8px; padding-top: 10px; border-top: 1px solid var(--line); }
        .materi .t { color: var(--muted); margin-bottom: 6px; }
        .materi ul { padding-left: 18px; line-height: 1.7; font-weight: 500; }
        .btn { display: block; width: 100%; padding: 15px; border-radius: 12px; font-size: .95rem; font-weight: 700; text-decoration: none; background: var(--brand); color: #fff; box-shadow: 0 6px 16px rgba(79,70,229,.3); }
        .note { font-size: .74rem; color: var(--muted); margin-top: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h1>Evaluasi Terkirim</h1>
        <p class="sub">Terima kasih, jawaban Anda sudah masuk ke PGA.</p>

        <div class="info">
            <div class="row"><span>Nama</span><span>{{ $info['nama'] }}</span></div>
            <div class="row"><span>NIK</span><span>{{ $info['nik'] }}</span></div>
            <div class="row"><span>Bagian</span><span>{{ $bagianList[$info['bagian']] ?? $info['bagian'] }}</span></div>

            <div class="materi">
                <div class="t">Materi yang diisi</div>
                <ul>
                    @foreach ($info['materi'] as $judul)
                        <li>{{ $judul }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Data diri dibawa supaya tidak diketik ulang bila ingin mengisi materi lain --}}
        <a class="btn" href="{{ route('training.form', ['nama' => $info['nama'], 'nik' => $info['nik'], 'bagian' => $info['bagian']]) }}">Isi Evaluasi Lainnya</a>
        <div class="note">Anda boleh menutup halaman ini.</div>
    </div>
</body>
</html>
