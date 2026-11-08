# Catatan Keputusan — Tong Yi Group

Versi 0.4 · 6 November 2026. Dibaca bersama [BRD](BRD.md).

## 1. Keputusan yang menjadi acuan

| ID | Keputusan |
|---|---|
| D-01 | Fokus pekerjaan awal adalah Admin Tong Yi dan pengelolaan program |
| D-02 | Halaman Program Akademik berupa tabel; tindakan tambah/ubah melalui halaman penuh |
| D-03 | Satu program memuat beberapa subprogram; pilihan peserta dan Simplified/Traditional dapat berbeda |
| D-04 | Level, paket, kurikulum, harga, dan kapasitas antarsubprogram harus independen |
| D-05 | Layanan ditambahkan sesuai kebutuhan; tidak wajib menyediakan semua kombinasi online/offline dan group/private |
| D-06 | Paket normal delapan sesi; kurikulum dipecah per paket dan per sesi |
| D-07 | Kemampuan guru dibuktikan dan diperiksa; izin mencakup program/level yang disetujui |
| D-08 | Guru dapat menggunakan bukti yang dimiliki atau jalur pelatihan dan asesmen internal Tong Yi |
| D-09 | Membayar/menyelesaikan pelatihan tidak otomatis berarti lulus atau mendapat izin |
| D-10 | Sertifikat kompetensi internal Tong Yi berbeda dari HSK resmi |
| D-11 | Memiliki izin tidak mewajibkan guru membuka semua level; siswa naik tidak otomatis menaikkan izin guru |
| D-12 | Kelanjutan offline mempertimbangkan daerah, jadwal, lokasi, dan guru tersedia; perubahan perlu persetujuan siswa/wali |
| D-13 | Konfirmasi lanjut dilakukan sebelum paket habis; contoh sesi ke-6 sampai sesi ke-7 |
| D-14 | Hak sesi ke-7/ke-8 yang sudah dibayar tetap diberikan; sesi ke-9 membutuhkan pembayaran berikutnya terverifikasi |
| D-15 | Hadir fisik di kelas offline tidak mengaktifkan hak belajar yang belum dibayar |
| D-16 | Semua sesi yang sudah dibayar tetap menjadi hak siswa dan tidak boleh diminta dibayar ulang |
| D-17 | Guru membuka sesi, mengisi presensi dan dokumentasi, lalu menyelesaikan sesi di sistem yang sama |
| D-18 | Tidak ada laporan presensi manual tambahan untuk payroll; sesi belum lengkap belum memenuhi syarat |
| D-19 | Ujian awal pilihan ganda di aplikasi, dinilai otomatis berdasarkan kunci berwenang |
| D-20 | Siswa belum lulus boleh lanjut dengan risiko, remedial, atau mengulang; hasil tiap level tetap dicatat |
| D-21 | Remedial pertama gratis satu kali |
| D-22 | Sertifikat tidak bisa dibeli; lanjut/lulus level atas tidak otomatis meluluskan level bawah |
| D-23 | Mengulang memakai profil yang sama dan tetap melalui pemilihan kelas serta pembayaran |
| D-24 | Perpindahan biasa layanan dan harga sama; perubahan layanan/harga ditangani Admin dengan persetujuan |
| D-25 | Klaim guru per kelas setiap delapan sesi lengkap; data/nominal bersumber dari sistem dan perjanjian |
| D-26 | Klaim yang sama tidak dapat dicairkan dua kali; transfer dilakukan Admin dan dicatat dengan bukti |
| D-27 | Batas pencairan hari operasional berikutnya; Minggu/tanggal merah dikecualikan, Sabtu dihitung |
| D-28 | Pengembangan selanjutnya dilakukan bersama di VS Code menggunakan Codex; dokumentasi menjadi acuan implementasi |
| D-30 | Basis data aplikasi menggunakan PostgreSQL, menggantikan MySQL; versi dan konfigurasi ditetapkan saat penyiapan fondasi |
| D-29 | Keputusan awal stack; pilihan basis data MySQL digantikan D-30. Laravel, Vue.js, dan Inertia.js tetap digunakan |
| D-31 | Logo PNG yang diberikan pemilik produk menjadi logo resmi; tema aplikasi mengikuti biru dan oranye logo, dengan biru utama dan oranye aksen. Aset asli tidak diubah; palet HEX antarmuka merupakan rancangan awal pada BRAND_GUIDELINES.md |

## 2. Koreksi yang menggantikan usulan terdahulu

| Usulan/perilaku terdahulu | Acuan pengganti |
|---|---|
| Satu kotak kurikulum untuk seluruh level | Rencana materi per paket dan masing-masing delapan sesi |
| Semua kolom harga online/offline/private selalu wajib ada | Hanya layanan yang ditambahkan pada subprogram yang diatur |
| Banyak halaman tombol tambah/simpan bertingkat | Tabel program dan halaman kerja program yang terpadu |
| Pop-up modal tambah program | Halaman penuh; rancangan draf mencegah kehilangan isian |
| Menunggu seminggu setelah paket habis untuk konfirmasi | Konfirmasi sebelum paket habis; sesi yang sudah dibayar tetap hak siswa |
| Guru lain datang ke lokasi guru lama sebagai solusi umum | Pilihan kelas sesuai daerah/ketersediaan dan persetujuan siswa |
| Guru mengikuti kenaikan level siswa otomatis | Izin/kompetensi dan kesediaan guru diperiksa terpisah |
| Pemeriksaan laporan presensi manual terpisah sebelum payroll | Presensi/dokumentasi terintegrasi dan syarat sesi selesai |
| Pencairan 1×24 jam tanpa pengecualian | Hari operasional berikutnya, mengecualikan Minggu/tanggal merah |
| Angka 70/30 menjadi default resmi | Menunggu konfirmasi perjanjian; angka itu tidak boleh di-hardcode |
| Prototipe dianggap aplikasi yang harus diteruskan kodenya | Pengembangan bersama melalui VS Code/Codex mengacu pada kebutuhan bisnis |
| Pilihan basis data MySQL pada D-29 | PostgreSQL sesuai keputusan terbaru D-30; Laravel, Vue.js, dan Inertia.js tetap |

## 3. Keputusan terbuka

| ID | Hal yang perlu ditetapkan | Dampak sebelum fitur operasional |
|---|---|---|
| O-01 | Daftar program/subprogram, nama dan urutan level, kurikulum aktual | Data akademik belum dapat dianggap lengkap |
| O-02 | Layanan lain, private/semi-private, durasi, minimum/maksimum aktual | Konfigurasi produk dan pembukaan kelas |
| O-03 | Harga, diskon, pajak/biaya jika ada, pembayaran beberapa paket | Tagihan dan penyesuaian harus mengikuti harga yang benar |
| O-04 | Dokumen wajib, standar kompetensi guru, penilai, asesmen ulang | Persetujuan izin harus punya dasar |
| O-05 | Paket/harga/durasi pelatihan dan mekanisme pembayaran/asesmennya | Operasional pendapatan pelatihan |
| O-06 | Aturan kelas di bawah minimum, pemerataan, kuota aman, waiting list | Tidak boleh mengarang algoritma penempatan |
| O-07 | Relasi akun siswa/wali dan pemberian persetujuan | Hak akses dan siapa yang boleh bertindak |
| O-08 | Batas konfirmasi untuk semua pola jadwal, jam layanan verifikasi | Notifikasi dan akses sesi berikutnya |
| O-09 | Transfer tepat waktu tetapi verifikasi terlambat oleh lembaga | Hak siswa tidak boleh diputus dengan aturan dugaan |
| O-10 | Pelepasan kursi jika siswa tidak merespons | Kapasitas dan siswa pengganti |
| O-11 | Bentuk dokumentasi sesi, tenggat/koreksi, pembatalan/penundaan | Sesi lengkap dan payroll |
| O-12 | Absensi siswa, konsumsi hak sesi, sesi pengganti | Saldo sesi tidak boleh dikurangi berdasarkan dugaan |
| O-13 | Ambang/bobot nilai, aturan ujian, jadwal remedial lanjutan dan biaya | Hasil kelulusan dan sertifikat |
| O-14 | Template, nomor, penandatangan, dan validasi sertifikat | Penerbitan yang sah menurut lembaga |
| O-15 | Harga/diskon pengulangan level | Pembayaran pengulangan |
| O-16 | Rumus prorata/perpindahan dan paket yang sudah sebagian digunakan | Selisih biaya dan sisa nilai |
| O-17 | Prosedur layanan pengganti/refund, pembatasan pembayaran lintas level | Perlindungan hak yang sudah dibayar |
| O-18 | Perjanjian bagi hasil aktual serta perlakuan koreksi/pengecualian | Payroll tidak dapat memakai angka contoh |
| O-19 | Jam batas klaim, sumber kalender libur, zona waktu operasional | Tanggal jatuh tempo pencairan |
| O-20 | Hak koreksi data setelah klaim/pembayaran | Audit dan konsistensi keuangan |
| O-21 | Kanal, penerima, dan frekuensi notifikasi | Pengingat sesuai otorisasi pengguna |
| O-22 | Versi stack/runtime, deployment, konfigurasi PostgreSQL, berkas, detail autentikasi, backup, retensi | Stack mengacu pada D-29 dan D-30; detail implementasi dan kesiapan produksi masih perlu dirancang |
| O-23 | Versi harga/kurikulum dan kapan perubahan berlaku | Riwayat transaksi/kelas harus tetap benar |
| O-24 | Target performa, aksesibilitas terukur, SLA, privasi operasional | Kriteria nonfungsional produksi |

Kebijakan terbuka boleh memiliki field konfigurasi kosong/draf. Keberadaan field tidak berarti fiturnya siap dijalankan dengan nilai default sembarang.

## 4. Format pencatatan perubahan berikutnya

Setiap keputusan baru mencatat: ID, tanggal, keputusan, pihak yang menyetujui, alasan singkat, aturan lama yang diganti, kebutuhan/fitur terdampak, dan skenario penerimaan yang berubah. Perbarui BRD serta panduan terkait dalam perubahan yang sama jika relevan.

## 5. Riwayat perubahan keputusan

### D-29 — Penetapan stack aplikasi

- **Tanggal:** 26 Oktober 2026.
- **Disetujui oleh:** pemilik produk melalui percakapan pengembangan.
- **Keputusan:** menggunakan Laravel, Vue.js, Inertia.js, dan MySQL.
- **Alasan:** mengikuti pilihan teknologi yang dinyatakan langsung oleh pemilik produk sebagai acuan pengembangan.
- **Menggantikan:** status stack belum diputuskan dan usulan sebelumnya yang menggunakan React, Livewire, atau PostgreSQL.
- **Dampak:** fondasi aplikasi dan seluruh modul, dimulai dari Admin Program, menggunakan stack ini. O-22 tetap terbuka untuk detail implementasi dan infrastruktur.
- **Skenario penerimaan:** AC-01–AC-26 tidak berubah; keputusan ini menetapkan teknologi implementasi tanpa mengubah aturan bisnis.
- **Batas keputusan:** versi framework/runtime, penyedia hosting, metode autentikasi, dan strategi berkas/backup belum ditetapkan. Tahun pada uraian referensi teknologi bukan pin versi dependensi.


### D-30 — Perubahan basis data ke PostgreSQL

