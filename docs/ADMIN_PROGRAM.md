# Implementasi awal Admin Program

Tanggal: 6 November 2026. Acuan: BRD 7, FR-02–FR-06, AC-01–AC-09, serta koreksi pemilik produk bahwa program tidak terbatas pada bahasa.

## Alur yang tersedia

1. Masuk ke Portal Admin, lalu pilih Program Akademik (`/admin/programs`). Daftar tampil sebagai tabel, dengan pencarian nama/kategori dan paginasi.
2. Pilih Tambah Program. Identitas, subprogram, level, layanan, dan kurikulum diisi pada satu halaman penuh.
3. Kategori, nama program, kelompok peserta, variasi, nama level, serta jenis layanan merupakan isian fleksibel. Datalist hanyalah saran, bukan enum kategori atau program.
4. Tambah layanan hanya bila ditawarkan. Group/Private dapat dipilih sebagai jenis layanan; metode Online/Offline dipilih secara terpisah. Harga, minimum, dan maksimum tidak diberi nilai resmi bawaan. Dasar tagihan/perhitungan pembayaran belum ditetapkan di modul ini.
5. Setiap level memiliki jumlah paket aktif; setiap paket memiliki delapan rencana sesi, masing-masing berisi judul, tujuan, materi, latihan, dan referensi. Jumlah sesi aktif = paket aktif × 8.
6. Simpan draf untuk menyimpan ke server. Tombol kembali di formulir menyimpan draf dahulu bila ada perubahan; bila penyimpanan gagal, halaman tidak ditinggalkan. Navigasi melalui menu dan penutupan halaman memperingatkan isian yang belum tersimpan. Sebagai lapisan pemulihan tambahan, sessionStorage menyimpan salinan isian per Admin/program pada tab yang sama. Setelah navigasi riwayat atau reload, pengguna dapat memilih Pulihkan isian atau Abaikan salinan; salinan ini bukan draf server dan tidak dijamin tersedia bila penyimpanan browser penuh/dinonaktifkan atau tab ditutup. Revisi lama hasil pemulihan tetap ditolak server bila ada perubahan yang lebih baru. Tidak ada klaim autosave: mengetik saja belum menyimpan ke server. Draf yang sudah disimpan dapat dilanjutkan lintas perangkat oleh Admin berwenang.
7. Terbitkan program membuat versi terbit terpisah. Menyimpan draf setelah terbit tidak mengubah versi terbit. Publikasi tidak membuka kelas, pendaftaran, pembayaran, atau sertifikasi.

## Penyimpanan dan integritas

