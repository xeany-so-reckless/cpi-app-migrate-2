<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data migration - insert role baru 'tally_by_product', dipakai
     * modul Produksi Fresh untuk membatasi akses tipe login "By Product"
     * (menggantikan role "tally By Product" yang di Apps Script lama
     * cuma teks bebas, bukan role sungguhan).
     *
     * Dicek dulu belum ada supaya migration ini aman dijalankan ulang
     * / tidak duplikat kalau baris role ini kebetulan sudah pernah
     * ditambahkan manual lewat tinker.
     */
    public function up(): void
    {
        $exists = DB::table('roles')->where('name', 'tally_by_product')->exists();

                if (! $exists) {
            DB::table('roles')->insert([
                'name'       => 'tally_by_product',
                'label'      => 'Tally By Product',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'tally_by_product')->delete();
    }
};