- **Tanggal:** 6 November 2026.
- **Disetujui oleh:** pemilik produk melalui percakapan pengembangan.
- **Keputusan:** menggunakan PostgreSQL sebagai basis data, menggantikan MySQL pada D-29. Laravel, Vue.js, dan Inertia.js tetap digunakan.
- **Alasan:** mengikuti perubahan pilihan basis data yang dinyatakan langsung oleh pemilik produk.
- **Dampak:** seluruh rancangan data, migrasi, koneksi lokal, dan panduan implementasi menggunakan PostgreSQL. Versi, hosting, backup, dan konfigurasi tetap terbuka pada O-22.
- **Skenario penerimaan:** AC-01–AC-26 tidak berubah; koneksi dan migrasi fondasi diverifikasi menggunakan PostgreSQL.
- **Batas perubahan:** dokumentasi repositori diperbarui; aplikasi belum diimplementasikan. Penyebutan MySQL pada riwayat D-29 dipertahankan sebagai catatan historis.


### D-31 — Logo dan arah tema Tong Yi Group

- **Tanggal:** 6 November 2026.
- **Disetujui oleh:** pemilik produk melalui unggahan logo dan instruksi pembaruan.
- **Keputusan:** menggunakan logo PNG yang diberikan sebagai identitas Tong Yi Group; tema aplikasi memakai warna serupa, yaitu biru utama dan oranye sebagai aksen.
- **Alasan:** menyelaraskan antarmuka dengan identitas yang dipilih pemilik produk.
- **Menggantikan:** catatan logo masih menunggu dan arahan warna biru tanpa acuan aset.
- **Dampak:** aset disimpan pada `assets/brand/tong-yi-group-logo.png`; pedoman ada pada [Identitas Visual](BRAND_GUIDELINES.md). Semua peran mengikuti arah yang sama.
- **Skenario penerimaan:** logo asli tampil dengan proporsi benar; warna antarmuka mengikuti biru/oranye; tabel dan formulir tetap terbaca. AC-01–AC-26 tidak berubah.
- **Batas keputusan:** kode HEX antarmuka adalah rancangan implementasi, belum keputusan kode merek resmi. Tidak membuat aplikasi atau prototipe baru dalam perubahan ini.

## Catatan implementasi fondasi lokal — 6 November 2026

Pelaksanaan D-29–D-31 menggunakan Laravel 13, Vue 3, Inertia 3, Tailwind CSS 4, Vite 8, dan PostgreSQL 18. Versi tepat dikunci dalam lockfile. Detail runtime, sumber resmi, dan verifikasi ada pada [Fondasi Lokal](LOCAL_DEVELOPMENT.md).

O-22 terselesaikan sebagian untuk runtime, koneksi lokal, session/cache/antrean berbasis PostgreSQL, dan penyimpanan lokal. Autentikasi/otorisasi Admin, deployment, penyimpanan operasional, backup, serta retensi tetap terbuka. Tidak ada perubahan kebijakan bisnis atau klaim tahap 0 selesai seluruhnya.

### D-32 — Penggunaan PNG transparan pilihan pemilik produk

- **Tanggal:** 6 November 2026.
- **Disetujui oleh:** pemilik produk melalui lampiran PNG dan instruksi “oke pakai yah”.
- **Keputusan:** gunakan PNG transparan yang dilampirkan sebagai logo aplikasi, tanpa pengeditan tambahan.
- **Menggantikan:** aset aktif berlatar terang pada D-31; arah tema biru/oranye tetap berlaku.
- **Dampak:** `assets/brand/tong-yi-group-logo.png` memakai berkas pilihan pengguna berukuran 1718 × 915 piksel. Sumber sebelumnya disimpan sebagai `tong-yi-group-logo-original.png`. Dimensi intrinsik gambar halaman awal disesuaikan.
- **Verifikasi:** salinan identik dengan lampiran, alpha transparan tersedia, dan build frontend berhasil. Aturan bisnis serta AC-01–AC-26 tidak berubah.

### D-33 — Halaman depan pengenalan sebelum akses Admin

- **Tanggal:** 6 November 2026.
- **Disetujui oleh:** pemilik produk melalui permintaan memulai halaman depan berisi pengenalan dan tampilan program.
- **Keputusan:** dahulukan halaman publik pada `/`: pengenalan Tong Yi Group, gambaran program, alur belajar, dan tanya jawab. Autentikasi serta modul Admin Program dilanjutkan setelah peninjauan halaman depan.
- **Batas:** kartu Mandarin anak/dewasa adalah contoh berlabel, bukan katalog atau kelas operasional. Harga, jadwal, kontak, testimoni, dan statistik tidak dikarang. Tidak ada pendaftaran atau login baru pada perubahan ini.
- **Interaksi:** navigasi bagian, menu seluler, filter kelompok peserta, serta detail program dan tanya jawab yang dapat dibuka/tutup. Logo D-32 dipertahankan.

### D-34 — Penegasan cakupan banyak program dan struktur Mandarin

- **Tanggal:** 6 November 2026.
- **Disetujui oleh:** pemilik produk melalui koreksi halaman depan.
- **Keputusan:** Tong Yi Group dapat memiliki banyak program di berbagai bidang; Admin dapat menambahkan program apa saja. Bahasa adalah salah satu kategori, Mandarin adalah program bahasa saat ini, dan Mandarin Tradisional/Sederhana merupakan variasi di dalam program Mandarin dengan konfigurasi independen.
- **Koreksi:** halaman depan tidak berfokus seolah Tong Yi hanya lembaga bahasa. Kartu Mandarin anak/dewasa dari D-33 diganti pengenalan program Mandarin dan variasinya. Tidak mengarang program nonbahasa yang belum ditetapkan.
- **Dampak:** BRD 7.1 dan pengenalan halaman depan diperbarui. Katalog dinamis melalui Admin belum diimplementasikan pada tahap tampilan ini.

### D-35 — Gerakan ringan halaman depan

- **Tanggal:** 6 November 2026.
- **Permintaan pemilik produk:** scroll navigasi lebih halus dan portal bergerak pelan untuk mencerminkan dunia.
- **Implementasi:** navigasi anchor memakai smooth scroll; cincin portal dan titik orbit berputar perlahan, sementara logo dan teks tetap tegak. Judul tujuan dan hover kartu/tombol memiliki transisi ringan. Gerakan tambahan dinonaktifkan saat `prefers-reduced-motion: reduce`. Tidak mengubah aset logo atau aturan bisnis.

### D-36 — Implementasi akses Admin awal dan isolasi tes

- **Tanggal:** 6 November 2026.
- **Otorisasi:** pemilik produk menyetujui pengerjaan login Admin dan database tes terpisah.
- **Rancangan teknis diterapkan:** session Laravel, login/logout dengan CSRF dan rotasi session, Gate `access-admin`, serta flag `is_admin` bernilai awal false yang tidak dapat diisi melalui mass assignment. Ini bukan penetapan seluruh pembagian role akhir.
- **Akun awal:** perintah interaktif `admin:create`; kata sandi tersembunyi, tidak ada nilai bawaan. Email dinormalisasi; akun dengan email yang sudah ada tidak diubah. Minimum kata sandi 12 karakter dan maksimum 72 byte adalah parameter teknis awal (bcrypt), bukan kebijakan bisnis. Belum membuat akun operasional otomatis.
- **Pembatasan login:** lima kegagalan per pasangan email/IP dalam jendela 60 detik; keberhasilan menghapus penghitung. Parameter teknis awal dapat ditinjau sesuai kebutuhan operasional.
- **Database:** database tes `tong_yi_group_test` dibuat terpisah. Nama database aktual dan driver diperiksa sebelum RefreshDatabase; tes menolak berjalan pada database pengembangan. `.env.testing` lokal diabaikan Git; contoh konfigurasinya tersedia.
- **Perilaku:** tamu dialihkan ke login; akun non-Admin ditolak; Admin mendapat ruang awal dengan identitas akun dan tombol keluar. Tautan portal ada pada footer halaman publik.
- **Verifikasi:** 15 tes dengan 106 assertions lulus; build frontend berhasil; login HTTP 200, dashboard tamu 302 ke login, POST tanpa CSRF 419. Database pengembangan tidak berisi akun tes.
- **Belum tercakup:** modul Program Akademik, pembagian kewenangan akademik/keuangan, audit operasional, pemulihan kata sandi mandiri, MFA, dan kesiapan produksi. FR-01 baru terpenuhi untuk akses Admin awal.

### D-37 — Implementasi awal katalog Admin Program

- **Tanggal:** 6 November 2026.
- **Otorisasi:** pemilik produk meminta melanjutkan bagian Admin setelah login selesai.
- **Rancangan teknis diterapkan:** tabel program dan formulir terpadu, draf server dan versi terbit terpisah, agregat JSONB dengan UUID stabil pada tiap bagian, snapshot audit per penyimpanan, transaksi, serta konflik revisi untuk mencegah penimpaan lintas tab. Detail ada pada [Admin Program](ADMIN_PROGRAM.md).
- **Perilaku:** program/kategori fleksibel, layanan hanya ditambahkan sesuai kebutuhan, kurikulum delapan sesi per paket. Jumlah paket aktif dapat dikurangi tanpa menghapus paket/materi tersimpan. Draf disimpan lewat tindakan eksplisit; kembali dari formulir menyimpan draf terlebih dahulu bila ada perubahan. Salinan sessionStorage per Admin/program membantu pemulihan pada tab yang sama, terpisah dari draf server dan tidak menggantikan penyimpanan server.
- **Batas:** kategori/program nonbahasa tidak dikunci. Nama kelompok peserta, variasi, dan jenis layanan adalah isian bebas dengan saran. Metode awal Online/Offline. Tidak menetapkan tarif, kapasitas resmi, atau dasar tagihan. Batas 100 paket per level adalah batas teknis awal, bukan keputusan akademik.
- **Perlindungan:** penghapusan/pemindahan bagian yang sudah tersimpan ditolak; pembatalan bagian baru memerlukan konfirmasi. Arsip/hapus, integrasi kelas/transaksi, serta katalog publik masih tahap berikutnya. Terbit tidak berarti kelas terbuka atau pemeriksaan akademik final selesai.
- **Verifikasi:** 26 tes/190 assertions lulus, build berhasil, migrasi PostgreSQL diterapkan tanpa reset. Pemeriksaan visual/interaksi browser belum dilakukan.

### D-38 — Perapian ruang kerja formulir program

- **Tanggal:** 6 November 2026.
- **Permintaan pemilik produk:** formulir dianggap bertumpuk dan sulit digunakan; diminta tampilan lebih rapi dan halus.
- **Implementasi:** navigasi bagian di samping (select pada seluler), satu subprogram dan level terpilih, area Layanan/Kurikulum, serta editor satu sesi per pilihan paket. Header dipadatkan, identitas subprogram tersimpan dapat dilipat, pembatalan bagian baru ditampilkan sebagai tindakan sekunder, dan kesalahan validasi memakai label yang mudah dibaca dengan fokus ke bagian terkait.
- **Integritas:** satu formulir halaman penuh dengan penyimpanan terpadu; seluruh data tetap dipertahankan saat berpindah bagian. Tidak ada migrasi atau perubahan kebijakan bisnis. Gerak transisi ringan mengikuti prefers-reduced-motion.
- **Verifikasi:** Chrome desktop dan seluler memeriksa navigasi, isian, validasi, simpan/buka ulang draf, publikasi, serta perlindungan materi paket nonaktif. Tidak ada error JavaScript atau overflow horizontal pada viewport seluler 390 px.

