@extends('pga.layouts.app')

@section('title', 'Evaluasi Training')
@section('page_title', 'Evaluasi Training')
@section('page_sub', 'Hasil evaluasi penerapan training karyawan')

@push('styles')
    @include('pga.partials.ui')
    @include('pga.training._ui')
@endpush

@section('content')
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="panel" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('pga.training.index') }}" class="filters">
            <div class="fgroup">
                <label for="dari">Dari tanggal</label>
                <input type="date" id="dari" name="dari" class="in" value="{{ $filter['dari'] ?? '' }}">
            </div>
            <div class="fgroup">
                <label for="sampai">Sampai tanggal</label>
                <input type="date" id="sampai" name="sampai" class="in" value="{{ $filter['sampai'] ?? '' }}">
            </div>
            <div class="fgroup">
                <label for="bagian">Bagian</label>
                <select id="bagian" name="bagian" class="in">
                    <option value="">Semua</option>
                    @foreach ($bagianList as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['bagian'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgroup">
                <label for="materi">Materi</label>
                <select id="materi" name="materi" class="in">
                    <option value="">Semua</option>
                    @foreach ($materiList as $kode => $m)
                        <option value="{{ $kode }}" @selected(($filter['materi'] ?? '') === $kode)>{{ $m['judul'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgroup">
                <label for="status">Status</label>
                <select id="status" name="status" class="in">
                    <option value="">Semua</option>
                    <option value="ok" @selected(($filter['status'] ?? '') === 'ok')>OK</option>
                    <option value="perhatian" @selected(($filter['status'] ?? '') === 'perhatian')>Perlu Perhatian</option>
                </select>
            </div>
            <div class="fgroup">
                <label for="q">Cari</label>
                <input type="text" id="q" name="q" class="in" placeholder="Nama / NIK" value="{{ $filter['q'] ?? '' }}">
            </div>
            <div class="fgroup" style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('pga.training.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>

    <div class="toolbar">
        <span class="muted" style="font-size:.84rem;">
            <strong style="color:var(--text);">{{ number_format($ringkas['total'], 0, ',', '.') }}</strong> evaluasi,
            <strong style="color:var(--danger);">{{ number_format($ringkas['perhatian'], 0, ',', '.') }}</strong> perlu perhatian
        </span>
        <span class="spacer"></span>
        <a href="{{ route('pga.training.qr') }}" class="btn btn-light"><span class="material-symbols-outlined">qr_code_2</span> Cetak QR</a>
    </div>

    <div class="panel" style="padding:8px 14px;">
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Bagian</th>
                        <th>Materi</th>
                        <th>Penerapan</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $i)
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ $i->tanggal_pengisian->format('d/m/Y') }}
                                <div class="muted" style="font-size:.74rem;">dikirim {{ $i->created_at->timezone('Asia/Jakarta')->format('H:i') }}</div>
                            </td>
                            <td>
                                <a class="lnk" href="{{ route('pga.training.show', $i) }}">{{ $i->nama }}</a>
                                <div class="muted" style="font-size:.74rem;">{{ $i->nik }}</div>
                            </td>
                            <td>{{ $i->bagian_label }}</td>
                            <td>{{ count($i->materi_diikuti ?? []) }} materi</td>
                            <td>
                                @if ($i->rata_nilai !== null)
                                    @include('pga.training._label', ['teks' => \App\Models\TrainingEvaluation::labelRataRata((float) $i->rata_nilai)])
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($i->has_findings)
                                    <span class="badge bad">Perlu Perhatian</span>
                                @else
                                    <span class="badge ok">OK</span>
                                @endif
                                @if ($i->diedit_pada)
                                    <span class="badge edited" title="Diedit {{ $i->diedit_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} oleh {{ $i->diedit_oleh }}">Diedit</span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="{{ route('pga.training.show', $i) }}" class="btn btn-light btn-sm">Detail</a>
                                <a href="{{ route('pga.training.edit', $i) }}" class="btn btn-light btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada data evaluasi yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($items->hasPages())
        <div class="pager">
            @if ($items->onFirstPage())
                <span class="btn btn-light btn-sm disabled">‹ Sebelumnya</span>
            @else
                <a class="btn btn-light btn-sm" href="{{ $items->previousPageUrl() }}">‹ Sebelumnya</a>
            @endif

            <span>Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}</span>

            @if ($items->hasMorePages())
                <a class="btn btn-light btn-sm" href="{{ $items->nextPageUrl() }}">Berikutnya ›</a>
            @else
                <span class="btn btn-light btn-sm disabled">Berikutnya ›</span>
            @endif
        </div>
    @endif
@endsection
