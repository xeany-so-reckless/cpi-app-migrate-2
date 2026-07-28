@extends('tally-pro.layout')

@section('title', 'Input Tally')

@push('styles')
<style>
    :root {
        --primary-color: #27ae60;
        --secondary-color: #2c3e50;
        --bg-color: #f4f7f6;
        --text-light: #ecf0f1;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
    }



    .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
    .card h2 { margin-bottom: 20px; color: var(--secondary-color); border-left: 5px solid var(--primary-color); padding-left: 15px; font-size: 1.5rem; }
    .card h3 { color: var(--secondary-color); font-size: 1.3rem; }

    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .full-width { grid-column: span 2; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #555; }
    .form-group input { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; outline: none; transition: border 0.2s; }
    .form-group input:focus { border-color: var(--primary-color); }

    .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; text-align: center; }
    .btn-save {
    background: var(--primary-color);
    color: white;

    width: 220px;       /* Lebar tombol */
    padding: 10px 18px; /* Tinggi tombol */
    font-size: 14px;
    border-radius: 8px;

    display: block;
    margin: 0 auto;     /* Agar berada di tengah */
}
    .btn-export-raw { background: #3498db; color: white; font-size: 0.85rem; }
    .btn-export-rekap { background: var(--warning-color); color: white; font-size: 0.85rem; }
    .btn:disabled { background: #ccc; cursor: not-allowed; }

    .btn-action { padding: 6px 12px; font-size: 0.8rem; font-weight: 600; border-radius: 4px; border: none; cursor: pointer; color: white; transition: 0.2s; }
    .btn-edit { background: var(--warning-color); margin-right: 4px; }
    .btn-edit:hover { background: #d35400; }
    .btn-delete { background: var(--danger-color); }
    .btn-delete:hover { background: #c0392b; }

    .table-container { overflow-x: auto; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 14px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #f8f9fa; color: #666; font-weight: 600; }
    .badge-kode { background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .lookup-label { margin-top: 5px; font-size: 0.85rem; color: var(--primary-color); font-style: italic; min-height: 1.2em; }

    .btn-group { display: flex; gap: 10px; }

    /* ===========================
   SWEET ALERT MODERN STYLE
=========================== */

        .modern-popup {
            box-shadow: 0 25px 60px rgba(0,0,0,.18) !important;
        }

        .modern-confirm-btn {
            border-radius: 12px !important;
            padding: 12px 24px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            transition: .25s;
        }

        .modern-confirm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(231,76,60,.35);
        }

        .modern-cancel-btn {
            border-radius: 12px !important;
            padding: 12px 24px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            transition: .25s;
        }

        .modern-cancel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(100,116,139,.25);
        }

        .swal2-icon.swal2-warning {
            border-color: #f39c12 !important;
            color: #f39c12 !important;
        }
</style>
@endpush

@section('content')

<div class="card">
    <h2>Input Data Produksi</h2>
    <form id="prodForm" class="form-grid">
        <div class="form-group full-width">
            <label>Tanggal Produksi</label>
            <input type="date" id="tgl" required>
        </div>

        <div class="form-group">
            <label>Kode Produk</label>
            <input type="text" id="kodeInput" placeholder="Ketik kode..." required autocomplete="off">
            <div id="namaProdukLabel" class="lookup-label"></div>
        </div>
        <div class="form-group">
            <label>Jumlah (Ekor)</label>
            <input type="number" id="ekor" placeholder="0" required>
        </div>
        <div class="form-group full-width">
            <label>Berat Total (Kg) - <small>Ketik 185 untuk 18.5</small></label>
            <input type="text" id="berat" placeholder="0.0" required>
        </div>
        <button type="submit" class="btn btn-save">SIMPAN KE TABEL</button>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <h3>Laporan Produksi</h3>
        <div class="btn-group">
            <button onclick="exportDataTally('raw')" class="btn btn-export-raw" type="button">📥 Export Raw Data (xlsx)</button>
            <button onclick="exportDataTally('rekap')" class="btn btn-export-rekap" type="button">📊 Export Konsolidasi (Rekap)</button>
        </div>
    </div>
    <div class="table-container">
        <table id="reportTable">
            <thead>
                <tr>
                    <th>Tanggal</th>

                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Ekor</th>
                    <th>Berat (kg)</th>
                    <th style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="mainTbody"></tbody>
        </table>
    </div>
</div>

<!-- Canvas tersembunyi, dipakai untuk render QR Code sebelum diselipkan ke Excel -->
<canvas id="qrCanvas" style="display:none;"></canvas>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.0.3/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
    // Master produk sekarang dari database (lihat TallyInputController@index),
    // bukan lagi array hardcode. Struktur disamakan: [kode, nama, defaultEkor]
    const productDatabase = @json($products->map(fn ($p) => [$p['code'], $p['name'], $p['default_ekor']]));

    // Identitas pegawai yang sedang login - dipakai di header Excel Konsolidasi
    const namaPegawaiTally = "{{ auth()->guard('tally')->user()->name }}";
    const kodeBarcodePegawai = "{{ auth()->guard('tally')->user()->employee_code }}";

    let temporaryData = [];

    const tglInput = document.getElementById('tgl');
    const kodeInput = document.getElementById('kodeInput');
    const ekorInput = document.getElementById('ekor');
    const beratInput = document.getElementById('berat');
    const namaLabel = document.getElementById('namaProdukLabel');



    tglInput.value = new Date().toISOString().split('T')[0];

    function checkProductCode(val) {
        const found = productDatabase.find(p => p[0].toString() === val);
        if (found) {
            namaLabel.textContent = "Produk: " + found[1];
            namaLabel.style.color = "#27ae60";
            return found;
        } else {
            namaLabel.textContent = val ? "Kode tidak ditemukan" : "";
            namaLabel.style.color = "#e74c3c";
            return null;
        }
    }

    kodeInput.addEventListener('input', function() {
        const val = this.value.trim();
        const found = checkProductCode(val);
        if (found) {
            ekorInput.value = found[2] || "";
        }
    });

    beratInput.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        if (val.length > 0) {
            let num = parseFloat(val) / 10;
            this.value = num.toFixed(1);
        }
    });

    document.getElementById('prodForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const found = productDatabase.find(p => p[0].toString() === kodeInput.value.trim());
        const nama = found ? found[1] : "Tidak Diketahui";

        const newData = {
            tanggal: tglInput.value,
            kode: kodeInput.value.trim(),
            nama: nama,
            ekor: parseFloat(ekorInput.value) || 0,
            berat: parseFloat(beratInput.value) || 0
        };

        temporaryData.push(newData);
        renderTableTally();

        const currentTgl = tglInput.value;
        this.reset();
        tglInput.value = currentTgl;
        namaLabel.textContent = "";
        kodeInput.focus();
    });

    function renderTableTally() {
        const tbody = document.getElementById('mainTbody');
        let htmlContent = "";
        for (let i = temporaryData.length - 1; i >= 0; i--) {
            const d = temporaryData[i];
            htmlContent += `
                <tr>
                    <td>${d.tanggal}</td>
                    <td><span class="badge-kode">${d.kode}</span></td>
                    <td>${d.nama}</td>
                    <td>${d.ekor}</td>
                    <td>${d.berat.toFixed(1)}</td>
                    <td>
                        <button onclick="editDataTally(${i})" class="btn-action btn-edit" type="button">📝 Edit</button>
                        <button onclick="deleteDataTally(${i})" class="btn-action btn-delete" type="button">❌ Hapus</button>
                    </td>
                </tr>
            `;
        }
        tbody.innerHTML = htmlContent;
    }

    function editDataTally(index) {
        const targetData = temporaryData[index];
        tglInput.value = targetData.tanggal;
        kodeInput.value = targetData.kode;
        ekorInput.value = targetData.ekor;
        beratInput.value = targetData.berat.toFixed(1);

        checkProductCode(targetData.kode);
        temporaryData.splice(index, 1);
        renderTableTally();
        ekorInput.focus();
    }

    function deleteDataTally(index) {
    Swal.fire({
        title: '<span style="font-size:26px;font-weight:700;">Hapus Data?</span>',
        html: `
            <div style="font-size:15px;color:#6b7280;margin-top:8px;">
                Data yang sudah dihapus
                <b style="color:#e74c3c;">tidak dapat dikembalikan</b>.
            </div>
        `,
        icon: 'warning',
        background: '#ffffff',
        color: '#1f2937',
        width: 420,
        padding: '2em',
        borderRadius: '18px',

        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',

        reverseButtons: true,
        focusCancel: true,

        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#64748b',

        buttonsStyling: true,

        showClass: {
            popup: 'animate__animated animate__zoomIn'
        },
        hideClass: {
            popup: 'animate__animated animate__zoomOut'
        },

        customClass: {
            popup: 'modern-popup',
            confirmButton: 'modern-confirm-btn',
            cancelButton: 'modern-cancel-btn'
        }
    }).then((result) => {
        if (result.isConfirmed) {

            temporaryData.splice(index, 1);
            renderTableTally();

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data berhasil dihapus.',
                width: 380,
                background: '#ffffff',
                color: '#1f2937',
                timer: 1700,
                showConfirmButton: false,
                borderRadius: '18px',
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                }
            });
        }
    });
}

    // Generate QR Code dari kode pegawai menggunakan library qrcode-generator.
    // Library ini cuma menghasilkan pola matriks (dark/light per sel), jadi kita
    // gambar sendiri ke canvas lalu convert ke PNG base64 -- lebih terkontrol
    // dan tidak bergantung pada fungsi bawaan yang bisa saja tidak tersedia.
    function generateQRCodeBase64(value) {
        try {
            // qrcode-generator punya bug: typeNumber 0 (auto-detect ukuran) sering
            // gagal untuk panjang data tertentu. Solusinya coba ukuran (typeNumber)
            // secara manual, mulai dari yang terkecil, sampai ketemu yang muat.
            let qr = null;
            for (let typeNumber = 1; typeNumber <= 40; typeNumber++) {
                try {
                    qr = qrcode(typeNumber, 'M');
                    qr.addData(String(value));
                    qr.make();
                    break;
                } catch (innerErr) {
                    qr = null; // ukuran ini belum cukup / tidak cocok, coba ukuran berikutnya
                }
            }

            if (!qr) {
                console.error("Gagal generate QR Code: tidak ada ukuran yang cocok");
                return null;
            }

            const moduleCount = qr.getModuleCount();
            const cellSize = 6;   // px per modul QR
            const margin = 2;     // jumlah modul kosong di pinggir
            const size = (moduleCount + margin * 2) * cellSize;

            const canvas = document.getElementById('qrCanvas');
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, size, size);
            ctx.fillStyle = '#000000';

            for (let row = 0; row < moduleCount; row++) {
                for (let col = 0; col < moduleCount; col++) {
                    if (qr.isDark(row, col)) {
                        ctx.fillRect(
                            (col + margin) * cellSize,
                            (row + margin) * cellSize,
                            cellSize,
                            cellSize
                        );
                    }
                }
            }

            return canvas.toDataURL("image/png");
        } catch (err) {
            console.error("Gagal generate QR Code:", err);
            return null;
        }
    }

    async function exportDataTally(type) {
        if (temporaryData.length === 0) return alert("Belum ada data untuk diekspor!");

        if (type === 'raw') {
            exportRawXlsx();
        } else if (type === 'rekap') {
            await exportRekapXlsxWithBarcode();
        }
    }

    // Export Raw tetap pakai SheetJS (ringan, tidak butuh gambar)
    function exportRawXlsx() {
        const dataToExport = temporaryData.map(d => ({
            "Tanggal": d.tanggal,
            "Kode": d.kode,
            "Nama Produk": d.nama,
            "Ekor": d.ekor,
            "Berat (kg)": d.berat
        }));

        const ws = XLSX.utils.json_to_sheet(dataToExport);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Laporan");
        XLSX.writeFile(wb, `Data_Produksi_Raw_${tglInput.value}.xlsx`);
    }

    // Export Rekap pakai ExcelJS supaya barcode pegawai bisa disisipkan
    // sebagai GAMBAR asli (bisa discan), bukan sekadar teks.
    async function exportRekapXlsxWithBarcode() {
        const rekapObj = {};
        temporaryData.forEach(d => {
            if (!rekapObj[d.kode]) {
                rekapObj[d.kode] = { "Nama Produk": d.nama, "Total Ekor": 0, "Total Berat": 0 };
            }
            rekapObj[d.kode]["Total Ekor"] += d.ekor;
            rekapObj[d.kode]["Total Berat"] += d.berat;
        });

        const rows = [];
        for (let kode in rekapObj) {
            const item = rekapObj[kode];
            rows.push({
                kode,
                nama: item["Nama Produk"],
                totalEkor: item["Total Ekor"],
                totalBerat: parseFloat(item["Total Berat"].toFixed(2)),
                rataRata: parseFloat((item["Total Berat"] / item["Total Ekor"]).toFixed(2))
            });
        }

        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet("Laporan");

        // Header info pegawai di pojok atas
        sheet.getCell('A1').value = "Nama Pegawai";
        sheet.getCell('B1').value = namaPegawaiTally;
        sheet.getCell('A2').value = "Kode Barcode Pegawai";
        // Sel B2 sengaja dikosongkan (tanpa teks) -- QR Code akan ditempel di sini
        // Baris 3 sengaja kosong (spasi bawah QR Code)
        sheet.getCell('A4').value = "Tanggal";
        sheet.getCell('B4').value = tglInput.value;
        // Baris 5 sengaja kosong (break) sebelum tabel data dimulai

        // Perbesar tinggi baris 2 supaya QR Code muat di dalam sel B2
        sheet.getRow(2).height = 70;

        // Sisipkan gambar QR Code LANGSUNG di dalam sel B2, menggantikan teks kode
        const qrBase64 = generateQRCodeBase64(kodeBarcodePegawai);
        if (qrBase64) {
            const imageId = workbook.addImage({
                base64: qrBase64,
                extension: 'png'
            });
            sheet.addImage(imageId, {
                tl: { col: 1, row: 1 },   // top-left: kolom B (index 1), baris 2 (index 1)
                ext: { width: 90, height: 90 } 
            });
        }

        // Tabel data mulai baris ke-6 (setelah break kosong di baris 5)
        const headerRow = 6;
        const headers = ["Kode Produk", "Nama Produk", "Total Ekor", "Total Berat (kg)", "Rata-rata (kg/ek)"];
        headers.forEach((h, idx) => {
            const cell = sheet.getRow(headerRow).getCell(idx + 1);
            cell.value = h;
            cell.font = { bold: true };
        });

        rows.forEach((r, i) => {
            const row = sheet.getRow(headerRow + 1 + i);
            row.getCell(1).value = r.kode;
            row.getCell(2).value = r.nama;
            row.getCell(3).value = r.totalEkor;
            row.getCell(4).value = r.totalBerat;
            row.getCell(5).value = r.rataRata;
        });

        // Lebarkan kolom biar rapi
        sheet.columns.forEach(col => { col.width = 20; });

        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/octet-stream" });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Hasil_Konsolidasi_${tglInput.value}.xlsx`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
</script>
@endpush