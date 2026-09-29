# SIMAPO - Sistem Informasi Manajemen Apotek Bintan 88

[![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-v4.x-38bdf8.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> **Tugas Kuliah:** Manajemen Proyek Sistem Informasi (Prodi Sistem Informasi, Fakultas Ilmu Komputer)  
> **Judul:** Pengembangan Sistem Informasi Manajemen Apotek Berbasis Web Menggunakan Framework Laravel yang Dilengkapi Fitur Notifikasi Menggunakan Telegram pada Apotek Bintan 88.

---

## 📌 Daftar Isi
1. [Tentang SIMAPO](#-tentang-simapo)
2. [Fitur Unggulan](#-fitur-unggulan)
3. [Teknologi & Arsitektur](#-teknologi--arsitektur)
4. [Role & Hak Akses (RBAC)](#-role--hak-akses-rbac)
5. [Akun Demo Pengujian](#-akun-demo-pengujian)
6. [Persyaratan Sistem](#-persyaratan-sistem)
7. [Panduan Instalasi Lokal](#-panduan-instalasi-lokal)
8. [Panduan Lengkap Setup Bot Telegram](#-panduan-lengkap-setup-bot-telegram)
9. [Penjadwalan Otomatis (Cron / Scheduler)](#-penjadwalan-otomatis-cron--scheduler)
10. [Panduan Deployment ke Server Produksi](#-panduan-deployment-ke-server-produksi)
11. [Dokumentasi Teknis Tambahan](#-dokumentasi-teknis-tambahan)

---

## 🏥 Tentang SIMAPO

**SIMAPO (Sistem Informasi Manajemen Apotek Bintan 88)** adalah aplikasi web terintegrasi yang dirancang khusus untuk mengelola seluruh rantai operasional apotek modern. Mulai dari manajemen master data obat, multi-batch expired date, pelacakan mutasi kartu stok, pengadaan barang dan purchase order (PO), pembayaran hutang suplier, kasir Point of Sale (POS) cepat dengan metode **FEFO (*First Expired, First Out*)**, validasi resep dokter, pencetakan struk termal, rekonsiliasi kas shift kasir, hingga pelaporan laba rugi analitis dan sistem peringatan dini otomatis berbasis **Telegram Bot & In-App Notification**.

---

## ✨ Fitur Unggulan

### 1. Manajemen Stok Multi-Batch & FEFO
- **Multi-Batch per Obat**: Satu obat dapat memiliki banyak nomor batch dengan tanggal kedaluwarsa (*expired date*) dan harga beli berbeda.
- **Metode FEFO Otomatis**: Pengurangan stok saat transaksi kasir memprioritaskan batch yang paling cepat kedaluwarsa. Batch kedaluwarsa otomatis diblokir dari penjualan.
- **Indikator Visual Real-Time**: Status stok aman (hijau), menipis (kuning), kosong (merah), hampir kedaluwarsa (oranye), dan kedaluwarsa (merah gelap).
- **Kartu Stok (Mutasi)**: Pelacakan riwayat masuk, keluar, penyesuaian, dan retur dengan saldo berjalan.
- **Stock Opname**: Pencatatan stok fisik berkala dengan kalkulasi selisih otomatis dan pencatatan alasan penyesuaian.
- **Pemusnahan Obat**: Pembuatan Berita Acara Pemusnahan Obat Rusak/Expired dengan cetak dokumen resmi.

### 2. Transaksi Kasir (POS) Satu Layar
- **Antarmuka Responsif & Cepat**: Dibangun dengan Vanilla JS AJAX tanpa reload halaman.
- **Shortcut Keyboard**:
  - `F2`: Fokus cepat ke kolom pencarian obat.
  - `F8`: Buka modal bayar / selesaikan transaksi.
  - `Escape`: Tutup modal aktif.
- **Dukungan Resep & Obat Keras**: Form validasi resep dokter (Nomor SIP, Dokter, Nama Pasien, Tanggal Resep). Obat bertanda `butuh_resep` wajib melalui resep.
- **Metode Pembayaran Fleksibel**: Tunai (kalkulasi kembalian otomatis), Transfer Bank, dan QRIS.
- **Cetak Struk Termal**: Diformat khusus untuk printer kasir 58mm & 80mm dengan CSS `@media print`.
- **Void & Retur Penjualan**: Pembatalan transaksi aman dengan pengembalian stok ke batch asal serta alert otomatis ke Owner/Apoteker.
- **Manajemen Shift Kasir**: Buka shift dengan modal awal dan rekonsiliasi kas akhir shift dengan deteksi selisih uang kas.

### 3. Pengadaan Barang & Hutang Suplier
- **Rekomendasi Pemesanan Otomatis**: Mendeteksi obat yang stoknya di bawah batas minimum dengan tombol cepat *"Buat PO dari Rekomendasi"*.
- **Purchase Order (PO)**: Siklus status PO (*Draft* &rarr; *Dipesan* &rarr; *Diterima Sebagian* &rarr; *Diterima* &rarr; *Batal*).
- **Penerimaan Pembelian**: Input barang masuk beserta nomor batch dan tanggal kedaluwarsa (dari PO maupun langsung).
- **Manajemen Hutang Suplier**: Pembelian tempo/kredit dengan pencatatan tanggal jatuh tempo, tracking sisa hutang, dan riwayat cicilan pelunasan.
- **Retur Pembelian**: Pengembalian obat rusak/salah kirim/hampir kedaluwarsa ke suplier dengan pengurangan stok batch.

### 4. Sistem Notifikasi Cerdas (Telegram & In-App)
- **12 Trigger Peringatan Otomatis**:
  1. ⚠️ Stok obat menipis (≤ stok minimum)
  2. 🚨 Stok obat kosong (= 0)
  3. ⏳ Obat hampir kedaluwarsa (H-90, H-60, H-30 hari)
  4. ❌ Obat sudah kedaluwarsa
  5. 💳 Hutang suplier jatuh tempo (H-3, H-1, dan lewat jatuh tempo)
  6. 📦 PO belum diterima melebihi estimasi
  7. 📊 Ringkasan penjualan harian (dikirim otomatis jam 21:00)
  8. 🚫 Pembatalan transaksi (VOID) & Retur penjualan
  9. 🚚 Penerimaan barang pembelian baru
  10. ⚖️ Selisih signifikan hasil Stock Opname
  11. 💰 Selisih kas saat tutup shift kasir
  12. 🔒 Peringatan login gagal mencurigakan
- **Anti-Spam Cerdas**: Sistem mencegah pengiriman notifikasi berulang untuk entitas yang sama pada hari yang sama.
- **In-App Real-time Polling**: Ikon lonceng interaktif dengan indikator badge belum dibaca dan dropdown mutakhir.
- **Riwayat & Retry Notifikasi**: Tabel log pengiriman notifikasi dengan tombol kirim ulang (*retry*) jika Telegram sempat gagal/timeout.

### 5. Laporan Lengkap & Ekspor PDF / Excel
Tersedia 11 laporan operasional dengan filter periode fleksibel:
1. Laporan Penjualan (Harian, Bulanan, per Kasir, per Metode Bayar)
2. Laporan Penjualan per Obat (Top Terlaris)
3. Laporan Penjualan Obat Resep (Kepatuhan Apoteker & Regulasi BPOM)
4. Laporan Pembelian & Hutang Suplier
5. Laporan Nilai Persediaan Stok Saat Ini
6. Laporan Obat Menipis & Kosong
7. Laporan Obat Kedaluwarsa & Hampir Kedaluwarsa
8. Laporan Retur (Penjualan & Pembelian)
9. Laporan Mutasi & Kartu Stok
10. Laporan Laba Rugi Sederhana (Penjualan − HPP − Diskon)
11. Audit Trail / Log Aktivitas User

---

## 🛠 Teknologi & Arsitektur

- **Backend**: Laravel 13 (PHP 8.2+) dengan arsitektur MVC bersih.
- **Database**: MySQL / MariaDB (29 tabel ternormalisasi dengan foreign keys & soft deletes).
- **Frontend Styling**: Tailwind CSS v4 via `@tailwindcss/vite` dengan palet warna modern apotek (*Emerald & Teal*).
- **Client Scripting**: Pure Vanilla JavaScript (Modern ES6 Fetch API, tidak menggunakan jQuery, Vue, React, atau Livewire).
- **Visualisasi Data**: Chart.js v4 untuk grafik tren penjualan dan top 5 obat.
- **Ekspor Dokumen**:
  - `barryvdh/laravel-dompdf` untuk cetak PDF laporan & berita acara.
  - Streaming CSV/Excel kompatibel Microsoft Excel.
- **Autentikasi**: Custom Session-Based Auth dengan password bcrypt dan penguncian session berbasis database.

---

## 👥 Role & Hak Akses (RBAC)

Aplikasi menerapkan kontrol hak akses peran (Role-Based Access Control) yang ketat melalui `RoleMiddleware`:

| Modul / Fitur | Owner | Apoteker | Kasir | Admin Gudang |
|---|:---:|:---:|:---:|:---:|
| **Dashboard Analitik** | ✅ Penuh | ✅ Operasional | ✅ Kasir | ✅ Gudang |
| **Manajemen User** | ✅ Penuh | ❌ | ❌ | ❌ |
| **Master Obat & Kategori** | ✅ Penuh | ✅ Penuh | 👁️ Read-Only | 👁️ Read-Only |
| **Master Suplier & Kontak** | ✅ Penuh | ✅ Penuh | ❌ | ✅ Penuh |
| **Stok & Batch Obat** | ✅ Penuh | ✅ Penuh | 👁️ Read-Only | ✅ Penuh |
| **Stock Opname & Pemusnahan** | ✅ Penuh | ✅ Penuh | ❌ | ✅ Penuh |
| **Pengadaan & PO** | ✅ Penuh | ✅ Penuh | ❌ | ✅ Penuh |
| **Penerimaan Pembelian & Hutang**| ✅ Penuh | ✅ Penuh | ❌ | ✅ Penuh |
| **Kasir POS & Shift** | ✅ Penuh | ✅ Penuh | ✅ Penuh | ❌ |
| **Void Transaksi POS** | ✅ Penuh | ✅ Penuh | ❌ | ❌ |
| **Semua Laporan & Laba Rugi** | ✅ Penuh | ⚠️ Stok & Jual | ❌ | ⚠️ Stok & Beli |
| **Pengaturan & Notifikasi** | ✅ Penuh | ❌ | ❌ | ❌ |

---

## 🔑 Akun Demo Pengujian

Semua akun demo di bawah ini telah disiapkan melalui seeder database dengan password default: **`password`**

| Role | Nama Pengguna | Email Login | Password |
|---|---|---|---|
| 👑 **Owner** | Budi Santoso, S.Farm | `owner@apotekbintan88.com` | `password` |
| 💊 **Apoteker** | Apt. Siti Nurhaliza, S.Farm | `apoteker@apotekbintan88.com` | `password` |
| 💵 **Kasir** | Rina Wulandari | `kasir@apotekbintan88.com` | `password` |
| 📦 **Admin Gudang** | Dedi Pratama | `gudang@apotekbintan88.com` | `password` |

---

## 💻 Persyaratan Sistem

- PHP >= 8.2 dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`.
- Composer >= 2.x
- Node.js >= 18.x & NPM >= 9.x
- MySQL >= 8.0 atau MariaDB >= 10.4

---

## 🚀 Panduan Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan SIMAPO di komputer lokal:

### 1. Clone Repositori
```bash
git clone https://github.com/ad4n6/simapo.git
cd simapo
```

### 2. Pasang Dependensi Backend & Frontend
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Buka file `.env` dan sesuaikan kredensial database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simapo
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Migrasi dan Seeding Database
Jalankan migrasi seluruh 29 tabel dan isi dengan data uji coba realistis:
```bash
php artisan migrate:fresh --seed
```

### 6. Build Frontend Assets
Kompilasi asset Tailwind CSS v4 dan JavaScript menggunakan Vite:
```bash
npm run build
```
*(Atau gunakan `npm run dev` jika sedang dalam tahap pengembangan aktif)*.

### 7. Hubungkan Storage Link (Opsional)
```bash
php artisan storage:link
```

### 8. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui peramban web di: **`http://127.0.0.1:8000`** atau URL lokal Herd/Valet Anda.

---

## 🤖 Panduan Lengkap Setup Bot Telegram

SIMAPO mengintegrasikan Bot Telegram resmi untuk mengirimkan peringatan stok, jatuh tempo hutang, dan laporan harian secara otomatis.

### Langkah 1: Buat Bot Baru via `@BotFather`
1. Buka aplikasi Telegram di HP atau Desktop Anda.
2. Cari akun resmi **`@BotFather`** (bertanda centang biru).
3. Klik tombol **Start** atau ketik pesan `/start`.
4. Ketik perintah `/newbot`.
5. Beri nama bot Anda (contoh: `Apotek Bintan 88 Bot`).
6. Tentukan username unik berakhiran `bot` (contoh: `bintan88_apotek_bot`).
7. BotFather akan memberikan **HTTP API Bot Token**, misalnya:
   ```
   7123456789:AAFlkjhsdf89234jsdf-kJhsdf98234
   ```
   *Simpan token ini dengan aman!*

### Langkah 2: Dapatkan Telegram Chat ID
Anda dapat mengirim notifikasi ke **Chat Pribadi** atau ke **Grup Karyawan Apotek**.

#### Opsi A: Chat ID Akun Pribadi
1. Di Telegram, cari akun bot **`@userinfobot`** atau **`@myidbot`**.
2. Klik **Start**. Bot akan membalas dengan menampilkan **Id** angka Anda (contoh: `123456789`).

#### Opsi B: Chat ID Grup / Channel Apotek
1. Buat Grup Telegram baru (misalnya *"Notifikasi Apotek Bintan 88"*).
2. Masukkan bot Anda (`@bintan88_apotek_bot`) ke dalam grup tersebut sebagai **Administrator**.
3. Kirim satu pesan acak apa saja ke dalam grup (misalnya: `tes bot`).
4. Buka peramban dan akses URL berikut (ganti `{BOT_TOKEN}` dengan token bot Anda):
   ```
   https://api.telegram.org/bot{BOT_TOKEN}/getUpdates
   ```
5. Cari potongan JSON `"chat":{"id": -1001234567890, ...}`. Angka negatif tersebut (misal `-1001234567890`) adalah Group Chat ID Anda.

### Langkah 3: Konfigurasi di SIMAPO
1. Login ke SIMAPO sebagai **Owner** (`owner@apotekbintan88.com`).
2. Masuk ke menu **Pengaturan &gt; Pengaturan Apotek & Notifikasi**.
3. Masukkan **Telegram Bot Token** dan **Telegram Chat ID** pada formulir yang tersedia.
4. Klik tombol **"Kirim Pesan Tes"**.
5. Cek aplikasi Telegram Anda. Jika pesan tes berhasil masuk, integrasi Telegram siap digunakan!

---

## ⏰ Penjadwalan Otomatis (Cron / Scheduler)

Pengecekan stok, kedaluwarsa, dan pengiriman notifikasi harian dijalankan oleh Laravel Scheduler.

### Perintah Pengecekan Manual
Anda dapat menguji perintah kapan saja via terminal:
```bash
# Cek obat kosong & menipis
php artisan notifikasi:cek-stok --force

# Cek obat expired & hampir expired
php artisan notifikasi:cek-expired --force

# Cek hutang jatuh tempo
php artisan notifikasi:cek-hutang --force

# Kirim ringkasan penjualan harian
php artisan notifikasi:ringkasan-harian --force
```

### Konfigurasi Cron di Linux VPS / cPanel
Tambahkan satu baris cron job berikut di server Anda (jalankan `crontab -e`):
```cron
* * * * * cd /path/to/simapo && php artisan schedule:run >> /dev/null 2>&1
```

### Jadwal Otomatis yang Terdaftar di `routes/console.php`:
- **08:00 WIB**: Pengecekan stok obat kosong & menipis.
- **08:30 WIB**: Pengecekan batch obat kedaluwarsa & hampir expired.
- **09:00 WIB**: Pengecekan invoice hutang suplier jatuh tempo.
- **21:00 WIB**: Pengiriman rekapitulasi ringkasan penjualan harian ke Owner.

---

## 🌐 Panduan Deployment ke Server Produksi

### 1. Konfigurasi Virtual Host (Nginx)
Contoh konfigurasi Nginx untuk SIMAPO:
```nginx
server {
    listen 80;
    server_name simapo.apotekbintan88.com;
    root /var/www/simapo/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 2. Hak Akses Folder Server
```bash
sudo chown -R www-data:www-data /var/www/simapo
sudo chmod -R 775 /var/www/simapo/storage /var/www/simapo/bootstrap/cache
```

### 3. Optimasi Cache Produksi
Jalankan perintah ini di server produksi untuk performa maksimal:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

---

## 📚 Dokumentasi Teknis Tambahan

Untuk keperluan lampiran laporan tugas besar dan tinjauan teknis, dokumen pendukung telah disediakan di direktori `docs/`:
- 📄 [**Kamus Data Database (29 Tabel)**](docs/kamus_data.md): Struktur tabel, tipe data, indeks, dan relasi foreign key.
- 🔐 [**Matriks Hak Akses Role (RBAC)**](docs/hak_akses_role.md): Pembagian hak akses per modul, rute, dan tindakan.
- 🔄 [**Diagram Alur Sistem (Mermaid)**](docs/diagram_alur.md): Diagram alur kasir POS & FEFO, pengadaan barang, notifikasi Telegram, dan rekonsiliasi kas shift.

---
© 2026 **Apotek Bintan 88** — Dikembangkan untuk Mata Kuliah Manajemen Proyek Sistem Informasi.
