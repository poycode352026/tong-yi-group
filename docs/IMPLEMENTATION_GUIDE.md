# Panduan Pengembangan Tong Yi Group di VS Code dengan Codex

## 1. Posisi awal

Repositori dimulai dengan dokumentasi kebutuhan. Tahap berikutnya adalah pengembangan sistem secara bertahap bersama pemilik produk melalui VS Code dan Codex. Pembuatan prototipe tambahan bukan prasyarat dan tidak termasuk tugas dokumentasi ini.

Baca berurutan:

1. [BRD](BRD.md).
2. [Catatan keputusan](DECISIONS.md).
3. [Instruksi repositori untuk agent](../AGENTS.md).

Stack telah disepakati oleh pemilik produk: **Laravel + Vue.js + Inertia.js + MySQL** (D-29). Laravel menangani route/controller, logika bisnis, validasi, autentikasi, otorisasi, dan akses data. Vue.js membangun komponen halaman, sementara Inertia.js menghubungkannya dengan Laravel. Data aplikasi disimpan di MySQL.

Versi Laravel, Vue.js, Inertia.js, MySQL, PHP, dan Node.js belum dipin. Saat penyiapan fondasi, pilih versi yang saling kompatibel dan catat kebutuhan runtime serta dependensinya. Penyedia hosting, metode autentikasi, penyimpanan berkas, backup, dan retensi masih perlu dirancang; keputusan stack tidak berarti environment sudah terpasang atau aplikasi sudah dapat dijalankan.

## 2. Langkah awal di VS Code

1. Clone repositori `https://github.com/poycode352026/tong-yi-group.git` dan buka folder tersebut di VS Code.
2. Gunakan Codex pada folder repositori yang sama agar agent dapat membaca instruksi dan dokumentasi.
3. Periksa prasyarat dan siapkan fondasi Laravel + Vue.js + Inertia.js + MySQL sesuai D-29; tetapkan versi yang kompatibel dan konfigurasi pengembangan.
4. Rancang modul program dan model datanya sebelum membangun modul operasional lain.
5. Kerjakan satu perubahan yang dapat diperiksa; cocokkan hasil dengan aturan dan skenario penerimaan.

Jika repositori nantinya sudah berisi aplikasi, baca kode dan instruksi terbarunya sebelum mengubah arsitektur atau memasang dependensi baru.

## 3. Urutan implementasi yang diusulkan

| Tahap | Hasil konkret | Batas dan verifikasi |
|---|---|---|
| 0. Fondasi | Laravel + Vue.js + Inertia.js + MySQL, versi/runtime yang kompatibel, struktur repositori, lingkungan lokal, autentikasi/otorisasi dasar, strategi database/berkas | Aplikasi lokal dapat dijalankan, halaman Vue melalui Inertia tampil, dan koneksi serta migrasi MySQL diverifikasi |
| 1. Admin program | Tabel program, tambah/ubah halaman penuh, subprogram, level, layanan opsional, kurikulum per paket/sesi, draf | AC-01–AC-09; data benar-benar persisten sesuai desain yang dipilih |
| 2. Kualifikasi guru | Profil, dokumen, pemeriksaan izin, perluasan, kesediaan, pelatihan/asesmen | AC-10–AC-12; pembayaran tidak otomatis meluluskan |
| 3. Kelas dan pendaftaran | Izin guru → pengajuan kelas → persetujuan → pendaftaran siswa | Detail layanan/jadwal/lokasi/kuota konsisten dengan katalog; algoritma penempatan menunggu kebijakan |
| 4. Pembayaran/hak sesi | Tagihan, bukti, verifikasi, hak sesi, konfirmasi lanjut, monitoring sisa pembayaran | AC-13–AC-14 dan AC-25; aturan kasus terlambat verifikasi perlu diputuskan |
| 5. Sesi | Materi, buka sesi, presensi, dokumentasi, selesai | AC-15–AC-16; tidak membuat laporan manual terpisah |
| 6. Ujian dan kelanjutan | Bank soal, ujian otomatis, hasil, remedial, pengulangan, sertifikat, tawaran lanjutan/perpindahan | AC-21–AC-24; penerbitan dan perpindahan menunggu detail kebijakan terkait |
| 7. Penghasilan | Perjanjian, rincian klaim, status, bukti transfer, kalender operasional | AC-17–AC-20 dan AC-26; tidak ada transfer otomatis |
| 8. Kesiapan operasional | Audit, notifikasi, keamanan berkas, backup/pemulihan, pengecualian, pengujian peran | Skenario lintas modul, integritas transaksi, dan rencana pemulihan diverifikasi |

Fondasi data untuk pembayaran/kelas dapat disiapkan lebih awal jika diperlukan, tetapi jangan menyatakan modul bisnis selesai sebelum alurnya tersedia dan diuji.

