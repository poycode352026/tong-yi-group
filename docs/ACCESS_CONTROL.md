# Akses Admin, Superadmin, dan menu

Tanggal: 7 November 2026. Acuan: permintaan pemilik produk dan BRD bagian 5/FR-01. Implementasi menggunakan Gate Laravel, session yang sudah tersedia, dan PostgreSQL; tidak menambah paket otorisasi.

## Peran dan permission awal

- Admin (`is_admin`) dapat masuk ke ruang Admin. Menu serta tindakan program mengikuti permission akun.
- Superadmin (`is_admin` dan `is_superadmin`) memiliki seluruh permission program dan dapat mengatur kewenangan serta menu.
- Permission program yang tersedia: `programs.view` (daftar/ringkasan), `programs.manage` (tambah/ubah/simpan draf), `programs.publish` (terbitkan).
- Mengubah memerlukan izin melihat. Menerbitkan memerlukan izin mengubah dan melihat. Server menolak kombinasi yang tidak valid atau permission yang tidak dikenal.
- Permission khusus pengaturan akses tidak dapat diberikan sebagai string permission program: hanya flag Superadmin yang mengaktifkan Gate `manage-access`.
- Akun tanpa izin melihat program tidak menerima ringkasan/data program di dashboard. Akun dengan izin lihat saja belum memiliki halaman detail program khusus; akses Ubah tetap memerlukan izin mengubah.
- Field kewenangan tidak termasuk mass assignment akun. Route GET maupun mutasi diperiksa pada server. Menyembunyikan tombol bukan pengaman utama.

Ini pembagian akses awal untuk modul yang sudah tersedia, belum matriks akhir Guru, Siswa/Wali, Admin akademik/keuangan, atau role kustom.

## Pengguna & Permission

Superadmin membuka `/admin/settings/users`, mencari akun, lalu memilih Atur akses. Tersedia pemberian/pencabutan akses Admin, peran Superadmin, dan tiga permission program.

Perubahan memerlukan nomor revisi akun yang sesuai. Transaksi dan lock kebijakan akses mencegah penimpaan serta perubahan bersamaan yang meninggalkan sistem tanpa Superadmin. Superadmin tidak dapat mencabut akses Admin/Superadmin miliknya sendiri. Pemeriksaan ulang pelaku di dalam transaksi menolak pelaku yang aksesnya telah dicabut saat menunggu proses.

Perubahan berlaku pada pemeriksaan request berikutnya, termasuk sesi login yang sudah aktif. Akun yang dicabut akses Admin-nya tidak dapat membuka route Admin. Halaman pengaturan belum menyediakan pembuatan akun, hapus akun, atau reset kata sandi; buat akun Admin melalui perintah terminal yang tersedia.

## Manajemen Menu

Superadmin membuka `/admin/settings/menus`. Nama, keterangan, urutan, dan visibilitas menu yang sudah terdaftar dapat diubah. Sidebar dihasilkan dari pengaturan server dan disaring berdasarkan kewenangan akun.

Path dan kewenangan menu berasal dari registry `config/admin.php`, tidak dapat ditulis sebagai URL bebas melalui UI. Menu baru untuk modul yang belum tersedia tidak dibuat lewat pengaturan ini. Dashboard dan dua halaman pengaturan inti tidak dapat disembunyikan untuk mencegah hilangnya jalur administrasi; halaman Superadmin tetap hanya terlihat bagi Superadmin.

Visibilitas menu berbeda dari izin akses. Menyembunyikan Program Akademik hanya mengubah navigasi. Untuk melarang akses route, cabut permission melalui Pengguna & Permission. Urutan memakai posisi dan key sebagai pemecah urutan yang sama.

## Akun awal dan kompatibilitas

Migrasi mempertahankan tiga izin program untuk akun Admin yang sudah ada. Tidak ada akun yang otomatis dinaikkan menjadi Superadmin oleh migrasi. Akun Admin yang dibuat setelah migrasi memiliki permission kosong sampai diberikan oleh Superadmin.

Pemilik produk secara eksplisit memilih akun tongyi yang sedang digunakannya sebagai Superadmin awal. Akun tersebut sudah diverifikasi dan dinaikkan melalui perintah terminal yang mencatat audit. Kata sandi tidak diubah.

Untuk penyiapan lain, operator berwenang dapat menjalankan:

```sh
php artisan admin:create
php artisan admin:superadmin EMAIL_ADMIN_YANG_DIPILIH
```

Perintah kedua hanya menerima akun yang sudah memiliki akses Admin. Jangan menjalankannya untuk semua akun; akses terminal merupakan kewenangan operasional tersendiri.

## Audit dan integritas

`admin_audit_logs` menyimpan aksi, pelaku, target akun bila ada, keadaan sebelum/sesudah, dan waktu. Pemberian Superadmin lewat CLI memakai aksi khusus dengan pelaku null (operator terminal). Tidak mencatat kata sandi. Jika penulisan audit gagal, perubahan pengaturan dibatalkan dalam transaksi yang sama.

`access_revision` pada akun dan `revision` pada menu menolak perubahan dari tab lama. Belum tersedia layar penelusuran audit, MFA, atau alur persetujuan berlapis. Audit aplikasi bukan klaim bahwa administrator database tidak dapat mengubah tabel langsung.

