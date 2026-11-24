@extends('pga.layouts.app')

@section('title', 'Detail Evaluasi Training')
@section('page_title', 'Detail Evaluasi Training')
@section('page_sub', $e->nama . ' · ' . $e->tanggal_pengisian->format('d/m/Y'))

@push('styles')
    @include('pga.partials.ui')
    @include('pga.training._ui')
@endpush

@section('content')
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="toolbar">
        <a href="{{ route('pga.training.index') }}" class="btn btn-light"><span class="material-symbols-outlined">arrow_back</span> Kembali</a>
        <span class="spacer"></span>
        <a href="{{ route('pga.training.edit', $e) }}" class="btn btn-light"><span class="material-symbols-outlined">edit</span> Edit</a>
        <form method="POST" action="{{ route('pga.training.destroy', $e) }}" style="display:inline;"
              data-nama="{{ $e->nama }}"
              onsubmit="return confirm('Hapus evaluasi training milik ' + this.dataset.nama + '? Data tidak bisa dikembalikan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-light btn-hapus"><span class="material-symbols-outlined">delete</span> Hapus</button>
        </form>
    </div>

    {{-- ============ IDENTITAS ============ --}}
    <div class="panel" style="margin-bottom:16px;">
        <div class="det-grid">
            <div><div class="det-k">Nama</div><div class="det-v">{{ $e->nama }}</div></div>
            <div><div class="det-k">Nomor Induk Karyawan</div><div class="det-v">{{ $e->nik }}</div></div>
            <div><div class="det-k">Bagian</div><div class="det-v">{{ $e->bagian_label }}</div></div>
            <div><div class="det-k">Tanggal pengisian</div><div class="det-v">{{ $e->tanggal_pengisian->format('d/m/Y') }}</div></div>
            <div><div class="det-k">Dikirim pada</div><div class="det-v">{{ $e->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</div></div>
            <div>
                <div class="det-k">Status</div>
                <div class="det-v">
                    @if ($e->has_findings)
                        <span class="badge bad">Perlu Perhatian</span>
                    @else
                        <span class="badge ok">OK</span>
                    @endif
                </div>
            </div>
            <div><div class="det-k">Materi diisi</div><div class="det-v">{{ count($e->materi_diikuti ?? []) }} materi</div></div>
            <div><div class="det-k">IP pengirim</div><div class="det-v">{{ $e->ip_address ?? '-' }}</div></div>
            @if ($e->diedit_pada)
                <div>
                    <div class="det-k">Terakhir diedit</div>
                    <div class="det-v">{{ $e->diedit_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}<br><span class="muted" style="font-weight:500;">oleh {{ $e->diedit_oleh }}</span></div>
                </div>
            @endif
        </div>
    </div>

    {{-- ============ JAWABAN PER MATERI ============ --}}
    @foreach ($materi as $kode => $m)
        @continue(! in_array($kode, $e->materi_diikuti ?? [], true))

        @php
            $dj      = $jawaban->get($kode, collect());
            $r       = $rata[$kode] ?? null;
            $lemah   = $dj->where('nilai', '<=', \App\Models\TrainingEvaluation::BATAS_PERHATIAN)->count();
        @endphp

        <div class="panel mat">
            <div class="mat-head">
                <h2><span class="kode">{{ $m['kode'] }}</span> {{ $m['judul'] }}</h2>
                @if ($r !== null)
                    @include('pga.training._label', ['teks' => \App\Models\TrainingEvaluation::labelRataRata((float) $r)])
                @endif
                <span class="spacer"></span>
                @if ($lemah > 0)
                    <span class="mat-warn">{{ $lemah }} jawaban perlu perhatian</span>
                @endif
            </div>

            @foreach ($m['pertanyaan'] as $no => $teks)
                @php $a = $dj->get($no); @endphp
                <div class="qrow {{ $a && $a->nilai <= \App\Models\TrainingEvaluation::BATAS_PERHATIAN ? 'warn' : '' }}">
                    <span class="qno">{{ $loop->iteration }}</span>
                    <span>{{ $teks }}</span>
                    <span class="qans">
                        @if ($a)
                            @include('pga.training._label', ['teks' => $a->label])
                        @else
                            <span class="muted">-</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection
