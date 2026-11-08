# Tong Yi Group

![Logo Tong Yi Group](assets/brand/tong-yi-group-logo.png)

Repositori pengembangan sistem pengelolaan pembelajaran **Tong Yi Group**.

## Mulai dari dokumentasi

| Dokumen | Kegunaan |
|---|---|
| [BRD — Kebutuhan Bisnis](docs/BRD.md) | Tujuan produk, lingkup, peran, aturan bisnis, kebutuhan, dan skenario penerimaan |
| [Catatan Keputusan](docs/DECISIONS.md) | Keputusan disepakati, koreksi yang menggantikan usulan lama, dan detail yang masih terbuka |
| [Panduan Pengembangan](docs/IMPLEMENTATION_GUIDE.md) | Urutan implementasi dan aturan penggunaan BRD oleh pengembang/agent |
| [Identitas Visual](docs/BRAND_GUIDELINES.md) | Logo resmi dan arah tema biru dengan aksen oranye |

**Status:** fondasi aplikasi lokal tersedia, 6 November 2026. Laravel + Vue + Inertia + Tailwind CSS berjalan dengan PostgreSQL. Login/logout dan pembatasan akses Admin tersedia. Modul Admin Program awal tersedia (tabel, formulir, draf, dan versi terbit); aplikasi belum siap produksi.

## Stack teknologi

| Teknologi | Peran |
|---|---|
| Laravel | Logika bisnis, route/controller, validasi, akses basis data, autentikasi, dan otorisasi pada server |
| Vue.js | Antarmuka pengguna berbasis komponen dan formulir interaktif |
| Inertia.js | Menghubungkan halaman Vue dengan route/controller Laravel dan menangani navigasi antarhalaman |
| PostgreSQL | Penyimpanan dan pengolahan data terstruktur |

Keputusan stack tercatat pada [D-29 dan D-30](docs/DECISIONS.md). Versi dependensi dikunci dalam `composer.lock` dan `package-lock.json`. Deployment, rincian kewenangan lintas peran, penyimpanan berkas operasional, backup, dan retensi masih perlu dirancang.

Prioritas awal adalah ruang **Admin Tong Yi**, terutama pengelolaan program. Satu program dapat memiliki beberapa subprogram; setiap subprogram memiliki level/kurikulum dan hanya layanan yang benar-benar ditawarkan. Satu paket belajar normal terdiri dari delapan sesi.

## Prinsip pengembangan

- Jangan mengubah keputusan yang sudah disepakati tanpa mencatat perubahan.
- Jangan menetapkan angka/kebijakan yang masih terbuka secara diam-diam.
- Jangan menganggap pembayaran sebagai bukti kelulusan atau izin mengajar.
- Presensi, dokumentasi, hak sesi, dan klaim harus terintegrasi.
- Bangun dan uji bertahap; cocokkan dengan skenario penerimaan dalam BRD.

## Menjalankan secara lokal

Prasyarat terverifikasi di Mac pengembangan: PHP 8.5.12 (termasuk `pdo_pgsql`), Composer 2.10.3, Node.js 22.23.4, npm 10.9.9, dan PostgreSQL 18.6. Gunakan PHP 8.5 dan Node.js 22.12+ pada lini 22 atau Node.js 24+; persyaratan paket sebenarnya diperiksa oleh Composer/npm.

Database lokal mesin ini: `tong_yi_group_dev`, host `127.0.0.1`, port `5432`. `.env` sudah disiapkan secara lokal dan diabaikan Git; jangan membagikan APP_KEY atau kredensialnya.

Dari root repositori, jalankan seluruh proses pengembangan dengan:

```sh
composer run dev
```

Sebagai alternatif, jalankan Laravel dan Vite di dua terminal:

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

```sh
npm run dev
```

Buka **http://127.0.0.1:8000**. Vite melayani aset pengembangan, bukan alamat utama aplikasi. Hentikan proses dengan Ctrl+C. Jika memakai hasil `npm run build`, cukup jalankan server Laravel tanpa Vite.

### Penyiapan pada mesin lain

1. Jalankan `composer install` dan `npm ci` untuk menggunakan lockfile.
2. Salin `.env.example` menjadi `.env`; isi `DB_USERNAME` dan `DB_PASSWORD` sesuai PostgreSQL lokal.
3. Pilih nama database proyek yang belum ada. Buat dengan `createdb -h 127.0.0.1 -U NAMA_ROLE NAMA_DATABASE`, lalu sesuaikan `DB_DATABASE`. Jika nama sudah ada, periksa dahulu; jangan menimpanya atau memakai `migrate:fresh`.
4. Jalankan `php artisan key:generate`, `php artisan migrate`, dan `npm run build`.
5. Jalankan server sesuai petunjuk di atas.

