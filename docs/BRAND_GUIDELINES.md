# Identitas Visual — Tong Yi Group

Tanggal: 6 November 2026 · Acuan keputusan D-31 dan D-32.

## Logo resmi

![Logo Tong Yi Group](../assets/brand/tong-yi-group-logo.png)

Berkas sumber: [tong-yi-group-logo.png](../assets/brand/tong-yi-group-logo.png).

Logo PNG diberikan dan dipilih oleh pemilik produk. Simpan dan gunakan berkas asli tanpa menggambar ulang, mengubah warna, teks Mandarin/Latin, komposisi, atau proporsinya. Berkas aktif adalah PNG transparan berukuran 1718 × 915 piksel yang dipilih dan disetujui pemilik produk pada D-32. Sumber sebelumnya dengan latar terang dipertahankan di `assets/brand/tong-yi-group-logo-original.png`.

Tampilkan dengan rasio asli (`object-fit: contain`), tanpa peregangan atau pemotongan. Untuk ruang sempit, tampilkan nama Tong Yi Group dan sediakan tempat logo yang cukup; jangan memotong simbol sendiri untuk membuat varian baru. Varian transparan saat ini sudah disetujui pemilik produk. Varian monokrom, ikon terpisah, dan favicon membutuhkan aset tersendiri yang ditinjau pemilik produk.

## Arah tema — Disepakati

Tema aplikasi mengikuti warna logo: **biru sebagai warna utama, oranye sebagai aksen**. Gunakan latar terang, teks gelap, dan tampilan rapi serta profesional. Berlaku untuk Admin, Guru, dan Siswa/Wali; pengerjaan awal tetap Admin Program.

## Palet awal implementasi — Rancangan

Logo mempunyai gradasi dan banyak warna. Nilai berikut merupakan padanan antarmuka yang diusulkan dari arah logo, bukan kode merek resmi yang telah ditetapkan pemilik produk. Jangan mewarnai ulang logo mengikuti token ini.

| Peran | Warna | Penggunaan |
|---|---|---|
| Biru utama | `#0B3B78` | Tombol utama, identitas navigasi, judul terpilih |
| Biru interaktif | `#006EB8` | Tautan, penanda fokus dan navigasi aktif |
| Biru lembut | `#EAF3FB` | Latar pilihan aktif atau informasi |
| Oranye aksen | `#F5A602` | Sorotan dan aksen pendukung; gunakan teks gelap |
| Oranye lembut | `#FFF3DA` | Latar sorotan ringan |
| Latar halaman | `#F7F9FC` | Ruang kerja aplikasi |
| Permukaan | `#FFFFFF` | Tabel, kartu, formulir |
| Teks utama | `#172B4D` | Isi dan label |
| Teks sekunder | `#526279` | Keterangan |
| Garis pembatas | `#DCE4EE` | Tabel, input, pemisah |

## Penerapan

- Gunakan token semantik terpusat ketika fondasi aplikasi dibuat; jangan menyebar kode warna di setiap halaman.
- Satu tindakan utama berwarna biru pada area kerja; oranye sebagai aksen secukupnya agar formulir dan tabel tetap tenang.
- Periksa kontras teks, tombol, tautan, serta fokus keyboard pada pasangan warna sebenarnya. Hindari teks putih kecil pada oranye terang.
- Status berhasil, peringatan, dan gagal tetap memiliki warna semantik, teks, serta ikon yang jelas. Warna merek tidak menggantikan arti status.
- Pertahankan tabel program, formulir halaman penuh, dan pengelompokan pengaturan sesuai BRD.
- Dilarang menambah fitur/prototipe atau mengubah aturan bisnis hanya karena pembaruan identitas visual.

## Judul dan ikon tab

Sesuai permintaan pemilik produk, tab halaman depan memakai judul Mandarin `同一集团`. Ikon tab menggunakan salinan PNG logo yang telah disetujui di `public/tong-yi-logo.png`, tanpa pemotongan atau pengubahan gambar.

## Tombol dan tautan

Utamakan label teks yang jelas. Sesuai arahan pemilik produk, hindari panah atau simbol dekoratif berulang pada tombol dan tautan; ikon hanya digunakan bila membantu memahami tindakan atau status.

## Layout ruang Admin

Ruang Admin memakai sidebar navigasi pada desktop yang dapat ditutup/dibuka melalui header, identitas akun, dan area konten yang konsisten. Toolbar Simpan draf/Terbitkan berada di atas formulir; jangan menambahkan toolbar bawah yang menutup isian. Pada layar kecil, navigasi dapat dibuka melalui tombol Menu dan ditutup dengan Escape. Gunakan status aktif yang jelas, ringkasan berdasarkan data sebenarnya, serta tombol berlabel teks tanpa panah dekoratif. Navigasi utama hanya memuat modul yang sudah tersedia.

