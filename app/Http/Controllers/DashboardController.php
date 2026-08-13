<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $system_phase = "Phase: 2026 Production Digitalization";
        $plant_name   = "PRODUCTION DEPARTMENT CPI - PLANT JOMBANG";
        $version      = "v1.1";

        $production_docs = [
            [
                "name" => "Laporan Produksi Harian",
                "icon" => "Add_To_Drive",
                "info" => "Upload Laporan harian Hasil Produksi Bersih Griller & Bahan Baku ke database",
                "url"  => "https://drive.google.com/drive/folders/1vSTNp6m7q3P13NRXWXVOvpjzNBsC2uF-",
            ],
            [
                "name" => "Tally Pro 2026",
                "icon" => "Docs",
                "info" => "Sistem input data tally harian otomatis berbasis web dan Excel VBA",
                "url"  => route('tally.login'),
            ],
            [
                "name" => "E-GMP",
                "icon" => "shield_lock",
                "info" => "Web GMP Patrol: Digitalisasi audit, pantau kepatuhan real-time, dan pelaporan instan",
                "url"  => "http://10.71.3.27/gmp-patrol/",
            ],
            // [
            //     "name" => "Man Power Produksi",
            //     "icon" => "Groups",
            //     "info" => "Data Absensi, borongan Harian & Bulanan tim Produksi",
            //     "url"  => "#",
            // ],
            [
                "name" => "Serah Terima Hasil Produksi",
                "icon" => "File_Copy",
                "info" => "Sistem web input Serah Terima Hasil Produksi dengan Tim Warehouse",
                "url"  => route('serahterima.login'),
            ],
            [
                "name" => "Laporan Hasil Produksi",
                "icon" => "description",
                "info" => "Data hasil Produksi harian, Monitoring yield dan Hasil Produksi",
                "url"  => "https://10.60.22.9/index.php/s/bxSpXbMyCxCsQ0D",
            ],
            [
                "name" => "Dashboard Produksi Bulanan",
                "icon" => "monitoring",
                "info" => "Rekap Grafik Performa Produksi",
                "url"  => route('produksi-dashboard.index'),
            ],
            // [
            //     "name" => "OEE",
            //     "icon" => "Add_Chart",
            //     "info" => "(Overall Equipment Effectiveness) Monitoring efektivitas Proses Produksi ",
            //     "url"  => null,
            // ],
            [
                "name" => "Report Harian Bahan Baku Live Birds",
                "icon" => "Assignment_Add",
                "info" => "Laporan Harian Kedatangan LB dari Berat Bersih, Counter LB, Ayam Mati, Susut Berat Bersih to DTA ",
                "url"  => route('lbreport.dashboard'),
            ],
            [
                "name" => "Riwayat Log Aktivitas",
                "icon" => "manage_history",
                "info" => "Log aktivitas seluruh sistem - Khusus Akses IT",
                "url"  => route('it.login'),
            ],
            [
                "name" => "Uniformity Live Birds",
                "icon" => "table_chart",
                "info" => "Sistem input Uniformity Harian, dan pelaporan data Hasil Uniformity tiap Bulan",
                "url"  => route('uniformity.index'),
            ],
                        [
    "name" => "PPIC",
    "icon" => "precision_manufacturing",
    "info" => "Sistem perencanaan, pengendalian, dan pelaporan produksi",
    "url"  => route('ppic.login'),
]
        ];

        // Kalau url null berarti belum aktif -> jadi "#"
        foreach ($production_docs as &$doc) {
            if (empty($doc['url'])) {
                $doc['url'] = '#';
            }
        }

        return view('dashboard', compact('system_phase', 'plant_name', 'version', 'production_docs'));
    }
}