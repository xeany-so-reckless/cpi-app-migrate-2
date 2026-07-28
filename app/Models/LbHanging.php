<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LbHanging extends Model
{
    protected $table = 'lb_hanging';

    protected $fillable = [
        'no_rit',
        'jam_bongkar',
        'jam_selesai',
        'total_diterima',
        'total_sj',
        'total_kosong',
        'status',
        'grid_json',
        'tanggal_penerimaan',
        'no_po',
        'nama_tally',
        'nama_foreman',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penerimaan' => 'date',
            'grid_json'          => 'array',
        ];
    }
}
