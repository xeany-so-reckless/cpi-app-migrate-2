<?php

namespace App\Http\Controllers\Pga;

use App\Http\Controllers\Controller;
use App\Models\TrainingEvaluation;
use App\Models\TrainingEvaluationAnswer;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Menu Evaluasi Training di sisi PGA: daftar, detail, edit, hapus.
 */
class PgaTrainingController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'bagian' => ['nullable', Rule::in(array_keys(TrainingEvaluation::BAGIAN))],
            'materi' => ['nullable', Rule::in(array_keys(TrainingEvaluation::materi()))],
            'status' => ['nullable', Rule::in(['ok', 'perhatian'])],
            'q'      => ['nullable', 'string', 'max:100'],
        ]);

        $query = TrainingEvaluation::query();

        if (! empty($f['dari'])) {
            $query->whereDate('tanggal_pengisian', '>=', $f['dari']);
        }
        if (! empty($f['sampai'])) {
            $query->whereDate('tanggal_pengisian', '<=', $f['sampai']);
        }
        if (! empty($f['bagian'])) {
            $query->where('bagian', $f['bagian']);
        }
        if (! empty($f['materi'])) {
            $query->whereJsonContains('materi_diikuti', $f['materi']);
        }
        if (($f['status'] ?? null) === 'perhatian') {
            $query->where('has_findings', true);
        } elseif (($f['status'] ?? null) === 'ok') {
            $query->where('has_findings', false);
        }
        if (! empty($f['q'])) {
            $cari = '%' . $f['q'] . '%';
            $query->where(function ($w) use ($cari) {
                $w->where('nama', 'like', $cari)
                  ->orWhere('nik', 'like', $cari);
            });
        }

        $ringkas = [
            'total'     => (clone $query)->count(),
            'perhatian' => (clone $query)->where('has_findings', true)->count(),
        ];

        $items = $query
            ->withAvg('answers as rata_nilai', 'nilai')
            ->orderByDesc('tanggal_pengisian')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('pga.training.index', [
            'items'      => $items,
            'ringkas'    => $ringkas,
            'bagianList' => TrainingEvaluation::BAGIAN,
            'materiList' => TrainingEvaluation::materi(),
            'filter'     => $f,
        ]);
    }

    public function show(TrainingEvaluation $evaluasi): View
    {
        $evaluasi->load('answers');

        return view('pga.training.show', [
            'e'       => $evaluasi,
            'materi'  => TrainingEvaluation::materi(),
            // [kode_materi => [nomor => jawaban]]
            'jawaban' => $evaluasi->answers->groupBy('materi')->map(fn ($g) => $g->keyBy('nomor')),
            'rata'    => $evaluasi->rataRataPerMateri(),
        ]);
    }

    public function edit(TrainingEvaluation $evaluasi): View
    {
        $evaluasi->load('answers');

        return view('pga.training.edit', [
            'e'          => $evaluasi,
            'materi'     => TrainingEvaluation::materi(),
            'wajib'      => TrainingEvaluation::materiWajib(),
            'bagianList' => TrainingEvaluation::BAGIAN,
            'skala'      => TrainingEvaluation::SKALA,
            'hariIni'    => now('Asia/Jakarta')->format('Y-m-d'),
            // [kode_materi => [nomor => nilai]] - nilai awal radio di form
            'nilaiLama'  => $this->petaJawaban($evaluasi),
        ]);
    }

    public function update(Request $request, TrainingEvaluation $evaluasi): RedirectResponse
    {
        $semuaMateri = TrainingEvaluation::materi();
        $hariIni     = now('Asia/Jakarta')->format('Y-m-d');

        // Materi wajib selalu ikut; urutan mengikuti config.
        $diminta = collect((array) $request->input('materi', []))
            ->filter(fn ($k) => is_string($k) && isset($semuaMateri[$k]))
            ->merge(TrainingEvaluation::materiWajib())
            ->all();

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

        // Pertanyaan hanya diwajibkan untuk materi yang dipilih (materi baru dicentang = wajib dijawab).
        $nilaiValid = array_keys(TrainingEvaluation::SKALA);
        foreach ($dipilih as $kode) {
            foreach (array_keys($semuaMateri[$kode]['pertanyaan']) as $nomor) {
                $rules["jawaban.{$kode}.{$nomor}"] = ['required', Rule::in($nilaiValid)];
            }
        }

        $data = $request->validate($rules, $this->messages(), $this->attributes());

        $jawabanBaru = [];
        foreach ($dipilih as $kode) {
            foreach ($data['jawaban'][$kode] as $nomor => $nilai) {
                $jawabanBaru[$kode][(int) $nomor] = (int) $nilai;
            }
        }

        $evaluasi->load('answers');
        $jawabanLama = $this->petaJawaban($evaluasi);

        // Materi yang jawabannya berubah, ditambah, atau dihapus.
        $materiBerubah = [];
        foreach (array_unique(array_merge(array_keys($jawabanLama), array_keys($jawabanBaru))) as $kode) {
            if (($jawabanLama[$kode] ?? null) != ($jawabanBaru[$kode] ?? null)) {
                $materiBerubah[] = $kode;
            }
        }

        $evaluasi->fill([
            'nama'              => $data['nama'],
            'nik'               => $data['nik'],
            'tanggal_pengisian' => $data['tanggal_pengisian'],
            'bagian'            => $data['bagian'],
            'materi_diikuti'    => $dipilih,
            'has_findings'      => TrainingEvaluation::hitungPerhatian($jawabanBaru),
        ]);

        // has_findings hanya turunan dari jawaban, bukan perubahan yang diketik PGA.
        $diubah = array_values(array_diff(array_keys($evaluasi->getDirty()), ['has_findings']));

        if (empty($diubah) && empty($materiBerubah) && ! $evaluasi->isDirty('has_findings')) {
            return redirect()
                ->route('pga.training.show', $evaluasi)
                ->with('status', 'Tidak ada perubahan yang disimpan.');
        }

        $user = $request->user('tally');
        $evaluasi->diedit_oleh = "{$user->name} ({$user->employee_code})";
        $evaluasi->diedit_pada = now();

        DB::transaction(function () use ($evaluasi, $materiBerubah, $jawabanBaru) {
            $evaluasi->save();

            if (! empty($materiBerubah)) {
                TrainingEvaluationAnswer::where('training_evaluation_id', $evaluasi->id)->delete();

                $baris = [];
                foreach ($jawabanBaru as $kode => $perMateri) {
                    foreach ($perMateri as $nomor => $nilai) {
                        $baris[] = [
                            'training_evaluation_id' => $evaluasi->id,
                            'materi'                 => $kode,
                            'nomor'                  => $nomor,
                            'nilai'                  => $nilai,
                        ];
                    }
                }
                TrainingEvaluationAnswer::insert($baris);
            }
        });

        $rincian = [];
        if (! empty($diubah)) {
            $rincian[] = 'field: ' . implode(', ', $diubah);
        }
        if (! empty($materiBerubah)) {
            $rincian[] = 'jawaban materi: ' . implode(', ', $materiBerubah);
        }

        ActivityLogger::log(
            'pga',
            'training_edit',
            "{$user->employee_code} ({$user->name}) mengedit evaluasi training #{$evaluasi->id} ({$evaluasi->nama}); " . implode('; ', $rincian),
            $user
        );

        return redirect()
            ->route('pga.training.show', $evaluasi)
            ->with('status', 'Perubahan evaluasi training berhasil disimpan.');
    }

    public function destroy(Request $request, TrainingEvaluation $evaluasi): RedirectResponse
    {
        $user = $request->user('tally');
        $id   = $evaluasi->id;
        $nama = $evaluasi->nama;
        $nik  = $evaluasi->nik;

        // Jawaban ikut terhapus lewat foreign key (cascadeOnDelete).
        $evaluasi->delete();

        ActivityLogger::log(
            'pga',
            'training_hapus',
            "{$user->employee_code} ({$user->name}) menghapus evaluasi training #{$id} ({$nama}, NIK {$nik})",
            $user
        );

        return redirect()
            ->route('pga.training.index')
            ->with('status', "Evaluasi training milik {$nama} berhasil dihapus.");
    }

    /** [kode_materi => [nomor => nilai]] dari jawaban yang tersimpan. */
    private function petaJawaban(TrainingEvaluation $evaluasi): array
    {
        return $evaluasi->answers
            ->groupBy('materi')
            ->map(fn ($g) => $g->pluck('nilai', 'nomor')->map(fn ($v) => (int) $v)->all())
            ->all();
    }

    private function messages(): array
    {
        return [
            'required'             => ':attribute wajib diisi.',
            'in'                   => ':attribute tidak valid.',
            'max.string'           => ':attribute terlalu panjang (maksimal :max karakter).',
            'before_or_equal'      => 'Tanggal tidak boleh melebihi hari ini.',
            'jawaban.*.*.required' => 'Pilih salah satu jawaban.',
            'jawaban.*.*.in'       => 'Jawaban tidak valid.',
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
