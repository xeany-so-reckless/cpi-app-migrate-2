@extends('pga.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_sub', 'Ringkasan APAR & Evaluasi Training · ' . $labelBulan)

@push('styles')
    @include('pga.partials.ui')
    @include('pga.training._ui')
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

        /* ===== Bagian Evaluasi Training ===== */
        .sec-title { display: flex; align-items: center; gap: 10px; margin: 30px 0 14px; font-size: 1.05rem; font-weight: 700; }
        .sec-title .material-symbols-outlined { color: var(--primary); }
        .sec-title .spacer { flex: 1; }

        .tr-row { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(80px, 1fr) 150px; align-items: center; gap: 12px; padding: 9px 0; border-bottom: 1px solid #eef0f7; font-size: .84rem; }
        .tr-row:last-child { border-bottom: none; }
        .tr-name { display: flex; align-items: flex-start; gap: 8px; line-height: 1.35; }
        .tr-kode { flex-shrink: 0; background: var(--primary-soft); color: var(--primary); border-radius: 6px; padding: 1px 7px; font-size: .7rem; font-weight: 700; margin-top: 1px; }
        .tr-sub { font-size: .72rem; color: var(--muted); margin-top: 2px; }
        .tr-lbl { display: flex; flex-direction: column; align-items: flex-start; gap: 3px; }
        .tr-warn { font-size: .68rem; font-weight: 700; color: var(--danger); display: flex; align-items: center; gap: 3px; }
        .tr-warn .material-symbols-outlined { font-size: 14px; }
        .bar-fill.fill-sl { background: #16a34a; }
        .bar-fill.fill-sr { background: #2563eb; }
        .bar-fill.fill-kd { background: #d97706; }
        .bar-fill.fill-tp { background: #dc2626; }
        .tr-legend { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; align-items: center; font-size: .76rem; color: var(--muted); }

        @media (max-width: 900px) {
            .grid-2 { grid-template-columns: 1fr; }
            .bar-row { grid-template-columns: 110px 1fr 30px; }
            .tr-row { grid-template-columns: 1fr; gap: 6px; }
        }
    </style>
@endpush

@section('content')
    @php
        $kelasFill = ['Selalu' => 'fill-sl', 'Sering' => 'fill-sr', 'Kadang-Kadang' => 'fill-kd', 'Tidak Pernah' => 'fill-tp'];
    @endphp

    <div class="toolbar">
        <form method="GET" action="{{ route('pga.index') }}" class="month-form">
            <label for="bulan" class="muted" style="font-size:.8rem; font-weight:600;">Bulan</label>
            <input type="month" id="bulan" name="bulan" class="in" style="width:auto;" value="{{ $bulan }}" onchange="this.form.submit()">
        </form>
        <span class="spacer"></span>
        <a href="{{ route('pga.apar.index') }}" class="btn btn-light"><span class="material-symbols-outlined">list_alt</span> Lihat Semua Inspeksi</a>
        <a href="{{ route('pga.training.index') }}" class="btn btn-light"><span class="material-symbols-outlined">school</span> Lihat Semua Evaluasi</a>
    </div>

    {{-- ====================== APAR ====================== --}}
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

    {{-- ====================== EVALUASI TRAINING ====================== --}}
    <div class="sec-title">
        <span class="material-symbols-outlined">school</span>
        Evaluasi Training · {{ $labelBulan }}
    </div>

    <div class="stat-grid">
        <div class="stat-box info">
            <div class="stat-label">Total Evaluasi</div>
            <div class="stat-value">{{ number_format($training['total'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Peserta (NIK berbeda)</div>
            <div class="stat-value">{{ number_format($training['peserta'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-box danger">
            <div class="stat-label">Perlu Perhatian</div>
            <div class="stat-value">{{ number_format($training['perhatian'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Evaluasi Hari Ini</div>
            <div class="stat-value">{{ number_format($training['hariIni'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="panel">
            <h2>Penerapan per Materi</h2>
            <div class="tr-legend">
                <span>Rata-rata jawaban peserta:</span>
                @foreach (\App\Models\TrainingEvaluation::SKALA as $teks)
                    @include('pga.training._label', ['teks' => $teks])
                @endforeach
            </div>

            @foreach ($training['perMateri'] as $m)
                <div class="tr-row">
                    <div class="tr-name">
                        <span class="tr-kode">{{ $m['kode'] }}</span>
                        <div>
                            {{ $m['nama'] }}
                            <div class="tr-sub">{{ $m['pengisi'] > 0 ? $m['pengisi'] . ' pengisi' : 'Belum ada data' }}{{ $m['wajib'] ? ' · Wajib' : '' }}</div>
                        </div>
                    </div>
                    <div class="bar-track"><div class="bar-fill {{ $kelasFill[$m['label']] ?? '' }}" style="width: {{ $m['persen'] }}%;"></div></div>
                    <div class="tr-lbl">
                        @if ($m['label'])
                            @include('pga.training._label', ['teks' => $m['label']])
                            @if ($m['perhatian'])
                                <span class="tr-warn"><span class="material-symbols-outlined">warning</span> Perlu perhatian</span>
                            @endif
                        @else
                            <span class="muted">-</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="panel">
            <h2>Penerapan per Bagian</h2>
            <p class="muted" style="font-size:.78rem; margin-bottom:10px;">Diurutkan dari bagian yang paling lemah.</p>

            @forelse ($training['perBagian'] as $b)
                <div class="tr-row">
                    <div class="tr-name">
                        <div>
                            {{ $b['nama'] }}
                            <div class="tr-sub">{{ $b['pengisi'] }} pengisi</div>
                        </div>
                    </div>
                    <div class="bar-track"><div class="bar-fill {{ $kelasFill[$b['label']] ?? '' }}" style="width: {{ $b['persen'] }}%;"></div></div>
                    <div class="tr-lbl">
                        @include('pga.training._label', ['teks' => $b['label']])
                        @if ($b['perhatian'])
                            <span class="tr-warn"><span class="material-symbols-outlined">warning</span> Perlu perhatian</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="muted" style="font-size:.86rem;">Belum ada evaluasi training pada bulan ini.</p>
            @endforelse
        </div>
    </div>

    <div class="panel" style="padding-bottom:10px;">
        <h2>Evaluasi Terbaru</h2>
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Bagian</th>
                        <th>Penerapan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($training['terbaru'] as $t)
                        <tr>
                            <td style="white-space:nowrap;">{{ $t->tanggal_pengisian->format('d/m/Y') }}</td>
                            <td>
                                <a class="lnk" href="{{ route('pga.training.show', $t) }}">{{ $t->nama }}</a>
                                <div class="muted" style="font-size:.74rem;">{{ $t->nik }}</div>
                            </td>
                            <td>{{ $t->bagian_label }}</td>
                            <td>
                                @if ($t->rata_nilai !== null)
                                    @include('pga.training._label', ['teks' => \App\Models\TrainingEvaluation::labelRataRata((float) $t->rata_nilai)])
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($t->has_findings)
                                    <span class="badge bad">Perlu Perhatian</span>
                                @else
                                    <span class="badge ok">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada evaluasi training masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