## Referensi mendatang — placement test

Pada 7 November 2026 pemilik produk meminta menyimpan screenshot proses analisis sebagai referensi tampilan placement test. Deskripsi dan batas penggunaannya dicatat pada [Referensi Placement Test](references/PLACEMENT_TEST.md). Ini referensi visual, bukan keputusan algoritma/nilai penempatan dan bukan permintaan implementasi saat ini.

### D-39 — Sidebar dan dashboard Admin

- **Tanggal:** 7 November 2026.
- **Permintaan pemilik produk:** ruang Admin perlu lebih profesional dan memiliki sidebar.
- **Implementasi:** sidebar tetap berisi Dashboard dan Program Akademik, logo resmi, tautan halaman depan, identitas akun, dan logout. Header menampilkan konteks halaman. Navigasi seluler memakai menu buka/tutup dengan dukungan Escape dan pengembalian fokus.
- **Dashboard:** jumlah total program, draf belum diterbitkan, dan program yang memiliki versi terbit berasal dari PostgreSQL. Lima program terakhir diperbarui ditampilkan dengan status dan akses Ubah; versi terbit dengan perubahan draf tetap dihitung sebagai program terbit. Tidak ada statistik guru/siswa/keuangan rekaan atau tautan modul yang belum tersedia.
- **Formulir:** navigasi internal subprogram beralih ke pemilih ringkas pada lebar menengah agar tidak terjepit oleh sidebar utama. Data, penyimpanan, dan kebijakan bisnis tetap mengikuti D-37/D-38.
- **Verifikasi:** 27 tes/217 assertions lulus, termasuk penghitungan dashboard dan perubahan draf pada program terbit. Chrome memeriksa sidebar tetap, menu aktif, navigasi desktop/seluler, Escape/fokus, logout, serta regresi formulir sampai simpan/publikasi. Tampilan seluler 390 px tidak melebar; tabel bergulir dalam panel. Tidak ada error JavaScript pada pemeriksaan tersebut.

### D-40 — Format Rupiah dan koreksi salah tambah level

- **Tanggal:** 7 November 2026.
- **Permintaan pemilik produk:** semua input nominal memakai format Rp; level yang tidak sengaja ditambahkan harus dapat dihapus.
- **Implementasi nominal:** komponen Rupiah bersama, awalan Rp, titik ribuan/koma desimal, model/API numerik kanonis berbasis string. Tidak mengubah nilai tarif atau membulatkan nominal.
- **Implementasi level:** tombol Hapus level yang jelas, konfirmasi, pengarsipan objek lengkap di dalam subprogram, serta tombol Kembalikan level. Berlaku untuk level baru/tersimpan. Identitas, materi, dan snapshot terdahulu dipertahankan; draf dan versi terbit tetap terpisah. Menggantikan batas lama bahwa level tersimpan belum dapat dikeluarkan dari daftar aktif.
- **Batas:** bukan penghapusan fisik, bukan perubahan aturan kelas/refund/kelulusan. Program/subprogram belum memiliki penghapusan/arsip.
- **Verifikasi:** 31 tes backend dengan 244 assertions, 3 tes formatter, serta pemeriksaan Chrome untuk nominal dan pengarsipan/pemulihan level berhasil.

### D-41 — Toolbar atas, box konsisten, dan sidebar yang dapat ditutup

- **Tanggal:** 7 November 2026.
- **Permintaan pemilik produk:** Simpan draf dan Terbitkan dipindahkan ke atas, box dirapikan kembali, dan sidebar dapat ditutup/dibuka untuk memperluas ruang kerja.
- **Implementasi:** satu toolbar atas mengikuti scroll; toolbar bawah dihapus. Gaya formulir disatukan dalam stylesheet utama dengan namespace, perataan kiri dan ukuran konsisten. Sidebar desktop memiliki kontrol Buka/Tutup; preferensi lokal diingat browser dan area kerja melebar saat sidebar ditutup. Navigasi seluler tetap memakai Menu.
- **Integritas:** formulir dan data tidak di-reset saat sidebar berubah. Tombol atas mengirim formulir/aksi yang sama; kedua tombol dinonaktifkan selama simpan atau ketika menunggu pemulihan isian. Tidak mengubah aturan bisnis atau penyimpanan.
- **Verifikasi:** build dan pemeriksaan Chrome berhasil, termasuk lebar ruang kerja, toolbar sticky, gaya box, preferensi reload, isian/pemulihan, validasi, simpan/publikasi, serta viewport seluler 320–390 px.

### D-42 — Superadmin, permission, menu, dan kontrol sidebar

- **Tanggal:** 7 November 2026.
- **Permintaan pemilik produk:** pengaturan permission serta menu menjadi tugas Superadmin; kontrol sidebar cukup ikon yang ditempatkan di tepi/sudut sidebar.
- **Pilihan akun awal:** pemilik produk secara eksplisit memilih akun tongyi yang sedang dipakai. Akun tersebut diberi peran Superadmin tanpa mengubah kata sandi. Tidak ada promosi otomatis untuk seluruh Admin.
- **Implementasi:** halaman Pengguna & Permission dan Manajemen Menu; Gate server, tiga permission program, penyaringan navigasi, audit transaksi, revisi optimistis, serta perlindungan diri/Superadmin terakhir. Admin lama mempertahankan akses program; Admin baru memerlukan pemberian permission.
- **Menu:** label, keterangan, urutan, dan visibilitas menu terdaftar dapat diatur. URL/ability tidak dapat ditulis sembarang. Visibilitas tidak menggantikan otorisasi. Menu inti administrasi tidak dapat disembunyikan, tetap disaring sesuai role.
- **Sidebar:** tombol teks pada header dihapus; ikon tutup berada di sudut sidebar dan ikon buka berada di tepi kiri ketika sidebar tersembunyi, tetap dengan label aksesibilitas dan dukungan keyboard.
- **Batas:** belum menetapkan seluruh role bisnis, belum ada role kustom, pembuatan akun web, reset kata sandi, UI audit, atau MFA. Penjelasan dan perintah bootstrap ada pada [Kontrol Akses](ACCESS_CONTROL.md).
- **Verifikasi:** 42 tes backend/320 assertions dan pemeriksaan Chrome permission/menu/sidebar berhasil. Migrasi lokal diterapkan tanpa reset dan perubahan akun awal diaudit.

### D-43 — Acuan Pembelajaran dan pemisahan latihan

- **Tanggal:** 7 November 2026.
- **Disepakati:** pemilik produk menyetujui master Acuan Pembelajaran yang dikelola Admin, pemetaan ke level Tong Yi, serta cakupan tingkatan pilihan (contoh HSK 1 dan HSK 2). Acuan dapat mempunyai versi/edisi; satu level dapat memakai beberapa acuan dan tidak semua tingkatan harus berupa angka. Mulai dari master dan pemetaan level, rincian paket/sesi dapat menyusul. Ini belum diimplementasikan.
- **Batas:** cakupan materi tidak menyatakan kesetaraan/kelulusan ujian atau sertifikasi resmi. Nama level internal tetap independen dari acuan.
- **Penegasan latihan:** latihan wajib buatan Admin harus dapat dikerjakan di sistem dan berbeda dari kolom panduan latihan pada kurikulum sesi. Kolom lama `exercise` diperjelas labelnya menjadi Panduan latihan untuk guru; isi tersimpan tidak dihapus atau dipindah. Panduan membantu guru menyiapkan latihan, bukan objek soal/hasil pengerjaan siswa. Jenis soal, penugasan, penilaian, serta kaitan dengan kelulusan/sesi/klaim masih perlu dirancang.
- **Usulan yang masih dibahas:** satu Library Materi Tong Yi yang dapat menampung unggahan dan tautan eksternal. Lokasi penyimpanan serta kebijakan akses/versi belum disepakati melalui pertanyaan pengguna ini. Rekomendasi dan batas rancangan ada pada [Rancangan Materi dan Latihan](LEARNING_RESOURCES.md).

### D-44 — Latihan resmi per paket/akhir level dan Library dalam LMS

- **Tanggal:** 7 November 2026.
- **Disepakati oleh pemilik produk:** latihan resmi internal Tong Yi dibuat Admin, dikerjakan siswa dalam sistem, dan hasilnya direkap. Biasanya penugasan per paket; dapat pula berupa latihan akhir level. Admin dapat membuat soal pilihan ganda atau isian. Tidak menjadikan latihan resmi wajib ada pada setiap sesi.
- **Library:** pemilik produk menyetujui unggahan PDF referensi dan penghubungannya ke aplikasi/LMS Tong Yi yang sedang dikembangkan, mengikuti arah katalog unggahan/tautan sebelumnya. Ini bukan integrasi dengan LMS eksternal yang telah diidentifikasi.
- **Pemisahan:** panduan latihan guru pada kurikulum bukan set soal resmi. PDF referensi bukan otomatis latihan interaktif. Latihan akhir level tidak otomatis menjadi ujian penentu kelulusan; tidak mengubah aturan sertifikat atau ketentuan ujian akhir yang telah disepakati.
- **Masih terbuka:** pemeriksaan isian, skor/bobot, waktu buka/tenggat, jumlah upaya, format rekap, sasaran akses, kuota/penyimpanan berkas, serta pengaruh latihan terhadap hasil akademik. Tidak menetapkan nilai bawaan akademik.
- **Dampak:** BRD 7.3/7.3.1 dan [Rancangan Materi dan Latihan](LEARNING_RESOURCES.md) diperbarui. Fitur acuan/library/soal/pengerjaan/rekap belum diimplementasikan.

### D-45 — Sumber belajar Admin dan tema biru/oranye

- **Tanggal:** 7 November 2026.
- **Persetujuan:** pemilik produk meminta mulai membangun rancangan D-43/D-44 dan mengurangi dominasi putih dengan perpaduan warna logo yang rapi, elegan, dan halus.
- **Implementasi Admin:** master acuan/tingkatan, Library PDF privat/tautan HTTPS, soal resmi PG/isian dengan cakupan akhir paket/level, status draf/siap dipakai, riwayat versi, serta pengaitan versi ke kurikulum program. Permission baru `learning.manage` diatur Superadmin; akses kunci jawaban terpisah dari metadata pilihan program. Snapshot versi menyimpan aktor/waktu, konflik revisi ditolak, berkas lama tidak ditimpa.
- **Batas tahap:** penugasan kelas/siswa, pengerjaan, pemeriksaan hasil, serta rekap belum dibangun. Tidak menebak kebijakan skor/kelulusan/upaya. Rincian implementasi dan batas teknis PDF lokal 2 MB ada pada [Materi dan Latihan](LEARNING_RESOURCES.md). Menggantikan status “belum diimplementasikan” pada bagian Admin dari D-43/D-44; kebutuhan LMS siswa tetap berikutnya.
- **Tema:** sidebar biru tua, latar biru keabu-abuan, permukaan krem, header panel biru lembut, dan aksen oranye pada bagian aktif/toolbar. Logo asli tidak diubah. Token warna khusus Admin terpusat di `resources/css/admin-theme.css`; halaman publik tetap memakai temanya. Navigasi dikelompokkan ruang kerja lalu Superadmin, dengan urutan pengaturan di dalam kelompok.
- **Verifikasi:** 50 tes backend/413 assertions dan 3 tes formatter lulus; build dan migrasi tambahan berhasil. Chrome memeriksa pembuatan versi, PDF/unduh, PG/isian, pemetaan kurikulum, simpan/buka ulang, serta tidak ada overflow pada layar 320–390 px.

