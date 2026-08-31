<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundShipmentCellBag extends Model
{
    /**
     * Detail 1 bag yang keluar dalam 1 shipment_cell.
     *
     * batch_id + nomor_bag NULL kalau bag ini berasal dari baris generik
     * "Stock Adjustment" (stock tanpa identitas batch asli, dari hasil
     * upload Excel) - keterangan diisi untuk menjaga jejak.
     *
     * PENTING: baris di tabel ini JUGA berfungsi sebagai penanda "bag X
     * di batch Y sudah pernah keluar" - dipakai Cell::availableBags()
     * (ditambahkan di Step 4) untuk exclude bag ini dari daftar centang
     * cell yang sama di Outbound berikutnya.
     */
    protected $fillable = [
        'outbound_shipment_cell_id',
        'batch_id',
        'nomor_bag',
        'kg',
        'kode_produksi',
        'keterangan',
    ];

    public function shipmentCell(): BelongsTo
    {
        return $this->belongsTo(OutboundShipmentCell::class, 'outbound_shipment_cell_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SerahTerimaBatch::class, 'batch_id');
    }

    /**
     * True kalau bag ini asalnya dari batch asli (punya identitas),
     * false kalau dari baris generik "Stock Adjustment".
     */
    public function isFromBatch(): bool
    {
        return ! is_null($this->batch_id) && ! is_null($this->nomor_bag);
    }
}
