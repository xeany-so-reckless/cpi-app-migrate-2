@extends('pga.layouts.app')

@section('title', 'Edit Evaluasi Training')
@section('page_title', 'Edit Evaluasi Training')
@section('page_sub', $e->nama . ' · ' . $e->nik)

@push('styles')
    @include('pga.partials.ui')
    @include('pga.training._ui')
    <style>
        .ed-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .ed-grid .fgroup label { display: block; font-size: .7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px; }
        .ferr { color: var(--danger); font-size: .78rem; margin-top: 4px; font-weight: 500; }

        .chk-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 8px; margin-top: 10px; }
        .chk { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; background: #fff; cursor: pointer; font-size: .86rem; font-weight: 500; }
        .chk input { width: 18px; height: 18px; accent-color: var(--primary); flex-shrink: 0; cursor: pointer; }
        .chk:has(input:checked) { border-color: var(--primary); background: var(--primary-soft); }
        .chk.locked, .chk.locked input { cursor: default; }
        .chk .txt { flex: 1; line-height: 1.35; }
        .chk .tag { font-size: .6rem; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; padding: 2px 8px; border-radius: 99px; background: var(--primary); color: #fff; }

        .mat[hidden] { display: none; }
        .eq { padding: 12px 0; border-top: 1px solid #eef0f7; }
        .eq .qt { display: flex; gap: 10px; font-size: .86rem; line-height: 1.5; margin-bottom: 8px; }
        .eopts { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        .eopt { position: relative; cursor: pointer; }
        .eopt input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; cursor: pointer; }
        .epill { display: flex; align-items: center; justify-content: center; text-align: center; min-height: 40px; padding: 4px 6px; border-radius: 10px; border: 2px solid var(--line); background: #f8fafc; color: #475569; font-size: .8rem; font-weight: 600; }
        .eopt input:checked + .epill.o4 { background: #dcfce7; border-color: #16a34a; color: #166534; }
        .eopt input:checked + .epill.o3 { background: #dbeafe; border-color: #2563eb; color: #1e40af; }
        .eopt input:checked + .epill.o2 { background: #fef3c7; border-color: #d97706; color: #92400e; }
        .eopt input:checked + .epill.o1 { background: #fee2e2; border-color: #dc2626; color: #991b1b; }
        .eopt input:focus-visible + .epill { outline: 3px solid #93c5fd; outline-offset: 1px; }
        @media (max-width: 700px) { .eopts { grid-template-columns: 1fr 1fr; } }
    </style>
@endpush

@section('content')
    @php
        // Awal: materi yang tersimpan. Setelah gagal validasi: materi yang dicentang tadi.
        $terpilih = array_values(array_unique(array_merge(
            $wajib,
            array_map('strval', (array) old('materi', $e->materi_diikuti ?? []))
        )));
        $belumDijawab = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'jawaban.'))->count();
    @endphp

    @if ($errors->any())
        <div class="flash flash-err">
            <strong>Perubahan belum bisa disimpan.</strong>
            @if ($belumDijawab > 0)
                Masih ada {{ $belumDijawab }} pertanyaan yang belum dijawab (bertanda merah).
            @else
                Periksa isian yang bertanda merah.
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('pga.training.update', $e) }}" id="editForm">
        @csrf
        @method('PUT')

        {{-- ============ DATA DIRI ============ --}}
        <div class="panel" style="margin-bottom:16px;">
            <h2>Data Diri</h2>
            <div class="ed-grid" style="margin-top:12px;">
                <div class="fgroup">
                    <label for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" class="in" maxlength="100" value="{{ old('nama', $e->nama) }}" required>
                    @error('nama') <div class="ferr">{{ $message }}</div> @enderror
                </div>
                <div class="fgroup">
                    <label for="nik">Nomor Induk Karyawan</label>
                    <input type="text" id="nik" name="nik" class="in" maxlength="30" value="{{ old('nik', $e->nik) }}" required>
                    @error('nik') <div class="ferr">{{ $message }}</div> @enderror
                </div>
                <div class="fgroup">
                    <label for="tanggal_pengisian">Tanggal pengisian</label>
                    <input type="date" id="tanggal_pengisian" name="tanggal_pengisian" class="in" max="{{ $hariIni }}" value="{{ old('tanggal_pengisian', $e->tanggal_pengisian->format('Y-m-d')) }}" required>
                    @error('tanggal_pengisian') <div class="ferr">{{ $message }}</div> @enderror
                </div>
                <div class="fgroup">
                    <label for="bagian">Bagian</label>
                    <select id="bagian" name="bagian" class="in" required>
                        @foreach ($bagianList as $kode => $label)
                            <option value="{{ $kode }}" @selected(old('bagian', $e->bagian) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('bagian') <div class="ferr">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- ============ MATERI ============ --}}
        <div class="panel" style="margin-bottom:16px;">
            <h2>Training yang Diikuti</h2>
            <p class="muted" style="font-size:.82rem; margin-top:4px;">
                Materi bertanda <strong>Wajib</strong> tidak bisa dilepas. Mencentang materi baru berarti semua pertanyaannya harus dijawab;
                melepas centang materi pilihan akan menghapus jawabannya.
            </p>
            <div class="chk-grid">
                @foreach ($materi as $kode => $m)
                    @php $isWajib = in_array($kode, $wajib, true); @endphp
                    <label class="chk {{ $isWajib ? 'locked' : '' }}">
                        <input type="checkbox" name="materi[]" value="{{ $kode }}"
                            @checked(in_array($kode, $terpilih, true))
                            @if ($isWajib) onclick="return false" tabindex="-1" aria-disabled="true" @endif>
                        <span class="txt">{{ $m['judul'] }}</span>
                        @if ($isWajib) <span class="tag">Wajib</span> @endif
                    </label>
                @endforeach
            </div>
        </div>

        {{-- ============ JAWABAN PER MATERI ============ --}}
        @foreach ($materi as $kode => $m)
            <div class="panel mat" data-materi="{{ $kode }}" @if (! in_array($kode, $terpilih, true)) hidden @endif>
                <div class="mat-head">
                    <h2><span class="kode">{{ $m['kode'] }}</span> {{ $m['judul'] }}</h2>
                </div>

                @foreach ($m['pertanyaan'] as $no => $teks)
                    <div class="eq">
                        <div class="qt"><span class="qno">{{ $loop->iteration }}</span><span>{{ $teks }}</span></div>

                        <div class="eopts">
                            @foreach ($skala as $nilai => $label)
                                <label class="eopt">
                                    <input type="radio" name="jawaban[{{ $kode }}][{{ $no }}]" value="{{ $nilai }}"
                                        @checked((string) old("jawaban.{$kode}.{$no}", $nilaiLama[$kode][$no] ?? '') === (string) $nilai)>
                                    <span class="epill o{{ $nilai }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error("jawaban.{$kode}.{$no}") <div class="ferr">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="toolbar">
            <a href="{{ route('pga.training.show', $e) }}" class="btn btn-light">Batal</a>
            <span class="spacer"></span>
            <button type="submit" class="btn btn-primary" id="btnSimpan"><span class="material-symbols-outlined">save</span> Simpan Perubahan</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('editForm');
    const btn  = document.getElementById('btnSimpan');

    // Pertanyaan hanya tampil, wajib, dan terkirim untuk materi yang dicentang.
    function sinkron() {
        document.querySelectorAll('.mat').forEach(function (sec) {
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

    form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        btn.disabled = true;
    });
})();
</script>
@endpush