### D-46 — Keseimbangan putih dan warna merek pada Admin

- **Tanggal:** 7 November 2026.
- **Koreksi pemilik produk:** warna D-45 dianggap kurang baik dan terlalu banyak biru; diminta keseimbangan dengan putih.
- **Implementasi:** sidebar/header/panel/toolbar putih, kanvas abu-abu netral, judul gelap, pilihan aktif biru sangat tipis, tombol utama biru, aksen oranye kecil. Gradasi dan krem dihapus. Menggantikan palet Admin D-45, tanpa mengubah layout, data, atau akses.

### D-47 — Modul level dan pembagian materi sesi

- **Tanggal:** 7 November 2026.
- **Klarifikasi pemilik produk:** tiap level mempunyai modul buatan Tong Yi, dan tiap sesi memakai materi dari modul, termasuk PDF. Diminta memperbaiki struktur agar dapat ditinjau kembali.
- **Implementasi:** nama/ringkasan modul pada level; tab Acuan, Layanan, Modul, dan Latihan; pembagian paket/sesi dengan pilihan berkas bersama atau khusus sesi serta catatan bab/halaman. Menu Modul mengelola berkas PDF/tautan; susunan modul level dikerjakan dari Program Akademik. Data dan pengaitan terdahulu dipertahankan.
- **Batas:** tidak menetapkan format berkas wajib, bobot/nilai kelulusan, atau kebijakan tugas. Audio/video unggahan, penugasan siswa, pengerjaan, dan rekap belum dibangun. Acuan tidak sama dengan bahan ajar dan bukan sertifikasi resmi.
- **Verifikasi:** 51 tes backend/423 assertions, build, serta browser desktop/seluler untuk alur PDF level → dua sesi, simpan/publikasi dan buka ulang.

### D-48 — Dua tab kerja Modul dan Latihan

- **Permintaan pemilik produk:** dapat membuka Modul dan Latihan sebagai dua tab dalam aplikasi tanpa membuka browser lain atau kehilangan pekerjaan ketika berpindah.
- **Implementasi:** dua tab navigasi dengan lokasi terakhir, buffer isian sementara di memori browser, retensi PDF/soal/kunci/validasi, serta penanda isian belum disimpan. Sidebar mengikuti lokasi tab terakhir. Logout/pergantian akun membersihkan buffer.
- **Batas:** bukan penyimpanan draf server atau jaminan pemulihan setelah refresh. Tombol Simpan tetap diperlukan; browser memperingatkan sebelum meninggalkan isian sementara. Konflik revisi dan hak akses server tetap berlaku.
- **Verifikasi:** build dan alur Chrome sampai simpan kedua tab, edit setelah simpan, pemilihan PDF, retensi soal/kunci, serta layar seluler berhasil.

### D-49 — Tab kerja seluruh menu dan tombol tutup

- **Permintaan pemilik produk:** setiap menu yang dibuka menjadi tab kerja yang dapat ditutup dengan ×, bukan hanya Modul/Latihan.
- **Implementasi:** satu tab per menu berizin, lokasi halaman terakhir, navigasi sidebar ke tab yang sudah dibuka, tutup aktif/nonaktif, serta konfirmasi membuang isian sebelum menutup tab kotor. Tab aktif kembali ke tab tetangga; saat semua tab ditutup ruang kerja meminta memilih menu. Tidak ada penghapusan data server ketika menutup tab.
- **Retensi isian:** buffer memori diperluas ke Acuan, Program Akademik, Manajemen Menu, dan Pengguna & Hak Akses; PDF/soal tetap dipertahankan. Revisi awal tetap digunakan ketika dipulihkan. Salinan pemulihan program dibersihkan saat tab ditutup secara eksplisit. Logout/pergantian akun membersihkan ruang kerja.
- **Batas:** bukan simpan draf server. Simpan sebelum refresh/menutup browser. Tombol tutup dinonaktifkan selama penyimpanan berlangsung. Hak akses tetap diperiksa server dan tab mengikuti menu yang diizinkan.
- **Verifikasi:** build serta Chrome untuk retensi lintas menu, pembatalan/setuju tutup tab, tab aktif/nonaktif, buka ulang tanpa isian yang dibuang, formulir hak akses/acuan, semua tab ditutup, serta viewport 390 px tanpa overflow dan tanpa kesalahan JavaScript.

### D-50 — Seed HSK dan BCT

- **Permintaan pemilik produk:** sediakan acuan awal agar tidak perlu diinput manual: HSK 1–6, BCT A untuk pemula–dasar, BCT B untuk menengah–mahir.
- **Implementasi:** seeder data awal siap dipakai, dapat dijalankan ulang, tidak membuat akun otomatis, mempertahankan acuan yang sudah ada dan mencatat pembuatan dalam audit.
- **Penerapan lokal:** HSK revisi 2 yang sudah lengkap tidak diubah; BCT ditambahkan beserta tingkatan/keterangan. Seed dijalankan dua kali tanpa duplikat. Data awal tidak membatasi penambahan acuan lain oleh Admin.
- **Verifikasi:** 54 tes backend/440 assertions lulus.

### D-51 — Materi sesi, modul referensi guru, dan latihan

- **Koreksi pemilik produk:** materi satu sesi mempunyai PDF tersendiri; satu PDF tidak menjadi materi banyak sesi. Istilah Modul dipakai untuk bahan lengkap yang mencakup banyak materi dan menjadi referensi mengajar dalam Pustaka Guru. Menggantikan alur PDF bersama lintas sesi pada D-47.
- **Admin yang dibangun:** tab Materi sesi dengan unggah PDF langsung untuk sesi terpilih; Pustaka Guru untuk modul lengkap/tautan referensi; referensi dapat dikaitkan pada level/paket tanpa dijadikan materi sesi. Tab Latihan tetap mengaitkan soal Tong Yi pada akhir paket atau akhir level. Panduan latihan guru tetap terpisah.
- **Integritas:** berkas baru dibedakan dengan metadata penggunaan `session`/`reference`. Materi sesi tidak dapat dipasang sebagai referensi level/paket, dan referensi guru tidak dapat dijadikan materi utama sesi. Pemakaian baru satu sumber materi pada beberapa sesi dalam program ditolak, termasuk memakai versi berbeda dari sumber yang sama. Pengaitan lama yang sudah berulang dipertahankan bila tidak diubah; konten/metadata lama tidak dihapus atau diklasifikasikan ulang diam-diam.
- **Penilaian yang dibahas:** nilai kelak dapat menggabungkan soal Tong Yi, latihan/penilaian guru, dan kehadiran dengan persentase. Bobot, cakupan per paket/level, serta penanganan komponen belum dinilai masih terbuka. Tidak menambahkan angka kebijakan, mesin penilaian, atau status kelulusan pada tahap ini.
- **Batas:** Pustaka Guru pada tahap ini dikelola melalui Admin; portal guru, pembuatan/pengerjaan latihan guru, kehadiran, pengerjaan siswa, rekap, dan perhitungan nilai gabungan belum dibangun.
- **Verifikasi:** 58 tes backend/489 assertions, build, dan Chrome untuk referensi guru, dua PDF sesi berbeda, tiga paket latihan, publikasi/buka ulang, pemisahan pustaka, serta layar 320–390 px/mode gelap berhasil. Migrasi label diterapkan tanpa reset.

### D-52 — Satu PDF per sesi dengan pratinjau

- **Permintaan pemilik produk:** materi yang sudah ada menampilkan pratinjau dan tidak boleh diunggah lagi sampai materi sebelumnya dilepas.
- **Implementasi:** PDF tampil langsung dalam pratinjau, disertai Buka PDF, Unduh PDF, dan Lepas. Input unggah hilang selama ada materi. Melepas perlu konfirmasi, lalu unggah pengganti tersedia. Sesi baru menerima satu PDF; data lama yang sudah memiliki beberapa berkas tidak dibuang dan tetap dapat ditampilkan sampai dilepas.
- **Keamanan:** rute pratinjau privat memakai autentikasi/izin yang sama dengan unduhan, respons PDF inline tanpa cache bersama, embedding hanya dari origin yang sama. Berkas tidak dipublikasikan. Lepas hanya mengubah hubungan kurikulum; berkas/snapshot sebelumnya dipertahankan.
- **Verifikasi:** 60 tes backend/508 assertions dan Chrome dengan PDF valid untuk pratinjau, kunci unggah, batal/setuju Lepas, unggah pengganti, terbit/buka ulang, serta seluler/mode gelap berhasil. Build berhasil.

### D-53 — Sistem Superadmin: riwayat aktivitas dan arsip level

- **Permintaan pemilik produk:** pindahkan informasi pada kartu level yang dikeluarkan dari daftar ke area Sistem khusus Superadmin, dan sediakan pelacakan perubahan/aktivitas.
- **Pembedaan:** kartu lama merupakan arsip pemulihan, bukan log. Kartu di editor dihapus; pengelolaan arsip dipindah ke Sistem → Arsip Level. Admin biasa tetap dapat mengarsipkan level aktif melalui program, tetapi tidak memulihkan/mengubah isi arsip tersimpan. Superadmin mengembalikan level ke draf dengan pemeriksaan revisi; versi terbit tidak ditimpa otomatis.
- **Riwayat:** Sistem → Riwayat Aktivitas menampilkan waktu WIB, pelaku, aktivitas, status, pencarian/filter jenis/tanggal, dan detail perubahan sebelum/sesudah. Versi sumber belajar mempunyai tautan ke snapshot terkait. Catatan akses/menu lama dipertahankan; snapshot program/materi/soal lama dimasukkan ke riwayat memakai waktu/aktor asal.
- **Cakupan:** perubahan program/draf/publikasi/pengarsipan/pemulihan, penyimpanan versi acuan/pustaka/materi/soal, perubahan akses/menu, pembuatan akun lewat terminal, login/logout, permintaan buka/unduh PDF, dan permintaan web yang ditolak/gagal. Perubahan domain dan audit ditulis dalam transaksi yang sama. Permintaan gagal dicatat terpisah setelah respons kesalahan, bila database audit tersedia.
- **Batas:** isian lokal yang belum disimpan dan klik navigasi biasa tidak menjadi perubahan server. Catatan bukan bukti bahwa pengguna selesai membaca/mengunduh PDF. Password, token, path penyimpanan, dan kunci jawaban tidak disalin ke log. Sumber soal tetap dapat ditinjau melalui halaman versi dengan izin yang berlaku. Riwayat teknis sebelum fitur ini yang tidak pernah direkam tidak dapat direkonstruksi.
- **Verifikasi:** 64 tes backend/572 assertions, build, dan Chrome untuk menu Sistem, arsip/pemulihan draf, filter/detail riwayat, penolakan Admin biasa, serta seluler/mode gelap berhasil. Migrasi diterapkan tanpa reset data.


