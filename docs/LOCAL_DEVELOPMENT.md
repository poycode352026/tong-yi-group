# Fondasi lokal Tong Yi Group

Tanggal verifikasi: 6 November 2026. Aplikasi berada di root repositori; dokumentasi, logo sumber, dan riwayat Git dipertahankan.

## Versi terverifikasi

| Komponen | Versi |
|---|---|
| PHP / Composer | 8.5.12 / 2.10.3 |
| Node.js / npm | 22.23.4 / 10.9.9 |
| PostgreSQL | 18.6 |
| Laravel | 13.40.0 |
| Vue | 3.5.45 |
| Inertia Laravel | 3.7.0 |
| Inertia Vue / Vite | 3.11.0 |
| Tailwind CSS | 4.3.4 |
| Vite / plugin Vue | 8.3.6 / 6.0.10 |
| Laravel Vite plugin | 3.2.1 |

Versi patch kedua adapter Inertia tidak harus sama; keduanya menggunakan protokol mayor 3. Composer dan npm berhasil menyelesaikan dependensi tanpa pengabaian persyaratan platform. PHP 8.5 adalah runtime acuan lokal; lockfile menentukan kebutuhan transitif aktual.

## Dasar kompatibilitas

- [Laravel 13 release notes](https://laravel.com/docs/13.x/releases): mendukung PHP 8.3–8.5.
- [Instalasi Laravel](https://laravel.com/docs/13.x/installation): fondasi menggunakan skeleton resmi `laravel/laravel`.
- [Inertia server](https://inertiajs.com/docs/v3/installation/server-side-setup): adapter Laravel, middleware web, root template Blade.
- [Inertia client](https://inertiajs.com/docs/v3/installation/client-side-setup): Vue 3, plugin Vue, plugin Inertia untuk Vite 7/8, dan `createInertiaApp`.
- [Tailwind dengan Vite](https://tailwindcss.com/docs/installation/using-vite): plugin `@tailwindcss/vite` dan impor CSS.
- [Vite](https://vite.dev/guide/): Node.js 20.19+ atau 22.12+; Node lokal 22.23.4 memenuhi persyaratan.

## Konfigurasi lokal

- Database `tong_yi_group_dev` dibuat setelah memastikan nama belum digunakan; tidak ada database lain diubah atau dihapus.
- Role lokal memakai role PostgreSQL yang tersedia pada mesin; nilai sebenarnya hanya dalam `.env`. `.env.example` sengaja mengosongkan username/password dan APP_KEY.
- Tidak ada akun demo yang dibuat. Seeder bawaan dikosongkan agar tidak menambahkan akun dengan kredensial contoh.
- Migrasi bawaan menyiapkan users, reset password, sessions, cache/locks, jobs/batches/failed jobs. Belum ada tabel bisnis.
- Session, cache, dan antrean memakai database. Mail memakai log; berkas memakai disk lokal. Redis tidak diperlukan.
- `.env`, varian `.env.*` selain `.env.example`, vendor, node_modules, dan hasil build diabaikan Git.
- Logo diimpor langsung dari aset resmi melalui Vite. Token warna awal terpusat pada `resources/css/app.css`; bukan kode merek resmi. Aset aktif diperbarui ke PNG transparan pilihan pemilik produk pada D-32; sumber lama tetap diarsipkan.
- Waktu internal UTC; kebijakan zona waktu operasional tetap terbuka.

## Hasil verifikasi

- Instalasi Composer dan npm berhasil; `composer check-platform-reqs` serta `composer validate --strict` lulus.
- `npm run build` berhasil; npm melaporkan nol kerentanan saat instalasi.
- Tiga migrasi bawaan berhasil pada PostgreSQL; `migrate:status` menunjukkan semuanya telah dijalankan.
- `composer test`: 3 tes, 11 assertions lulus; termasuk render komponen Inertia Home dan kueri PostgreSQL `SELECT 1` melalui Laravel.
- HTTP GET `/` dan `/up` merespons 200. Request Inertia dengan versi aset yang sesuai merespons 200 dan komponen Home.
- Seluruh aset pada manifest build dapat diakses HTTP 200. Hash logo hasil build sama dengan sumber.
- Verifikasi tampilan browser secara visual belum dilakukan; build dan respons HTTP tidak membuktikan seluruh perilaku browser.

## Batas tahap

Fondasi runtime dan integrasi aplikasi tersedia. Login, akun Admin, otorisasi, audit, dan modul Admin Program belum tersedia. Belum ada konfigurasi produksi, backup/pemulihan, atau penyimpanan berkas operasional. Tahap 0 pada panduan belum selesai seluruhnya. Tidak ada push ke GitHub.

Perintah menjalankan dan penyiapan mesin lain ada pada [README](../README.md).

## Pembaruan akses Admin dan database tes

Login dan logout session Laravel tersedia pada `/admin/login` dan POST `/admin/logout`. `/admin` membutuhkan akun berizin Admin. Buat akun operasional melalui `php artisan admin:create`; tidak ada akun bawaan. Lihat README untuk perintah dan batas implementasi.

Database `tong_yi_group_test` dibuat terpisah dari `tong_yi_group_dev`. `.env.testing` lokal berisi konfigurasi koneksi tes dan diabaikan Git; template tersedia pada `.env.testing.example`. `phpunit.xml` memaksa driver PostgreSQL dan nama database tes, serta mengosongkan DB_URL. `tests/TestCase.php` memeriksa database aktual sebelum trait migrasi tes bekerja. Jangan memakai database tes untuk data operasional.

Verifikasi terbaru: 15 tes / 106 assertions lulus, meliputi login, validasi, pembatasan percobaan dan pemulihannya, rotasi session, logout, penolakan non-Admin, pencabutan akses, mass assignment, serta perintah pembuatan akun. Build berhasil, login HTTP 200, tamu dialihkan ke login, dan POST tanpa CSRF menghasilkan 419. Jumlah akun pada database pengembangan tetap nol setelah tes. Tampilan visual browser belum diperiksa. Autentikasi Admin awal menggantikan batas lama yang menyebut login belum tersedia; rincian kewenangan dan batas produksi lainnya tetap berlaku.

Acuan implementasi: [autentikasi Laravel](https://laravel.com/docs/13.x/authentication), [rate limiting Laravel](https://laravel.com/docs/13.x/rate-limiting), dan [form Inertia](https://inertiajs.com/docs/v3/the-basics/forms).
