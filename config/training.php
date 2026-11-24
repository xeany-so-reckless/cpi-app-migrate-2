<?php

/**
 * Materi & pertanyaan Evaluasi Penerapan Training Karyawan.
 *
 * - Kunci materi (mis. 'k3') disimpan di database: JANGAN diubah setelah ada data.
 * - Nomor pertanyaan (1, 2, 3, ...) juga disimpan di database: JANGAN diurutkan ulang
 *   atau disisipkan di tengah setelah ada data. Pertanyaan baru ditaruh di paling bawah
 *   dengan nomor berikutnya. Teks pertanyaan boleh diperbaiki kapan saja.
 * - 'wajib' => true : otomatis tercentang & terkunci di form.
 */
return [

    'materi' => [

        // ===================== WAJIB =====================

        'company_profile' => [
            'kode'  => 'A',
            'judul' => 'Company Profile dan Budaya 5R',
            'wajib' => true,
            'pertanyaan' => [
                1 => 'Saya dapat menjelaskan secara umum profil serta bidang usaha perusahaan.',
                2 => 'Saya mengetahui visi, misi, dan tujuan perusahaan dalam menjalankan kegiatan usaha.',
                3 => 'Saya menjaga nama baik perusahaan melalui sikap dan perilaku yang baik selama bekerja.',
                4 => 'Saya menerapkan budaya Ringkas dengan memilah barang yang diperlukan dan tidak diperlukan di area kerja.',
                5 => 'Saya menerapkan budaya Rapi dengan menempatkan peralatan dan barang sesuai tempat yang telah ditentukan.',
                6 => 'Saya menerapkan budaya Resik dengan membersihkan area kerja dan peralatan setelah digunakan.',
                7 => 'Saya menjalankan prosedur masuk area perusahaan (Berpakaian Sopan, Memakai Sepatu)',
                8 => 'Saya mengembalikan peralatan kerja ke tempat semula setelah selesai digunakan.',
                9 => 'Saya ikut menjaga kebersihan, kerapian, dan ketertiban fasilitas umum serta lingkungan perusahaan.',
            ],
        ],

        'sistem_jaminan_halal' => [
            'kode'  => 'B',
            'judul' => 'Sistem Jaminan Halal',
            'wajib' => true,
            'pertanyaan' => [
                1  => 'Saya menjaga kebersihan diri, pakaian kerja, dan perlengkapan yang digunakan selama berada di lingkungan perusahaan.',
                2  => 'Saya menjaga kebersihan area kerja agar tidak terjadi kontaminasi terhadap bahan maupun produk.',
                3  => 'Saya menggunakan peralatan kerja sesuai fungsi dan ketentuan yang berlaku di perusahaan.',
                4  => 'Saya menjaga agar bahan dan produk tidak tercampur dengan sesuatu yang dapat memengaruhi status kehalalannya.',
                5  => 'Saya mengikuti alur kerja dan prosedur yang telah ditetapkan perusahaan untuk menjaga kehalalan produk.',
                6  => 'Saya hanya membawa atau menggunakan bahan dari luar perusahaan dengan persetujuan pihak terkait.',
                7  => 'Saya menjaga kebersihan peralatan dan fasilitas kerja setelah selesai digunakan.',
                8  => 'Saya memastikan bahan atau produk yang ditangani tetap berada pada area dan tempat yang telah ditentukan.',
                9  => 'Saya segera melaporkan kepada atasan apabila menemukan kondisi atau aktivitas yang berpotensi memengaruhi kehalalan produk.',
                10 => 'Saya mematuhi ketentuan Sistem Jaminan Produk Halal dalam setiap aktivitas pekerjaan sesuai tanggung jawab saya.',
            ],
        ],

        'k3' => [
            'kode'  => 'C',
            'judul' => 'Keselamatan dan Kesehatan Kerja (K3)',
            'wajib' => true,
            'pertanyaan' => [
                1  => 'Saya menggunakan APD sesuai ketentuan saat melakukan pekerjaan di area kerja.',
                2  => 'Saya melakukan pekerjaan sesuai prosedur keselamatan kerja yang berlaku.',
                3  => 'Saya menjaga kebersihan dan kerapian area kerja untuk mencegah kecelakaan.',
                4  => 'Saya menggunakan peralatan kerja sesuai cara penggunaan yang aman.',
                5  => 'Saya melaporkan kepada atasan apabila menemukan kondisi tidak aman atau potensi bahaya di area kerja.',
                6  => 'Saya mematuhi rambu-rambu keselamatan yang terdapat di area kerja.',
                7  => 'Saya dapat menunjukkan jalur evakuasi serta titik kumpul di area kerja.',
                8  => 'Saya melakukan tindakan sesuai prosedur apabila terjadi kecelakaan kerja atau keadaan darurat.',
                9  => 'Saya menghindari tindakan yang berisiko membahayakan diri sendiri maupun orang lain saat bekerja.',
                10 => 'Saya menerapkan kebiasaan kerja yang aman dalam melaksanakan aktivitas pekerjaan sehari-hari.',
            ],
        ],

        'p3k' => [
            'kode'  => 'D',
            'judul' => 'Pertolongan Pertama pada Kecelakaan (P3K)',
            'wajib' => true,
            'pertanyaan' => [
                1 => 'Saya dapat menunjukkan lokasi kotak P3K yang tersedia di area perusahaan.',
                2 => 'Saya segera melaporkan kepada atasan apabila menemukan rekan kerja yang mengalami kecelakaan atau kondisi darurat.',
                3 => 'Saya memastikan kondisi lingkungan sekitar aman sebelum mendekati korban kecelakaan.',
                4 => 'Saya segera meminta bantuan rekan kerja atau atasan ketika menemukan korban yang membutuhkan pertolongan.',
                5 => 'Saya membiarkan korban yang diduga mengalami cedera serius tetap di tempat, kecuali terdapat bahaya langsung di lokasi kejadian.',
                6 => 'Saya menggunakan sarung tangan atau pelindung yang tersedia sebelum memberikan bantuan kepada korban.',
                7 => 'Saya hanya memberikan obat atau melakukan penanganan luka yang sudah saya pahami atau pernah diajarkan.',
                8 => 'Saya membantu memastikan korban mendapatkan pertolongan medis lebih lanjut apabila kondisinya membutuhkan penanganan tenaga medis.',
            ],
        ],

        'product_knowledge' => [
            'kode'  => 'E',
            'judul' => 'Product Knowledge',
            'wajib' => true,
            'pertanyaan' => [
                1 => 'Saya dapat menyebutkan jenis-jenis produk yang dihasilkan perusahaan.',
                2 => 'Saya dapat mengenali produk ayam berdasarkan bentuk fisiknya.',
                3 => 'Saya dapat membedakan produk ayam utuh dan produk cut up.',
                4 => 'Saya dapat menyebutkan nama atau istilah produk sesuai penyebutan di perusahaan.',
                5 => 'Saya dapat mengenali produk berdasarkan contoh gambar atau produk yang ditunjukkan.',
                6 => 'Saya dapat mengenali perbedaan antara satu jenis produk dengan jenis produk lainnya.',
            ],
        ],

        'gmp' => [
            'kode'  => 'F',
            'judul' => 'GMP, Higiene Sanitasi & Food Safety',
            'wajib' => true,
            'pertanyaan' => [
                1  => 'Saya mencuci tangan, alas kaki, dan perlengkapan kerja sesuai prosedur sanitasi sebelum memasuki area produksi dan keluar area produksi.',
                2  => 'Saya menggunakan pakaian kerja dan perlengkapan pelindung sesuai ketentuan perusahaan.',
                3  => 'Saya menjaga kebersihan diri selama berada di lingkungan perusahaan.',
                4  => 'Saya mematuhi larangan makan, minum, merokok, dan meludah di area yang dilarang oleh perusahaan.',
                5  => 'Saya menjaga kebersihan dan kerapian area kerja selama melakukan aktivitas pekerjaan.',
                6  => 'Saya membersihkan peralatan kerja setelah selesai digunakan sesuai ketentuan perusahaan.',
                7  => 'Saya membuang sampah pada tempat yang telah disediakan.',
                8  => 'Saya meninggalkan barang pribadi atau benda yang berpotensi mencemari produk (seperti aksesoris) di luar area produksi.',
                9  => 'Saya menggunakan APD (seragam, masker, penutup kepala) di area produksi.',
                10 => 'Saya melaporkan kepada atasan apabila menemukan kondisi lingkungan, peralatan, atau fasilitas kerja yang kotor dan berpotensi mencemari produk.',
            ],
        ],

        // ===================== PILIHAN =====================

        'animal_welfare' => [
            'kode'  => 'G',
            'judul' => 'Animal Welfare',
            'wajib' => false,
            'pertanyaan' => [
                1 => 'Saya memperlakukan ayam dengan baik dan menghindari tindakan kasar selama berada di area kerja.',
                2 => 'Saya menangani ayam dengan hati-hati, tanpa melempar atau membanting, saat melakukan aktivitas pekerjaan.',
                3 => 'Saya menghindari tindakan yang dapat menyebabkan ayam mengalami stres atau cedera.',
                4 => 'Saya hanya melakukan tindakan terhadap ayam sesuai prosedur pekerjaan.',
                5 => 'Saya segera melaporkan kepada atasan apabila menemukan ayam yang mengalami cedera atau kondisi tidak normal.',
                6 => 'Saya mengingatkan atau melaporkan kepada atasan apabila melihat rekan kerja memperlakukan ayam secara kasar atau tidak sesuai prosedur.',
            ],
        ],

        'pembersihan_sanitasi' => [
            'kode'  => 'H',
            'judul' => 'Pembersihan dan Sanitasi',
            'wajib' => false,
            'pertanyaan' => [
                1  => 'Saya mengetahui area dan peralatan yang harus dibersihkan sesuai tanggung jawab masing-masing.',
                2  => 'Saya menggunakan alat kebersihan yang sesuai dengan jenis area dan kebutuhan pembersihan.',
                3  => 'Saya melakukan pembersihan dengan metode dan urutan yang benar sesuai prosedur perusahaan.',
                4  => 'Saya memastikan kotoran, sisa bahan, lemak, dan noda telah dibersihkan sebelum proses sanitasi.',
                5  => 'Saya menggunakan bahan kimia pembersih dan sanitasi sesuai fungsi dan peruntukannya.',
                6  => 'Saya menerapkan praktik pembersihan dan sanitasi sesuai materi yang telah diberikan saat training.',
                7  => 'Saya memastikan area dan peralatan dalam kondisi bersih setelah proses pembersihan dan sanitasi.',
                8  => 'Saya menyimpan bahan kimia dan peralatan kebersihan pada tempat yang telah ditentukan setelah digunakan.',
                9  => 'Saya melakukan pemeriksaan untuk memastikan hasil pembersihan dan sanitasi telah sesuai standar perusahaan.',
                10 => 'Saya segera melakukan perbaikan atau melaporkan apabila ditemukan hasil pembersihan dan sanitasi yang tidak sesuai.',
            ],
        ],

        'kalibrasi' => [
            'kode'  => 'I',
            'judul' => 'Kalibrasi',
            'wajib' => false,
            'pertanyaan' => [
                1 => 'Saya melakukan pemeriksaan kondisi fisik dan kelayakan alat ukur sebelum digunakan.',
                2 => 'Saya menggunakan alat ukur sesuai fungsi dan prosedur penggunaannya.',
                3 => 'Saya memahami cara membaca dan membandingkan hasil pengukuran dengan standar yang ditentukan.',
                4 => 'Saya memastikan alat ukur memiliki identitas dan status kalibrasi yang jelas.',
                5 => 'Saya merawat dan menyimpan alat ukur agar tetap dalam kondisi baik dan akurat.',
                6 => 'Saya melaporkan apabila ditemukan alat ukur yang rusak atau hasil pengukurannya tidak sesuai standar.',
            ],
        ],

        'kebakaran' => [
            'kode'  => 'J',
            'judul' => 'Penanggulangan Kebakaran (APAR dan Hydrant)',
            'wajib' => false,
            'pertanyaan' => [
                1 => 'Saya dapat menunjukkan lokasi penempatan APAR dan hydrant di lingkungan perusahaan.',
                2 => 'Saya mengetahui fungsi dan jenis APAR serta hydrant yang tersedia di perusahaan.',
                3 => 'Saya mengetahui cara menggunakan APAR sesuai prosedur yang telah diajarkan.',
                4 => 'Saya mengetahui fungsi dan komponen utama instalasi hydrant.',
                5 => 'Saya menerapkan praktik penggunaan APAR dan hydrant sesuai materi training.',
                6 => 'Saya mengetahui potensi bahaya dan sumber penyebab kebakaran di area kerja.',
                7 => 'Saya menjaga area kerja agar terhindar dari kondisi yang dapat memicu kebakaran.',
                8 => 'Saya mengetahui tindakan awal yang harus dilakukan apabila menemukan indikasi kebakaran.',
                9 => 'Saya dapat menunjukkan jalur evakuasi dan titik kumpul apabila terjadi kebakaran.',
            ],
        ],

        'metal_detector' => [
            'kode'  => 'K',
            'judul' => 'Metal Detector',
            'wajib' => false,
            'pertanyaan' => [
                1 => 'Saya mengoperasikan metal detector sesuai prosedur kerja yang telah ditetapkan.',
                2 => 'Saya melakukan pemeriksaan kondisi dan kesiapan mesin metal detector sebelum digunakan.',
                3 => 'Saya melakukan pengecekan sensitivitas metal detector menggunakan test piece sesuai standar perusahaan.',
                4 => 'Saya mengetahui fungsi dan penggunaan test piece Fe, Non-Fe, dan Stainless Steel.',
                5 => 'Saya memastikan setiap produk melewati mesin metal detector sesuai prosedur pemeriksaan.',
                6 => 'Saya memperhatikan indikator atau alarm ketika metal detector mendeteksi adanya kontaminasi logam.',
                7 => 'Saya segera melaporkan apabila metal detector mengalami gangguan, alarm tidak berfungsi, atau hasil pengujian tidak sesuai standar.',
            ],
        ],

    ],
];