### D-54 — Pemulihan tab dan modul dengan beberapa berkas

- **Tanggal:** 8 November 2026.
- **Permintaan pemilik produk:** tab tidak hilang saat navigasi memicu refresh; modul memilih acuan/tingkatan yang sudah tersedia, dan satu modul memuat beberapa berkas dengan fungsi/deskripsi masing-masing.
- **Tab:** daftar tab/lokasi terakhir serta salinan isian teks dipulihkan dari `sessionStorage` pada tab browser yang sama. Menggantikan batas memori saja pada D-48/D-49. Salinan dipisahkan per akun, dibersihkan saat logout/menutup tab kerja, dan disaring sesuai izin. Revisi awal tetap dipakai agar konflik server tidak dilewati. CSS navigasi/tab/layout dipindah ke stylesheet utama.
- **Batas pemulihan:** bukan simpan server. Berkas yang baru dipilih tetap berada di memori saat pindah menu; setelah refresh penuh harus dipilih kembali, dengan pemberitahuan nama berkas. Penyimpanan browser bergantung ketersediaan/kuota dan tidak menjamin pemulihan setelah browser ditutup.
- **Pustaka Guru:** pilih acuan dan tingkatan melalui dropdown, misalnya HSK → HSK 1. Nama menjadi Modul HSK 1; setiap berkas mempunyai nama, fungsi, deskripsi, dan sumber PDF privat atau tautan HTTPS. Modul dapat dikaitkan sebagai referensi level/paket; materi khusus sesi tetap terpisah.
- **Integritas:** simpan membuat versi baru; mengeluarkan/mengganti berkas tidak menghapus versi atau berkas lama. Berkas privat diperiksa kepemilikannya pada modul dan aksesnya pada permintaan buka/unduh. Modul lama tetap dapat dibaca dan diperbarui tanpa membuang PDF asal.
- **Batas lokal:** PDF 2 MB per berkas, total unggahan 6 MB sekali simpan; berkas berikutnya dapat ditambahkan pada penyimpanan berikutnya. Ini batas teknis pengembangan, bukan kuota bisnis/produksi.
- **Verifikasi:** 69 tes backend/611 assertions, 3 tes formatter, build, dan pemeriksaan format PHP lulus. Chrome memverifikasi refresh Inertia 409, pemulihan tab/isian, pemilihan ulang PDF setelah refresh, dua berkas/fungsi/deskripsi, simpan dua revisi/buka ulang, akses PDF, serta layar 320–390 px dan mode gelap. Data uji browser dibersihkan; database pengembangan tidak direset.


### D-55 — Pengelolaan akses dan menu dalam Sistem

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** Pengguna & Hak Akses serta Manajemen Menu digabung ke kelompok Sistem bersama Riwayat Aktivitas dan Arsip Level. Kelompok Pengaturan terpisah dihapus dari sidebar.
- **Implementasi:** kelompok navigasi ditetapkan pada registri menu; urutan tetap mengikuti pengaturan menu dalam kelompok. Izin Superadmin, lokasi halaman, dan tab kerja tetap berlaku.


### D-56 — Ujian Tong Yi per paket dan level

- **Tanggal:** 8 November 2026.
- **Persetujuan pemilik produk:** soal yang disiapkan Admin sebagai penilaian Tong Yi dinamai **Ujian**, dengan cakupan **Ujian Akhir Paket** dan **Ujian Akhir Level**. Menu, tab, formulir, dan pemilih kurikulum mengikuti istilah ini.
- **Pemisahan:** latihan tetap berarti panduan/kegiatan tambahan guru pada sesi. Data soal, versi, kaitan kurikulum, izin, serta rute teknis `assessments` dipertahankan. Migrasi hanya memperbarui label menu bawaan, tidak menimpa nama khusus pengguna.
- **Batas:** perubahan istilah tidak menetapkan bobot nilai, ambang kelulusan, atau aturan sertifikat. Pengerjaan dan rekap siswa masih tahap berikutnya.


### D-57 — Simpan tanpa perubahan tidak membuat revisi

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** penyimpanan berulang tanpa perubahan tidak boleh menambah versi di seluruh tempat.
- **Implementasi:** server membandingkan isi acuan, modul/referensi, ujian, program, hak akses, dan susunan menu sebelum menaikkan revisi atau mencatat perubahan. Urutan properti JSON serta perbedaan isian opsional kosong/null tidak dianggap perubahan; urutan daftar materi/soal/menu tetap diperhatikan. Urutan daftar permission tidak mengubah kewenangan.
- **Berkas:** unggah ulang PDF pada berkas modul yang sama, dengan nama dan isi identik, mempertahankan berkas/versi. Perubahan isi atau metadata tetap membuat versi baru. Pengaitan versi acuan/sumber yang berbeda tetap merupakan perubahan.
- **Status:** penerbitan pertama atau penerbitan draf yang berbeda dari versi terbit tetap merupakan perubahan. Pemeriksaan izin dan konflik revisi dilakukan sebelum pemeriksaan isi sama; permintaan dengan revisi usang tetap ditolak. Catatan kegagalan/akses tetap mengikuti aturan audit yang berlaku.
- **Riwayat:** versi lama tidak dihapus atau digabung karena dapat sudah dipakai kurikulum. Perubahan berlaku pada penyimpanan berikutnya.
- **Verifikasi:** 73 tes backend/668 assertions lulus, mencakup simpan berulang acuan/modul/ujian, PDF identik, program draf/publikasi, hak akses/menu, dan perubahan nyata yang tetap membuat revisi.


### D-58 — Pilihan kurikulum tanpa nomor versi

- **Tanggal:** 8 November 2026.
- **Koreksi pemilik produk:** pilihan v1/v2 dan seterusnya membingungkan saat mengatur kurikulum.
- **Implementasi:** pemilih acuan, modul referensi, dan ujian menampilkan satu pilihan per sumber, memakai data siap dipakai terbaru, tanpa nomor versi. Sumber yang sudah dikaitkan tidak ditawarkan ulang sebagai versi lain.
- **Integritas:** UUID versi tetap disimpan; kaitan lama tetap dibaca dengan data asal dan tidak diperbarui otomatis. Riwayat versi tetap tersedia di pengelolaan sumber belajar untuk penelusuran.


### D-59 — Tujuan ujian dan cakupan sesi

- **Tanggal:** 8 November 2026.
- **Persetujuan pemilik produk:** saat membuat ujian pilih Program → Subprogram → Level → jenis ujian; Ujian Akhir Paket juga memilih paket. Ringkasan menunjukkan level, rentang sesi, dan setelah sesi keberapa ujian diberikan. Contoh tiga paket normal: Paket 2 mencakup sesi 9–16; akhir level mencakup sesi 1–24.
- **Implementasi:** pilihan berasal dari susunan draf program tersimpan, hanya level/paket aktif; perubahan struktur yang belum disimpan belum muncul. Jumlah sesi dihitung dari paket kurikulum, bukan diketik manual. Snapshot ujian menyimpan identitas tujuan dan nama/cakupan saat disimpan. Daftar ujian dan tampilan riwayat menampilkan tujuan.
- **Integritas:** server memeriksa hubungan program/subprogram/level/paket serta revisi program sebelum menyimpan. Tujuan ujian baru wajib diisi. Pilihan kurikulum disaring sesuai tujuan dan pengaitan baru yang salah ditolak server. Data ujian/kaitan terdahulu tanpa tujuan tetap dibaca; saat dilengkapi lewat formulir, Admin memilih tujuan. Tidak menebak tujuan dari judul lama atau memindahkan kaitan terdahulu otomatis.
- **Batas:** menyimpan soal tidak menerbitkan program atau menugaskan ujian kepada siswa otomatis. Hubungkan ujian pada paket/level melalui Program Akademik, lalu Terbitkan. Pengerjaan siswa, bobot, dan kebijakan kelulusan belum ditetapkan.
- **Verifikasi:** 77 tes backend/714 assertions lulus, meliputi rentang sesi, paket aktif, tujuan kosong/salah/revisi usang, tanpa revisi tambahan saat isi sama, serta penolakan pengaitan ke paket lain. Build berhasil.
- **Browser:** Chrome memverifikasi pilihan bertingkat, cakupan sesi 9–16 dan 1–24, pergantian jenis ujian, simpan/buka ulang, simpan tanpa revisi tambahan, serta layout stabil 320–390 px tanpa kesalahan JavaScript. Data uji sementara dibersihkan.


### D-60 — Satu pemilih modul referensi pada level

- **Tanggal:** 8 November 2026.
- **Koreksi pemilik produk:** dua pengisian modul pada level/paket membingungkan; pertahankan bagian atas dan hapus bagian bawah.
- **Implementasi:** Materi sesi menampilkan satu panel **Modul referensi level**, tanpa pembungkus judul ganda. Pemilih referensi paket dihapus dari formulir. Modul level menjadi referensi untuk seluruh sesi pada level tersebut; PDF khusus sesi tetap terpisah.
- **Integritas:** kaitan referensi paket dan metadata lama yang sudah tersimpan tetap dipertahankan dalam payload/snapshot, tanpa penghapusan atau pemindahan otomatis. Tidak membuat migrasi data hanya untuk menyederhanakan formulir.


### D-61 — Modul langsung di bawah acuan

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** pilih acuan HSK, lalu modul langsung muncul di bawahnya agar tidak berpindah bagian. Menggantikan penempatan panel modul dalam Materi sesi pada D-60.
- **Implementasi:** pilihan acuan langsung menambahkan acuan pada formulir tanpa tombol Hubungkan. Modul siap dipakai terbaru yang sesuai acuan tampil sebagai daftar dengan kotak centang dan akses berkas. Sebelum tingkatan dipilih, semua modul acuan tersebut ditampilkan; setelah tingkatan dicentang, pilihan disaring sesuai cakupan. Modul dikelompokkan berdasarkan identitas acuan, sehingga revisi acuan berbeda tidak menyembunyikan modul yang masih sesuai.
- **Integritas:** mencentang modul menentukan kaitan level; Terbitkan tetap diperlukan untuk menyimpan kurikulum. Modul yang sudah terhubung tidak diganti/dihapus diam-diam saat acuan/tingkatan berubah; Admin dapat melepasnya dengan menghapus centang. PDF materi sesi tetap terpisah di Materi sesi. Tidak memindahkan kaitan referensi paket lama.


### D-62 — Modul tersembunyi sebelum acuan dipilih

- **Tanggal:** 8 November 2026.
- **Koreksi pemilik produk:** tanpa acuan, bagian Modul tidak boleh muncul, termasuk modul yang sudah terkait dari isian terdahulu.
- **Implementasi:** seluruh bagian Modul disembunyikan saat daftar acuan kosong; setelah acuan dipilih modul muncul mengikuti alur D-61. Menyembunyikan bagian tidak menghapus kaitan tersimpan.


### D-63 — Modul hanya untuk tingkatan yang dicentang

