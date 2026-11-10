@extends('pga.layouts.app')

@section('title', 'Edit Inspeksi')
@section('page_title', 'Edit Hasil Inspeksi APAR')
@section('page_sub', $i->nomor_apar . ' · dikirim ' . $i->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'))

@push('styles')
    @include('pga.partials.ui')
    <style>
        .form-wrap { max-width: 760px; }
        .fsec { margin-bottom: 16px; }
        .fsec h3 { font-size: .76rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: var(--primary); margin-bottom: 14px; }
        .field { margin-bottom: 14px; }
        .field[hidden] { display: none; }
        .field > .lbl { display: block; font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .req { color: var(--danger); }
        .hint { font-size: .72rem; color: var(--muted); margin-top: 4px; }
        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .seg { display: flex; gap: 8px; max-width: 420px; }
        .seg label { flex: 1; position: relative; cursor: pointer; }
        .seg input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; cursor: pointer; }
        .pill { display: flex; align-items: center; justify-content: center; height: 42px; border-radius: 10px; border: 2px solid var(--line); background: #f8fafc; color: #475569; font-size: .86rem; font-weight: 600; transition: all .15s ease; }
        .seg input:checked + .pill.ok  { background: #dcfce7; border-color: #16a34a; color: #166534; }
        .seg input:checked + .pill.bad { background: #fee2e2; border-color: #dc2626; color: #991b1b; }
        .seg input:checked + .pill.na  { background: #e2e8f0; border-color: #94a3b8; color: #0f172a; }
        .seg input:focus-visible + .pill { outline: 3px solid #a5b4fc; outline-offset: 1px; }

        .foto-now img { max-width: 260px; width: 100%; border-radius: 10px; border: 1px solid var(--line); display: block; }
        #fotoPreview { display: none; max-width: 260px; width: 100%; border-radius: 10px; border: 1px solid var(--line); margin-top: 10px; }
        .form-actions { display: flex; gap: 10px; margin-top: 6px; }
        @media (max-width: 600px) { .row2 { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    @if ($errors->any())
        <div class="flash flash-err">
            <strong>Perubahan belum bisa disimpan.</strong> Periksa isian yang bertanda merah.
            @if ($errors->has('foto')) Foto perlu dipilih ulang. @endif
        </div>
    @endif

    <form method="POST" action="{{ route('pga.apar.update', $i) }}" enctype="multipart/form-data" class="form-wrap" id="editForm">
        @csrf
        @method('PUT')

        {{-- ===== Data inspeksi ===== --}}
        <div class="panel fsec">
            <h3>Data Inspeksi</h3>

            <div class="field">
                <label class="lbl" for="nama_inspektor">Nama inspektor <span class="req">*</span></label>
                <input type="text" id="nama_inspektor" name="nama_inspektor" class="in" maxlength="100" value="{{ old('nama_inspektor', $i->nama_inspektor) }}" required>
                @error('nama_inspektor') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="jabatan">Jabatan <span class="req">*</span></label>
                <input type="text" id="jabatan" name="jabatan" class="in" maxlength="100" value="{{ old('jabatan', $i->jabatan) }}" required>
                @error('jabatan') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <div class="row2">
                    <div>
                        <label class="lbl" for="tanggal_inspeksi">Tanggal <span class="req">*</span></label>
                        <input type="date" id="tanggal_inspeksi" name="tanggal_inspeksi" class="in" max="{{ $hariIni }}" value="{{ old('tanggal_inspeksi', $i->tanggal_inspeksi->format('Y-m-d')) }}" required>
                        @error('tanggal_inspeksi') <div class="err">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="lbl" for="jam_inspeksi">Jam <span class="req">*</span></label>
                        <input type="time" id="jam_inspeksi" name="jam_inspeksi" class="in" value="{{ old('jam_inspeksi', $i->jam_tampil) }}" required>
                        @error('jam_inspeksi') <div class="err">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="lbl" for="nomor_apar">Nomor APAR <span class="req">*</span></label>
                <input type="text" id="nomor_apar" name="nomor_apar" class="in" maxlength="50" value="{{ old('nomor_apar', $i->nomor_apar) }}" required>
                @error('nomor_apar') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="jenis_apar">Jenis APAR <span class="req">*</span></label>
                <select id="jenis_apar" name="jenis_apar" class="in" required>
                    @foreach ($jenisList as $kode => $label)
                        <option value="{{ $kode }}" @selected(old('jenis_apar', $i->jenis_apar) === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis_apar') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label class="lbl" for="lokasi_apar">Lokasi APAR <span class="req">*</span></label>
                <input type="text" id="lokasi_apar" name="lokasi_apar" class="in" maxlength="150" value="{{ old('lokasi_apar', $i->lokasi_apar) }}" required>
                @error('lokasi_apar') <div class="err">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- ===== Kondisi komponen ===== --}}
        <div class="panel fsec">
            <h3>Kondisi Komponen</h3>

            @foreach (\App\Models\AparInspection::KOMPONEN as $field => $label)
                @php
                    $isGauge = $field === 'pressure_gauge';
                    $isRoda  = $field === 'roda';
                    $nilai   = old($field, $i->{$field});
                @endphp

                <div class="field" @if ($isGauge) id="gaugeField" hidden @endif>
                    <span class="lbl">{{ $label }} <span class="req">*</span></span>
                    <div class="seg">
                        <label>
                            <input type="radio" name="{{ $field }}" value="baik" @checked($nilai === 'baik') required>
                            <span class="pill ok">Baik</span>
                        </label>
                        <label>
                            <input type="radio" name="{{ $field }}" value="rusak" @checked($nilai === 'rusak')>
                            <span class="pill bad">Rusak</span>
                        </label>
                        @if ($isRoda)
                            <label>
                                <input type="radio" name="{{ $field }}" value="tidak_ada" @checked($nilai === 'tidak_ada' || $nilai === null)>
                                <span class="pill na">Tidak Ada</span>
                            </label>
                        @endif
                    </div>
                    @if ($isGauge)
                        <div class="hint">Hanya berlaku untuk jenis Dry Chemical Powder.</div>
                    @elseif ($isRoda)
                        <div class="hint">Khusus APAB. Pilih "Tidak Ada" bila bukan APAB.</div>
                    @endif
                    @error($field) <div class="err">{{ $message }}</div> @enderror
                </div>
            @endforeach
        </div>

        {{-- ===== Keterangan & foto ===== --}}
        <div class="panel fsec">
            <h3>Keterangan &amp; Foto</h3>

            <div class="field">
                <label class="lbl" for="keterangan">Keterangan kondisi lainnya</label>
                <textarea id="keterangan" name="keterangan" class="in" maxlength="1000">{{ old('keterangan', $i->keterangan) }}</textarea>
                @error('keterangan') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <span class="lbl">Foto saat ini</span>
                <div class="foto-now">
                    <a href="{{ $i->foto_url }}" target="_blank" rel="noopener"><img src="{{ $i->foto_url }}" alt="Foto saat ini"></a>
                </div>
            </div>

            <div class="field">
                <label class="lbl" for="foto">Ganti foto (opsional)</label>
                <input type="file" id="foto" name="foto" class="in" accept="image/*" style="height:auto; padding:8px;">
                <div class="hint">Kosongkan bila foto tidak diganti. Format JPG, PNG, atau WEBP, maksimal 10 MB.</div>
                <img id="fotoPreview" alt="Pratinjau foto baru">
                @error('foto') <div class="err">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="btnSimpan"><span class="material-symbols-outlined">save</span> Simpan Perubahan</button>
            <a href="{{ route('pga.apar.show', $i) }}" class="btn btn-light">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    // Pressure gauge: hanya untuk Dry Chemical Powder
    const jenis = document.getElementById('jenis_apar');
    const gaugeField = document.getElementById('gaugeField');
    const gaugeInputs = gaugeField.querySelectorAll('input');

    function toggleGauge() {
        const show = jenis.value === 'dry_powder';
        gaugeField.hidden = !show;
        gaugeInputs.forEach(i => { i.disabled = !show; i.required = show; });
    }
    jenis.addEventListener('change', toggleGauge);
    toggleGauge();

    // Pratinjau foto baru
    const foto = document.getElementById('foto');
    const preview = document.getElementById('fotoPreview');
    foto.addEventListener('change', function () {
        const f = foto.files[0];
        if (!f) { preview.style.display = 'none'; return; }
        preview.src = URL.createObjectURL(f);
        preview.style.display = 'block';
    });

    // Cegah kirim ganda
    document.getElementById('editForm').addEventListener('submit', function (e) {
        if (!e.target.checkValidity()) return;
        const b = document.getElementById('btnSimpan');
        b.disabled = true;
    });
})();
</script>
@endpush
