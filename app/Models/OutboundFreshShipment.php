<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BARU - Header 1 dokumen Outbound Fresh. Analog OutboundShipment (Frozen)
 * tapi TANPA relasi ke Cell/Tir sama sekali - produk fresh tidak masuk
 * cell stock, jadi cuma "lewat" saja di warehouse lewat dokumen ini.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $no_po
 * @property string $nama_customer
 * @property string $jam_muat
 * @property string $no_pol
 * @property string $nama_driver
 * @property int $checker_user_id
 * @property string $status
 */
class OutboundFreshShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'no_po',
        'nama_customer',
        'jam_muat',
        'no_pol',
        'nama_driver',
        'checker_user_id',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OutboundFreshShipmentItem::class);
    }

    /**
     * Total qty seluruh item dalam dokumen ini - dipakai untuk ringkasan
     * di halaman input maupun kolom di tabel Riwayat.
     */
    public function totalQty(): float
    {
        return (float) $this->items->sum('qty');
    }

    /**
     * Jumlah baris item (kode produksi) dalam dokumen ini.
     */
    public function totalItem(): int
    {
        return $this->items->count();
    }
}