- **Tanggal:** 8 November 2026.
- **Penegasan pemilik produk:** memilih acuan saja belum menampilkan modul. Modul muncul setelah tingkatan dipilih; dua tingkatan menampilkan gabungan modul keduanya. Menggantikan perilaku semua modul acuan sebelum pemilihan tingkatan pada D-61.
- **Implementasi:** HSK 1 hanya menampilkan modul HSK 1; HSK 1 dan HSK 2 menampilkan keduanya. Berlaku juga bila beberapa acuan dipilih. Modul yang sudah terhubung tetap mengikuti filter tampilan dan tidak boleh melewatinya. Menyembunyikan modul tidak menghapus UUID kaitan lama; ketika tingkatan dipilih kembali, pilihan sebelumnya ditampilkan kembali.


### D-64 — Acuan dan modul pengajaran per level Tong Yi

- **Tanggal:** 8 November 2026.
- **Persetujuan pemilik produk:** kelola acuan serta kumpulan modul dalam satu tab **Acuan & Modul** pada setiap level Tong Yi. Modul dapat mencakup buku HSK 1/2, panduan, dan bahan internal Tong Yi. Acuan menjelaskan cakupan; modul tidak wajib terikat pada satu tingkatan HSK. Materi per sesi tetap terpisah. Menggantikan filter/visibilitas modul berdasarkan centang acuan pada D-61–D-63.
- **Admin Program:** bagian Acuan pembelajaran dan Modul pengajaran level berada dalam satu tab. Modul untuk level yang sedang dibuka langsung tersedia tanpa memilih HSK. Admin berizin sumber belajar dapat mengunggah beberapa PDF/tautan beserta fungsi/deskripsi langsung dari tab ini. Modul yang baru disimpan langsung dicentang pada level; Terbitkan program diperlukan untuk menyimpan kaitan kurikulum. Program/level baru perlu disimpan terlebih dahulu agar modul mempunyai identitas tujuan yang valid.
- **Pustaka Guru:** tabel menampilkan program/subprogram/level pemilik modul. Editor memilih program/subprogram/level Tong Yi, nama modul, dan berkas; bukan wajib HSK. Daftar modul menjadi kumpulan referensi guru berdasarkan level. Portal guru dan izin penugasan guru tetap tahap berikutnya.
- **Integritas:** snapshot baru `format=module_level` menyimpan `level_target` dengan identitas/nama program, subprogram, dan level. Revisi program dan hubungan level diperiksa server. Pengaitan baru ke level lain ditolak. Pengubahan format pada modul lama mempertahankan berkas dan versi terdahulu; kaitan lama tetap ditampilkan sampai dilepas secara eksplisit. Tidak mengklasifikasikan ulang/memindahkan seluruh modul lama secara otomatis.
- **Isian:** unggahan baru pada level mempunyai buffer sendiri agar isian/File tetap tersedia ketika berpindah tab level/menu. Setelah refresh penuh, File yang belum diunggah perlu dipilih kembali. Izin, PDF privat, versi immutable, batas teknis 2 MB/PDF dan 6 MB per simpan tetap berlaku.
- **Verifikasi:** 80 tes backend/740 assertions, 7 tes frontend, dan build lulus. Chrome memverifikasi unggah dua PDF langsung tanpa HSK, retensi isian antar tab level, kaitan otomatis, publikasi/buka ulang, akses PDF, pemisahan materi sesi, editor pustaka berlevel, serta viewport 320–390 px tanpa kesalahan JavaScript.


### D-65 — Pindah tab kerja saat mengedit tanpa konfirmasi keluar

- **Tanggal:** 8 November 2026.
- **Penegasan pemilik produk:** tab yang masih terbuka harus dapat dipindah saat sedang mengedit; isian dipertahankan, bukan dianggap meninggalkan pekerjaan.
- **Perbaikan:** URL internal dikenali sebagai path, URL absolut origin sama, atau objek URL. Redirect/reload internal Inertia membekukan buffer isian sebelum navigasi dan tidak memunculkan peringatan keluar browser. Pengecualian dibatasi pada tujuan Admin origin sama, bukan login/logout atau situs lain.
- **Kesiapan input:** ruang Admin baru menerima interaksi setelah antarmuka terpasang, mencegah input/navigasi terlalu cepat sebelum event formulir siap. Buffer program diperbarui langsung setiap perubahan isian dan dipulihkan saat kembali ke tab. Mengembalikan isian persis ke kondisi awal tidak menyisakan buffer perubahan lama.
- **Perlindungan:** konfirmasi membuang isian tetap berlaku saat menutup tab melalui × atau keluar ruang kerja. Saat permintaan simpan/unggah berlangsung, perlindungan navigasi tetap berlaku. File dipertahankan dalam memori pada perpindahan tab biasa; refresh penuh masih memerlukan pemilihan ulang File yang belum diunggah sebagaimana D-54. Tidak menyimpan/publikasi otomatis ke server.
- **Verifikasi:** 9 tes frontend dan build lulus. Dua sesi Chrome baru memverifikasi reload internal 409 tanpa dialog, perpindahan tab program/acuan/pustaka dengan retensi teks/PDF, serta konfirmasi saat menutup tab kotor.


### D-66 — Judul acuan dan modul mengikuti level aktif

- **Tanggal:** 8 November 2026.
- **Konfirmasi pemilik produk:** judul bagian menjadi Acuan & Modul — nama level yang sedang dibuka, dengan modul langsung mengikuti level tersebut; HSK tetap merupakan cakupan pembelajaran.
- **Implementasi:** judul induk dan judul kumpulan modul memakai nama level aktif secara dinamis. Pemilih acuan/tingkatan tetap ada sebagai cakupan; modul tetap dicari berdasarkan identitas program/subprogram/level sebagaimana D-64, bukan kemiripan judul atau centang HSK.
- **Integritas:** tidak mengganti nama level tersimpan, judul buku/modul lama, atau acuan HSK secara massal. Data lama tetap tersedia. Materi khusus sesi tidak berubah.


### D-67 — Judul umum dan penggantian nama modul yang dipilih

- **Tanggal:** 8 November 2026.
- **Koreksi eksplisit pemilik produk:** judul bagian cukup **Acuan & Modul**, tanpa menempelkan nama level. Admin memilih modul. Modul yang sebelumnya bernama **Modul HSK 1** diminta bernama **Absolute Beginner - Beginner**. Mengoreksi penafsiran/judul dinamis pada D-66.
- **Implementasi:** judul induk menjadi Acuan & Modul dan subbagian menjadi Modul; teks nama level berulang dihapus. Nama modul yang ditunjuk di database pengembangan diganti melalui versi baru dan catatan audit dengan akun Superadmin tongyi. Satu berkas dan seluruh isi sebelumnya tetap dipertahankan. Tidak mengganti nama level, acuan HSK, atau judul berkas.
- **Kaitan lama:** katalog menambahkan `display_title` dari nama modul terkini agar kaitan ke snapshot terdahulu menampilkan nama baru. UUID versi, judul historis, isi, dan berkas snapshot tetap tidak ditimpa. Riwayat tetap menunjukkan nama saat versi dibuat. Tidak memperbarui kaitan kurikulum otomatis.
- **Verifikasi:** 17 tes backend/208 assertions serta build lulus, termasuk nama tampilan pada kaitan historis, berkas tetap sama, audit, dan pemanggilan ulang tanpa revisi tambahan. Modul yang diminta berhasil berganti nama pada revisi 8.


### D-68 — Pilih modul langsung, tampilkan acuan dan materi modul

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** tidak perlu pemilih acuan terpisah di Program Akademik; Admin memilih modul, lalu materi di dalamnya muncul. Acuan mengikuti metadata modul.
- **Implementasi:** tab Acuan & Modul berisi satu dropdown Pilih modul. Memilih langsung menambahkan modul pada isian level dan menampilkan materi/tautan tanpa langkah Hubungkan atau buka Lihat berkas. Acuan modul ditampilkan sebagai informasi. Modul yang sudah dipilih kembali ditampilkan saat membuka kurikulum; Lepas membuang kaitan dari isian secara eksplisit.
- **Kompatibilitas:** modul referensi lama tanpa level tujuan dapat dipilih; modul yang mempunyai tujuan level tetap disaring sesuai level. PDF khusus sesi tidak masuk pemilih. Pengaitan acuan level lama tetap tersimpan tanpa dihapus/diubah diam-diam, tetapi pemilihnya tidak ditampilkan di panel gabungan. Materi sesi dan ujian tetap terpisah.
- **Verifikasi:** 9 tes frontend dan build lulus. Chrome memverifikasi modul belum dipilih tidak menampilkan materi, pemilihan langsung menampilkan acuan/PDF, publikasi/buka ulang mempertahankan pilihan, Lepas tidak menghapus PDF, serta viewport 320–390 px tanpa kesalahan JavaScript.


### D-69 — Tambah modul melalui halaman penuh

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** tombol Tambah modul membuka halaman modul, bukan formulir unggah di dalam Program Akademik, agar panel tetap ringkas.
- **Implementasi:** tombol membuka halaman tambah di Pustaka Guru sebagai tab kerja, dengan program/subprogram/level asal sudah dipilih. Isian Program Akademik tetap menggunakan buffer tab; modul yang sudah dibuat dapat dipilih saat kembali. Form unggah langsung di panel dihapus.
- **Kompatibilitas:** buffer unggahan inline lama untuk level yang sama dipindahkan ke formulir halaman penuh jika tab tambah modul belum memiliki isian lain. Data/File yang masih di memori tidak dibuang; aturan pemilihan ulang File setelah refresh penuh tetap berlaku.
- **Verifikasi:** build dan 9 tes frontend lulus. Chrome memverifikasi tombol menuju halaman penuh, program/subprogram/level terpilih, tidak ada unggah inline, serta retensi isian kedua tab tanpa dialog keluar.


### D-70 — Penandaan acuan modul dan per berkas

- **Tanggal:** 8 November 2026.
- **Persetujuan pemilik produk:** acuan ditandai pada modul; setiap berkas mengikuti acuan modul secara bawaan dan dapat diberi acuan khusus. Bahan Internal Tong Yi tidak dipaksa memilih HSK.
- **Pengisian:** halaman penuh modul memuat Acuan modul, dengan beberapa acuan/tingkatan dari data yang sudah tersedia dan pilihan Internal Tong Yi. Pada setiap berkas, pilih Mengikuti acuan modul atau Acuan khusus berkas ini. Acuan khusus dapat berupa HSK 1 saja, acuan lain, atau Internal Tong Yi. Tidak menambahkan kembali pemilih acuan pada Program Akademik.
- **Penyimpanan:** `content.references` menyimpan penandaan modul; `attachments[].references` kosong/null berarti mengikuti modul, objek berisi penandaan khusus berarti menggantikan acuannya. Isi/versi berkas tidak ditimpa. Acuan dan tingkatannya diperiksa server; modul baru berformat level memerlukan minimal satu acuan atau Internal Tong Yi. Penandaan versi lama tetap dapat dibaca dari metadata lama tanpa migrasi massal.
- **Tampilan:** katalog Program, daftar Pustaka Guru, dan riwayat versi menampilkan acuan modul; setiap berkas menampilkan acuan efektifnya. Riwayat perubahan menyertakan metadata penandaan tanpa path privat/kunci jawaban.
- **Integritas:** perubahan acuan induk memengaruhi berkas yang mengikuti modul pada versi baru; berkas berpenandaan khusus dan snapshot lama tetap mengikuti data asal. Urutan berkas dipertahankan. Urutan centang acuan/tingkatan dinormalisasi agar tidak membuat versi tambahan bila cakupannya tetap sama.
- **Verifikasi:** 83 tes backend/766 assertions, 9 tes frontend, dan build lulus. Chrome memverifikasi HSK 1–2 pada modul, pewarisan acuan, berkas khusus Internal Tong Yi/HSK 1, urutan berkas, simpan/buka ulang tanpa revisi tambahan, riwayat, serta viewport 320–390 px tanpa kesalahan JavaScript. Data uji sementara dibersihkan.