## Isian nominal uang

Semua isian nominal menampilkan awalan `Rp` dan pemisah angka Indonesia, misalnya `Rp 1.250.000,50` (contoh format, bukan tarif). Gunakan komponen bersama `RupiahField.vue`; jangan memakai input angka polos untuk nominal. Nilai kosong tetap dibedakan dari nol, dan angka tidak dibulatkan diam-diam. Jumlah peserta/paket/sesi bukan nominal uang.

Kontrol sidebar desktop memakai ikon kecil di sudut sidebar untuk menutup dan di tepi kiri untuk membuka. Tidak menggunakan teks Buka/Tutup pada header. Setiap ikon tindakan tetap mempunyai label aksesibilitas, tooltip, dan fokus keyboard.

## Penyempurnaan warna Admin — D-46 (menggantikan palet D-45)

Pemilik produk mengoreksi warna D-45 karena dominasi biru/krem terlalu berat. Putih menjadi permukaan utama untuk sidebar, header, formulir, dan toolbar. Kanvas abu-abu netral `#F4F5F7` membedakan area kerja dari panel. Biru dipakai pada tombol utama, tautan, dan pilihan aktif dengan latar tipis; oranye menjadi penanda kecil, bukan bidang besar. Hindari gradasi serta panel krem. Judul dan isi menggunakan teks gelap netral agar tidak seluruh halaman berwarna biru.

Aturan khusus Admin terpusat pada `resources/css/admin-theme.css`; logo dan tema publik tidak berubah. Urutan navigasi tetap per kelompok Ruang Kerja/Superadmin.

## Teks antarmuka

Gunakan label singkat dan langsung sesuai koreksi pemilik produk: **Latihan**, bukan “Latihan Resmi”. Hindari slogan, judul kecil berulang, dan keterangan yang tidak membantu tindakan pengguna. Pertahankan penjelasan singkat bila diperlukan untuk membedakan soal siswa dari panduan guru. Penyederhanaan label tidak mengubah aturan latihan wajib atau kewenangannya.

Navigasi Admin diringkas menjadi Dashboard serta kelompok Akademik dan Pengaturan yang dapat dibuka/tutup. Kelompok halaman aktif terbuka otomatis; hanya menu berizin yang muncul. Deskripsi berulang pada setiap tautan sidebar dihilangkan. Pola yang sama dipakai pada seluler.

## Mode gelap Admin

Tombol ikon bulan/matahari pada header mengganti mode terang/gelap. Pilihan diingat browser melalui `tong-yi-admin-theme` dan disinkronkan antartab; tanpa pilihan tersimpan, tampilan awal mengikuti preferensi sistem. Tema dibatasi pada ruang Admin, dengan token untuk kanvas, panel, input, garis, teks, pilihan aktif, serta status kesalahan. Logo asli tetap tanpa filter/perubahan warna dan diberi bidang putih agar terbaca pada mode gelap. Tombol memiliki label aksesibilitas dan fokus keyboard.

Verifikasi browser mencakup tabel menu, formulir program/soal, pengguna, perpindahan halaman, reload, retensi isian ketika mengganti tema, tombol seluler, dan preferensi awal sistem. Halaman publik tidak ikut berubah.

## Istilah bahasa Indonesia

Utamakan bahasa Indonesia baku dan label ringkas. Sesuai arahan pemilik produk: Library Materi → **Modul**, Dashboard → **Dasbor**, Permission → **Hak Akses**. Modul tetap dapat memuat PDF dan tautan bahan ajar. Perubahan istilah hanya pada antarmuka; nama teknis/route, data pengguna, dan kewenangan tidak berubah. Label menu yang sudah dikustomisasi tidak ditimpa oleh pembaruan bawaan.

## Tab kerja Admin

Semua menu menggunakan tab kerja ringkas di atas konten. Tab menampilkan nama menu, titik oranye jika ada isian belum disimpan, dan ikon × berlabel aksesibilitas untuk menutup. Klik menu yang sudah terbuka kembali ke tab tersebut. Pada seluler, deretan tab bergulir di dalam area tab tanpa memperlebar halaman. Isian belum disimpan memerlukan konfirmasi sebelum tab ditutup; selama penyimpanan tombol tutup dinonaktifkan.

Area Sistem khusus Superadmin memakai tabel sederhana untuk Riwayat Aktivitas dan Arsip Level, dengan filter, detail perubahan halaman penuh, serta konfirmasi pemulihan. Jangan menampilkan kartu arsip/histori berulang pada editor program. Riwayat transaksi dan arsip pemulihan memiliki fungsi berbeda.
