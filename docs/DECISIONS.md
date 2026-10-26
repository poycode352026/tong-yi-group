# Catatan Keputusan — Tong Yi Group

Versi 0.2 · 26 Oktober 2026. Dibaca bersama [BRD](BRD.md).

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
| D-29 | Stack aplikasi: Laravel, Vue.js, Inertia.js, dan MySQL; route/controller berada di Laravel dengan halaman Vue melalui Inertia |

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
| Stack belum diputuskan; usulan React/Livewire dan PostgreSQL | Stack yang dipilih pengguna adalah Laravel + Vue.js + Inertia.js + MySQL |

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
| O-22 | Versi stack/runtime, deployment, konfigurasi MySQL, berkas, detail autentikasi, backup, retensi | Stack telah ditetapkan pada D-29; detail implementasi dan kesiapan produksi masih perlu dirancang |
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