### D-71 — Satu katalog Modul: kelompok, modul, bahan

- **Tanggal:** 8 November 2026.
- **Persetujuan pemilik produk:** sederhanakan menjadi satu menu **Modul**, dengan susunan **Kelompok → Modul → Bahan**. Contoh HSK → HSK 1/HSK 2, BCT → BCT A, Tong Yi → bahan percakapan; tiap modul berisi beberapa PDF/tautan dengan fungsi dan deskripsi. Kelompok/nama modul diisi langsung pada editor, tanpa menu Acuan terpisah atau checkbox Internal Tong Yi. Menggantikan alur penandaan/per-level pada D-64/D-70 untuk pengisian baru.
- **Program Akademik:** tab Acuan & Modul menjadi Modul. Admin dapat memilih beberapa modul dari kelompok berbeda; bahan langsung tampil. Modul menjadi katalog umum yang dapat digunakan pada beberapa level/program. Tidak lagi membatasi modul berdasarkan level tujuan saat dibuat. Materi sesi dan ujian tetap terpisah; tidak mengubah kebijakan nilai/kelulusan.
- **Penyimpanan:** `format=module_catalog`, kelompok wajib, nama modul, dan beberapa bahan. Tidak memerlukan level tujuan atau master acuan. Saat modul lama diperbarui ke format baru, konteks/penandaan lama disimpan dalam `legacy_metadata` dan seluruh versi lama dipertahankan. Metadata berkas lama tidak dihapus; alur baru tidak menampilkan pengisian acuan per berkas.
- **Navigasi:** label Pustaka Guru menjadi Modul; menu Acuan disembunyikan dari sidebar dan Manajemen Menu. Rute/data historis tetap tersedia untuk kompatibilitas; tab lama yang masih terbuka tidak dibuang paksa. Label navigasi bawaan diperbarui melalui migrasi tanpa reset database atau menimpa nama menu khusus.
- **Tampilan:** daftar modul memuat kelompok, nama, jumlah bahan, status, pencarian dan filter kelompok. Pilihan di program dikelompokkan menurut kelompok dan mendukung beberapa modul sekaligus. Tambah modul tetap membuka halaman penuh, dengan buffer tab.
- **Integritas:** UUID versi tetap dipakai pada kurikulum. Kaitan/versi/berkas lama tetap utuh; perubahan benar-benar baru membuat versi, simpan tanpa perubahan tidak menambah versi. Kelompok lama dapat ditampilkan dari metadata acuan yang tidak ambigu; yang belum jelas ditandai Belum dikelompokkan sampai Admin mengisinya. Tidak mengubah nama modul pengguna secara massal.
- **Verifikasi:** 85 tes backend/808 assertions, 9 tes frontend, pemeriksaan format PHP, build, dan `git diff --check` lulus. Chrome memverifikasi satu menu Modul, pembuatan HSK/BCT tanpa level/acuan/checkbox internal, PDF+tautan, simpan tanpa versi tambahan, filter kelompok, pemilihan dua modul pada level, publikasi/buka ulang, pemisahan materi sesi, dan viewport 320–390 px tanpa kesalahan JavaScript. Data uji dibersihkan; migrasi label diterapkan tanpa reset data pengembangan.


### D-72 — Nama modul bebas dengan cakupan acuan di dalamnya

- **Tanggal:** 8 November 2026.
- **Persetujuan pemilik produk:** modul bernama, misalnya Absolute Beginner - Beginner; di dalamnya Admin mencentang HSK/BCT beserta tingkatannya, lalu menambahkan bahan. Ketika modul dipilih pada Program Akademik, seluruh informasi cakupan dan bahan langsung terlihat. Menggantikan pengisian Kelompok wajib pada D-71; katalog tetap umum dan dapat dipakai di beberapa level.
- **Editor:** Nama modul, keterangan/pengelola, Cakupan modul, dan Bahan modul. Cakupan memakai centang acuan dari master yang tersedia dan centang tingkatannya, misalnya HSK 1–2 serta BCT A pada satu modul. Tidak ada checkbox Internal Tong Yi, pengisian acuan per berkas, pemilihan level tujuan, atau menu Acuan terpisah. Acuan bertingkatan yang dicentang memerlukan minimal satu tingkatan yang sesuai. Modul tanpa acuan standar tetap boleh dibuat.
- **Program:** semua modul referensi siap dipakai tersedia. Pilihan menampilkan nama beserta cakupan; setelah dipilih, ringkasan cakupan dan semua bahan langsung tampil. Acuan tidak diinput ulang di Program Akademik. Beberapa modul dapat dipilih pada satu level. Materi sesi dan ujian tetap terpisah.
- **Integritas:** cakupan disimpan sebagai referensi versi master dan daftar tingkatan dalam snapshot modul. Validasi server memeriksa sumber/tingkatan. Nama modul tidak dibuat otomatis dari nama acuan. Metadata kelompok/tujuan/acuan lama tetap dipertahankan untuk kompatibilitas dan riwayat; cakupan lama yang disimpan dalam legacy_metadata dapat ditampilkan kembali tanpa mengubah data saat dibaca. Cakupan kosong bawaan atau perubahan urutan centang tidak membuat versi tambahan bila maknanya sama.
- **Verifikasi:** 87 tes backend/823 assertions, 9 tes frontend, build, dan pemeriksaan format/diff lulus. Chrome memverifikasi nama Absolute Beginner - Beginner dengan HSK 1–2 dan BCT A, tanpa Kelompok/Internal, simpan/buka ulang tanpa revisi tambahan, informasi cakupan pada pemilih Program Akademik, publikasi, serta viewport 320–390 px tanpa kesalahan JavaScript. Data uji dibersihkan.


### D-73 — Dropdown modul hanya menampilkan nama

- **Tanggal:** 8 November 2026.
- **Koreksi pemilik produk:** informasi cakupan tidak perlu ditulis dalam kotak pilihan modul.
- **Implementasi:** dropdown hanya menampilkan nama modul; cakupan HSK/BCT dan bahan tetap ditampilkan setelah modul dipilih. Tidak mengubah metadata atau kaitan kurikulum.

### D-74 — Pengelolaan cakupan dari halaman Modul

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** cakupan seperti HSK/BCT beserta tingkatannya dapat ditambah, diubah, atau dihapus dari halaman utama Modul.
- **Implementasi:** tombol **Kelola cakupan** membuka halaman pengelolaan dalam tab Modul. Tabel memuat cakupan dan tingkatannya; formulir mendukung nama, keterangan, serta tambah/ubah/hapus tingkatan. Tidak menambah menu Acuan terpisah pada sidebar.
- **Integritas:** perubahan menghasilkan snapshot baru hanya jika isi berubah. Penghapusan mengarsipkan sumber dari pilihan baru, dengan opsi Pulihkan. Cakupan yang sudah dipakai modul tetap mengikuti snapshot asal; tidak menghapus atau mengganti metadata modul/kurikulum secara diam-diam. Server menolak penggunaan baru sumber yang diarsipkan, tetapi mengizinkan modul lama mempertahankan cakupannya tanpa perubahan. Simpan/hapus/pulihkan memeriksa revisi untuk mencegah penimpaan; perubahan tercatat dalam riwayat aktivitas. Hak pengelolaan mengikuti `learning.manage`.
- **Verifikasi:** 90 tes backend/866 assertions, 9 tes frontend, build, dan pemeriksaan format/diff lulus. Chrome memverifikasi tambah, edit tingkatan, simpan tanpa perubahan, hapus/pulihkan, pilihan cakupan pada modul, dan viewport 320–390 px. Data sementara dibersihkan; migrasi arsip diterapkan pada database pengembangan tanpa reset data.

### D-75 — Pilih cakupan sebelum menampilkan tingkatannya

- **Tanggal:** 8 November 2026.
- **Koreksi pemilik produk:** editor Modul tidak menampilkan seluruh cakupan sebagai kotak centang sekaligus.
- **Implementasi:** dropdown **Pilih cakupan** hanya menawarkan sumber aktif yang belum dipilih. Setelah dipilih, kartu cakupan dan centang tingkatannya muncul di bawah; beberapa cakupan tetap didukung. **Lepas** mengeluarkan cakupan dari isian modul. Pilihan yang sudah tersimpan langsung ditampilkan saat membuka editor; snapshot historis dan aturan arsip tetap dipertahankan.

### D-76 — Cakupan per bahan dari tingkatan modul

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** tiap PDF/tautan perlu dropdown untuk menandai cakupannya, dengan pilihan hanya dari tingkatan yang dicentang pada modul.
- **Implementasi:** dropdown **Cakupan bahan** menawarkan, misalnya HSK 1 dan HSK 2, sesuai pilihan induk. Bawaan **Mengikuti seluruh cakupan modul** mempertahankan pewarisan. Pilihan khusus disimpan pada referensi bahan dan ditampilkan pada katalog kurikulum. Metadata lama tetap dipertahankan; cakupan khusus lama yang tidak ada pada pilihan induk ditandai sebagai cakupan tersimpan, tidak dibuang otomatis. Server menolak perubahan cakupan bahan di luar tingkatan induk; mengubah cakupan induk tidak menghapus penandaan historis diam-diam.

### D-77 — Riwayat versi dipusatkan pada Sistem

- **Tanggal:** 8 November 2026.
- **Persetujuan pemilik produk:** daftar Riwayat versi tidak ditampilkan pada halaman pengisian sumber belajar agar pekerjaan Admin lebih ringkas.
- **Implementasi:** panel riwayat di editor Modul, Ujian, dan sumber belajar lama dihapus. Superadmin menelusuri perubahan melalui **Sistem → Riwayat Aktivitas**, yang tetap menyediakan detail sebelum/sesudah dan tautan versi sumber. Snapshot, berkas lama, serta kaitan kurikulum tetap tersimpan; tidak ada penghapusan data atau perubahan aturan versi.

### D-78 — Ujian Paket 1 dan media soal

