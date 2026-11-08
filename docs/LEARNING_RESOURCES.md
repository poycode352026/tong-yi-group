# Acuan, pustaka materi, dan latihan

Catatan desain: 7 November 2026. Dokumen membedakan persetujuan pengguna dari usulan yang masih dibahas.

## Acuan Pembelajaran — Admin awal tersedia (D-45)

Admin mengelola master acuan yang fleksibel, misalnya HSK, BCT, HSKK, TOEFL, kurikulum internal, dan acuan bidang lain. Acuan mempunyai daftar tingkatan serta versi/edisi bila diperlukan. Level Tong Yi dapat memilih beberapa acuan dan beberapa tingkatan, misalnya Absolute Beginner–Beginner mencakup materi HSK 1 serta HSK 2. Contoh ini bukan pemetaan akademik resmi dan tidak otomatis menyatakan lulus/sertifikasi.

Awali dari master serta pemetaan level; rincian bab/unit pada paket dan sesi dapat menyusul. Tidak mengunci program pada bahasa atau menganggap semua acuan mempunyai tingkatan numerik.

## Library Materi Tong Yi — Admin awal tersedia (D-45)

Pemilik produk menyetujui library terintegrasi dalam aplikasi/LMS Tong Yi, dengan unggahan PDF referensi dan dukungan tautan eksternal sesuai rancangan sebelumnya. Jenis berkas lain, penyimpanan deployment, kuota, dan kebijakan akses rinci masih perlu ditetapkan. Satu katalog pustaka dapat menampung unggahan dan tautan eksternal. Admin memilih sumber; materi dapat dipakai ulang pada beberapa kurikulum/sesi tanpa mengunggah salinan berulang. Metadata yang dirancang: judul, jenis sumber, acuan/tingkatan, versi, penulis/pengelola, dan sasaran akses (guru/siswa) sesuai kewenangan.

Untuk berkas inti resmi seperti modul/PDF/audio, arah yang diusulkan adalah unggah melalui aplikasi ke penyimpanan privat yang dapat berganti dari lokal pengembangan ke object storage pada deployment. Ini bukan usulan menaruh semua berkas pada database atau direktori public. Hak akses diperiksa server; versi yang sudah digunakan tidak ditimpa tanpa riwayat. Infrastruktur, kapasitas, batas berkas, dan backup masih perlu ditentukan.

Tautan Google Drive dapat dipakai untuk bahan yang sudah ada atau video berukuran besar. Hak akses Drive tetap terpisah dari izin aplikasi; menyimpan tautan tidak otomatis memberi atau mencabut akses Drive. Tidak ada integrasi/sinkronisasi Drive yang sudah dibangun atau disepakati dalam tahap ini.

