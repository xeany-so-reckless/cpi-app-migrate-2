<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Support\AparFoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Form inspeksi APAR - PUBLIK (tanpa login), tujuan QR code.
 * URL sengaja pendek & stabil: /apar
 */
class InspeksiController extends Controller
{
    public function form(Request $request): View
    {
        $now = now('Asia/Jakarta');

        return view('apar.form', [
            'jenisList'      => AparInspection::JENIS,
            'defaultTanggal' => $now->format('Y-m-d'),
            'defaultJam'     => $now->format('H:i'),
            // Dipakai tombol "Inspeksi APAR Lainnya" supaya nama & jabatan tidak diketik ulang
            'prefillNama'    => Str::limit((string) $request->query('nama', ''), 100, ''),
            'prefillJabatan' => Str::limit((string) $request->query('jabatan', ''), 100, ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $baikRusak = Rule::in(['baik', 'rusak']);
        $hariIni   = now('Asia/Jakarta')->format('Y-m-d');

        $data = $request->validate([
            'nama_inspektor'   => ['required', 'string', 'max:100'],
            'jabatan'          => ['required', 'string', 'max:100'],
            'tanggal_inspeksi' => ['required', 'date', 'before_or_equal:' . $hariIni],
            'jam_inspeksi'     => ['required', 'date_format:H:i'],
            'nomor_apar'       => ['required', 'string', 'max:50'],
            'jenis_apar'       => ['required', Rule::in(array_keys(AparInspection::JENIS))],
            'lokasi_apar'      => ['required', 'string', 'max:150'],

            'pin_segel'        => ['required', $baikRusak],
            'selang'           => ['required', $baikRusak],
            'klem_selang'      => ['required', $baikRusak],
            'nozzle'           => ['required', $baikRusak],
            // Pressure gauge hanya wajib untuk Dry Chemical Powder
            'pressure_gauge'   => [
                Rule::requiredIf(fn () => $request->input('jenis_apar') === 'dry_powder'),
                'nullable',
                $baikRusak,
            ],
            'handle'           => ['required', $baikRusak],
            'kondisi_fisik'    => ['required', $baikRusak],
            'roda'             => ['required', Rule::in(['baik', 'rusak', 'tidak_ada'])],

            'keterangan'       => ['nullable', 'string', 'max:1000'],
            'foto'             => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], $this->messages(), $this->attributes());

        // Pressure gauge tidak berlaku selain Dry Chemical Powder -> simpan kosong (bukan "Baik")
        if ($data['jenis_apar'] !== 'dry_powder') {
            $data['pressure_gauge'] = null;
        }

        try {
            $fotoPath = AparFoto::simpan($request->file('foto'));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['foto' => 'Foto gagal diproses. Silakan pilih ulang foto lalu kirim kembali.']);
        }

        $payload = collect($data)->except('foto')->all();

        $inspeksi = AparInspection::create($payload + [
            'foto_path'    => $fotoPath,
            'has_findings' => AparInspection::hitungTemuan($payload),
            'ip_address'   => $request->ip(),
        ]);

        return redirect()->route('apar.sukses')->with('inspeksi', [
            'nomor_apar'   => $inspeksi->nomor_apar,
            'lokasi'       => $inspeksi->lokasi_apar,
            'jenis'        => $inspeksi->jenis_label,
            'has_findings' => $inspeksi->has_findings,
            'nama'         => $inspeksi->nama_inspektor,
            'jabatan'      => $inspeksi->jabatan,
        ]);
    }

    public function sukses(): View|RedirectResponse
    {
        $info = session('inspeksi');

        // Dibuka langsung / di-refresh tanpa habis kirim -> kembali ke form
        if (! $info) {
            return redirect()->route('apar.form');
        }

        return view('apar.sukses', ['info' => $info]);
    }

    private function messages(): array
    {
        return [
            'required'         => ':attribute wajib diisi.',
            'in'               => ':attribute tidak valid.',
            'max.string'       => ':attribute terlalu panjang (maksimal :max karakter).',
            'before_or_equal'  => 'Tanggal tidak boleh melebihi hari ini.',
            'date_format'      => 'Format jam tidak valid.',
            'foto.uploaded'    => 'Foto gagal diunggah (ukuran terlalu besar atau koneksi terputus). Pilih ulang foto.',
            'foto.image'       => 'File harus berupa gambar.',
            'foto.mimes'       => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max'         => 'Ukuran foto maksimal 10 MB.',
        ];
    }

    private function attributes(): array
    {
        return [
            'nama_inspektor'   => 'Nama inspektor',
            'jabatan'          => 'Jabatan',
            'tanggal_inspeksi' => 'Tanggal',
            'jam_inspeksi'     => 'Jam inspeksi',
            'nomor_apar'       => 'Nomor APAR',
            'jenis_apar'       => 'Jenis APAR',
            'lokasi_apar'      => 'Lokasi APAR',
            'keterangan'       => 'Keterangan',
            'foto'             => 'Foto dokumentasi',
        ] + AparInspection::KOMPONEN;
    }
}