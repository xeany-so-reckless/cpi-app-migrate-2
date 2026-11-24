<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\TrainingEvaluation;
use App\Models\TrainingEvaluationAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Form Evaluasi Penerapan Training - PUBLIK (tanpa login), tujuan QR code.
 * URL sengaja pendek & stabil: /training
 */
class EvaluasiController extends Controller
{
    public function form(Request $request): View
    {
        $bagian = (string) $request->query('bagian', '');

        return view('training.form', [
            'materi'         => TrainingEvaluation::materi(),
            'wajib'          => TrainingEvaluation::materiWajib(),
            'bagianList'     => TrainingEvaluation::BAGIAN,
            'skala'          => TrainingEvaluation::SKALA,
            'defaultTanggal' => now('Asia/Jakarta')->format('Y-m-d'),
            // Dipakai tombol "Isi Evaluasi Lainnya" supaya data diri tidak diketik ulang
            'prefillNama'    => Str::limit((string) $request->query('nama', ''), 100, ''),
            'prefillNik'     => Str::limit((string) $request->query('nik', ''), 30, ''),
            'prefillBagian'  => array_key_exists($bagian, TrainingEvaluation::BAGIAN) ? $bagian : '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $semuaMateri = TrainingEvaluation::materi();
        $hariIni     = now('Asia/Jakarta')->format('Y-m-d');

        // Materi wajib selalu ikut, walau checkbox-nya dimanipulasi dari browser.
        $diminta = collect((array) $request->input('materi', []))
            ->filter(fn ($k) => is_string($k) && isset($semuaMateri[$k]))
            ->merge(TrainingEvaluation::materiWajib())
            ->all();

        // Urutan mengikuti config, bukan urutan kiriman.
        $dipilih = array_values(array_filter(
            array_keys($semuaMateri),
            fn ($k) => in_array($k, $diminta, true)
        ));

        $rules = [
            'nama'              => ['required', 'string', 'max:100'],
            'nik'               => ['required', 'string', 'max:30'],
            'tanggal_pengisian' => ['required', 'date', 'before_or_equal:' . $hariIni],
            'bagian'            => ['required', Rule::in(array_keys(TrainingEvaluation::BAGIAN))],
        ];

        // Pertanyaan hanya diwajibkan untuk materi yang dipilih.
        $nilaiValid = array_keys(TrainingEvaluation::SKALA);
        foreach ($dipilih as $kode) {
            foreach (array_keys($semuaMateri[$kode]['pertanyaan']) as $nomor) {
                $rules["jawaban.{$kode}.{$nomor}"] = ['required', Rule::in($nilaiValid)];
            }
        }

        $data = $request->validate($rules, $this->messages(), $this->attributes());

        $jawaban = [];
        foreach ($dipilih as $kode) {
            foreach ($data['jawaban'][$kode] as $nomor => $nilai) {
                $jawaban[$kode][(int) $nomor] = (int) $nilai;
            }
        }

        try {
            $evaluasi = DB::transaction(function () use ($data, $dipilih, $jawaban, $request) {
                $e = TrainingEvaluation::create([
                    'nama'              => $data['nama'],
                    'nik'               => $data['nik'],
                    'tanggal_pengisian' => $data['tanggal_pengisian'],
                    'bagian'            => $data['bagian'],
                    'materi_diikuti'    => $dipilih,
                    'has_findings'      => TrainingEvaluation::hitungPerhatian($jawaban),
                    'ip_address'        => $request->ip(),
                ]);

                $baris = [];
                foreach ($jawaban as $kode => $perMateri) {
                    foreach ($perMateri as $nomor => $nilai) {
                        $baris[] = [
                            'training_evaluation_id' => $e->id,
                            'materi'                 => $kode,
                            'nomor'                  => $nomor,
                            'nilai'                  => $nilai,
                        ];
                    }
                }
                TrainingEvaluationAnswer::insert($baris);

                return $e;
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['umum' => 'Data gagal disimpan. Silakan coba kirim ulang.']);
        }

        return redirect()->route('training.sukses')->with('evaluasi', [
            'nama'   => $evaluasi->nama,
            'nik'    => $evaluasi->nik,
            'bagian' => $evaluasi->bagian,
            'materi' => array_map(fn ($k) => $semuaMateri[$k]['judul'], $dipilih),
        ]);
    }

    public function sukses(): View|RedirectResponse
    {
        $info = session('evaluasi');

        // Dibuka langsung / di-refresh tanpa habis kirim -> kembali ke form
        if (! $info) {
            return redirect()->route('training.form');
        }

        return view('training.sukses', [
            'info'       => $info,
            'bagianList' => TrainingEvaluation::BAGIAN,
        ]);
    }

    private function messages(): array
    {
        return [
            'required'                => ':attribute wajib diisi.',
            'in'                      => ':attribute tidak valid.',
            'max.string'              => ':attribute terlalu panjang (maksimal :max karakter).',
            'before_or_equal'         => 'Tanggal tidak boleh melebihi hari ini.',
            'jawaban.*.*.required'    => 'Pilih salah satu jawaban.',
            'jawaban.*.*.in'          => 'Jawaban tidak valid.',
        ];
    }

    private function attributes(): array
    {
        return [
            'nama'              => 'Nama',
            'nik'               => 'Nomor Induk Karyawan',
            'tanggal_pengisian' => 'Tanggal pengisian',
            'bagian'            => 'Bagian',
        ];
    }
}