Acuan teknis: [penyimpanan privat dan URL sementara Laravel](https://laravel.com/docs/13.x/filesystem) serta [pengaturan akses Google Drive](https://support.google.com/drive/answer/2494822).

## Tiga bentuk latihan

1. **Panduan latihan untuk guru — ditegaskan pengguna.** Kolom kurikulum sesi berisi tujuan/harapan, arahan, dan referensi untuk guru saat menyiapkan latihan. Field penyimpanan lama `exercise` dipertahankan, label menjadi Panduan latihan untuk guru. Tidak dianggap soal siap dikerjakan siswa.
2. **Latihan wajib Admin — pembuatan soal tersedia; pengerjaan siswa berikutnya.** Objek latihan terstruktur yang dibuat petugas Admin berwenang dan dikerjakan langsung di sistem, dengan penugasan, jawaban/upaya, dan hasil tersendiri. Lampiran saja tidak otomatis menjadi latihan interaktif. Kunci jawaban harus memiliki akses terpisah dari materi siswa. Penegasan terbaru: ini latihan/soal resmi internal Tong Yi, biasanya ditugaskan per paket, dan dapat berupa latihan akhir level. Admin membuat soal pilihan ganda atau isian di sistem; pengerjaan dan hasil siswa direkap. Aturan pemeriksaan isian, skor/bobot, kesempatan mengulang, tenggat, dan cakupan rekap perlu dirancang. Latihan akhir level tidak otomatis menjadi ujian penentu kelulusan.
3. **Latihan tambahan guru — rancangan.** Guru dapat menyiapkan latihan berdasarkan panduan dan kebutuhan kelas dalam lingkup penugasan. Tidak otomatis menggantikan latihan wajib Admin. Detail fitur/hak penerbitan belum diputuskan.

Paket dan level menjadi cakupan utama penugasan latihan resmi; latihan tidak diwajibkan pada setiap sesi. Sesi tetap dapat menampilkan bahan ajar dari pustaka dan panduan guru, dengan tautan penugasan resmi bila relevan. Pengerjaan latihan tidak otomatis menentukan kelulusan, penerbitan sertifikat, pengurangan hak sesi, atau kelayakan klaim sebelum aturan terkait diputuskan.

## Alur LMS yang dirancang

1. Admin menyiapkan acuan, tingkatan, dan bahan referensi di library.
2. Admin menghubungkan bahan ke program/level/paket/sesi yang relevan; unggahan PDF tetap bahan referensi, bukan otomatis soal interaktif.
3. Admin membuat set latihan resmi dengan soal pilihan ganda/isian, lalu menetapkan cakupannya pada paket atau akhir level.
4. Saat modul kelas/siswa tersedia, siswa memperoleh materi dan penugasan sesuai pendaftaran/kewenangan, mengerjakan di LMS, dan hasil/upayanya direkap.
5. Rekap perlu menyimpan versi soal dan jawaban/upaya agar perubahan soal berikutnya tidak mengubah hasil lama. Kunci jawaban tidak ikut diberikan melalui library siswa.

Layar acuan, library, pembuatan soal, dan pengaitan kurikulum tersedia pada D-45. Penugasan kelas, pengerjaan siswa, serta rekap belum diimplementasikan. Ini pengembangan aplikasi Tong Yi yang sedang dibangun, bukan klaim integrasi dengan LMS eksternal yang belum diidentifikasi.

## Implementasi Admin — 7 November 2026

Tiga halaman tersedia bagi Superadmin atau Admin dengan permission `learning.manage`:

- `/admin/learning/frameworks`: acuan, edisi, dan tingkatan bebas angka/teks.
- `/admin/learning/materials`: PDF privat atau tautan HTTPS, judul, edisi, penulis/pengelola, dan keterangan.
- `/admin/learning/assessments`: latihan akhir paket/level dengan soal pilihan ganda (2–6 pilihan, satu kunci) dan isian beserta acuan pemeriksaan petugas.

Status **Draf** belum dapat dipilih pada program. **Siap dipakai** memvalidasi kelengkapan sumber/soal dan tersedia dalam pemilih kurikulum. Tidak berarti materi telah ditugaskan kepada siswa. Setiap simpan membuat versi baru yang tidak menimpa versi terdahulu. Versi siap terdahulu tetap dapat dipilih walaupun versi terbaru masih draf. Riwayat versi dapat dibuka dari editor; kunci jawaban hanya dapat dibaca petugas dengan permission pengelolaan.

Program mengaitkan UUID versi sumber, bukan versi terbaru yang berubah otomatis:

- Tab **Acuan & materi** pada level: beberapa acuan beserta tingkatan yang dicakup, bahan referensi level, dan latihan akhir level.
- Tab **Kurikulum**: bahan referensi paket dan latihan akhir paket; bahan referensi juga dapat dihubungkan pada sesi.
- Pilihan versi dan tingkatan divalidasi server, termasuk jenis sumber serta kesesuaian cakupan latihan. Semua pengaitan disimpan bersama draf/versi terbit program. Melepas pengaitan pada draf tidak menghapus versi sumber maupun snapshot sebelumnya.

Tabel `learning_resources` menyimpan jenis dan revisi terkini; `learning_resource_versions` menyimpan isi setiap versi, aktor, waktu, serta metadata PDF. Materi/soal belum dapat dihapus permanen. Berkas memakai disk privat `learning` pada `storage/app/private/learning-library`, bukan disk public atau database. Download memeriksa izin setiap permintaan. Editor program menerima metadata aman tanpa kunci jawaban atau path berkas. Admin berizin `programs.manage` dapat mengunduh versi materi siap dipakai; draf hanya pengelola sumber belajar.

Batas PDF lokal **2 MB** menyesuaikan konfigurasi PHP Mac saat ini; ini batas teknis pengembangan, bukan kuota resmi. Berkas pengganti tidak menimpa berkas lama; jika transaksi database gagal, unggahan baru dibersihkan. Tautan eksternal tidak diambil server dan tidak disinkronkan ke Drive. Izin berbagi layanan asal tetap berlaku. Konfigurasi penyimpanan deployment, backup, pemindaian berkas, retensi/arsip, dan kuota operasional belum tersedia.

**Belum dibangun:** penugasan ke kelas/siswa, pengerjaan LMS siswa, pemeriksaan/penilaian hasil, rekap upaya, serta latihan tambahan guru. Modul kelas/pendaftaran dan hak akses siswa belum tersedia. Tidak ada skor, ambang lulus, batas upaya, tenggat, atau perubahan sertifikat/klaim yang ditetapkan oleh implementasi Admin ini. Pengaitan latihan resmi menyimpan rencana latihan wajib per paket/level; belum menerbitkan tugas kepada siswa.

Verifikasi: 50 tes backend/413 assertions, 3 tes formatter, build frontend, migrasi tambahan tanpa reset, dan pemeriksaan Chrome untuk versi acuan, PDF privat, soal PG/isian, pengaitan sampai simpan/buka ulang, serta viewport 320–390 px. Data pengujian menggunakan database terpisah dan dibersihkan setelah pemeriksaan.

## Penyederhanaan isian

Pemilik produk menghapus kebutuhan isian Edisi dari antarmuka acuan/materi/latihan. Formulir, daftar, dan pemilih sumber tidak lagi menampilkan edisi. Metadata lama tetap dipertahankan agar penyederhanaan tampilan tidak menghilangkan data. Revisi penyimpanan serta riwayat versi otomatis tetap berjalan dan berbeda dari isian edisi manual yang dihapus.

## Struktur modul per level — D-47

Koreksi pemilik produk: modul adalah bahan ajar Tong Yi untuk suatu level, kemudian dibagi menjadi materi setiap sesi. Bukan hanya nama baru untuk katalog berkas. Struktur pada Program Akademik menjadi Program → Subprogram → Level → Modul → Paket → Sesi. Acuan menjadi dasar penyusunan modul (misalnya cakupan HSK 1–HSK 2); latihan tetap soal siswa pada akhir paket/level.

Pada level tersedia tab terpisah:

- **Acuan:** memilih dasar penyusunan dan tingkatan yang dicakup.
- **Layanan:** metode, harga, dan kapasitas yang ditawarkan.
- **Modul:** nama/ringkasan modul level, berkas modul bersama, pembagian paket, serta materi sesi. Sesi dapat memilih kembali PDF level/paket tanpa mengunggah salinan, mencatat bagian/bab/halaman, atau memilih berkas khusus sesi. Berkas tambahan paket tetap opsional dan dilipat agar tidak memenuhi halaman.
- **Latihan:** pemilihan latihan akhir level dan akhir paket, dipisahkan dari editor bahan ajar. Panduan latihan guru tetap pada materi sesi.

Menu Modul mengelola unggahan PDF/tautan yang dapat digunakan kembali; penyusunan modul level dan pemakaian berkas per sesi dilakukan dari Program Akademik. Unggahan langsung audio/video belum ditambahkan; tautan HTTPS tetap tersedia. Belum ada penugasan/pengerjaan siswa atau rekap hasil.

Penyimpanan menambahkan field opsional `module_title`, `module_description` pada level serta `module_section` pada sesi. Seluruh pengaitan versi dan isi lama dipertahankan, termasuk pada level arsip. Tidak mewajibkan format satu PDF atau PDF per sesi karena pemilik produk belum memilih; keduanya didukung melalui berkas bersama dan pilihan materi sesi. Ini pembagian bahan ajar, bukan perubahan delapan sesi per paket atau kebijakan akademik lainnya.

Verifikasi: 51 tes backend/423 assertions, build frontend, dan Chrome untuk unggah PDF, gunakan kembali pada dua sesi dengan bab berbeda, pemisahan tab, terbit/buka ulang, tema gelap, serta viewport 320–390 px. Pengujian tidak mengubah program pengguna.

## Dua tab kerja Modul dan Latihan — D-48

Halaman daftar/formulir Modul dan Latihan mempunyai dua tab navigasi bersama. Setiap tab kembali ke daftar atau formulir terakhir yang dibuka. Tautan Modul/Latihan pada sidebar juga menggunakan lokasi tab terakhir. Isian belum tersimpan dipertahankan sementara ketika berpindah di antara kedua tab, termasuk pertanyaan, pilihan/kunci jawaban, kesalahan validasi, posisi gulir, dan objek PDF yang sudah dipilih. Titik oranye menandai tab dengan isian sementara.

Penyimpanan sementara hanya memakai memori tab browser, bukan database atau penyimpanan web. Berpindah tab kerja tidak memunculkan konfirmasi kehilangan isian; refresh/menutup halaman tetap memberikan peringatan browser bila ada isian sementara. Simpan sebelum refresh/menutup browser. PDF belum diunggah sampai tombol Simpan dijalankan. Keluar akun membersihkan ruang kerja; pergantian akun tidak mewarisi isian akun lain. Revisi pada isian yang dipulihkan tetap revisi awal sehingga pemeriksaan konflik server tidak dilewati. Acuan tidak termasuk tab kerja ini.

Pemeriksaan Chrome: pilih PDF → pindah ke Latihan → buat soal/kunci → kembali ke Modul → simpan PDF → ubah kembali → pindah tab → pulihkan seluruh isian → simpan kedua sumber. Alur berjalan tanpa dialog kehilangan isian saat berpindah tab; viewport seluler tidak melebar dan tidak ada kesalahan JavaScript. Build berhasil; tidak ada perubahan skema/otorisasi server.

## Perluasan tab — D-49

Dua tab khusus D-48 diganti tab kerja seluruh menu Admin pada layout bersama. Menu menjadi tab saat dibuka dan memiliki tombol ×. Isian sementara mengikuti tab; menutup tab yang belum disimpan meminta konfirmasi dan membuang hanya isian lokal terkait. Tidak menghapus sumber/program di server. Tab yang ditutup dapat dibuka kembali dari sidebar. Buffer tetap di memori browser, bukan jaminan pemulihan setelah refresh.

## Data awal acuan — D-50

`LearningFrameworkSeeder` menyediakan data awal yang diminta pemilik produk:

- **HSK:** HSK 1, HSK 2, HSK 3, HSK 4, HSK 5, HSK 6.
- **BCT — Business Chinese Test:** BCT A (pembelajar Mandarin bisnis tingkat pemula hingga dasar) dan BCT B (pembelajar tingkat menengah hingga mahir). Keterangan setiap tingkatan dicantumkan dalam keterangan acuan BCT.

Data berstatus siap dipakai sehingga langsung dapat dipilih pada level. Ini data awal sesuai permintaan, bukan pemetaan sertifikasi atau pembatasan agar Admin tidak dapat menambah acuan lain. Seed menggunakan UUID stabil untuk data baru, mengenali acuan bernama sama yang sudah ada, dan tidak menimpa konten/revisi pengguna. Pemanggilan ulang tidak menambah duplikat atau snapshot yang tidak berubah.

Jalankan `php artisan db:seed --class=LearningFrameworkSeeder` atau `php artisan db:seed` setelah mempunyai akun Superadmin. Tidak membuat akun/kredensial otomatis; tanpa Superadmin seed berhenti tanpa data parsial. Pembuat/aktor memakai akun Superadmin yang sudah ada (ID terkecil), dengan catatan audit sumber seed. Pada mesin pengembangan pengguna, HSK yang sudah lengkap dipertahankan pada revisi 2; BCT ditambahkan pada revisi 1. Dua pemanggilan berurutan sudah diperiksa dan hasilnya tetap dua acuan.

Verifikasi: 54 tes backend/440 assertions lulus, termasuk idempotensi, konten tingkatan, perlindungan isian pengguna, dan tidak membuat akun ketika Superadmin belum tersedia.

## Struktur yang berlaku — D-51 (menggantikan alur PDF bersama D-47)

**Materi sesi** adalah isi khusus satu pertemuan. Admin membuka Program Akademik → level → Materi sesi → paket → sesi, mengisi topik/tujuan/materi/panduan guru, lalu memilih PDF. Pemilihan PDF mengunggah dan mengaitkannya sementara ke sesi tersebut. Terbitkan program untuk menyimpan hubungan pada kurikulum. PDF berada di penyimpanan privat dan dapat diunduh dengan izin yang sama seperti materi sebelumnya. Unggahan gagal tidak menghilangkan isian; perpindahan/penutupan tab dan penerbitan ditahan selama unggah berlangsung. Batas lokal tetap 2 MB.

**Modul** adalah bahan lengkap yang mencakup banyak topik, sebagai referensi guru. Menu lama Modul diganti **Pustaka Guru** (hanya label bawaan yang diperbarui); rute teknis tetap `/admin/learning/materials`. Admin mengunggah PDF atau tautan modul lengkap di sana. Bagian Referensi guru pada level/paket dapat memilih modul tersebut. Modul referensi tidak otomatis diberikan sebagai materi sesi atau soal siswa. Daftar pustaka tidak memasukkan unggahan khusus sesi.

Metadata versi bahan: `usage=session` untuk unggahan langsung dari sesi, `usage=reference` untuk modul referensi. Materi sesi dan referensi guru disaring pada pemilih dan diperiksa server. Satu sumber materi tidak dapat baru dihubungkan pada beberapa sesi aktif dalam program yang sama. Versi berbeda dari sumber yang sama tetap dihitung sebagai sumber yang sama. Data tanpa metadata penggunaan diperlakukan sebagai data lama; tidak diberi kategori baru secara diam-diam. Tautan berulang lama dapat dipertahankan tanpa diubah, dan catatan bagian/bab lama ditampilkan hanya untuk dibaca.

Unggahan materi langsung hanya memerlukan izin mengelola program. Pustaka/soal/kunci tetap memakai izin pengelolaan sumber belajar. Unggahan belum dikaitkan ke snapshot program sebelum Terbitkan; menutup tab program tidak otomatis menghapus berkas yang sudah diunggah ke server. Penghapusan/retensi berkas belum tersedia. Tidak ada layanan publik untuk PDF. Portal guru dan akses berdasarkan penugasan mengajar belum dibangun.

Latihan Tong Yi tetap per paket/akhir level. Kolom Panduan latihan untuk guru adalah arahan kurikulum, bukan objek soal guru. Pembuatan latihan tambahan guru dan akumulasi nilai persentase belum diimplementasikan; bobot dan aturan komponen kosong menunggu keputusan. Acuan tetap merupakan data cakupan pembelajaran, bukan slug atau bukti sertifikasi.

Verifikasi D-51: 58 tes backend/489 assertions serta build dan pengujian Chrome hingga terbit/buka ulang, pemisahan Pustaka Guru, PDF berbeda per sesi, referensi level, tiga paket latihan, seluler, dan tema gelap.

D-52 menambahkan pratinjau PDF privat pada materi sesi dan membatasi penempatan baru menjadi satu PDF per sesi. Input unggah hanya muncul jika tidak ada materi; Lepas diperlukan sebelum mengganti. Berkas lama tetap tersimpan dan tidak dihapus otomatis. Materi lama yang berjumlah lebih dari satu tidak dibuang ketika program disimpan tanpa mengubah daftar tersebut. Perubahan hubungan tetap perlu diterbitkan.


## Modul beberapa berkas dan pemulihan tab — D-54

Struktur aktif Pustaka Guru: **acuan → tingkatan → modul → beberapa berkas**. Contoh: HSK → HSK 1 → Modul HSK 1 → buku utama, panduan pengajar, dan referensi tambahan. Acuan/tingkatan dipilih dari data siap dipakai yang sudah dibuat, bukan diketik ulang. Setiap berkas mempunyai nama, fungsi, deskripsi, dan sumber PDF atau tautan HTTPS. Buka/unduh tersedia per PDF. Pengaitan pada kurikulum tetap memakai versi modul tertentu dan tidak berubah otomatis ketika modul diperbarui.

Tabel `learning_resource_files` menyimpan metadata berkas privat yang tidak ditimpa; snapshot modul menyimpan susunan serta metadata setiap berkas. Rute lampiran memeriksa modul pemilik dan izin pengguna. Versi lama dan PDF tunggal terdahulu tetap tersedia. PDF sesi tetap satu bahan khusus pertemuan, diunggah melalui Program Akademik.

Batas pengembangan lokal: 2 MB per PDF dan 6 MB total berkas baru per penyimpanan. Tambahkan berkas berikutnya setelah menyimpan. Penyimpanan produksi/backup dan akses portal guru tetap tahap berikutnya.

Tab dan salinan isian kini memakai `sessionStorage` pada tab browser yang sama. Refresh akibat navigasi/perubahan aset tidak lagi membuang daftar tab. Isian belum tersimpan tetap memerlukan tindakan Simpan/Terbitkan; revisi asal dipertahankan. PDF yang belum diunggah harus dipilih kembali setelah refresh penuh dan nama berkas ditampilkan dalam pemberitahuan. Logout dan penutupan tab kerja membersihkan salinan terkait. Batas memori saja pada D-48/D-49 sudah digantikan.


## Istilah aktif — D-56

Menu dan tab **Latihan** untuk soal Admin kini bernama **Ujian**. Cakupannya **Ujian Akhir Paket** dan **Ujian Akhir Level**. Latihan/panduan guru tetap terpisah pada materi sesi. Istilah terdahulu dalam catatan historis tidak mengubah data soal atau aturan nilai/kelulusan. Rute dan jenis penyimpanan tetap `assessments`.


## Penyimpanan tanpa perubahan — D-57

Simpan tidak selalu membuat versi: server membandingkan judul, status, isi, susunan berkas, serta sumber berkas dengan versi terakhir. Jika sama, tampil pesan **Tidak ada perubahan. Versi tetap sama.** Perbedaan urutan properti JSON/isian opsional kosong tidak membuat versi tambahan. Perubahan nyata pada isi/status/kaitan sumber tetap membuat versi; PDF identik yang diunggah ulang pada berkas modul yang sama dipertahankan. Revisi usang tetap ditolak untuk melindungi perubahan dari tab lain. Riwayat lama tidak dihapus.


## Tujuan ujian — D-59

Formulir Ujian memilih Program, Subprogram, Level, jenis Ujian Akhir Paket/Level, dan Paket bila relevan. Pilihan memakai susunan draf program yang sudah disimpan, bukan level acuan seperti HSK 1. Ringkasan menghitung cakupan dari jumlah sesi paket aktif: misalnya paket kedua dari tiga paket delapan sesi mencakup sesi 9–16 dan diberikan setelah sesi 16. Akhir level mencakup seluruh paket aktif.

Identitas tujuan, nama, dan rentang sesi menjadi snapshot versi soal. Revisi program diperiksa ketika ujian disimpan. Pilihan kurikulum serta validasi server mencegah pengaitan baru ke program/level/paket lain. Riwayat dan kaitan lama tetap tersedia. Simpan ujian tidak otomatis mengubah kurikulum atau menugaskan siswa; hubungkan dari Program Akademik lalu terbitkan.


## Pemilih modul level — D-60

Formulir Materi sesi hanya menampilkan satu pemilih Modul referensi level di bagian atas. Bagian referensi paket dihapus agar tidak ada dua pengisian modul. Kaitan paket lama tetap tersimpan tanpa dihapus saat program disimpan. PDF materi setiap sesi tetap diunggah melalui bagian sesi masing-masing.


## Modul di bawah acuan — D-61

Pada tab Acuan, memilih HSK langsung menampilkan modul HSK di bawahnya. Pilihan tingkatan mempersempit daftar, misalnya HSK 1 hanya menampilkan modul HSK 1. Centang modul yang digunakan; daftar berkas dapat dibuka langsung. Tidak ada lagi pemilih modul terpisah dalam Materi sesi. Kaitan lama tetap ditampilkan dan dipertahankan sampai Admin melepasnya; penerbitan menyimpan perubahan kurikulum.


D-63 memperjelas filter: memilih acuan saja tidak menampilkan bagian Modul. Centang minimal satu tingkatan; modul ditampilkan hanya untuk gabungan tingkatan yang dipilih. Pilihan modul terdahulu ikut difilter pada tampilan tanpa dihapus dari isian/snapshot.


## Struktur aktif — Acuan & Modul per level Tong Yi (D-64)

Pada setiap level, tab **Acuan & Modul** menjadi tempat mengatur dua hal: cakupan pembelajaran (misalnya HSK 1–2) dan kumpulan modul pengajaran (buku HSK, modul internal Tong Yi, panduan, referensi lain). Modul terikat pada program/subprogram/level Tong Yi, bukan wajib satu tingkatan acuan. Filter/penyembunyian modul berdasarkan centang HSK dari D-61–D-63 tidak berlaku lagi.

Admin berizin `learning.manage` dapat menambahkan modul multi-berkas langsung dari tab ini. Program dan level harus sudah tersimpan. Simpan modul menyimpan bahan privat dan menambahkan kaitannya ke isian level; **Terbitkan perubahan** menyimpan kaitan kurikulum. Materi khusus satu pertemuan tetap di tab **Materi sesi** dan tidak berubah menjadi modul level. Ujian tetap akhir paket/level.

Pustaka Guru menampilkan program/subprogram/level pemilik modul; editor memakai pilihan level Tong Yi. Snapshot `level_target` menyimpan identitas dan nama tujuan saat modul disimpan. Server memeriksa revisi program serta hubungan level; modul baru tidak boleh dikaitkan ke level lain. API format lama tetap dapat dibaca; versi dan PDF lama tidak dihapus. Modul lama tanpa kelompok dapat dilengkapi levelnya dari editor Pustaka Guru.

Form unggah langsung menggunakan buffer per level, mengunci navigasi/publikasi saat unggah berjalan, dan menampilkan kesalahan tanpa menghapus isian. File belum diunggah perlu dipilih kembali setelah refresh penuh. Portal guru untuk membaca berdasarkan penugasan belum dibangun; tahap ini pengelolaan Admin.


## Perpindahan tab saat mengedit — D-65

Pindah antar tab kerja atau menu Admin mempertahankan isian tanpa konfirmasi keluar. Buffer program mengikuti perubahan isian langsung. Pemuatan ulang internal Inertia menyimpan buffer sebelum berpindah dan tidak memunculkan peringatan keluar; File belum diunggah tetap mengikuti batas refresh penuh pada D-54. Interaksi awal ditahan sampai antarmuka siap menerima input. Konfirmasi membuang isian tetap berlaku pada tombol ×; pindah tab tidak menyimpan/publikasi otomatis ke server.


D-67 mengoreksi judul D-66: bagian tetap bernama **Acuan & Modul**, subbagian **Modul**. Nama modul yang dipilih ditampilkan pada kartunya, bukan ditempelkan pada judul bagian. Modul pengembangan **Modul HSK 1** telah diganti namanya menjadi **Absolute Beginner - Beginner** sesuai permintaan pemilik produk; isi/berkas lama tetap. Katalog memakai `display_title` untuk nama modul terkini, sementara snapshot historis tetap menyimpan judul aslinya.


D-68 menyederhanakan Program Akademik menjadi **Pilih modul → materi modul tampil**. Pemilih acuan/tingkatan terpisah disembunyikan; metadata acuan mengikuti modul dan ditampilkan sebagai informasi. Kaitan acuan level lama tetap dipertahankan. Isi modul yang dipilih langsung terlihat, termasuk tautan PDF privat. Terbitkan tetap menyimpan kaitan pada kurikulum.


D-69 mengganti unggah inline D-64: **Tambah modul** membuka halaman penuh Pustaka Guru, dengan program/subprogram/level asal terpilih. Program tetap terbuka dalam tabnya dan isian dipertahankan. Setelah modul disimpan, kembali ke Program dan pilih modul tersebut. Panel program hanya memuat pilihan serta materi modul. Buffer inline lama dipindahkan ke editor modul bila belum ada isian lain di tab tambah modul.


## Acuan modul dan berkas — D-70

Acuan diisi pada halaman modul, bukan diulang saat memilih modul pada program. Modul dapat memiliki beberapa acuan/tingkatan, misalnya HSK 1 dan HSK 2, serta penanda Internal Tong Yi. Setiap berkas secara bawaan mengikuti acuan modul. Pilih Acuan khusus berkas ini bila perlu, misalnya buku khusus HSK 1 atau panduan Internal Tong Yi. Acuan khusus menggantikan penandaan induk untuk berkas itu.

Metadata penandaan disimpan dalam snapshot versi; mengubah acuan modul tidak mengubah versi lama atau berkas berpenandaan khusus. Tampilan program/riwayat menampilkan acuan efektif setiap berkas. Acuan dan tingkatannya harus berasal dari master siap dipakai yang sesuai; penanda internal dapat digunakan sendiri. Metadata lama dibaca tanpa memaksakan kategorisasi baru pada seluruh berkas.


## Struktur aktif — Modul umum (D-71)

Menu **Modul** menggantikan Pustaka Guru. Susunan aktif adalah **Kelompok → Modul → Bahan**. Contoh HSK → HSK 1 → buku utama/PDF/daftar kosakata/tautan. Kelompok dapat diisi langsung, misalnya BCT atau Tong Yi; tidak memerlukan pembuatan acuan di menu lain. Checkbox Internal Tong Yi dan pengisian acuan per berkas dihapus dari alur baru.

Modul tidak diikat ke level tertentu saat dibuat. Pada Program Akademik, tab **Modul** dapat memilih beberapa modul sekaligus—misalnya HSK 1 dan BCT A—sebagai referensi pengajaran level itu. Sumber yang sama dapat digunakan di level lain. Materi sesi tetap bahan khusus pertemuan, dan ujian tetap per paket/akhir level.

`module_catalog` menyimpan kelompok, nama, penjelasan/pengelola, serta beberapa bahan. Snapshot baru dari modul lama menyimpan metadata sebelumnya dalam `legacy_metadata`; versi lama, acuan historis, PDF dan kaitan kurikulum tidak dihapus. Pemilihan tetap memakai versi tertentu. Menu Acuan tidak lagi ditampilkan di sidebar/Manajemen Menu; endpoint lama hanya dipertahankan untuk kompatibilitas dan penelusuran data lama.


## Struktur aktif — Modul bernama dengan cakupan (D-72)

Nama modul tetap bebas, misalnya Absolute Beginner - Beginner. Pada halaman modul, Admin mencentang HSK/BCT dan tingkatan yang digunakan—contohnya HSK 1, HSK 2, serta BCT A pada modul yang sama—lalu mengisi bahan PDF/tautan. Cakupan ini menggantikan kolom Kelompok wajib D-71. Menu Acuan tetap tersembunyi; checkbox Internal Tong Yi, target level, dan penandaan acuan per berkas tidak dihadirkan dalam alur baru.

Program Akademik cukup memilih modul. Nama dan cakupan terlihat di dropdown; setelah dipilih, cakupan dan semua bahan tampil otomatis. Satu level dapat memilih beberapa modul dan satu modul dapat dipakai pada beberapa level. Materi sesi dan ujian tetap terpisah. Cakupan disimpan pada snapshot versi, diperiksa terhadap master yang dipilih, serta dinormalisasi agar urutan centang tidak menambah revisi palsu. Data kelompok/acuan historis tidak dihapus.

### Kelola cakupan modul

Pada halaman utama **Modul**, gunakan **Kelola cakupan** untuk menambah acuan (misalnya HSK/BCT), mengubah nama/keterangan, atau menambah, mengubah, dan menghapus tingkatannya. Pengelolaan tetap berada dalam tab Modul, tanpa menu Acuan terpisah.

Cakupan baru/hasil perubahan tersedia pada formulir modul. Modul yang sudah menyimpan cakupan menggunakan snapshot saat dipilih; perubahan master tidak menimpa modul lama. **Hapus** menghilangkan sumber dari pilihan baru, sementara riwayat dan kaitan lama tetap tersimpan. Centang **Tampilkan yang dihapus** untuk memulihkannya. Cakupan yang diarsipkan pada modul lama dapat dilepas, tetapi tingkatannya tidak dapat diubah atau ditambahkan sampai sumber dipulihkan. Simpan tanpa perubahan tidak menambah versi. Semua tindakan memerlukan hak pengelolaan sumber belajar dan perubahan dicatat pada riwayat aktivitas.

Editor Modul menggunakan dropdown **Pilih cakupan**. Hanya cakupan yang sudah dipilih yang tampil bersama tingkatannya. Beberapa cakupan dapat ditambahkan; gunakan **Lepas** untuk mengeluarkannya dari isian. Simpan modul untuk menerapkan perubahan.

Pada setiap PDF atau tautan, dropdown **Cakupan bahan** hanya menawarkan tingkatan yang sudah dicentang pada modul, misalnya HSK 1 dan HSK 2. Pilih satu tingkatan untuk bahan khusus, atau **Mengikuti seluruh cakupan modul** untuk bahan umum. Penandaan khusus yang sudah tersimpan tetap dipertahankan jika cakupan induk kemudian berubah; Admin dapat memilih ulang secara eksplisit.

Riwayat versi tidak ditampilkan pada halaman pengisian sumber belajar. Superadmin dapat menelusuri perubahan melalui **Sistem → Riwayat Aktivitas**, termasuk detail perubahan dan tautan versi sumber. Versi dan berkas terdahulu tetap disimpan untuk menjaga riwayat serta kaitan kurikulum.

### Gambar dan audio pada soal ujian

Setiap pilihan ganda/isian mendukung gambar JPG/PNG/WebP dan audio MP3/WAV/M4A/OGG, maksimal 2 MB per media, total 6 MB sekali simpan. Gunakan pratinjau gambar/pemutar audio untuk memeriksa unggahan. **Lepas gambar/audio** diterapkan setelah Simpan; media versi sebelumnya tetap tersimpan. Penyimpanan privat dan akses pratinjau saat ini khusus petugas dengan hak `learning.manage`; pengiriman ujian/jawaban siswa termasuk tahap LMS berikutnya. Rekaman langsung dari mikrofon belum tersedia.

Ujian Paket 1 lokal berisi 30 soal dari materi sesi 1–8 (22 pilihan ganda, 8 isian), dengan acuan pemeriksaan manual bagi isian. Belum menetapkan skor maupun kelulusan. Soal yang belum memiliki audio tetap berupa soal teks; tidak diklaim sebagai pengujian menyimak atau pelafalan langsung.

Unggahan dibatasi 20 media dan total 6 MB per penyimpanan mengikuti batas lingkungan lokal. Tambahkan media berikutnya setelah menyimpan; jumlah soal tidak dibatasi menjadi 20.

### Ujian otomatis pada Program Akademik

Tujuan program, subprogram, level, serta paket ditetapkan **sekali di editor Ujian**. Ujian siap dipakai pada revisi terkini tampil otomatis di tab Ujian pada Program Akademik: ujian akhir level pada level yang sesuai, ujian akhir paket pada paket yang sesuai. Daftar memuat jumlah soal dan cakupan sesi; **Buka ujian** menuju editor bagi petugas berwenang. Tidak perlu Hubungkan atau menerbitkan ulang program untuk sekadar menampilkan ujian. Beberapa ujian yang mempunyai tujuan sama ditampilkan semuanya. Kaitan manual historis tetap disimpan; ujian lama tanpa tujuan dapat dilengkapi melalui tautan Atur tujuan.

### Perubahan bahan pada modul yang sudah dipilih

Modul referensi level mengikuti **revisi siap dipakai terbaru dari sumber modul yang sama**. Setelah menambah/mengubah bahan dan menyimpan modul, kembali ke Program Akademik untuk melihat perubahan; tidak perlu Lepas, memilih ulang, atau mengunggah berkas yang sama lagi. Katalog diperbarui saat halaman dibuka atau kembali mendapat fokus, dengan isian akademik yang belum disimpan tetap dipertahankan. Kaitan serta snapshot historis tetap disimpan. Perilaku mengikuti bahan terbaru ini berlaku untuk modul referensi level; PDF materi khusus sesi tetap memakai pilihan sesi masing-masing.

Titik kuning pada tab aktif mengikuti perubahan formulir yang sedang dibuka. Setelah simpan berhasil, indikator bersih meskipun ada buffer untuk isian lain pada menu yang sama. Buffer lain tetap dipertahankan, dan menutup tab tetap meminta konfirmasi bila masih ada isian yang belum disimpan.

Status titik kuning formulir yang sedang diwakili suatu tab tetap sama ketika tab aktif, tidak aktif, atau setelah refresh. Status terakhir ikut disimpan dalam workspace. Pemeriksaan penutupan tetap melindungi semua buffer, termasuk isian lain pada menu yang sama; tidak menghapus isian tersebut hanya untuk membersihkan indikator.

### Pratinjau bahan pada editor Modul

Tautan HTTPS memiliki kartu pratinjau ringkas berisi domain/alamat tujuan, judul, serta deskripsi bahan yang diisi Admin. Kartu tidak mengambil isi atau metadata dari situs luar. PDF yang baru dipilih maupun sudah tersimpan tampil langsung melalui pembaca PDF browser. Jika perangkat tidak mendukung tampilan tertanam, gunakan **Buka PDF**; **Unduh PDF tersimpan** tetap tersedia. PDF tersimpan tetap dilindungi endpoint privat dan otorisasi yang sama.
