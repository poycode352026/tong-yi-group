# Tong Yi Group

Repositori pengembangan sistem pengelolaan pembelajaran **Tong Yi Group**.

## Mulai dari dokumentasi

| Dokumen | Kegunaan |
|---|---|
| [BRD — Kebutuhan Bisnis](docs/BRD.md) | Tujuan produk, lingkup, peran, aturan bisnis, kebutuhan, dan skenario penerimaan |
| [Catatan Keputusan](docs/DECISIONS.md) | Keputusan disepakati, koreksi yang menggantikan usulan lama, dan detail yang masih terbuka |
| [Panduan Pengembangan](docs/IMPLEMENTATION_GUIDE.md) | Urutan implementasi dan aturan penggunaan BRD oleh pengembang/agent |

**Status:** dokumentasi versi 0.2, 26 Oktober 2026. Stack teknologi telah disepakati; repositori belum berisi aplikasi produksi.

## Stack teknologi

| Teknologi | Peran |
|---|---|
| Laravel | Logika bisnis, route/controller, validasi, akses basis data, autentikasi, dan otorisasi pada server |
| Vue.js | Antarmuka pengguna berbasis komponen dan formulir interaktif |
| Inertia.js | Menghubungkan halaman Vue dengan route/controller Laravel dan menangani navigasi antarhalaman |
| MySQL | Penyimpanan dan pengolahan data terstruktur |

Keputusan stack tercatat pada [D-29](docs/DECISIONS.md). Versi dependensi, deployment, penyimpanan berkas, dan detail autentikasi masih perlu ditetapkan saat penyiapan fondasi.

Prioritas awal adalah ruang **Admin Tong Yi**, terutama pengelolaan program. Satu program dapat memiliki beberapa subprogram; setiap subprogram memiliki level/kurikulum dan hanya layanan yang benar-benar ditawarkan. Satu paket belajar normal terdiri dari delapan sesi.

## Pengembangan di VS Code dengan Codex

Clone repositori ini, buka foldernya di VS Code, lalu gunakan Codex untuk membaca [AGENTS.md](AGENTS.md) dan dokumen acuan. Pengembangan dilakukan bersama secara bertahap, dimulai dari fondasi teknis dan modul Admin Program.

Prototipe yang pernah dibahas hanya referensi percobaan interaksi; pengembangan aplikasi mengacu pada kebutuhan bisnis, tanpa kewajiban menyalin kode atau membuat prototipe tambahan.

## Prinsip pengembangan

- Jangan mengubah keputusan yang sudah disepakati tanpa mencatat perubahan.
- Jangan menetapkan angka/kebijakan yang masih terbuka secara diam-diam.
- Jangan menganggap pembayaran sebagai bukti kelulusan atau izin mengajar.
- Presensi, dokumentasi, hak sesi, dan klaim harus terintegrasi.
- Bangun dan uji bertahap; cocokkan dengan skenario penerimaan dalam BRD.