Session, cache, dan antrean menggunakan PostgreSQL. Redis tidak dibutuhkan untuk fondasi ini. Penyimpanan berkas menggunakan disk lokal; email memakai log. Waktu internal tetap UTC; zona waktu kebijakan operasional belum ditetapkan.

### Verifikasi

```sh
composer check-platform-reqs
composer test
npm run build
php artisan migrate:status
```

Tes menggunakan database PostgreSQL khusus `tong_yi_group_test`. Salin `.env.testing.example` menjadi `.env.testing`, isi role/kredensial lokal, dan jalankan `php artisan key:generate --env=testing` pada penyiapan mesin lain. Database ini harus dibuat dahulu tanpa menimpa database yang sudah ada. Tes akun memakai migrasi dan transaksi di database tes; data di dalamnya boleh dibersihkan otomatis. Pengaman memeriksa nama database aktual sebelum `RefreshDatabase` dijalankan, sehingga tes berhenti bila koneksi mengarah ke database selain `tong_yi_group_test`.

Versi terpasang dan rujukan resmi: lihat [Fondasi Lokal](docs/LOCAL_DEVELOPMENT.md).

## Halaman depan

Halaman `/` berisi pengenalan Tong Yi Group, pengenalan program Mandarin dalam kategori Bahasa beserta variasi Tradisional/Sederhana, alur belajar, dan tanya jawab. Kartu pengenalan belum terhubung ke versi terbit katalog Admin; pendaftaran, harga, dan jadwal belum tersedia. Tautan Portal Admin tersedia di footer.

## Portal Admin

Buka **http://127.0.0.1:8000/admin/login** atau pilih **Portal Admin** di footer halaman depan. Setelah login, `/admin` menampilkan ruang awal Admin. Pengelolaan Program Akademik tersedia pada `/admin/programs`.

Buat akun pertama dari terminal proyek:

```sh
php artisan admin:create
```

Isi nama, email, kata sandi, dan konfirmasinya. Kata sandi tidak ditampilkan di terminal dan tidak dimasukkan lewat argumen shell. Minimum 12 karakter, maksimum 72 byte mengikuti batas bcrypt. Email dinormalisasi ke huruf kecil; email yang sudah digunakan ditolak tanpa mengubah akun lama. Tidak ada akun/kata sandi bawaan atau registrasi Admin publik. Akun operasional belum dibuat otomatis.

Hanya akun berizin `is_admin` yang dapat login di portal ini; tindakan program mengikuti permission, sedangkan pengaturan akses/menu khusus Superadmin. Izin tersebut tidak dapat diisi melalui mass assignment. Route `/admin` memeriksa autentikasi dan otorisasi pada server. Ini merupakan pembatasan akses awal; pemisahan kewenangan akademik/keuangan belum diimplementasikan.

Session diganti setelah login; logout memakai POST dengan CSRF, menghapus session dan mengganti token CSRF. Lima kegagalan login per email/IP membatasi percobaan berikutnya selama sisa jendela 60 detik. Ini parameter teknis keamanan awal, bukan kebijakan akademik. Pemulihan kata sandi mandiri dan MFA belum tersedia.

## Admin Program Akademik

Setelah login, pilih **Program Akademik** atau buka `/admin/programs`. Tersedia tabel dan pencarian program, formulir tambah/ubah halaman penuh, subprogram/level independen, layanan opsional, serta kurikulum delapan sesi per paket. Draf disimpan ke server melalui tombol Simpan draf, terpisah dari versi terbit. Materi paket tetap disimpan ketika jumlah paket aktif dikurangi.

Versi terbit belum dihubungkan ke halaman depan. Hapus/pulihkan level tersedia melalui arsip; penghapusan program/subprogram dan kelas operasional belum tersedia. Detail desain, verifikasi, serta batasnya ada pada [Admin Program](docs/ADMIN_PROGRAM.md).

## Dashboard Admin

Dashboard `/admin` menggunakan sidebar dan ringkasan katalog dari database: total program, draf belum terbit, serta program terbit. Daftar terbaru memuat lima program terakhir diperbarui. Sidebar tersedia pada seluruh halaman Admin dan berubah menjadi menu pada layar kecil.

