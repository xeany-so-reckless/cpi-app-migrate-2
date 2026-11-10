@extends('pga.layouts.app')

@section('title', 'APAR')
@section('page_title', 'APAR')
@section('page_sub', 'Hasil inspeksi Alat Pemadam Api Ringan')

@push('styles')
    @include('pga.partials.ui')
@endpush

@section('content')
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="panel" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('pga.apar.index') }}" class="filters">
            <div class="fgroup">
                <label for="dari">Dari tanggal</label>
                <input type="date" id="dari" name="dari" class="in" value="{{ $filter['dari'] ?? '' }}">
            </div>
            <div class="fgroup">
                <label for="sampai">Sampai tanggal</label>
                <input type="date" id="sampai" name="sampai" class="in" value="{{ $filter['sampai'] ?? '' }}">
            </div>
            <div class="fgroup">
                <label for="jenis">Jenis APAR</label>
                <select id="jenis" name="jenis" class="in">
                    <option value="">Semua</option>
                    @foreach ($jenisList as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['jenis'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fgroup">
                <label for="status">Status</label>
                <select id="status" name="status" class="in">
                    <option value="">Semua</option>
                    <option value="ok" @selected(($filter['status'] ?? '') === 'ok')>OK</option>
                    <option value="temuan" @selected(($filter['status'] ?? '') === 'temuan')>Ada Temuan</option>
                </select>
            </div>
            <div class="fgroup">
                <label for="q">Cari</label>
                <input type="text" id="q" name="q" class="in" placeholder="Nomor / lokasi / inspektor" value="{{ $filter['q'] ?? '' }}">
            </div>
            <div class="fgroup" style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('pga.apar.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>

    <div class="toolbar">
        <span class="muted" style="font-size:.84rem;">
            <strong style="color:var(--text);">{{ number_format($ringkas['total'], 0, ',', '.') }}</strong> inspeksi,
            <strong style="color:var(--danger);">{{ number_format($ringkas['temuan'], 0, ',', '.') }}</strong> ada temuan
        </span>
        <span class="spacer"></span>
        <a href="{{ route('pga.apar.qr') }}" class="btn btn-light"><span class="material-symbols-outlined">qr_code_2</span> Cetak QR</a>
        {{-- Slot tombol Export Excel (nanti) --}}
        {{-- Slot tombol Cetak QR & Export Excel (langkah berikutnya) --}}
    </div>

    <div class="panel" style="padding:8px 14px;">
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor APAR</th>
                        <th>Jenis</th>
                        <th>Lokasi</th>
                        <th>Inspektor</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $i)
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ $i->tanggal_inspeksi->format('d/m/Y') }}
                                <div class="muted" style="font-size:.74rem;">{{ $i->jam_tampil }}</div>
                            </td>
                            <td><a class="lnk" href="{{ route('pga.apar.show', $i) }}">{{ $i->nomor_apar }}</a></td>
                            <td>{{ \App\Models\AparInspection::JENIS[$i->jenis_apar] ?? $i->jenis_apar }}</td>
                            <td>{{ $i->lokasi_apar }}</td>
                            <td>
                                {{ $i->nama_inspektor }}
                                <div class="muted" style="font-size:.74rem;">{{ $i->jabatan }}</div>
                            </td>
                            <td>
                                @if ($i->has_findings)
                                    <span class="badge bad">Ada Temuan</span>
                                @else
                                    <span class="badge ok">OK</span>
                                @endif
                                @if ($i->diedit_pada)
                                    <span class="badge edited" title="Diedit {{ $i->diedit_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} oleh {{ $i->diedit_oleh }}">Diedit</span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="{{ route('pga.apar.show', $i) }}" class="btn btn-light btn-sm">Detail</a>
                                <a href="{{ route('pga.apar.edit', $i) }}" class="btn btn-light btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada data inspeksi yang cocok.</td></tr>
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