@extends('pga.layouts.app')

@section('title', 'Detail Inspeksi')
@section('page_title', 'Detail Inspeksi APAR')
@section('page_sub', $i->nomor_apar . ' · ' . $i->tanggal_inspeksi->format('d/m/Y') . ' ' . $i->jam_tampil)

@push('styles')
    @include('pga.partials.ui')
    <style>
        .detail-grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 16px; align-items: start; }
        .kv { display: grid; grid-template-columns: 150px 1fr; gap: 0; }
        .kv > div { padding: 10px 0; border-bottom: 1px solid #eef0f7; font-size: .86rem; }
        .kv > div:nth-child(odd) { color: var(--muted); font-weight: 600; }
        .kv > div:nth-last-child(-n+2) { border-bottom: none; }
        .sec-title { font-size: .76rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: var(--primary); margin-bottom: 8px; }
        .foto-box img { width: 100%; border-radius: 12px; border: 1px solid var(--line); display: block; background: #000; }
        .meta { font-size: .76rem; color: var(--muted); line-height: 1.7; margin-top: 12px; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .actions .spacer { flex: 1; }
        @media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } .kv { grid-template-columns: 120px 1fr; } }
    </style>
@endpush

@section('content')
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="actions">
        <a href="{{ route('pga.apar.index') }}" class="btn btn-light"><span class="material-symbols-outlined">arrow_back</span> Kembali</a>
        <span class="spacer"></span>
        <a href="{{ route('pga.apar.edit', $i) }}" class="btn btn-primary"><span class="material-symbols-outlined">edit</span> Edit</a>
        <form method="POST" action="{{ route('pga.apar.destroy', $i) }}" onsubmit="return confirm('Hapus inspeksi APAR {{ e($i->nomor_apar) }}? Data dan foto tidak bisa dikembalikan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><span class="material-symbols-outlined">delete</span> Hapus</button>
        </form>
    </div>

    <div class="detail-grid">
        <div>
            <div class="panel" style="margin-bottom:16px;">
                <div class="sec-title">Data Inspeksi</div>
                <div class="kv">
                    <div>Status</div>
                    <div>
                        @if ($i->has_findings)
                            <span class="badge bad">Ada Temuan</span>
                        @else
                            <span class="badge ok">OK</span>
                        @endif
                    </div>
                    <div>Nomor APAR</div><div><strong>{{ $i->nomor_apar }}</strong></div>
                    <div>Jenis APAR</div><div>{{ $i->jenis_label }}</div>
                    <div>Lokasi</div><div>{{ $i->lokasi_apar }}</div>
                    <div>Tanggal</div><div>{{ $i->tanggal_inspeksi->format('d/m/Y') }}</div>
                    <div>Jam</div><div>{{ $i->jam_tampil }}</div>
                    <div>Inspektor</div><div>{{ $i->nama_inspektor }}</div>
                    <div>Jabatan</div><div>{{ $i->jabatan }}</div>
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="sec-title">Kondisi Komponen</div>
                <div class="kv">
                    @foreach (\App\Models\AparInspection::KOMPONEN as $field => $label)
                        @php $nilai = $i->{$field}; @endphp
                        <div>{{ $label }}</div>
                        <div>
                            @if ($nilai === 'baik')
                                <span class="badge ok">Baik</span>
                            @elseif ($nilai === 'rusak')
                                <span class="badge bad">Rusak</span>
                            @elseif ($nilai === 'tidak_ada')
                                <span class="badge na">Tidak Ada</span>
                            @else
                                <span class="muted">Tidak berlaku</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <div class="sec-title">Keterangan Kondisi Lainnya</div>
                @if ($i->keterangan)
                    <p style="font-size:.88rem; line-height:1.7; color:var(--text); white-space:pre-line;">{{ $i->keterangan }}</p>
                @else
                    <p class="muted" style="font-size:.86rem;">Tidak ada keterangan.</p>
                @endif
            </div>
        </div>

        <div>
            <div class="panel foto-box">
                <div class="sec-title">Foto Dokumentasi</div>
                <a href="{{ $i->foto_url }}" target="_blank" rel="noopener">
                    <img src="{{ $i->foto_url }}" alt="Foto APAR {{ $i->nomor_apar }}">
                </a>
                <div class="meta">
                    Klik foto untuk membuka ukuran penuh.<br>
                    Dikirim: {{ $i->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB<br>
                    @if ($i->ip_address) IP pengirim: {{ $i->ip_address }}<br> @endif
                    @if ($i->diedit_pada)
                        Terakhir diedit: {{ $i->diedit_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB oleh {{ $i->diedit_oleh }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
