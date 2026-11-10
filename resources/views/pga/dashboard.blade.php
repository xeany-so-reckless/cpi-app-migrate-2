@extends('pga.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_sub', 'Ringkasan inspeksi APAR · ' . $labelBulan)

@push('styles')
    @include('pga.partials.ui')
    <style>
        .grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 16px; }
        .bar-row { display: grid; grid-template-columns: 140px 1fr 36px; align-items: center; gap: 10px; padding: 7px 0; font-size: .84rem; }
        .bar-track { height: 10px; background: #eef0f7; border-radius: 99px; overflow: hidden; }
        .bar-fill { height: 100%; background: var(--danger); border-radius: 99px; }
        .bar-num { text-align: right; font-weight: 700; }
        .rank-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #eef0f7; font-size: .85rem; }
        .rank-row:last-child { border-bottom: none; }
        .rank-no { width: 24px; height: 24px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); font-size: .72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .rank-n { margin-left: auto; font-weight: 700; color: var(--danger); }
        .panel h2 { margin-bottom: 12px; }
        .month-form { display: flex; align-items: center; gap: 8px; }
        @media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } .bar-row { grid-template-columns: 110px 1fr 30px; } }
    </style>
@endpush

@section('content')
    <div class="toolbar">
        <form method="GET" action="{{ route('pga.index') }}" class="month-form">
            <label for="bulan" class="muted" style="font-size:.8rem; font-weight:600;">Bulan</label>
            <input type="month" id="bulan" name="bulan" class="in" style="width:auto;" value="{{ $bulan }}" onchange="this.form.submit()">
        </form>
        <span class="spacer"></span>
        <a href="{{ route('pga.apar.index') }}" class="btn btn-light"><span class="material-symbols-outlined">list_alt</span> Lihat Semua Inspeksi</a>
    </div>

    <div class="stat-grid">
        <div class="stat-box info">
            <div class="stat-label">Total Inspeksi</div>
            <div class="stat-value">{{ number_format($total, 0, ',', '.') }}</div>
        </div>
        <div class="stat-box success">
            <div class="stat-label">Kondisi OK</div>
            <div class="stat-value">{{ number_format($ok, 0, ',', '.') }}</div>
        </div>
        <div class="stat-box danger">
            <div class="stat-label">Ada Temuan</div>
            <div class="stat-value">{{ number_format($temuan, 0, ',', '.') }}</div>
        </div>
        <div class="stat-box danger">
            <div class="stat-label">% Temuan</div>
            <div class="stat-value">{{ number_format($persenTemuan, 1, ',', '.') }}%</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Inspeksi Hari Ini</div>
            <div class="stat-value">{{ number_format($hariIni, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="panel">
            <h2>Komponen Rusak ({{ $labelBulan }})</h2>
            @foreach ($perKomponen as $k)
                <div class="bar-row">
                    <span>{{ $k['label'] }}</span>
                    <div class="bar-track"><div class="bar-fill" style="width: {{ $k['jumlah'] > 0 ? max(3, round($k['jumlah'] / $maxKomponen * 100)) : 0 }}%;"></div></div>
                    <span class="bar-num">{{ $k['jumlah'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="panel">
            <h2>Lokasi dengan Temuan Terbanyak</h2>
            @forelse ($topLokasi as $n => $l)
                <div class="rank-row">
                    <span class="rank-no">{{ $n + 1 }}</span>
                    <span>{{ $l->lokasi_apar }}</span>
                    <span class="rank-n">{{ $l->jumlah }} temuan</span>
                </div>
            @empty
                <p class="muted" style="font-size:.86rem;">Tidak ada temuan pada bulan ini.</p>
            @endforelse
        </div>
    </div>

    <div class="panel" style="padding-bottom:10px;">
        <h2>Inspeksi Terbaru</h2>
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor APAR</th>
                        <th>Lokasi</th>
                        <th>Inspektor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($terbaru as $i)
                        <tr>
                            <td style="white-space:nowrap;">{{ $i->tanggal_inspeksi->format('d/m/Y') }} <span class="muted">{{ $i->jam_tampil }}</span></td>
                            <td><a class="lnk" href="{{ route('pga.apar.show', $i) }}">{{ $i->nomor_apar }}</a></td>
                            <td>{{ $i->lokasi_apar }}</td>
                            <td>{{ $i->nama_inspektor }}</td>
                            <td>
                                @if ($i->has_findings)
                                    <span class="badge bad">Ada Temuan</span>
                                @else
                                    <span class="badge ok">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada inspeksi masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection