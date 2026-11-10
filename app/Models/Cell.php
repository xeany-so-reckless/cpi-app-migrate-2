<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cell extends Model
{
    protected $fillable = [
        'kode_cell',
        'cold_storage',
        'lantai',
        'tipe_rak',
        'kapasitas_max',
        'kapasitas_max_kg',
        'current_product_id',
        'is_active',
        'is_simpanan', // BARU - cell simpanan (1C01-1C06)
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'is_simpanan' => 'boolean', // BARU
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_cell', 'cell_id', 'produk_id');
    }

    /**
     * BARU - Produk yang SEDANG mengunci/mengisi Cell ini sekarang.
     * null = Cell kosong, boleh diisi produk apapun.
     *
     * CATATAN: relasi `products()` (pivot statis, di atas) TIDAK dihapus
     * dan TIDAK dipakai lagi untuk validasi/lock - dibiarkan ada dulu
     * sebagai jaga-jaga, sampai dipastikan tidak ada bagian lain yang
     * masih bergantung ke situ.
     */
    public function currentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'current_product_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(CellReservation::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(CellStockAdjustment::class);
    }

    // =====================================================================
    // BARU - Cell Simpanan & Pemindahan Stock (stock_transfer_bags).
    //
    // Tabel stock_transfer_bags adalah RIWAYAT: satu baris = satu kali
    // pemindahan satu bag. Satu bag boleh dipindah berkali-kali (cell biasa
    // -> simpanan -> cell biasa -> ...), jadi lokasi bag SAAT INI adalah
    // to_cell_id dari baris TERAKHIR (id terbesar) untuk bag itu; kalau
    // belum pernah dipindah, lokasinya cell reservasi aslinya.
    //
    // Hitungan jumlah (bag/kg) per cell tetap sederhana: total yang MASUK
    // dikurangi total yang KELUAR dari riwayat, jadi tetap benar walau bag
    // berpindah-pindah.
    // =====================================================================

    /**
     * True kalau Cell ini adalah cell simpanan (1C01-1C06): tidak pernah
     * terkunci produk (boleh campur), tidak menerima dari Inbound/TPR,
     * tidak muncul di Outbound, dan tidak bisa di-upload Excel. Isinya
     * hanya dari fitur "Pindahkan" di halaman Stock.
     */
    public function isSimpanan(): bool
    {
        return (bool) $this->is_simpanan;
    }

    /**
     * Riwayat bag yang DIPINDAHKAN KELUAR dari Cell ini (Cell ini = asal).
     */
    public function transfersOut(): HasMany
    {
        return $this->hasMany(StockTransferBag::class, 'from_cell_id');
    }

    /**
     * Riwayat bag yang DIPINDAHKAN MASUK ke Cell ini (Cell ini = tujuan).
     */
    public function transfersIn(): HasMany
    {
        return $this->hasMany(StockTransferBag::class, 'to_cell_id');
    }

    public function transferKeluarBag(): int
    {
        return (int) $this->transfersOut()->count();
    }

    public function transferMasukBag(): int
    {
        return (int) $this->transfersIn()->count();
    }

    public function transferKeluarKg(): float
    {
        return (float) $this->transfersOut()->sum('kg');
    }

    public function transferMasukKg(): float
    {
        return (float) $this->transfersIn()->sum('kg');
    }

    /**
     * True kalau Cell ini SEDANG terkunci ke produk dengan id $produkId.
     * Dipakai untuk memvalidasi tujuan pemindahan dari cell simpanan:
     * bag hanya boleh pindah ke cell yang sudah terkunci produk yang sama.
     */
    public function terkunciKeProdukId(int $produkId): bool
    {
        return $this->current_product_id !== null
            && (int) $this->current_product_id === $produkId;
    }

    /**
     * Daftar bag yang SAAT INI benar-benar ada secara fisik di Cell ini
     * (belum keluar lewat Outbound). Ini sumber kebenaran lokasi bag
     * untuk tampilan batch di halaman Stock dan untuk availableBags().
     *
     * Bag dianggap ada di Cell ini kalau:
     * - pernah dipindah, dan pemindahan TERAKHIR-nya menuju Cell ini; atau
     * - belum pernah dipindah, dan batch-nya memang reservasi (USED) Cell ini.
     *
     * Tiap elemen:
     *  - 'batch'        SerahTerimaBatch (relasi produk sudah di-load)
     *  - 'batch_id', 'nomor_bag', 'kg'
     *  - 'dipindah'     true kalau bag ini ada di sini hasil pemindahan
     *  - 'asal_cell_id' id cell asal pemindahan terakhir (null kalau tidak dipindah)
     *
     * $termasukReject = true dipakai halaman Stock (konsisten dengan angka
     * Terpakai yang menghitung jumlah_bag penuh); false dipakai Outbound
     * (bag reject tidak pernah dikeluarkan).
     */
    public function daftarBagFisik(bool $termasukReject = true): array
    {
        $reservasi = $this->reservations()
            ->where('status', 'USED')
            ->get(['batch_id']);

        $batchIdMilik = $reservasi->pluck('batch_id')->filter()->unique()->values()->all();

        // Batch yang pernah ada bag-nya dipindah MASUK ke Cell ini
        // (bisa dari batch milik cell lain).
        $batchIdMasuk = StockTransferBag::where('to_cell_id', $this->id)
            ->pluck('batch_id')
            ->unique()
            ->values()
            ->all();

        $semuaBatchId = array_values(array_unique(array_merge($batchIdMilik, $batchIdMasuk)));

        if (count($semuaBatchId) === 0) {
            return [];
        }

        // Pemindahan TERAKHIR tiap bag (baris ber-id terbesar menang).
        $terakhir = [];
        StockTransferBag::whereIn('batch_id', $semuaBatchId)
            ->orderBy('id')
            ->get(['id', 'batch_id', 'nomor_bag', 'from_cell_id', 'to_cell_id'])
            ->each(function ($row) use (&$terakhir) {
                $terakhir[$row->batch_id.'-'.$row->nomor_bag] = $row;
            });

        $sudahKeluar = OutboundShipmentCellBag::whereIn('batch_id', $semuaBatchId)
            ->whereNotNull('nomor_bag')
            ->get(['batch_id', 'nomor_bag'])
            ->map(fn ($row) => $row->batch_id.'-'.$row->nomor_bag)
            ->flip();

        $batches = SerahTerimaBatch::with('produk')
            ->whereIn('id', $semuaBatchId)
            ->orderBy('id')
            ->get();

        $milik = array_flip($batchIdMilik);
        $idCellIni = (int) $this->id;
        $hasil = [];

        foreach ($batches as $batch) {
            $batchMilikCellIni = isset($milik[$batch->id]);

            for ($i = 1; $i <= $batch->jumlah_bag; $i++) {
                $key = $batch->id.'-'.$i;
                $row = $terakhir[$key] ?? null;

                if ($row) {
                    $lokasi = (int) $row->to_cell_id;
                } else {
                    $lokasi = $batchMilikCellIni ? $idCellIni : null;
                }

                if ($lokasi === null || $lokasi !== $idCellIni) {
                    continue; // bag ini tidak sedang berada di Cell ini
                }

                if (isset($sudahKeluar[$key])) {
                    continue; // sudah keluar via Outbound
                }

                if (! $termasukReject && $batch->{"status_bag_{$i}"} === 'TOLAK (REJECT)') {
                    continue;
                }

                $hasil[] = [
                    'batch'        => $batch,
                    'batch_id'     => $batch->id,
                    'nomor_bag'    => $i,
                    'kg'           => (float) $batch->{"kg_bag_{$i}"},
                    'dipindah'     => $row !== null,
                    'asal_cell_id' => $row ? (int) $row->from_cell_id : null,
                ];
            }
        }

        return $hasil;
    }

    /**
     * Total selisih dari semua penyesuaian (upload Excel) yang pernah ada
     * untuk cell ini. Boleh negatif (kalau fisik lebih sedikit dari
     * hitungan sistem) atau positif (fisik lebih banyak).
     */
    public function totalAdjustment(): int
    {
        return (int) $this->adjustments()->sum('selisih');
    }

    /**
     * Total selisih KG dari semua penyesuaian (upload Excel) - dipakai
     * untuk hitung total kg per cell, dikombinasikan dengan kg dari batch
     * Inbound asli (kg_bag_1..10 di serah_terima_batches).
     */
    public function totalAdjustmentKg(): float
    {
        return (float) $this->adjustments()->sum('selisih_kg');
    }

    /**
     * Total kg terpakai saat ini. Beda dengan versi bag, disini kg
     * PENDING reservation TIDAK dihitung (karena belum ada berat pasti
     * sebelum TPR input kg per bag-nya) - cuma dari batch yang statusnya
     * sudah USED (kg_bag_1..10 asli) + penyesuaian kg dari upload Excel.
     *
     * REVISI (Pemindahan): dikurangi kg bag yang dipindah KELUAR dari
     * cell ini, dan ditambah kg bag yang dipindah MASUK ke cell ini.
     */
    public function terpakaiKg(): float
    {
        $usedKg = $this->reservations()
            ->where('status', 'USED')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->selectRaw('COALESCE(SUM(
                kg_bag_1 + kg_bag_2 + kg_bag_3 + kg_bag_4 + kg_bag_5 +
                kg_bag_6 + kg_bag_7 + kg_bag_8 + kg_bag_9 + kg_bag_10
            ), 0) as total')
            ->value('total');

        return (float) $usedKg
            + $this->totalAdjustmentKg()
            - $this->transferKeluarKg()
            + $this->transferMasukKg();
    }

    public function sisaKapasitasKg(): float
    {
        if ($this->kapasitas_max_kg === null) {
            return 0;
        }

        return max(0, (float) $this->kapasitas_max_kg - $this->terpakaiKg());
    }

    /**
     * Breakdown stock per warna (kuartal produksi), gabungan dari semua
     * penyesuaian (upload Excel) yang pernah ada untuk cell ini.
     * Dipakai untuk tampilan "klik cell -> lihat rincian per warna".
     *
     * Merah = Jan-Mar, Biru = Apr-Jun, Hijau = Jul-Sep, Kuning = Okt-Des.
     *
     * CATATAN: method ini hanya membaca adjustment. Breakdown yang dipakai
     * halaman Stock dihitung di StockController::data().
     */
    public function breakdownWarna(): array
    {
        $row = $this->adjustments()
            ->selectRaw('
                COALESCE(SUM(bag_merah), 0) as bag_merah,
                COALESCE(SUM(bag_biru), 0) as bag_biru,
                COALESCE(SUM(bag_hijau), 0) as bag_hijau,
                COALESCE(SUM(bag_kuning), 0) as bag_kuning,
                COALESCE(SUM(kg_merah), 0) as kg_merah,
                COALESCE(SUM(kg_biru), 0) as kg_biru,
                COALESCE(SUM(kg_hijau), 0) as kg_hijau,
                COALESCE(SUM(kg_kuning), 0) as kg_kuning
            ')
            ->first();

        return [
            'merah'  => ['bag' => (int) $row->bag_merah, 'kg' => (float) $row->kg_merah],
            'biru'   => ['bag' => (int) $row->bag_biru, 'kg' => (float) $row->kg_biru],
            'hijau'  => ['bag' => (int) $row->bag_hijau, 'kg' => (float) $row->kg_hijau],
            'kuning' => ['bag' => (int) $row->bag_kuning, 'kg' => (float) $row->kg_kuning],
        ];
    }

    // =====================================================================
    // BARU (STEP 6) - Lock/release produk & kapasitas dinamis per produk.
    // =====================================================================

    /**
     * True kalau Cell ini kosong (tidak sedang terkunci ke produk manapun).
     * Sumber kebenarannya `current_product_id` - kolom ini HARUS selalu
     * sinkron lewat lockKeProduk() / releaseJikaKosong(), jangan diubah
     * manual di tempat lain.
     */
    public function isKosong(): bool
    {
        return $this->current_product_id === null;
    }

    /**
     * True kalau Cell ini boleh menerima produk $produk sekarang:
     * - Cell kosong -> boleh produk apapun.
     * - Cell terkunci -> HANYA boleh produk yang sama persis (lock per
     *   kode produk spesifik, bukan per kategori - sesuai keputusan).
     */
    public function bisaMenerimaProduk(Product $produk): bool
    {
        return $this->isKosong() || $this->current_product_id === $produk->id;
    }

    /**
     * Kunci Cell ini ke produk $produk. Idempotent - aman dipanggil
     * berkali-kali untuk produk yang sama (tidak melakukan write kalau
     * sudah terkunci ke produk itu juga).
     *
     * TIDAK memvalidasi bisaMenerimaProduk() di sini secara sengaja -
     * pengecekan itu tanggung jawab pemanggil (Controller) SEBELUM
     * transaksi utama jalan, supaya pesan error yang tepat bisa
     * ditampilkan ke user. Method ini murni aksi "kunci", bukan gate.
     *
     * REVISI (Cell Simpanan): cell simpanan TIDAK PERNAH dikunci produk
     * (boleh campur), jadi method ini tidak melakukan apa-apa untuk itu.
     */
    public function lockKeProduk(Product $produk): void
    {
        if ($this->isSimpanan()) {
            return;
        }

        if ($this->current_product_id !== $produk->id) {
            $this->update(['current_product_id' => $produk->id]);
        }
    }

    /**
     * Lepas kunci Cell ini KALAU stock fisiknya sudah 0 (habis).
     * Dipanggil setiap kali ada aksi yang berpotensi menghabiskan stock:
     * - StockController::uploadExcel() (penyesuaian fisik manual)
     * - OutboundController::processCellOutbound() (barang keluar)
     * - StockController::transfer() (bag dipindah keluar dari cell asal)
     *
     * Sengaja pakai stockBag() (stock FISIK riil) sebagai acuan "kosong",
     * BUKAN sisaKapasitas() (itu ruang sisa, bukan stock) - supaya
     * konsisten dengan makna "kosong" yang dipakai di seluruh sistem.
     *
     * REVISI (Cell Simpanan): cell simpanan tidak punya kunci produk,
     * jadi tidak ada yang perlu dilepas.
     */
    public function releaseJikaKosong(): void
    {
        if ($this->isSimpanan()) {
            return;
        }

        if ($this->stockBag() <= 0 && $this->current_product_id !== null) {
            $this->update(['current_product_id' => null]);
        }
    }

    /**
     * Kapasitas bag yang berlaku SEKARANG untuk Cell ini, tergantung
     * produk yang dipakai sebagai acuan:
     * - $produk diisi (biasanya currentProduct milik Cell ini) -> lookup
     *   ke standard_capacities berdasarkan (produk, lantai, tipe_rak).
     * - Kalau kombinasinya tidak ketemu di standard_capacities (data
     *   belum lengkap dari gudang), ATAU $produk null (Cell kosong,
     *   belum tahu produk apa yang akan masuk) -> fallback ke
     *   kapasitas_max statis milik Cell ini sendiri.
     *
     * REVISI (Cell Simpanan): isinya campur produk, jadi selalu pakai
     * kapasitas_max statis (90) tanpa lookup standard_capacities.
     */
    public function kapasitasBagUntukProduk(?Product $produk): ?int
    {
        if ($this->isSimpanan()) {
            return $this->kapasitas_max;
        }

        if ($produk === null) {
            return $this->kapasitas_max;
        }

        if (! $this->lantai || ! $this->tipe_rak) {
            return $this->kapasitas_max;
        }

        $standar = StandardCapacity::untukProdukDanCell($produk->id, $this->lantai, $this->tipe_rak)->first();

        return $standar->kapasitas_bag ?? $this->kapasitas_max;
    }

    /**
     * Sisa kapasitas (dalam bag) saat ini.
     * REVISI STEP 6: kapasitas acuan sekarang DINAMIS mengikuti produk
     * yang sedang mengunci Cell ini (currentProduct), bukan lagi selalu
     * pakai kapasitas_max statis secara langsung - lihat
     * kapasitasBagUntukProduk() untuk aturan fallback-nya.
     *
     * Terpakai = SUM bag dari reservasi yang sudah USED (jumlah_bag batch
     * sebenarnya) + reservasi yang masih PENDING (pakai max_bag_allowed,
     * supaya tidak dobel-reservasi lebih dari fisik yang tersedia)
     * + total penyesuaian manual (upload Excel) yang pernah dilakukan.
     *
     * REVISI (Pemindahan): dikurangi bag yang dipindah KELUAR dari cell
     * ini, dan ditambah bag yang dipindah MASUK ke cell ini.
     */
    public function sisaKapasitas(): int
    {
        $terpakaiUsed = $this->reservations()
            ->where('status', 'USED')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->sum('serah_terima_batches.jumlah_bag');

        $terpakaiPending = $this->reservations()
            ->where('status', 'PENDING')
            ->sum('max_bag_allowed');

        $adjustment = $this->totalAdjustment();

        $kapasitas = $this->kapasitasBagUntukProduk($this->currentProduct) ?? $this->kapasitas_max;

        $terpakai = $terpakaiUsed + $terpakaiPending + $adjustment
            - $this->transferKeluarBag()
            + $this->transferMasukBag();

        return max(0, $kapasitas - $terpakai);
    }

    // =====================================================================
    // BARU - Ditambahkan untuk fitur Outbound (Checker). Semua method di
    // bawah ini READ-ONLY dan tidak mengubah/menggantikan method di atas.
    // =====================================================================

    /**
     * Total bag yang FISIK ada di cell ini SEKARANG (stock riil).
     *
     * Beda dengan sisaKapasitas(): method itu menghitung SISA RUANG
     * (kapasitas dikurangi terpakai), sedangkan ini menghitung STOCK
     * yang ada, tanpa melibatkan kapasitas sama sekali. Reservasi
     * PENDING sengaja TIDAK diikutkan karena itu baru "dijatah", belum
     * tentu benar-benar sudah terisi produk.
     *
     * Setelah Outbound berjalan, baris adjustment sumber='outbound'
     * (selisih negatif) otomatis mengurangi angka ini juga - karena
     * totalAdjustment() menjumlahkan SEMUA sumber adjustment.
     *
     * REVISI (Pemindahan): dikurangi bag yang dipindah KELUAR dari cell
     * ini, ditambah bag yang dipindah MASUK ke cell ini.
     *
     * Ini juga acuan utama releaseJikaKosong() di atas.
     */
    public function stockBag(): int
    {
        $terpakaiUsed = $this->reservations()
            ->where('status', 'USED')
            ->join('serah_terima_batches', 'serah_terima_batches.id', '=', 'cell_reservations.batch_id')
            ->sum('serah_terima_batches.jumlah_bag');

        return max(
            0,
            $terpakaiUsed
                + $this->totalAdjustment()
                - $this->transferKeluarBag()
                + $this->transferMasukBag()
        );
    }

    /**
     * Total kg yang FISIK ada di cell ini SEKARANG. Sebenarnya identik
     * dengan terpakaiKg() yang sudah ada - dibuat alias di sini supaya
     * penamaan simetris dengan stockBag() dan lebih jelas maksudnya
     * dipakai di konteks Outbound.
     */
    public function stockKg(): float
    {
        return $this->terpakaiKg();
    }

    /**
     * Total bag yang stock-nya TIDAK punya identitas batch asli - murni
     * hasil penyesuaian manual upload Excel (sumber='upload_excel').
     * SENGAJA tidak mengikutkan sumber 'outbound' supaya tidak dobel
     * hitung dengan bag yang sudah pernah dikeluarkan.
     *
     * Kalau nilainya > 0, berarti ada stock "misterius" di cell ini yang
     * tidak bisa ditelusuri ke bag/batch tertentu - ditampilkan sebagai
     * 1 baris generik "Stock Adjustment" di daftar centang Outbound.
     */
    public function genericAdjustmentBag(): int
    {
        return max(0, (int) $this->adjustments()->where('sumber', 'upload_excel')->sum('selisih'));
    }

    public function genericAdjustmentKg(): float
    {
        return max(0, (float) $this->adjustments()->where('sumber', 'upload_excel')->sum('selisih_kg'));
    }

    /**
     * Daftar bag yang MASIH ADA fisik di cell ini & BELUM PERNAH keluar
     * lewat Outbound manapun. Dipakai untuk isi checklist saat Checker
     * klik 1 Kode Cell di form Outbound.
     *
     * Setiap elemen array:
     *  - 'type'          => 'batch' | 'generic'
     *  - 'batch_id'       (null kalau generic)
     *  - 'nomor_bag'      (null kalau generic)
     *  - 'kode_produksi'  (null kalau generic)
     *  - 'produk'         (nama produk, null kalau generic)
     *  - 'kg'
     *  - 'tanggal_produksi' (dipakai FE/BE untuk breakdown warna kuartal, null kalau generic)
     *
     * Baris 'generic' cuma MUNCUL 1 KALI mewakili SISA total
     * genericAdjustmentBag()/genericAdjustmentKg() - karena stock jenis
     * ini tidak punya identitas per-bag, jadi tidak bisa dipecah satu-satu.
     *
     * REVISI (Pemindahan):
     * - Cell simpanan selalu mengembalikan array kosong (barang simpanan
     *   tidak untuk di-Outbound).
     * - Lokasi bag ditentukan lewat daftarBagFisik(): bag yang sedang
     *   dipindah ke cell simpanan tidak tampil di cell asalnya, dan bag
     *   yang dikembalikan dari simpanan ke cell ini ikut tampil.
     */
    public function availableBags(): array
    {
        if ($this->isSimpanan()) {
            return [];
        }

        $bags = [];

        // Bag reject tidak pernah masuk stock Outbound, jadi dilewati.
        foreach ($this->daftarBagFisik(false) as $item) {
            $batch = $item['batch'];

            $bags[] = [
                'type'             => 'batch',
                'batch_id'         => $item['batch_id'],
                'nomor_bag'        => $item['nomor_bag'],
                'kode_produksi'    => $batch->kode_produksi,
                'produk'           => $batch->produk->name ?? null,
                'kg'               => $item['kg'],
                'tanggal_produksi' => optional($batch->tanggal_produksi)->format('Y-m-d'),
            ];
        }

        // Baris generik untuk sisa stock tanpa identitas (upload Excel)
        $genericBag = $this->genericAdjustmentBag();
        if ($genericBag > 0) {
            $bags[] = [
                'type'             => 'generic',
                'batch_id'         => null,
                'nomor_bag'        => null,
                'kode_produksi'    => null,
                'produk'           => null,
                'kg'               => $this->genericAdjustmentKg(),
                'tanggal_produksi' => null,
                'jumlah_bag'       => $genericBag, // baris ini mewakili banyak bag sekaligus
            ];
        }

        return $bags;
    }
}