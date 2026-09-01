<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundShipmentTir extends Model
{
    /**
     * 1 baris Tir dalam 1 DO. Opsional sepenuhnya - kalau customer pakai
     * mobil kecil, DO tersebut tidak akan punya baris di sini sama sekali.
     *
     * tir_ke adalah LABEL URUTAN OTOMATIS (Tir 1, Tir 2, dst) - bukan
     * nomor segel/identitas manual. User cuma mengisi jumlah_bag.
     */
    protected $fillable = [
        'outbound_shipment_id',
        'tir_ke',
        'jumlah_bag',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OutboundShipment::class, 'outbound_shipment_id');
    }
}
