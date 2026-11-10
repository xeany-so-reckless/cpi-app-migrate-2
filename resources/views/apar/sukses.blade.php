<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspeksi Terkirim</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --red: #d9232a; --green: #16a34a; --amber: #d97706; --slate: #0f172a; --muted: #64748b; --line: #e2e8f0; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif; }
        body { background: #f1f5f9; color: var(--slate); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { width: 100%; max-width: 460px; background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 32px 22px; text-align: center; box-shadow: 0 4px 18px rgba(15,23,42,.07); }
        .icon { width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
        .icon svg { width: 38px; height: 38px; stroke: #fff; }
        .icon.ok { background: var(--green); }
        .icon.warn { background: var(--amber); }
        h1 { font-size: 1.3rem; margin-bottom: 6px; }
        .sub { color: var(--muted); font-size: .88rem; line-height: 1.6; margin-bottom: 18px; }
        .badge { display: inline-block; padding: 6px 16px; border-radius: 999px; font-size: .82rem; font-weight: 700; margin-bottom: 20px; }
        .badge.ok { background: #dcfce7; color: #166534; }
        .badge.warn { background: #fef3c7; color: #92400e; }
        .sum { text-align: left; border: 1px solid var(--line); border-radius: 12px; padding: 4px 14px; margin-bottom: 22px; }
        .sum div { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--line); font-size: .85rem; }
        .sum div:last-child { border-bottom: none; }
        .sum span:first-child { color: var(--muted); }
        .sum span:last-child { font-weight: 600; text-align: right; }
        .btn { display: block; width: 100%; padding: 15px; border-radius: 12px; background: var(--red); color: #fff; font-weight: 700; font-size: .95rem; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    @if ($info['has_findings'])
        <div class="icon warn">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
        </div>
        <h1>Inspeksi Terkirim</h1>
        <p class="sub">Ada komponen yang berstatus rusak. Laporan sudah diteruskan ke PGA untuk ditindaklanjuti.</p>
        <span class="badge warn">ADA TEMUAN</span>
    @else
        <div class="icon ok">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h1>Inspeksi Terkirim</h1>
        <p class="sub">Terima kasih, hasil inspeksi sudah tersimpan dan masuk ke PGA.</p>
        <span class="badge ok">KONDISI OK</span>
    @endif

    <div class="sum">
        <div><span>Nomor APAR</span><span>{{ $info['nomor_apar'] }}</span></div>
        <div><span>Jenis</span><span>{{ $info['jenis'] }}</span></div>
        <div><span>Lokasi</span><span>{{ $info['lokasi'] }}</span></div>
        <div><span>Inspektor</span><span>{{ $info['nama'] }}</span></div>
    </div>

    <a class="btn" href="{{ route('apar.form', ['nama' => $info['nama'], 'jabatan' => $info['jabatan']]) }}">Inspeksi APAR Lainnya</a>
</div>
</body>
</html>
