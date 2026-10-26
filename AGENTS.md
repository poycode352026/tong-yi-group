# Instruksi Pengembangan Tong Yi Group

## Acuan

Baca `docs/BRD.md`, `docs/DECISIONS.md`, dan `docs/IMPLEMENTATION_GUIDE.md` sebelum mengerjakan fitur. Koreksi dan instruksi terbaru pemilik produk berlaku; jika berbeda dengan dokumen, perbarui dokumentasi yang relevan.

## Konteks kerja

- Pengembangan dilakukan bersama pengguna di VS Code menggunakan Codex.
- Prioritas awal adalah Admin Tong Yi, terutama pengelolaan program.
- Stack yang disepakati: Laravel untuk sisi server, Vue.js untuk antarmuka, Inertia.js sebagai penghubung halaman Vue dengan route/controller Laravel, dan MySQL sebagai basis data.
- Gunakan stack tersebut sebagai acuan pengembangan. Versi dependensi dan detail infrastruktur ditetapkan saat penyiapan fondasi; jangan mengganti stack tanpa keputusan pengguna.
- Jika kode aplikasi sudah ada, periksa fondasi yang digunakan sebelum mengubah arsitektur.

## Aturan bisnis yang wajib dijaga

- Satu program dapat mempunyai beberapa subprogram; level/kurikulum dan variasi Simplified/Traditional berdiri sendiri.
- Layanan ditambahkan sesuai kebutuhan, dengan harga dan kapasitas masing-masing. Jangan mewajibkan semua kombinasi online/offline dan group/private.
- Paket normal delapan sesi; kurikulum terstruktur per paket dan sesi.
- Program Akademik memakai tabel dan formulir halaman penuh; hindari navigasi bertingkat penuh tombol atau modal besar untuk pekerjaan panjang.
- Draf/validasi tidak boleh menghilangkan isian diam-diam. Bedakan draf dari data yang disimpan/diterbitkan.
- Kemampuan guru berbeda dari kesediaan membuka kelas. Izin tidak naik otomatis bersama siswa.
- Pembayaran pelatihan bukan kelulusan asesmen. Sertifikat internal Tong Yi bukan HSK resmi.
- Hak sesi yang sudah dibayar tetap dilindungi; ketentuan absensi dan refund yang belum diputuskan tidak boleh ditebak.
- Presensi dan dokumentasi bagian dari sesi dalam sistem; tidak ada laporan presensi manual tambahan untuk payroll.
- Klaim normal per kelas setelah delapan sesi lengkap; cegah klaim ganda.
- Transfer dilakukan Admin; status Dibayarkan memerlukan pencatatan transfer/bukti.
- Batas pencairan hari operasional berikutnya, mengecualikan Minggu/tanggal merah; Sabtu dihitung.
- Siswa belum lulus boleh lanjut/mengulang, tetapi sertifikat dan riwayat kelulusan per level tetap mengikuti hasil yang benar.
- Jangan menetapkan bagi hasil 70/30, tarif, ambang nilai, atau angka lain sebagai kebijakan resmi tanpa keputusan pengguna.

## Cara bekerja

- Kerjakan perubahan kecil yang utuh, dengan bahasa Indonesia yang rapi pada produk.
- Jangan meminta pengguna mengulang keputusan yang sudah tersedia di dokumen/konteks. Telusuri acuan lebih dahulu.
- Jika detail bisnis belum ditetapkan, identifikasi pertanyaan spesifik dan lanjutkan pekerjaan yang tidak bergantung padanya.
- Prototipe adalah referensi interaksi, bukan skema produksi atau mandat menyalin kodenya.
- Rancang penyimpanan, otorisasi, audit, dan integritas sesuai tahap implementasi.
- Verifikasi aturan penting menggunakan skenario penerimaan yang relevan. Jangan menulis tes yang menjadikan asumsi sebagai kebijakan.
- Laporkan apa yang berubah, apa yang diverifikasi, dan apa yang belum selesai secara jujur.
- Jangan menyatakan sistem siap produksi hanya karena tampilan atau demo berhasil.
