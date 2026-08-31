<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutboundShipment extends Model
{
    /**
     * Header dokumen pengiriman (DO) yang diinput Checker. Tidak
     * menyimpan angka stock sama sekali - murni data administratif.
     * Efek pengurangan stock ada di cell_stock_adjustments lewat
     * relasi OutboundShipmentCell::adjustment().
     */
    protected $fillable = [
        'tanggal',
        'no_do',
        'nama_customer',
        'jam_muat',
        'no_pol',
        'nama_driver',
        'checker_user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_user_id');
    }

    /**
     * Cell-cell yang dimuat dalam 1 DO ini (1 DO boleh mencakup
     * lebih dari 1 Cell).
     */
    public function cells(): HasMany
    {
        return $this->hasMany(OutboundShipmentCell::class);
    }

    /**
     * Total keseluruhan bag & kg yang keluar dalam DO ini, dijumlahkan
     * dari semua cell yang tercakup. Dipakai untuk tampilan ringkasan
     * (misal di halaman histori Outbound).
     */
    public function totalBag(): int
    {
        return (int) $this->cells()->sum('total_bag');
    }

    public function totalKg(): float
    {
        return (float) $this->cells()->sum('total_kg');
    }
}
