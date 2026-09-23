<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BARU - 1 baris item dalam dokumen Outbound Fresh, mewakili TEPAT 1
 * baris produksi_fresh yang dikeluarkan. produksi_fresh_id unique di
 * level database (lihat migration) - begitu 1 baris produksi_fresh
 * terpakai di sini, otomatis tidak akan muncul lagi di daftar "item
 * tersedia" (lihat ProduksiFresh::scopeAvailableForOutbound()).
 *
 * kode_produk, nama_produk, kode_produksi, qty adalah SNAPSHOT yang
 * disalin dari produksi_fresh saat store() di server - bukan dari input
 * client - supaya histori tetap akurat walau data master berubah
 * belakangan.
 *
 * @property int $id
 * @property int $outbound_fresh_shipment_id
 * @property int $produksi_fresh_id
 * @property string|null $kode_produk
 * @property string|null $nama_produk
 * @property string $kode_produksi
 * @property float $qty
 */
class OutboundFreshShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_fresh_shipment_id',
        'produksi_fresh_id',
        'kode_produk',
        'nama_produk',
        'kode_produksi',
        'qty',
        'keterangan',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function outboundFreshShipment(): BelongsTo
    {
        return $this->belongsTo(OutboundFreshShipment::class);
    }

    public function produksiFresh(): BelongsTo
    {
        return $this->belongsTo(ProduksiFresh::class);
    }
}