- `academic_programs` memakai UUID program, dokumen `draft` dan `published` PostgreSQL JSONB terpisah, nomor revisi, pembuat, waktu pembaruan dan penerbitan.
- Dokumen merupakan agregat katalog dengan skema tervalidasi di server, bukan salinan penyimpanan browser prototipe. Subprogram, level, layanan, paket, dan sesi memiliki UUID stabil; hubungan induk anak dipertahankan. Pilihan ini mendukung penyimpanan terpadu dan snapshot katalog pada iterasi awal. Tabel kelas, transaksi, dan relasi operasional belum dibuat; integrasi berikutnya perlu rancangan referensi program/revisi/node yang eksplisit, bukan membaca harga/kurikulum terbaru tanpa versi.
- `program_revisions` menyimpan snapshot setiap penyimpanan beserta aksi, pelaku, nomor revisi unik per program, dan waktu. Snapshot merupakan riwayat audit aplikasi; belum ada layar penelusuran/pemulihan revisi atau pembatasan database terhadap perubahan langsung oleh administrator database.
- Program dan snapshot ditulis dalam satu transaksi. Kegagalan snapshot membatalkan penulisan program.
- Pembaruan mengunci baris program dan membandingkan nomor revisi. Tab dengan revisi lama mendapat HTTP 409; isian di browser tetap tersedia untuk disalin sebelum memuat ulang.
- Pembuatan memakai UUID permintaan stabil pada halaman. Pengulangan permintaan pembuatan yang sama mengembalikan program yang sama; kombinasi UUID/revisi unik mencegah duplikasi.
- UUID yang sudah tersimpan tidak boleh dihilangkan atau dipindah induk melalui payload pembaruan. Pengurangan paket aktif mempertahankan seluruh paket dan materi; paket nonaktif ditandai di formulir dan dapat diaktifkan kembali.
- Bagian baru yang belum tersimpan dapat dibatalkan dengan konfirmasi. Pengarsipan level kini tersedia melalui Hapus level, dengan pemulihan melalui Kembalikan level. Penghapusan/arsip program dan subprogram belum disediakan. Tidak ada endpoint DELETE.
- Harga tidak negatif, menggunakan maksimal dua desimal. Kapasitas berupa bilangan bulat positif; maksimum tidak boleh kurang dari minimum. Ini pemeriksaan konsistensi isian, bukan penetapan tarif/kapasitas resmi.
- Batas 100 paket tersimpan per level serta panjang teks adalah batas teknis awal untuk ukuran formulir/payload, bukan kebijakan akademik resmi. Kebutuhan di atas batas tersebut perlu penyesuaian terukur.
- Publikasi memerlukan identitas program/kategori dan nama bagian yang disertakan; layanan yang disertakan harus dilengkapi. Tidak ada anggapan bahwa materi lengkap atau kelas siap dibuka hanya karena program diterbitkan. Materi dapat diisi bertahap; aturan pemeriksaan akademik final belum ditetapkan.
- Seluruh route baca/tulis dibatasi `auth` dan Gate `access-admin`. Kewenangan per Admin belum dipisah lebih lanjut. Semua Admin berwenang dapat mengelola katalog bersama.

## Verifikasi

`composer test` lulus 26 tes dengan 190 assertions, termasuk 11 skenario modul program pada PostgreSQL khusus tes:

- Route baca/tulis menolak tamu dan non-Admin.
- Daftar tabel dan formulir halaman penuh mengirim komponen Inertia yang sesuai.
- Group Online dan Private Offline sah tanpa layanan pengisi.
- Tiga paket menyimpan 24 sesi beserta materi yang tepat.
- Program nonbahasa dan draf belum lengkap dapat disimpan dan dibuka kembali.
- Perubahan Sederhana tidak mengubah Tradisional; simpan draf tidak mengubah versi terbit.
- Kapasitas tidak valid ditolak tanpa mengubah data tersimpan.
- Pengurangan jumlah paket aktif mempertahankan materi, dan payload yang menghilangkan paket ditolak.
- Revisi lama tidak menimpa revisi terbaru.
- Pengulangan simpan baru tidak membuat program kedua.
- Identitas duplikat dan paket yang bukan delapan sesi ditolak.
- Kegagalan penulisan snapshot membatalkan seluruh transaksi.

Build frontend dan pemeriksaan format/diff lulus. Migrasi database pengembangan berhasil tanpa reset. Pemeriksaan HTTP memastikan tamu dialihkan ke login. Formulir yang dirapikan telah diperiksa di Chrome desktop dan viewport seluler 390 px menggunakan data pada database tes. Perpindahan bagian mempertahankan isian; tautan kesalahan membuka bagian yang benar dan memfokuskan isian; simpan/buka ulang draf serta publikasi berhasil; materi sesi 8 pada paket nonaktif tetap tersedia. Tidak ada error JavaScript atau overflow horizontal halaman pada pemeriksaan tersebut. Screenshot layanan, kurikulum, dan seluler juga ditinjau.

## Belum tercakup

Katalog publik masih berisi pengenalan statis. Versi terbit Admin belum ditampilkan otomatis pada halaman depan. Penghapusan/arsip program dan subprogram, penelusuran/pemulihan riwayat di antarmuka, persetujuan akademik, kelas, transaksi pembayaran, dan kebijakan perubahan untuk kelas berjalan belum diimplementasikan. Pengaturan jenis layanan lain, durasi, serta dasar tagihan tidak dianggap selesai melalui formulir ini.

