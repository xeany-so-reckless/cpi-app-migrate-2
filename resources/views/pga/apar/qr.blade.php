@extends('pga.layouts.app')

@section('title', 'Cetak QR APAR')
@section('page_title', 'Cetak QR APAR')
@section('page_sub', 'Satu QR untuk semua APAR · mengarah ke form inspeksi')

@push('styles')
    @include('pga.partials.ui')
    <style>
        .warn { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; border-radius: 12px; padding: 12px 16px; font-size: .85rem; line-height: 1.6; margin-bottom: 16px; }
        .qr-form { display: grid; grid-template-columns: 1fr; gap: 12px; }
        .qr-row { display: grid; grid-template-columns: 180px 140px auto; gap: 12px; align-items: end; }
        .fgroup label { display: block; font-size: .7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px; }
        .tips { font-size: .8rem; color: var(--muted); line-height: 1.7; margin-top: 12px; }
        .tips strong { color: var(--text); }

        /* ===== Lembar label ===== */
        .sheet { display: grid; justify-content: center; margin-top: 18px; }
        .sheet.stiker { grid-template-columns: repeat(2, 90mm); }
        .sheet.besar  { grid-template-columns: 170mm; gap: 14px; }

        .label {
            background: #fff; color: #000;
            border: 1px dashed #94a3b8;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 4mm;
            break-inside: avoid; page-break-inside: avoid;
        }
        .sheet.stiker .label { width: 90mm; height: 85mm; }
        .sheet.besar  .label { width: 170mm; height: 250mm; }

        .label .co { font-size: 7pt; font-weight: 700; letter-spacing: .6px; color: #334155; margin-bottom: 2mm; }
        .label .banner { background: #d9232a; color: #fff; font-weight: 800; letter-spacing: 1.5px; border-radius: 2mm; padding: 1.5mm 6mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .label img { display: block; }
        .label .scan { font-weight: 700; }
        .label .sub { color: #334155; }
        .label .addr { color: #64748b; font-family: 'JetBrains Mono', monospace; word-break: break-all; }

        .sheet.stiker .label .banner { font-size: 12pt; }
        .sheet.stiker .label img { width: 52mm; height: 52mm; margin: 2.5mm 0; }
        .sheet.stiker .label .scan { font-size: 11pt; }
        .sheet.stiker .label .sub { font-size: 8pt; }
        .sheet.stiker .label .addr { font-size: 6pt; margin-top: 1.5mm; }

        .sheet.besar .label .co { font-size: 11pt; margin-bottom: 6mm; }
        .sheet.besar .label .banner { font-size: 30pt; padding: 4mm 14mm; border-radius: 4mm; }
        .sheet.besar .label img { width: 130mm; height: 130mm; margin: 10mm 0; }
        .sheet.besar .label .scan { font-size: 24pt; }
        .sheet.besar .label .sub { font-size: 14pt; margin-top: 3mm; }
        .sheet.besar .label .addr { font-size: 10pt; margin-top: 8mm; }

        @media (max-width: 700px) {
            .qr-row { grid-template-columns: 1fr 1fr; }
            .qr-row .fgroup:last-child { grid-column: 1 / -1; }
            .sheet.stiker { grid-template-columns: 90mm; }
        }

        @media print {
            @page { size: A4; margin: 10mm; }
            body { background: #fff; }
            .sidebar, .topbar, .overlay, .no-print { display: none !important; }
            .main { margin-left: 0 !important; }
            .content { padding: 0 !important; }
            .sheet { margin-top: 0; }
            .sheet.stiker { gap: 0; }
            .sheet.besar { gap: 0; }
            .sheet.besar .label { page-break-after: always; break-after: page; }
            .sheet.besar .label:last-child { page-break-after: auto; break-after: auto; }
        }
    </style>
@endpush

@section('content')
    <div class="no-print">
        @if ($errors->any())
            <div class="flash flash-err">
                @foreach ($errors->all() as $e) {{ $e }}<br> @endforeach
            </div>
        @endif

        @if ($qrError)
            <div class="flash flash-err">{{ $qrError }}</div>
        @endif

        @if ($lokal)
            <div class="warn">
                <strong>Alamat ini hanya bisa dibuka dari komputer ini.</strong>
                Isinya <code>{{ parse_url($url, PHP_URL_HOST) }}</code>, sehingga HP inspektor tidak akan bisa membukanya.
                Ganti dengan IP atau nama host server yang bisa dijangkau dari WiFi pabrik, lalu tekan "Tampilkan".
            </div>
        @endif

        <div class="panel" style="margin-bottom:16px;">
            <form method="GET" action="{{ route('pga.apar.qr') }}" class="qr-form">
                <div class="fgroup">
                    <label for="url">Alamat form inspeksi (isi QR)</label>
                    <input type="text" id="url" name="url" class="in" value="{{ $url }}" placeholder="http://10.60.22.31/apar" maxlength="255">
                </div>

                <div class="qr-row">
                    <div class="fgroup">
                        <label for="ukuran">Ukuran</label>
                        <select id="ukuran" name="ukuran" class="in">
                            <option value="stiker" @selected($ukuran === 'stiker')>Stiker (6 per A4)</option>
                            <option value="besar" @selected($ukuran === 'besar')>Besar (1 per A4)</option>
                        </select>
                    </div>
                    <div class="fgroup">
                        <label for="jumlah">Jumlah label</label>
                        <input type="number" id="jumlah" name="jumlah" class="in" min="1" max="48" value="{{ $jumlah }}">
                    </div>
                    <div class="fgroup" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-light"><span class="material-symbols-outlined">refresh</span> Tampilkan</button>
                        @if ($qrUri)
                            <button type="button" class="btn btn-primary" onclick="window.print()"><span class="material-symbols-outlined">print</span> Cetak</button>
                        @endif
                    </div>
                </div>
            </form>

            <div class="tips">
                <strong>Sebelum mencetak massal:</strong>
                (1) pastikan alamat di atas bisa dibuka dari HP yang tersambung ke WiFi pabrik,
                (2) scan satu label di layar atau hasil cetak untuk memastikan form terbuka,
                (3) alamat ini <strong>harus tetap</strong> setelah QR ditempel. Kalau IP server berubah, semua QR berhenti berfungsi.
                Gunakan IP statis atau nama host tetap dari IT.
            </div>
        </div>
    </div>

    @if ($qrUri)
        <div class="sheet {{ $ukuran }}">
            @for ($n = 0; $n < $jumlah; $n++)
                <div class="label">
                    <div class="co">PT. CHAROEN POKPHAND INDONESIA · JOMBANG</div>
                    <div class="banner">INSPEKSI APAR</div>
                    <img src="{{ $qrUri }}" alt="QR Inspeksi APAR">
                    <div class="scan">SCAN UNTUK MENGISI INSPEKSI</div>
                    <div class="sub">Isi kondisi APAR lewat form di HP Anda</div>
                    <div class="addr">{{ $url }}</div>
                </div>
            @endfor
        </div>
    @endif
@endsection