## Verifikasi

- 42 tes backend / 320 assertions lulus; termasuk penolakan Admin biasa, pemberian/pencabutan izin, dependensi permission, proteksi diri Superadmin, konflik akun/menu, rollback jika audit gagal, registrasi URL menu, dan CLI bootstrap.
- Chrome memeriksa pemberian izin lihat, penolakan Ubah, pemberian izin draf tanpa terbit, promosi Superadmin, pencabutan akses sesi aktif, label/urutan/visibilitas menu, serta navigasi seluler.
- Regresi formulir program sampai simpan/publikasi lulus. Tidak ada error JavaScript pada pemeriksaan browser tersebut.
- Kontrol sidebar memakai ikon fungsional kecil, tanpa teks visual Buka/Tutup pada header; label aksesibilitas dan tooltip tetap tersedia. Tombol tutup berada di sudut sidebar dan tombol buka berada di tepi kiri saat sidebar disembunyikan.
- Migrasi lokal diterapkan tanpa reset database. Data browser QA dibuat pada database tes dan dibersihkan setelah selesai.

Acuan resmi: [otorisasi Laravel](https://laravel.com/docs/13.x/authorization).

## Permission sumber belajar — D-45

`learning.manage` memberikan akses ke master acuan, Library Materi, pembuatan/riwayat soal resmi, dan kunci jawaban. Superadmin mengaturnya pada Pengguna & Permission. Izin ini tidak memerlukan izin penerbitan program dan tidak diberikan otomatis kepada Admin biasa. Pemilih sumber pada editor program hanya mengirim metadata versi siap dipakai, tanpa kunci jawaban. Download materi siap dipakai juga tersedia bagi Admin berizin `programs.manage`; download draf tetap khusus `learning.manage`.

## Tampilan Manajemen Menu

Pengaturan memakai satu tabel ringkas dengan kolom pegangan geser, Nama menu, Keterangan, dan Tampilkan. Tombol simpan berada di atas. Menu inti ditandai Wajib; catatan umum ditampilkan sekali. Pada layar kecil, tabel bergulir horizontal di dalam panel. Otorisasi, audit, validasi, serta pemeriksaan revisi tetap memakai alur penyimpanan yang sama.

Urutan menu diatur dengan drag melalui pegangan di kiri baris, bukan input angka. Tabel mengikuti kelompok Utama, Akademik, dan Pengaturan pada sidebar; perpindahan berlaku dalam kelompok. Alternatif keyboard: fokus pegangan, lalu panah atas/bawah. Escape membatalkan drag. Posisi dihitung saat selesai dan baru diterapkan ke server setelah Simpan menu. Pemeriksaan Chrome memverifikasi drag, keyboard, pembatalan, retensi isian, simpan/buka ulang, serta tidak ada overflow halaman pada lebar 390 px.

Tab kerja mengikuti daftar menu berizin; membuka/menutup tab tidak mengubah izin atau data server. Formulir pengaturan mempertahankan isian sementara saat berpindah tab, termasuk revisi awal untuk perlindungan konflik. Menutup tab kotor memerlukan konfirmasi dan membuang isian lokal saja.

## Sistem dan riwayat aktivitas — D-53

Menu **Riwayat Aktivitas** (`/admin/system/activity`) dan **Arsip Level** (`/admin/system/archives`) hanya tersedia untuk Superadmin melalui Gate `manage-access`, termasuk URL detail dan pemulihan. Tidak ada endpoint ubah/hapus riwayat. Tab kerja/visibilitas menu tidak menggantikan pembatasan server.

`admin_audit_logs` diperluas dengan identitas sumber unik, jenis/ID objek, ringkasan, serta hasil aktivitas. Snapshot program dan sumber belajar yang sudah ada diimpor dengan waktu/aktor asli. Perubahan program, versi sumber, pemulihan arsip, dan akun CLI menulis audit dalam transaksi perubahan. Pengaturan hak akses/menu tetap memakai audit transaksional yang sudah ada. Login/logout serta permintaan PDF dicatat; respons web gagal/ditolak mencatat metode, nama route, dan status, tanpa payload login atau isi berkas. Tidak mencatat setiap tombol/tab/ketikan lokal. Jika database audit tidak tersedia ketika menangani kesalahan, respons asli dipertahankan dan kegagalan audit masuk log teknis Laravel.

Detail menampilkan perubahan sebelum/sesudah dan tautan versi sumber. Kunci jawaban/penjelasan soal tidak disalin ke catatan; dapat ditinjau pada versi sumber oleh petugas yang berizin. Kata sandi, token, cookie, dan path berkas disaring rekursif dari catatan baru serta tampilan detail. Akses riwayat tidak dikirim ke Admin biasa. Log teknis Laravel tetap terpisah dari riwayat bisnis yang ditampilkan.

Tampilan waktu/filter tanggal memakai WIB (Asia/Pontianak); penyimpanan waktu tetap UTC. Pencarian mendukung nama pelaku, ringkasan, dan jenis aktivitas. Menu Sistem wajib tersedia bagi role yang berhak dan dapat diurutkan di dalam kelompoknya.