## Perapian formulir

Sesuai koreksi pengguna, formulir menggunakan satu ruang kerja: identitas program atau satu subprogram terpilih, pilihan level, serta area Layanan/Kurikulum. Semua bagian tetap memakai satu model data dan tindakan Simpan draf/Terbitkan; perpindahan pilihan tidak memuat route baru dan tidak menghapus isian. Kurikulum menampilkan paket dan satu sesi terpilih untuk diedit; paket nonaktif tetap dapat dipilih. Identitas subprogram tersimpan dapat dibuka/tutup, sedangkan subprogram baru langsung membuka isian identitas. Di seluler, pemilihan bagian memakai select ringkas. Tidak ada perubahan skema data atau kebijakan bisnis.

## Navigasi Admin dan dashboard — 7 November 2026

Semua halaman Admin menggunakan sidebar utama pada desktop. Dashboard menampilkan hitungan PostgreSQL dan lima program yang terakhir diperbarui, dengan status terbit/draf serta tautan Ubah. Draf belum diterbitkan dihitung terpisah dari program terbit yang memiliki perubahan draf; status perubahan ditampilkan pada daftar terbaru. Navigasi utama hanya memuat modul yang tersedia. Pada seluler, tombol Menu membuka navigasi dan Escape menutupnya serta mengembalikan fokus.

Formulir program tetap memakai data dan penyimpanan yang sama. Pada lebar menengah pemilih bagian ditampilkan secara ringkas untuk menjaga ruang isian setelah sidebar utama ditambahkan. Verifikasi terbaru: 27 tes, 217 assertions, serta pemeriksaan Chrome untuk dashboard, sidebar, menu seluler, logout, dan regresi formulir. Tabel seluler bergulir di dalam panel; halaman tidak meluber horizontal.

Saat mengisi subprogram, ringkasan program induk dan kategori selalu ditampilkan di atas area kerja, mengikuti isian terbaru termasuk yang belum disimpan. Tombol Ubah identitas program kembali ke bagian identitas tanpa membuang isian subprogram. Nama induk juga terlihat pada navigasi identitas.

## Rupiah dan hapus level — 7 November 2026

Isian harga menggunakan `RupiahField.vue` dengan awalan Rp, titik ribuan, dan koma desimal. Nilai model/API tetap string numerik kanonis seperti `1250000.50`, tanpa konversi floating point, tanpa pembulatan, dan tanpa menetapkan tarif baru. Formatter menerima ketikan/tempelan format Indonesia; angka negatif, bukan angka, dan presisi lebih dari dua desimal tetap ditolak validasi server. Tes memeriksa pemisahan tampilan/nilai simpan serta perubahan digit di tengah isian.

Hapus level kini terlihat pada bagian atas level terpilih dan bekerja untuk level baru maupun tersimpan. Setelah konfirmasi, level beserta layanan/paket/sesi dipindahkan dari `levels` ke `archived_levels` pada subprogram yang sama. Daftar aktif dan hitungan level tidak memasukkan arsip. Kembalikan level memulihkan seluruh objek dengan UUID yang sama ke daftar aktif. Perubahan tetap perlu disimpan; snapshot audit dan konflik revisi tetap berlaku.

Data arsip tidak dibuang; server tetap menolak payload yang menghilangkan node atau mengganti induknya. Level arsip yang belum memiliki nama tidak menghalangi penerbitan level aktif. Menyimpan perubahan sebagai draf tidak mengubah versi terbit; menerbitkan perubahan memperbarui struktur katalog aktif, sementara snapshot terdahulu tetap tersedia. Kelas/transaksi belum diimplementasikan; integrasi kelak wajib menjaga referensi revisi/hak yang sudah ada dan tidak boleh menjadikan pengarsipan sebagai penghapusan riwayat belajar.

