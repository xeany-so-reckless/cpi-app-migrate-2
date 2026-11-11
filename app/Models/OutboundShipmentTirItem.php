<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundShipmentTirItem extends Model
{
    /**
     * Rincian 1 item (produk) dalam 1 Tir. Satu Tir boleh punya beberapa
     * baris di sini (misal Tir 1 = Produk A 8 bag + Produk B 4 bag).
     *
     * produk_id NULL berarti baris "Stock Adjustment" (stock tanpa
     * identitas produk).
     */
    protected $fillable = [
        'outbound_shipment_tir_id',
        'produk_id',
        'jumlah_bag',
    ];

    public function tir(): BelongsTo
    {
        return $this->belongsTo(OutboundShipmentTir::class, 'outbound_shipment_tir_id');
    }

    /**
     * Produk item ini (null kalau baris "Stock Adjustment").
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }
}