## Superadmin dan permission

Akun tongyi yang dipilih pemilik produk sudah menjadi Superadmin. Muat ulang ruang Admin untuk melihat **Pengguna & Permission** dan **Manajemen Menu**. Permission program dibedakan menjadi lihat, ubah/simpan draf, dan terbitkan. Admin baru belum memperoleh izin program otomatis; Superadmin memberikannya dari pengaturan akses.

Menu yang disembunyikan tetap berbeda dari akses yang dicabut. Detail, batas, pengujian, dan bootstrap mesin lain ada pada [Kontrol Akses](docs/ACCESS_CONTROL.md).

## Acuan, Library, dan latihan resmi

Superadmin atau Admin berizin pengelolaan sumber belajar menggunakan menu **Modul** dan **Ujian**. Nama modul diisi bebas, misalnya **Absolute Beginner - Beginner**. Di dalam modul, centang acuan seperti HSK atau BCT beserta tingkatannya, lalu tambahkan beberapa bahan PDF/tautan. Tidak perlu membuat kelompok, memilih level tujuan, membuka menu Acuan terpisah, atau memakai checkbox Internal Tong Yi.

Pada Program Akademik, tab **Modul** dapat memilih beberapa referensi sekaligus, misalnya modul dasar dan modul bisnis. Cakupan HSK/BCT beserta tingkatannya langsung terlihat pada pemilih dan bahan modul. Modul bersifat umum dan dapat dipakai kembali pada beberapa level tanpa mengunggah salinan. Tab **Materi sesi** tetap menyimpan PDF khusus tiap pertemuan; tab **Ujian** mengatur akhir paket/level. Simpan modul membuat versi hanya bila isi berubah; Terbitkan program menyimpan kaitan kurikulumnya. Metadata/versi/kaitan lama dipertahankan.

Pembuatan modul dilakukan di halaman penuh. Batas lokal tetap 2 MB per PDF dan 6 MB unggahan per penyimpanan. Modul yang baru dibuat dapat dipilih saat kembali ke tab program tanpa membuang isian yang belum disimpan. Portal guru/siswa, pengerjaan ujian, dan perhitungan nilai gabungan belum tersedia.

Data acuan terdahulu tetap tersedia untuk membaca snapshot/riwayat lama. Acuan awal **HSK 1–6** dan **BCT A/B** tersedia melalui `php artisan db:seed --class=LearningFrameworkSeeder` (juga dipanggil oleh `php artisan db:seed`). Buat/tetapkan Superadmin terlebih dahulu. Seed aman diulang dan tidak menimpa acuan yang sudah ada. Data tersebut sudah diterapkan pada database pengembangan lokal.

## Sistem Superadmin

Buka kelompok **Sistem**: **Riwayat Aktivitas** untuk menelusuri perubahan tersimpan, pelaku, waktu, dan detail sebelum/sesudah; **Arsip Level** untuk mengembalikan level ke draf. Kartu arsip sudah dipindahkan dari formulir program. Pemulihan tidak langsung mengubah versi terbit. Hanya Superadmin yang dapat mengakses kedua halaman, termasuk melalui URL langsung. Riwayat tidak menyimpan kata sandi/token atau setiap ketikan lokal yang belum disimpan.

Pengelolaan acuan HSK/BCT beserta tingkatannya tersedia melalui **Modul → Kelola cakupan**, termasuk tambah, ubah, hapus dari pilihan baru, dan pemulihan. Riwayat/kaitan modul lama tetap dipertahankan.

Editor Ujian mendukung unggah gambar dan audio per soal dengan pratinjau privat. Ujian Paket 1 lokal disusun dari PDF sesi 1–8 dan disimpan melalui validasi/audit aplikasi; soal dan kunci tidak dimasukkan ke seed umum repositori.

Tujuan ujian cukup diatur sekali di editor Ujian. Program Akademik otomatis menampilkan ujian siap dipakai yang sesuai level/paketnya, tanpa pengaitan manual tambahan.

Modul referensi yang dipilih pada level mengikuti isi siap dipakai terbaru dari modul yang sama. Penambahan bahan tidak memerlukan Lepas/pilih ulang; kembali ke halaman akademik untuk memuat katalog terbaru. Materi PDF khusus sesi tetap mengikuti pilihan sesi, dan riwayat versi lama tetap disimpan.