- **Tanggal:** 8 November 2026.
- **Permintaan pemilik produk:** membaca materi PDF sesi Paket 1 dan membuat 30 soal relevan; mendukung unggah suara dan gambar pada soal.
- **Isi lokal:** PDF sesi 1–8 dibaca melalui OCR (152 halaman). Ujian baru **Ujian Paket 1 — Dasar Mandarin untuk Pekerja** dibuat dengan 22 pilihan ganda dan 8 isian, kunci/acuan pemeriksaan, serta tujuan Mandarin → Kerja → Absolute Beginner - Beginner → Paket 1 (sesi 1–8). Tidak menimpa ujian lain, menetapkan bobot/ambang kelulusan, atau menugaskan ujian ke siswa. Data soal/kunci disimpan privat dan tidak dijadikan seed umum repositori.
- **Media:** tiap soal dapat memiliki satu gambar (JPG/PNG/WebP) dan satu audio (MP3/WAV/M4A/OGG), masing-masing maksimal 2 MB dan total unggahan 6 MB sekali simpan. Mendukung pratinjau dan Lepas. Media disimpan privat; endpoint versi memerlukan hak `learning.manage`. Pemilih program tetap tidak menerima pertanyaan/kunci/path media. Bukan fitur rekam mikrofon atau jawaban suara siswa.
- **Integritas:** media dipertahankan berdasarkan ID soal pada versi sebelumnya; path dari klien tidak dipercaya. Pelepasan/penggantian hanya berlaku pada versi baru; media versi terdahulu tetap tersedia bagi petugas berwenang. Unggah identik/simpan tanpa perubahan tidak menambah versi. Penyimpanan gagal membersihkan berkas baru dan membatalkan perubahan database. Riwayat mencatat nama media tanpa path privat/kunci.
- **Verifikasi:** 92 tes backend/886 assertions, 9 tes frontend, build, format PHP, dan pemeriksaan diff lulus. Chrome memverifikasi unggah gambar/audio, akses pratinjau privat, buka ulang, simpan tanpa versi tambahan, Lepas tanpa merusak versi lama, serta viewport 320–390 px. Data/media uji sementara dibersihkan. Form membatasi unggahan ke 20 media/6 MB per penyimpanan untuk mengikuti batas parser lokal; media berikutnya dapat ditambahkan setelah menyimpan.

### D-79 — Tujuan ujian diatur sekali, tampil otomatis pada akademik

- **Tanggal:** 8 November 2026.
- **Koreksi pemilik produk:** program/subprogram/level/paket sudah ditetapkan pada Ujian; Admin tidak perlu menghubungkan ujian lagi di Program Akademik.
- **Implementasi:** tab Ujian pada Program Akademik menjadi daftar baca saja. Ujian siap dipakai pada revisi terkini otomatis ditampilkan berdasarkan kecocokan UUID program, subprogram, level, jenis ujian, dan paket. Memuat jumlah soal, cakupan sesi, serta tautan Buka ujian bagi pemegang hak pengelolaan. Tidak ada pemilih, Hubungkan, atau Lepas untuk ujian bertujuan. Seluruh ujian yang cocok ditampilkan, tanpa memilih salah satu secara tersembunyi.
- **Integritas:** tujuan pada sumber Ujian menjadi sumber penempatan. Tampilan otomatis tidak menulis ulang draf/publikasi program atau membuat revisi program. Kunci, soal, dan path media tidak dikirim ke katalog program. Snapshot lama serta kaitan manual historis tetap disimpan. Ujian lama tanpa tujuan yang sudah dikaitkan diberi tautan Atur tujuan agar Admin dapat melengkapi metadata melalui editor Ujian. Perubahan revisi sumber ditampilkan untuk pengaturan berikutnya; riwayat versi tetap tersedia, dan upaya/nilai siswa kelak harus menyimpan snapshot ujian saat pengerjaan (belum merupakan fitur tahap ini).
- **Verifikasi:** 93 tes backend/907 assertions, 11 tes frontend, build, dan pemeriksaan diff lulus. Chrome memverifikasi ujian baru otomatis muncul pada Paket 1 tanpa pengaitan/publikasi ulang program, tidak muncul pada Paket 2, jumlah soal/cakupan serta tautan editor, dan viewport 320–390 px. Data uji dibersihkan.

### D-80 — Pembatalan subprogram baru yang terlihat jelas

- **Tanggal:** 8 November 2026.
- **Laporan pemilik produk:** subprogram baru yang tidak sengaja ditambahkan sulit dihapus.
- **Pemeriksaan:** pembatalan lama berhasil pada pengujian termasuk setelah pemulihan tab, tetapi tombol berupa teks kecil di bagian bawah identitas.
- **Perbaikan:** tombol **Hapus subprogram baru** dipindahkan ke judul panel agar tetap terlihat ketika identitas ditutup. Subprogram kosong dibatalkan langsung; jika sudah berisi identitas atau level, konfirmasi diperlukan. Subprogram tersimpan dilindungi dari tindakan ini. Setelah pembatalan, pilihan subprogram/level/paket/sesi diatur ulang, pesan validasi lama dibersihkan, dan buffer tab dipersistenkan supaya subprogram yang dibatalkan tidak muncul lagi setelah pemulihan. Isian subprogram lain tetap dipertahankan.
- **Verifikasi:** build dan pemeriksaan diff lulus. Chrome memverifikasi pembatalan subprogram kosong/hasil pemulihan, penolakan konfirmasi mempertahankan isian, persetujuan membatalkan hanya subprogram baru, reload tidak memunculkannya kembali, dan tombol tetap terlihat pada viewport 320–390 px. Data uji dibersihkan.

### D-81 — Modul referensi mengikuti bahan terbaru tanpa pengaitan ulang

- **Tanggal:** 8 November 2026.
- **Keluhan pemilik produk:** penambahan/perubahan bahan di Modul tidak terlihat pada Program Akademik sampai modul dilepas lalu dipilih kembali.
- **Perubahan:** modul referensi yang dipilih pada level ditampilkan berdasarkan sumber modul yang sama dan revisi siap dipakai paling baru. Tidak lagi mengunci tampilan referensi pengajaran pada snapshot saat pertama dipilih. Katalog dimuat ulang saat membuka/kembali memfokuskan halaman; pembaruan katalog tidak menimpa isian akademik atau membuat revisi program. Menggantikan perilaku tampilan snapshot referensi level pada keputusan sebelumnya.
- **Integritas:** UUID kaitan historis tetap disimpan sebagai identitas sumber dan untuk Lepas; snapshot/berkas lama tidak ditimpa. PDF khusus sesi tetap terikat pada pilihan sesi dan tidak otomatis diganti. Respons katalog hanya metadata aman, dengan otorisasi `programs.manage` dan cache privat tanpa penyimpanan. Respons pemuatan yang mendahului unggahan sesi diabaikan untuk menghindari penimpaan katalog baru. Riwayat versi sumber tetap dapat ditelusuri melalui Sistem.
- **Verifikasi:** 93 tes backend/911 assertions, 13 tes frontend, build, dan pemeriksaan diff lulus. Chrome memverifikasi bahan baru muncul pada modul yang sudah dipilih setelah penyimpanan pada tab lain, tanpa Lepas/pilih ulang, isian akademik belum tersimpan tetap utuh, reload tetap menampilkan bahan terbaru, dan Lepas bekerja pada kaitan asli. Data uji dibersihkan.

### D-82 — Indikator tab aktif mengikuti formulir yang sedang dibuka

- **Tanggal:** 8 November 2026.
- **Laporan pemilik produk:** tab Ujian tetap bertitik kuning meskipun formulir menyatakan tersimpan dan simpan ulang tidak menghasilkan perubahan.
- **Reproduksi:** buffer `assessments:new` dari isian ujian lain membuat pemeriksaan tingkat menu tetap kotor, sementara ujian tersimpan yang dibuka sudah bersih. Menyimpan ujian tersebut tidak boleh menghapus isian ujian lain.
- **Perbaikan:** indikator visual tab aktif memakai status formulir yang sedang dibuka; tab tidak aktif tetap memperhitungkan buffer tersimpan. Pembaruan status formulir disinkronkan langsung. Pemeriksaan konfirmasi penutupan tetap memakai seluruh buffer/status sehingga isian lain tetap terlindungi, walaupun formulir aktif sudah tersimpan. Tidak mengubah penyimpanan, revisi, data soal, atau menghapus buffer lain diam-diam.
- **Verifikasi:** build, 13 tes frontend, dan pemeriksaan diff lulus. Chrome mereproduksi kasus asli dan memverifikasi indikator bersih setelah simpan/no-op, kuning setelah edit, isian ujian lain tetap dipulihkan, serta konfirmasi penutupan tetap melindungi buffer tersebut. Data uji dibersihkan.

### D-83 — Status titik kuning konsisten pada tab aktif dan tidak aktif

- **Tanggal:** 8 November 2026.
- **Koreksi pemilik produk:** setelah D-82, titik kuning hilang ketika tab aktif tetapi muncul kembali saat berpindah ke menu lain.
- **Penyebab:** D-82 masih memakai status formulir pada tab aktif dan seluruh buffer menu pada tab tidak aktif.
- **Perbaikan:** indikator selalu memakai status terakhir formulir yang ditampilkan pada tab tersebut, tanpa bergantung pada tab aktif. Status itu ikut disimpan/dipulihkan pada metadata workspace sehingga refresh tidak mengubah indikator. Untuk sesi lama yang belum memiliki metadata status, buffer menjadi fallback sampai formulir dibuka. Konfirmasi penutupan dan proteksi keluar tetap memperhitungkan semua buffer agar isian lain tidak terhapus diam-diam. Menggantikan perbedaan indikator aktif/nonaktif pada D-82.
- **Verifikasi:** 14 tes frontend, build, dan pemeriksaan diff lulus. Chrome memverifikasi tab yang sudah tersimpan tetap bersih saat berpindah, kembali, dan refresh; perubahan nyata tetap bertitik kuning pada tab tidak aktif lalu pulih saat dibuka kembali; simpan/no-op membersihkan indikator; buffer lain dan konfirmasi penutupan tetap utuh. Data uji dibersihkan.

### D-84 — Pratinjau tautan dan PDF pada bahan modul

- **Tanggal:** 8 November 2026.
- **Permintaan pemilik produk:** tautan mempunyai pratinjau ringkas dan PDF yang diunggah dapat dilihat langsung pada formulir Modul.
- **Implementasi:** kartu tautan HTTPS menampilkan judul/deskripsi bahan dari isian Admin, domain, alamat tujuan, dan Buka tautan. Ini bukan hasil pengambilan metadata/screenshot situs luar; tidak mengunduh halaman eksternal dari server. URL tidak valid, protokol selain HTTPS, dan URL berkredensial tidak ditampilkan sebagai pratinjau.
- **PDF:** panel pratinjau memakai pembaca PDF bawaan browser. Berkas baru memakai object URL lokal sebelum disimpan; setelah disimpan memakai endpoint preview privat yang sudah ada. Object URL dilepas ketika berkas berganti/komponen ditutup. iframe dimuat secara lazy; tersedia Buka PDF sebagai alternatif perangkat yang tidak mendukung tampilan tertanam, serta Unduh PDF tersimpan. Membuka pratinjau tidak mengubah isian/revisi atau hak akses berkas.
- **Verifikasi:** build, 16 tes frontend, dan pemeriksaan diff lulus. Chrome memverifikasi kartu tautan, PDF lokal sebelum simpan, PDF privat sesudah simpan/buka ulang (respons application/pdf), serta viewport 320–390 px tanpa overflow atau kesalahan JavaScript. Data/PDF uji dibersihkan.

### D-85 — Dasbor tanpa panel panduan dan penjelasan draf

- **Tanggal:** 8 November 2026.
- **Arahan pemilik produk:** penjelasan alur kerja dan draf terpisah pada dasbor membingungkan serta tidak perlu ditampilkan.
- **Implementasi:** panel Alur kerja dan Draf tetap terpisah dihapus. Tabel Program terbaru memakai lebar penuh di bawah ringkasan statistik. Tidak mengubah penyimpanan draf/publikasi, izin, data program, atau pemulihan isian.
