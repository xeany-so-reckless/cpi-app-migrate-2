<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingEvaluation extends Model
{
    public const BAGIAN = [
        'sales'              => 'Sales',
        'logistic'           => 'Logistic',
        'pga'                => 'PGA',
        'produksi'           => 'Produksi',
        'utility_maintenance' => 'Utility & Maintenance',
        'ppic'               => 'PPIC',
        'warehouse'          => 'Warehouse',
        'quality_control'    => 'Quality Control',
        'finance_fico'       => 'Finance/FICO',
        'wwtp'               => 'WWTP',
    ];

    /**
     * Nilai disimpan sebagai angka 1-4 hanya untuk perhitungan.
     * Di layar (form, daftar, dashboard) selalu tampil sebagai teks.
     */
    public const SKALA = [
        4 => 'Selalu',
        3 => 'Sering',
        2 => 'Kadang-Kadang',
        1 => 'Tidak Pernah',
    ];

    /** Jawaban dengan nilai <= batas ini dianggap "perlu perhatian" (Kadang-Kadang / Tidak Pernah). */
    public const BATAS_PERHATIAN = 2;

    protected $fillable = [
        'nama', 'nik', 'tanggal_pengisian', 'bagian', 'materi_diikuti',
        'has_findings', 'ip_address', 'diedit_oleh', 'diedit_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengisian' => 'date',
            'materi_diikuti'    => 'array',
            'has_findings'      => 'boolean',
            'diedit_pada'       => 'datetime',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TrainingEvaluationAnswer::class);
    }

    public function getBagianLabelAttribute(): string
    {
        return self::BAGIAN[$this->bagian] ?? (string) $this->bagian;
    }

    /** Semua materi (kode => data) dari config/training.php. */
    public static function materi(): array
    {
        return config('training.materi', []);
    }

    /** Kode materi yang wajib (otomatis tercentang di form). */
    public static function materiWajib(): array
    {
        return array_keys(array_filter(self::materi(), fn ($m) => ! empty($m['wajib'])));
    }

    public static function labelNilai(?int $nilai): string
    {
        return self::SKALA[$nilai] ?? '-';
    }

    /** Ubah rata-rata (1-4) menjadi label teks terdekat. */
    public static function labelRataRata(float $rata): string
    {
        return match (true) {
            $rata >= 3.5 => self::SKALA[4],
            $rata >= 2.5 => self::SKALA[3],
            $rata >= 1.5 => self::SKALA[2],
            default      => self::SKALA[1],
        };
    }

    /** Rata-rata di bawah 2,5 (label Kadang-Kadang / Tidak Pernah) dianggap perlu perhatian. */
    public static function rataRataPerluPerhatian(float $rata): bool
    {
        return $rata < 2.5;
    }

    /**
     * Ada minimal satu jawaban Kadang-Kadang / Tidak Pernah?
     * Format: [kode_materi => [nomor => nilai]].
     */
    public static function hitungPerhatian(array $jawaban): bool
    {
        foreach ($jawaban as $perMateri) {
            foreach ((array) $perMateri as $nilai) {
                if ((int) $nilai <= self::BATAS_PERHATIAN) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Rata-rata nilai per materi untuk satu pengisian: [kode_materi => float]. */
    public function rataRataPerMateri(): array
    {
        return $this->answers
            ->groupBy('materi')
            ->map(fn ($g) => round($g->avg('nilai'), 2))
            ->all();
    }
}
