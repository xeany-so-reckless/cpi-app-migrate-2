<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProduksiFresh extends Model
{
    protected $table = 'produksi_fresh';

    protected $fillable = [
        'no_po',
        'user_id',
        'tipe_input',
        'produk_id',
        'kode_produksi',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
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
     * Relasi ke PO (PPIC), key custom karena purchase_orders pakai
     * nomor_po (bukan id) sebagai identitas PO - pola sama seperti
     * ProduksiHarian::purchaseOrder().
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'no_po', 'nomor_po');
    }

    /**
     * BARU - Relasi ke item Outbound Fresh, KALAU baris produksi_fresh
     * ini sudah pernah dikeluarkan lewat dokumen Outbound Fresh. hasOne
     * karena produksi_fresh_id di outbound_fresh_shipment_items dibuat
     * UNIQUE - 1 baris produksi_fresh maksimal punya 1 outbound item.
     */
    public function outboundItem(): HasOne
    {
        return $this->hasOne(OutboundFreshShipmentItem::class);
    }

    /**
     * BARU - Scope baris produksi_fresh yang MASIH BISA dipilih untuk
     * Outbound Fresh: tipe_input harus 'main' (sesuai revisi manager -
     * by product tidak ikut Outbound Fresh) DAN belum pernah dipakai di
     * outbound manapun (belum punya outboundItem).
     *
     * Dipakai di OutboundFreshController: listPurchaseOrdersWithFreshStock(),
     * getPoItems(), dan store() (re-validasi server-side anti race-condition).
     */
    public function scopeAvailableForOutbound(Builder $query): Builder
    {
        return $query->where('tipe_input', 'main')
            ->whereDoesntHave('outboundItem');
    }

    /**
     * BARU - Replika PERSIS algoritma generateProductionCode() dari
     * Apps Script lama (JS). Dijalankan di SERVER (bukan dipercaya dari
     * input client) supaya Kode Produksi tidak bisa dimanipulasi lewat
     * jam/tanggal perangkat user.
     *
     * Format: JBG + [huruf tahun] + [huruf bulan] + [tanggal 2 digit]
     *         + J + [kode kategori produk] + [AA/BB/CC] + 0
     *
     * - Huruf tahun: (tahun - 2026 + 16) % 26, dikonversi ke huruf (A=0)
     * - Huruf bulan: Januari=A ... Desember=L
     * - Hari: Senin/Rabu/Jumat -> AA, Selasa/Kamis/Sabtu -> BB, Minggu -> CC
     *
     * $at opsional untuk keperluan testing (inject waktu tertentu),
     * default pakai waktu server saat ini.
     */
    public static function generateKodeProduksi(string $categoryCode, ?Carbon $at = null): string
    {
        $d = $at ?? now();

        $prefix = 'JBG';

        $yearIndex = ($d->year - 2026 + 16) % 26;
        $yearCode = chr(65 + $yearIndex);

        // Carbon month: 1-12, JS getMonth(): 0-11 -> selisih 1
        $monthCode = chr(65 + ($d->month - 1));

        $dateCode = str_pad((string) $d->day, 2, '0', STR_PAD_LEFT);

        $middleCode = 'J';

        // Carbon dayOfWeek: 0 (Minggu) - 6 (Sabtu), sama seperti JS getDay()
        $dayOfWeek = $d->dayOfWeek;
        $dayCode = 'CC';
        if (in_array($dayOfWeek, [1, 3, 5], true)) {
            $dayCode = 'AA';
        } elseif (in_array($dayOfWeek, [2, 4, 6], true)) {
            $dayCode = 'BB';
        }

        $suffix = '0';

        return $prefix.$yearCode.$monthCode.$dateCode.$middleCode.$categoryCode.$dayCode.$suffix;
    }
} //