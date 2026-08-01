<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WarehouseDashboardController extends Controller
{
    public function index()
    {
        $system_phase = "Phase: 2026 Warehouse Digitalization";
        $plant_name   = "WAREHOUSE DEPARTMENT CPI - PLANT JOMBANG";
        $version      = "v1.0";

        $warehouse_menus = [
            [
                "name" => "Inbound",
                "icon" => "move_to_inbox",
                "info" => "Serah Terima Hasil Produksi ke Warehouse - reservasi cell, verifikasi bag, approval",
                "url"  => route('serahterima.login'),
            ],
            [
                "name" => "Stock Warehouse",
                "icon" => "inventory_2",
                "info" => "Monitoring stock barang di seluruh Cell Cold Storage secara real-time",
                "url"  => route('warehouse.stock.index'),
            ],
            [
                "name" => "Outbound",
                "icon" => "outbox",
                "info" => "Pencatatan barang keluar dari gudang",
                "url"  => "#",
            ],
            [
                "name" => "Inbound STRSTO",
                "icon" => "local_shipping",
                "info" => "Barang masuk ke gudang yang bukan berasal dari hasil produksi",
                "url"  => "#",
            ],
            [
                "name" => "B2B",
                "icon" => "handshake",
                "info" => "Transaksi barang fresh yang tidak masuk Cell gudang",
                "url"  => "#",
            ],
            [
                "name" => "Transfer Cell",
                "icon" => "swap_horiz",
                "info" => "Pemindahan stock antar Cell - untuk kasus overstock atau relokasi",
                "url"  => "#",
            ],
            [
                "name" => "E-GMP",
                "icon" => "shield_lock",
                "info" => "Web GMP Patrol: Digitalisasi audit, pantau kepatuhan real-time, dan pelaporan instan",
                "url"  => "http://10.71.3.27/gmp-patrol/",
            ],
            [
                "name" => "Suhu Ruang CS",
                "icon" => "thermostat",
                "info" => "Monitoring suhu ruang CS secara real-time",
                "url"  => "http://10.60.22.21:3000/d/adhfkzm/cs-suhu-ruang?kiosk=true&orgId=1&from=now-24h&to=now&timezone=browser&refresh=1m",
            ],
        ];

        // Kalau url null/kosong berarti belum aktif -> jadi "#"
        foreach ($warehouse_menus as &$menu) {
            if (empty($menu['url'])) {
                $menu['url'] = '#';
            }
        }

        return view('warehouse.dashboard', compact('system_phase', 'plant_name', 'version', 'warehouse_menus'));
    }
}
