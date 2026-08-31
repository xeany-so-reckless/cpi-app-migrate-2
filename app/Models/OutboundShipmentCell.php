<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutboundShipmentCell extends Model
{
    /**
     * Rekap 1 Cell dalam 1 DO. total_bag/total_kg di sini murni untuk
     * tampilan histori - sumber kebenaran pengurangan stock tetap di
     * baris cell_stock_adjustments yang terhubung lewat adjustment().
     */
    protected $fillable = [
        'outbound_shipment_id',
        'cell_id',
        'total_bag',
        'total_kg',
        'cell_stock_adjustment_id',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OutboundShipment::class, 'outbound_shipment_id');
    }

    public function cell(): BelongsTo
    {
        return $this->belongsTo(Cell::class);
    }

    /**
     * Baris cell_stock_adjustments (sumber='outbound') yang dibuat
     * otomatis saat shipment ini disimpan - inilah yang benar-benar
     * mengurangi sisa kapasitas/stock di Cell::sisaKapasitas().
     */
    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(CellStockAdjustment::class, 'cell_stock_adjustment_id');
    }

    /**
     * Detail per bag yang dicentang & keluar untuk cell ini.
     */
    public function bags(): HasMany
    {
        return $this->hasMany(OutboundShipmentCellBag::class);
    }
}