## 4. Fokus pengerjaan pertama: Admin Program

### 4.1 Perilaku antarmuka

- Halaman awal: tabel program, tindakan Tambah Program, dan Ubah.
- Formulir: halaman penuh, bukan modal.
- Satu program dapat menampung beberapa subprogram.
- Subprogram memilih kelompok peserta/variasi dan memiliki level dengan pengaturan independen.
- Layanan ditambahkan hanya jika ditawarkan; jangan tampilkan harga wajib untuk semua kombinasi.
- Kurikulum: jumlah paket × delapan sesi, dengan materi/tujuan/latihan/referensi per sesi.
- Penyimpanan menyeluruh harus konsisten; validasi tidak menghilangkan input.
- Draf dan program yang telah disimpan/diterbitkan harus dibedakan.

### 4.2 Keputusan teknis yang perlu dirancang

- Identitas stabil untuk program, subprogram, level, layanan, paket, dan rencana sesi.
- Hubungan konfigurasi layanan dengan level; hindari kolom tetap `harga_online`, `harga_offline`, `harga_private` sebagai satu-satunya model jika membatasi kebutuhan fleksibel.
- Persistensi draf yang aman; jelaskan apakah lokal atau server dan perilaku saat koneksi gagal/berpindah perangkat.
- Transaksi penyimpanan program dan anak datanya.
- Penanganan perubahan kurikulum/harga yang telah dipakai kelas berjalan.
- Pencegahan duplikasi kombinasi yang tidak sah tanpa mengunci variasi bisnis yang memang diizinkan.
- Kebijakan arsip/hapus dan perlindungan data yang sudah direferensikan transaksi.

Rekomendasi teknis ini bukan skema database final. Dokumentasikan keputusan desain setelah dipilih.

## 5. Siklus kerja setiap perubahan

1. Identifikasi kebutuhan BRD dan keputusan yang terkait.
2. Baca kode/instruksi yang berlaku dan tentukan ruang perubahan yang kecil tetapi utuh.
3. Jika perilaku bergantung pada kebijakan terbuka, tandai dan minta keputusan yang spesifik; kerjakan bagian independen yang sudah jelas.
4. Implementasikan alur dari antarmuka sampai penyimpanan/otorisasi jika tahapnya memang mencakup sistem nyata.
5. Jalankan pemeriksaan yang relevan; untuk aturan penting, uji skenario berhasil dan penolakan.
6. Laporkan hasil, pengujian, batas yang masih ada, dan file yang berubah.
7. Perbarui dokumentasi jika ada keputusan baru; simpan perubahan dengan commit yang jelas.

## 6. Panduan integritas yang perlu dirancang

- Gunakan data harga/perjanjian yang berlaku untuk transaksi, bukan membaca nilai terbaru secara membabi buta saat menghitung riwayat.
- Jangan mengurangi hak sesi hanya karena status presensi tertentu sebelum kebijakannya diputuskan.
- Jangan membuka klaim sebelum delapan sesi memenuhi syarat.
- Cegah pengiriman ulang menimbulkan klaim, pembayaran, pendaftaran, atau sertifikat ganda.
- Simpan hasil setiap level/upaya; jangan menimpa riwayat saat lanjut atau mengulang.
- Bukti transfer belum otomatis berarti terverifikasi; tenggat belum otomatis berarti dibayarkan.
- Pisahkan data kurikulum rencana dari sesi aktual yang telah dilaksanakan.
- Pemeriksaan akses harus dilakukan pada server untuk aplikasi produksi.

## 7. Contoh instruksi awal untuk Codex

> Baca AGENTS.md, docs/BRD.md, docs/DECISIONS.md, dan docs/IMPLEMENTATION_GUIDE.md. Periksa kondisi repositori saat ini. Kita mengembangkan Tong Yi Group secara bertahap di VS Code, dimulai dari Admin Program. Gunakan Laravel + Vue.js + Inertia.js + MySQL sesuai D-29. Jelaskan detail fondasi teknis yang masih perlu diputuskan serta rancangan data/logika modul pertama. Gunakan keputusan yang sudah disepakati, tandai kebijakan terbuka, dan jangan menganggap prototipe atau angka contohnya sebagai spesifikasi final. Setelah detail rancangan yang diperlukan disepakati, implementasikan tahap pertama secara utuh dan verifikasi skenario penerimaannya.

## 8. Definisi selesai untuk suatu tahap

Suatu tahap selesai jika perilaku yang dijanjikan tersedia, data dan hak aksesnya benar sesuai lingkup, skenario relevan lulus, kesalahan utama ditangani, dan batas implementasinya dijelaskan. Halaman yang hanya terlihat lengkap tidak berarti proses bisnisnya selesai.
