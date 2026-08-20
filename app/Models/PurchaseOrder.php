<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'jenis_po',
        'nomor_po',
        'tanggal',
        'jumlah_rit',
        'produk_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'teco_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    /**
     * BARU - True kalau PO ini sudah ditandai TECO (Technically Complete,
     * istilah SAP) oleh PPIC. PO yang TECO otomatis hilang dari dropdown
     * "Nomor PO" di form Sebelum Bongkar (LB Report) - lihat
     * LbReportController::listPurchaseOrders().
     */
    public function isTeco(): bool
    {
        return $this->teco_at !== null;
    }
}