Verifikasi: 31 tes backend/244 assertions dan 3 tes formatter lulus. Chrome memeriksa format/tempel Rp, koma desimal, pengeditan digit tengah, nilai API tepat, penghapusan level kosong tersimpan, penerbitan, reload, serta pemulihan level dengan materi utuh. Tidak ada error JavaScript pada pemeriksaan tersebut.

## Toolbar atas dan lebar ruang kerja — 7 November 2026

Tombol Simpan draf dan Terbitkan hanya berada di toolbar atas formulir, yang mengikuti scroll di bawah header. Tidak ada toolbar bawah yang menutup isian. Tombol Simpan draf memakai atribut form untuk men-submit formulir yang sama; validasi, snapshot penyimpanan, dan pemulihan isian tetap berlaku. Saat pemulihan lokal menunggu pilihan pengguna, kedua tombol dinonaktifkan.

Sidebar utama dapat ditutup/dibuka melalui kontrol di header desktop. Pilihan disimpan sebagai preferensi tampilan lokal browser; tidak mengubah data program. Penutupan memperlebar ruang kerja tanpa me-remount formulir. Menu seluler tetap tersedia terpisah. Gaya panel, judul, navigasi bagian, dan perataan kiri kini dimuat melalui stylesheet utama dengan namespace `.program-editor`, bukan stylesheet scoped per halaman. Gerakan panel masuk dihilangkan agar box tidak tampak bergeser saat diedit.

Pemeriksaan Chrome memastikan toolbar di atas dan tetap terlihat saat scroll, ukuran/perataan teks navigasi, panel berbatas, tambahan lebar setelah sidebar ditutup, preferensi setelah reload, pemulihan isian, pengiriman draf melalui tombol atas, dan menu seluler 320/390 px tanpa pelebaran halaman. Regresi simpan/publikasi, validasi, serta perlindungan materi paket juga lulus. Data pemeriksaan hanya berada di database tes dan dibersihkan setelah selesai.

## Pengaitan sumber belajar — D-45

Level dapat menyimpan `frameworks: [{version_id, levels: []}]`, `material_versions: [UUID versi]`, dan `assessment_versions: [UUID versi akhir level]`. Paket mempunyai `material_versions` dan `assessment_versions` akhir paket; sesi mempunyai `material_versions`. Field opsional ini menjaga kompatibilitas kurikulum sebelumnya. UUID mengacu versi siap dipakai, jenis/cakupannya diperiksa server. Isian bebas `reference` serta panduan guru `exercise` sebelumnya tetap dipertahankan. Pengerjaan/rekap siswa belum tersedia; lihat [Materi dan Latihan](LEARNING_RESOURCES.md).

Kolom manual Referensi / bahan ajar dihapus dari formulir sesi sesuai arahan pemilik produk; gunakan materi terhubung. Isi `reference` lama tetap dipertahankan dalam penyimpanan dan hanya ditampilkan sebagai Catatan referensi lama yang dapat dibuka, tanpa kotak input tambahan.

## Penyederhanaan sementara tindakan simpan

Sesuai arahan pemilik produk, tombol Simpan draf disembunyikan sementara dari formulir program dan sumber belajar. Tindakan Terbitkan program/perubahan atau Simpan & siap dipakai tetap tersedia. Kembali ke daftar tidak lagi menyimpan draf otomatis; peringatan isian belum tersimpan tetap berlaku. Enter pada formulir tidak menjalankan penyimpanan draf atau publikasi terselubung. API, data draf lama, pemulihan lokal, serta riwayat versi dipertahankan. Admin tanpa izin terbit belum dapat menyimpan melalui formulir program selama tombol draf disembunyikan.

## Modul pada level — D-47

Level memiliki `module_title` dan `module_description` opsional; sesi memiliki `module_section` untuk bagian/bab/halaman PDF bersama. Tab Acuan hanya menampilkan cakupan standar; tab Modul menyusun bahan level/paket/sesi; tab Latihan mengatur soal akhir paket/level. Pilihan PDF level/paket dapat digunakan kembali pada sesi dengan UUID versi yang sama. Tidak ada penyalinan unggahan. Field baru ikut validasi, snapshot, dan perlindungan draf/versi terbit yang sudah ada.

