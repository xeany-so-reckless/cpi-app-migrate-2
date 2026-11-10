<?php

namespace App\Http\Controllers\Pga;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Support\ActivityLogger;
use App\Support\AparFoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Menu APAR di sisi PGA: daftar, detail, edit, hapus hasil inspeksi.
 */
class PgaAparController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'jenis'  => ['nullable', Rule::in(array_keys(AparInspection::JENIS))],
            'status' => ['nullable', Rule::in(['ok', 'temuan'])],
            'q'      => ['nullable', 'string', 'max:100'],
        ]);

        $query = AparInspection::query();

        if (! empty($f['dari'])) {
            $query->whereDate('tanggal_inspeksi', '>=', $f['dari']);
        }
        if (! empty($f['sampai'])) {
            $query->whereDate('tanggal_inspeksi', '<=', $f['sampai']);
        }
        if (! empty($f['jenis'])) {
            $query->where('jenis_apar', $f['jenis']);
        }
        if (($f['status'] ?? null) === 'temuan') {
            $query->where('has_findings', true);
        } elseif (($f['status'] ?? null) === 'ok') {
            $query->where('has_findings', false);
        }
        if (! empty($f['q'])) {
            $cari = '%' . $f['q'] . '%';
            $query->where(function ($w) use ($cari) {
                $w->where('nomor_apar', 'like', $cari)
                  ->orWhere('lokasi_apar', 'like', $cari)
                  ->orWhere('nama_inspektor', 'like', $cari);
            });
        }

        $ringkas = [
            'total'  => (clone $query)->count(),
            'temuan' => (clone $query)->where('has_findings', true)->count(),
        ];

        $items = $query
            ->orderByDesc('tanggal_inspeksi')
            ->orderByDesc('jam_inspeksi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pga.apar.index', [
            'items'     => $items,
            'ringkas'   => $ringkas,
            'jenisList' => AparInspection::JENIS,
            'filter'    => $f,
        ]);
    }

    public function show(AparInspection $inspeksi): View
    {
        return view('pga.apar.show', ['i' => $inspeksi]);
    }

    public function edit(AparInspection $inspeksi): View
    {
        return view('pga.apar.edit', [
            'i'         => $inspeksi,
            'jenisList' => AparInspection::JENIS,
            'hariIni'   => now('Asia/Jakarta')->format('Y-m-d'),
        ]);
    }

    public function update(Request $request, AparInspection $inspeksi): RedirectResponse
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
            'pressure_gauge'   => [
                Rule::requiredIf(fn () => $request->input('jenis_apar') === 'dry_powder'),
                'nullable',
                $baikRusak,
            ],
            'handle'           => ['required', $baikRusak],
            'kondisi_fisik'    => ['required', $baikRusak],
            'roda'             => ['required', Rule::in(['baik', 'rusak', 'tidak_ada'])],

            'keterangan'       => ['nullable', 'string', 'max:1000'],
            // Foto opsional di sini: kosong = tetap pakai foto lama
            'foto'             => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], $this->messages(), $this->attributes());

        if ($data['jenis_apar'] !== 'dry_powder') {
            $data['pressure_gauge'] = null;
        }

        // Samakan format dengan kolom TIME (HH:MM:SS) supaya deteksi perubahan akurat
        $data['jam_inspeksi'] .= ':00';

        $fotoBaru = null;
        if ($request->hasFile('foto')) {
            try {
                $fotoBaru = AparFoto::simpan($request->file('foto'));
            } catch (Throwable $e) {
                report($e);

                return back()
                    ->withInput()
                    ->withErrors(['foto' => 'Foto gagal diproses. Silakan pilih ulang foto.']);
            }
        }

        $payload = collect($data)->except('foto')->all();
        $payload['has_findings'] = AparInspection::hitungTemuan($payload);

        $fotoLama = $inspeksi->foto_path;
        if ($fotoBaru) {
            $payload['foto_path'] = $fotoBaru;
        }

        $inspeksi->fill($payload);
        $diubah = array_keys($inspeksi->getDirty());

        if (empty($diubah)) {
            return redirect()
                ->route('pga.apar.show', $inspeksi)
                ->with('status', 'Tidak ada perubahan yang disimpan.');
        }

        $user = $request->user('tally');
        $inspeksi->diedit_oleh = "{$user->name} ({$user->employee_code})";
        $inspeksi->diedit_pada = now();
        $inspeksi->save();

        if ($fotoBaru) {
            AparFoto::hapus($fotoLama);
        }

        ActivityLogger::log(
            'pga',
            'apar_edit',
            "{$user->employee_code} ({$user->name}) mengedit inspeksi APAR #{$inspeksi->id} ({$inspeksi->nomor_apar}); field: " . implode(', ', $diubah),
            $user
        );

        return redirect()
            ->route('pga.apar.show', $inspeksi)
            ->with('status', 'Perubahan hasil inspeksi berhasil disimpan.');
    }

    public function destroy(Request $request, AparInspection $inspeksi): RedirectResponse
    {
        $user  = $request->user('tally');
        $id    = $inspeksi->id;
        $nomor = $inspeksi->nomor_apar;
        $foto  = $inspeksi->foto_path;

        $inspeksi->delete();
        AparFoto::hapus($foto);

        ActivityLogger::log(
            'pga',
            'apar_hapus',
            "{$user->employee_code} ({$user->name}) menghapus inspeksi APAR #{$id} ({$nomor})",
            $user
        );

        return redirect()
            ->route('pga.apar.index')
            ->with('status', "Inspeksi APAR {$nomor} berhasil dihapus.");
    }

    private function messages(): array
    {
        return [
            'required'         => ':attribute wajib diisi.',
            'in'               => ':attribute tidak valid.',
            'max.string'       => ':attribute terlalu panjang (maksimal :max karakter).',
            'before_or_equal'  => 'Tanggal tidak boleh melebihi hari ini.',
            'date_format'      => 'Format jam tidak valid.',
            'foto.uploaded'    => 'Foto gagal diunggah (ukuran terlalu besar). Pilih foto yang lebih kecil.',
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
