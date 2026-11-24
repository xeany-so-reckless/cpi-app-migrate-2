{{-- Badge teks jawaban. Pakai: @include('pga.training._label', ['teks' => 'Sering']) --}}
@php
    $kelasLabel = ['Selalu' => 'sl', 'Sering' => 'sr', 'Kadang-Kadang' => 'kd', 'Tidak Pernah' => 'tp'][$teks] ?? 'na';
@endphp
<span class="lbl-badge {{ $kelasLabel }}">{{ $teks }}</span>