## Materi sesi dan referensi guru — D-51

Tab Modul diganti Materi sesi. PDF diunggah langsung melalui `/admin/learning/session-materials` dengan otorisasi `programs.manage`, validasi PDF/2 MB, penyimpanan privat, dan transaksi metadata. Hasil unggahan masuk `material_versions` sesi; hubungan persisten saat program disimpan/diterbitkan. Referensi pada level/paket memakai materi berjenis penggunaan `reference` dari Pustaka Guru. Data `module_title`, `module_description`, dan `module_section` lama tetap tersimpan; judul/ringkasan lama dapat dibaca pada referensi dan catatan bagian lama hanya tampil bila ada.

Alur memilih PDF level lalu membagikannya ke sesi dihapus. Server menolak pemasangan baru satu sumber pada beberapa sesi dalam program, namun tidak menghilangkan tautan lama yang tidak berubah. Snapshot draf/terbit serta pemeriksaan revisi tetap digunakan. Persentase nilai belum ditetapkan/dihitung.

## Penyederhanaan unggah materi sesi

Sesuai koreksi pemilik produk, materi sesi memakai satu area unggah: pilih PDF → otomatis diunggah dan ditambahkan ke sesi → berkas tampil di bawahnya dengan Unduh PDF/Lepas. Pemilih materi dan tombol Hubungkan materi pada sesi dihapus karena menimbulkan kesan perlu memilih ulang. Tombol unggah ulang hanya muncul ketika unggahan gagal. Terbitkan program tetap diperlukan untuk menyimpan hubungan ke kurikulum. Pemilih referensi level/paket tetap tersedia untuk Pustaka Guru. Build dan Chrome memverifikasi dua PDF sesi, terbit/buka ulang, dan tampilan seluler/mode gelap.

## Pratinjau dan penggantian PDF — D-52

Ketika sesi memiliki PDF, area materi menampilkan pratinjau inline dan menyembunyikan input unggah. Pilih Lepas dan konfirmasi sebelum memilih PDF pengganti. Satu PDF per sesi ditegakkan saat menyimpan penempatan baru; daftar berkas lama yang tidak berubah tetap dipertahankan. Rute `/admin/learning/versions/{version}/preview` memeriksa izin yang sama dengan download, memastikan berkas tersedia, dan tidak menampilkan path penyimpanan. Browser tanpa dukungan pratinjau PDF dapat memakai Buka PDF/Unduh PDF. Lepas/ganti tetap diterapkan pada kurikulum melalui Terbitkan perubahan.

## Arsip khusus Superadmin — D-53

Kartu Level yang dihapus dari daftar tidak lagi tampil di editor. Tindakan Hapus level tetap memindahkan objek lengkap ke `archived_levels`, baru tersimpan di server saat program disimpan/diterbitkan. Superadmin mengelolanya melalui Sistem → Arsip Level. Pemulihan mengunci revisi, memindahkan level beserta materi ke daftar aktif dalam draf, membuat snapshot dan audit, serta mempertahankan versi terbit. Admin biasa tidak boleh memulihkan atau mengubah arsip tersimpan lewat API; tetap dapat mengarsipkan level aktif. Pengarsipan/perubahan lokal yang belum disimpan belum muncul di Sistem.

Snapshot program/draf/publikasi dan peralihan arsip masuk Riwayat Aktivitas. Jika pencatatan audit gagal, perubahan program ikut dibatalkan dalam transaksi yang sama.

Subprogram baru yang belum tersimpan dapat dibatalkan melalui **Hapus subprogram baru** di judul panel identitas. Subprogram kosong langsung dikeluarkan; subprogram berisi isian atau level meminta konfirmasi. Pembatalan tetap berlaku setelah pemulihan tab dan tidak menghapus subprogram yang sudah tersimpan.
