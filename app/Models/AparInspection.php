<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AparInspection extends Model
{
    public const JENIS = [
        'co2'        => 'CO2',
        'foam'       => 'Foam (Busa)',
        'dry_powder' => 'Dry Chemical Powder (Tepung Kering)',
        'gas'        => 'Gas (Hydro Halogen, dsb)',
    ];

    // Komponen yang dicek Baik/Rusak (roda & pressure gauge bisa kosong/tidak berlaku)
    public const KOMPONEN = [
        'pin_segel'      => 'Pin / Segel',
        'selang'         => 'Selang',
        'klem_selang'    => 'Klem Selang',
        'nozzle'         => 'Nozzle',
        'pressure_gauge' => 'Pressure Gauge',
        'handle'         => 'Pegangan / Handle',
        'kondisi_fisik'  => 'Kondisi Fisik',
        'roda'           => 'Roda (APAB)',
    ];

    public const NILAI = [
        'baik'      => 'Baik',
        'rusak'     => 'Rusak',
        'tidak_ada' => 'Tidak Ada',
    ];

    protected $fillable = [
        'nama_inspektor', 'jabatan', 'tanggal_inspeksi', 'jam_inspeksi',
        'nomor_apar', 'jenis_apar', 'lokasi_apar',
        'pin_segel', 'selang', 'klem_selang', 'nozzle', 'pressure_gauge',
        'handle', 'kondisi_fisik', 'roda',
        'keterangan', 'foto_path', 'has_findings', 'ip_address',
        'diedit_oleh', 'diedit_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_inspeksi' => 'date',
            'has_findings'     => 'boolean',
            'diedit_pada'      => 'datetime',
        ];
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis_apar] ?? $this->jenis_apar;
    }

        /**
     * URL foto lewat route PGA (butuh login), bukan link public/storage.
     * Parameter v dipakai supaya browser memuat ulang saat foto diganti.
     */
    public function getFotoUrlAttribute(): string
    {
        return route('pga.apar.foto', [
            'inspeksi' => $this->id,
            'v'        => substr(md5((string) $this->foto_path), 0, 8),
        ]);
    }

    /** Jam tampil tanpa detik (HH:MM). */
    public function getJamTampilAttribute(): string
    {
        return substr((string) $this->jam_inspeksi, 0, 5);
    }

    /** Daftar label komponen yang berstatus rusak. */
    public function komponenRusak(): array
    {
        $rusak = [];
        foreach (self::KOMPONEN as $field => $label) {
            if ($this->{$field} === 'rusak') {
                $rusak[] = $label;
            }
        }
        return $rusak;
    }

    /**
     * Hanya nilai 'rusak' yang dihitung temuan.
     * null (pressure gauge tidak berlaku) dan 'tidak_ada' (roda) diabaikan.
     */
    public static function hitungTemuan(array $data): bool
    {
        foreach (array_keys(self::KOMPONEN) as $field) {
            if (($data[$field] ?? null) === 'rusak') {
                return true;
            }
        }
        return false;
    }
}