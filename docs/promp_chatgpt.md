# PROMPT PENGEMBANGAN SISTEM INFORMASI MANAJEMEN APOTEK BINTAN 88

## 1. KONTEKS PROYEK

Saya sedang mengembangkan sebuah proyek untuk memenuhi tugas mata kuliah:

**Manajemen Proyek Sistem Informasi**
**Fakultas Ilmu Komputer**
**Program Studi Sistem Informasi**

Judul proyek:

> **"Pengembangan Sistem Informasi Manajemen Apotek Berbasis Web Menggunakan Framework Laravel yang Dilengkapi Fitur Notifikasi Menggunakan Telegram pada Apotek Bintan 88"**

Bangun aplikasi berbasis web untuk membantu proses operasional dan administrasi **Apotek Bintan 88**.

Aplikasi ini ditujukan sebagai **proyek akademik/penugasan kuliah**, sehingga fokus utama adalah:

* kelengkapan fitur
* alur bisnis yang mudah dipahami
* UI yang rapi dan responsif
* implementasi CRUD dan transaksi
* laporan
* RBAC
* integrasi notifikasi Telegram
* mudah dipresentasikan dan didemonstrasikan

**Jangan membuat arsitektur enterprise yang terlalu kompleks.**

Jangan over-engineering.

Prioritaskan aplikasi yang benar-benar dapat dijalankan dan didemonstrasikan dari awal sampai akhir.

---

# 2. TEKNOLOGI

Gunakan teknologi berikut:

* Backend: **Laravel 13**
* Database: **MySQL**
* Frontend: **Blade**
* JavaScript: **Vanilla JavaScript**
* AJAX: gunakan `fetch()` / XMLHttpRequest bila diperlukan
* CSS: **Tailwind CSS**
* Authentication: Laravel Authentication
* Authorization: gunakan pendekatan RBAC sederhana, dapat menggunakan `spatie/laravel-permission` jika diperlukan
* Notification: **Telegram Bot API**
* Deployment target: **VPS atau Web Hosting**
* Database: MySQL

Hindari penggunaan:

* React
* Vue
* Next.js
* Inertia.js
* Livewire

kecuali benar-benar diperlukan.

Frontend harus tetap sederhana menggunakan Blade + Vanilla JS/AJAX.

---

# 3. TUJUAN SISTEM

Sistem harus membantu Apotek Bintan 88 dalam:

1. Mengelola data obat.
2. Mengelola stok obat.
3. Memantau nomor batch.
4. Memantau tanggal kedaluwarsa.
5. Mencegah stok kosong.
6. Mencatat pembelian/pengadaan obat.
7. Mencatat retur pembelian.
8. Mencatat transaksi penjualan.
9. Mendukung transaksi obat bebas.
10. Mendukung transaksi obat berdasarkan resep dokter.
11. Mengelola supplier.
12. Mengelola pelanggan/pasien jika diperlukan.
13. Menghasilkan laporan.
14. Memberikan notifikasi melalui Telegram.
15. Menyediakan dashboard informasi operasional apotek.

---

# 4. ROLE / RBAC

Gunakan role berikut:

### 1. Owner

Memiliki akses paling luas.

Hak akses:

* Dashboard
* Melihat seluruh data
* Manajemen pengguna
* Manajemen obat
* Manajemen kategori obat
* Manajemen supplier
* Manajemen stok
* Pengadaan/pembelian
* Retur pembelian
* Penjualan
* Resep
* Laporan
* Pengaturan sistem
* Pengaturan Telegram
* Melihat log aktivitas sederhana

---

### 2. Apoteker

Fokus pada pengelolaan obat dan pelayanan kefarmasian.

Hak akses:

* Dashboard
* Manajemen obat
* Kategori obat
* Satuan obat
* Stok obat
* Batch obat
* Expired date
* Supplier
* Pembelian
* Retur pembelian
* Resep dokter
* Penjualan
* Laporan yang berkaitan dengan obat
* Notifikasi stok dan expired

---

### 3. Kasir

Fokus pada transaksi penjualan.

Hak akses:

* Dashboard sederhana
* POS / transaksi penjualan
* Melihat data obat
* Mencari obat
* Melihat harga dan stok
* Transaksi berdasarkan resep jika diperlukan
* Cetak/print struk
* Riwayat transaksi miliknya
* Melihat pelanggan/pasien

Kasir tidak boleh mengubah data master obat dan stok secara langsung.

---

### 4. Petugas Gudang / Admin Gudang

Tambahkan role ini karena relevan dengan pengelolaan stok.

Hak akses:

* Dashboard gudang
* Data obat
* Stok
* Batch obat
* Pembelian
* Penerimaan barang
* Retur pembelian
* Supplier
* Laporan stok
* Notifikasi stok menipis
* Notifikasi expired

Tidak memiliki akses ke pengaturan pengguna dan transaksi kasir.

---

# 5. MODUL SISTEM

Bangun modul berikut.

---

## A. AUTHENTICATION

Implementasikan:

* Login
* Logout
* Profil pengguna
* Ubah password
* Role-based access

Gunakan UI login yang sederhana tetapi modern.

---

# 6. DASHBOARD

Dashboard harus berbeda berdasarkan role.

## Dashboard Owner

Tampilkan:

* Total jenis obat
* Total stok obat
* Obat stok menipis
* Obat stok kosong
* Obat akan expired
* Obat expired
* Total transaksi hari ini
* Total penjualan hari ini
* Total pembelian bulan berjalan
* Grafik penjualan
* Grafik pembelian
* Transaksi terbaru
* Notifikasi penting

---

## Dashboard Apoteker

Tampilkan:

* Total obat
* Obat stok menipis
* Obat kosong
* Obat akan expired
* Obat expired
* Pembelian terbaru
* Penjualan terbaru
* Notifikasi obat

---

## Dashboard Kasir

Tampilkan:

* Total transaksi hari ini
* Total penjualan hari ini
* Transaksi terbaru
* Shortcut menuju POS

---

## Dashboard Gudang

Tampilkan:

* Total stok
* Stok menipis
* Stok kosong
* Akan expired
* Expired
* Pembelian terbaru

---

# 7. MASTER DATA OBAT

Buat CRUD obat.

Field minimal:

* kode_obat
* barcode
* nama_obat
* kategori_obat
* jenis_obat
* satuan
* produsen
* deskripsi
* harga_beli
* harga_jual
* stok_minimum
* status_aktif

Jenis obat dapat berupa:

* Obat Bebas
* Obat Bebas Terbatas
* Obat Keras
* Obat Herbal
* Vitamin
* Suplemen
* Alat Kesehatan
* Lainnya

Tambahkan pencarian dan filter.

---

# 8. KATEGORI OBAT

CRUD:

* Nama kategori
* Deskripsi
* Status aktif

---

# 9. SATUAN OBAT

CRUD:

* Tablet
* Kapsul
* Botol
* Strip
* Tube
* Sachet
* Box
* Unit
* dan lainnya.

Buat tabel satuan sehingga dapat digunakan oleh obat.

---

# 10. BATCH DAN EXPIRY

Ini merupakan salah satu fitur utama sistem.

Satu obat dapat memiliki beberapa batch.

Contoh:

Paracetamol 500mg:

* Batch: PCM001
* Expired: 2027-05-10
* Stok: 100

Batch lain:

* Batch: PCM002
* Expired: 2028-01-20
* Stok: 200

Sediakan:

* nomor batch
* tanggal produksi jika diperlukan
* tanggal expired
* jumlah stok
* harga beli
* lokasi penyimpanan
* status batch

Gunakan konsep **FEFO (First Expired, First Out)** secara sederhana.

Ketika melakukan transaksi penjualan, prioritaskan batch yang memiliki tanggal expired paling dekat.

---

# 11. MANAJEMEN STOK

Sediakan fitur:

* Stok masuk
* Stok keluar
* Penyesuaian stok
* Riwayat perubahan stok
* Stok minimum
* Stok kosong
* Stok menipis
* Stok aman

Kategori status stok:

### Kosong

stok = 0

### Menipis

stok > 0 tetapi <= stok minimum

### Aman

stok > stok minimum

Tampilkan badge/status dengan jelas.

Sediakan halaman:

**Riwayat Stok**

dengan informasi:

* tanggal
* obat
* batch
* tipe transaksi
* jumlah
* stok sebelum
* stok sesudah
* keterangan
* user

---

# 12. SUPPLIER

CRUD supplier.

Field:

* kode supplier
* nama supplier
* alamat
* nomor telepon
* email
* nama kontak
* catatan
* status aktif

---

# 13. PENGADAAN / PEMBELIAN OBAT

Buat modul pembelian obat.

Data header:

* nomor pembelian
* tanggal pembelian
* supplier
* nomor faktur
* total pembelian
* status

Detail:

* obat
* batch
* expired date
* jumlah
* harga beli
* subtotal

Alur:

1. User membuat pembelian.
2. Memilih supplier.
3. Menambahkan obat.
4. Menentukan batch.
5. Menentukan expired date.
6. Menentukan jumlah.
7. Sistem menghitung subtotal.
8. Sistem menghitung total.
9. Pembelian disimpan.
10. Setelah pembelian diterima, stok bertambah.

Gunakan status:

* Draft
* Diterima
* Dibatalkan

Stok hanya bertambah ketika pembelian berstatus **Diterima**.

---

# 14. RETUR PEMBELIAN

Sediakan fitur retur obat kepada supplier.

Data:

* nomor retur
* tanggal
* supplier
* pembelian terkait
* obat
* batch
* jumlah
* alasan retur
* status

Ketika retur dikonfirmasi:

stok harus berkurang.

---

# 15. POS / TRANSAKSI KASIR

Buat halaman POS yang sederhana dan cepat.

Fitur:

* Search obat
* Search berdasarkan nama
* Search berdasarkan barcode
* Tambahkan obat ke keranjang
* Ubah jumlah
* Hapus item
* Hitung subtotal
* Diskon jika diperlukan
* Total
* Uang pembayaran
* Kembalian
* Simpan transaksi
* Cetak struk

Metode pembayaran:

* Tunai
* Transfer
* QRIS
* Debit
* Lainnya

Tidak perlu integrasi payment gateway.

---

# 16. PENJUALAN OBAT DENGAN RESEP

Sistem harus dapat membedakan:

### Penjualan tanpa resep

Untuk obat yang tidak membutuhkan resep.

### Penjualan dengan resep

Data tambahan:

* nomor resep
* tanggal resep
* nama dokter
* nomor SIP dokter jika diperlukan
* nama pasien
* catatan resep
* foto/upload resep jika diperlukan

Sediakan opsi untuk menyimpan informasi resep.

Untuk proyek akademik, tidak perlu membuat sistem e-resep yang terlalu kompleks.

---

# 17. DATA PASIEN / PELANGGAN

Buat modul sederhana.

Field:

* kode pasien
* nama
* jenis kelamin
* tanggal lahir
* alamat
* nomor telepon
* catatan

Data pasien dapat digunakan pada transaksi dengan resep.

---

# 18. TRANSAKSI PENJUALAN

Simpan:

* nomor transaksi
* tanggal
* kasir
* pasien/pelanggan jika ada
* tipe transaksi
* nomor resep jika ada
* subtotal
* diskon
* total
* pembayaran
* kembalian
* metode pembayaran

Detail transaksi:

* obat
* batch
* jumlah
* harga
* subtotal

Stok harus otomatis berkurang ketika transaksi selesai.

Gunakan FEFO untuk menentukan batch yang digunakan.

---

# 19. STRUK PENJUALAN

Sediakan halaman/detail transaksi yang dapat dicetak.

Isi:

**APOTEK BINTAN 88**

* Nomor transaksi
* Tanggal
* Kasir
* Item
* Qty
* Harga
* Subtotal
* Total
* Pembayaran
* Kembalian

Gunakan layout print yang sederhana dan cocok untuk printer thermal maupun kertas biasa.

Tidak perlu integrasi printer khusus.

---

# 20. LAPORAN

Buat modul laporan.

Minimal:

### Laporan Penjualan

Filter:

* Hari ini
* Minggu ini
* Bulan ini
* Custom tanggal

Tampilkan:

* nomor transaksi
* tanggal
* kasir
* total
* metode pembayaran

---

### Laporan Pembelian

Tampilkan:

* nomor pembelian
* tanggal
* supplier
* total
* status

---

### Laporan Stok

Tampilkan:

* kode obat
* nama obat
* batch
* expired
* stok
* status stok

---

### Laporan Obat Expired

Tampilkan:

* obat
* batch
* expired date
* jumlah stok
* status

---

### Laporan Obat Akan Expired

Gunakan parameter misalnya:

* 30 hari
* 60 hari
* 90 hari

---

### Laporan Obat Terlaris

Tampilkan:

* nama obat
* jumlah terjual
* total transaksi

---

### Laporan Pendapatan

Tampilkan:

* total penjualan
* total pembelian
* estimasi keuntungan kotor

Tidak perlu membuat akuntansi lengkap.

---

# 21. SISTEM NOTIFIKASI TELEGRAM

Ini merupakan salah satu fitur utama proyek.

Gunakan:

**Telegram Bot API**

Sediakan konfigurasi:

* Telegram Bot Token
* Chat ID
* status aktif/nonaktif

Jangan hardcode token Telegram di source code.

Gunakan `.env`.

Contoh:

TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=

---

# 22. NOTIFIKASI STOK MENIPIS

Sistem harus dapat mendeteksi:

### Stok kosong

Jika:

stok = 0

Kirim Telegram:

> ⚠️ PERINGATAN STOK KOSONG
>
> Obat: Paracetamol 500mg
> Kode: OBT001
> Stok: 0
>
> Segera lakukan pengadaan.

---

### Stok menipis

Jika:

stok > 0 tetapi <= stok minimum

Kirim:

> ⚠️ STOK MENIPIS
>
> Obat: Amoxicillin 500mg
> Stok saat ini: 5
> Stok minimum: 10
>
> Disarankan melakukan pengadaan.

---

# 23. NOTIFIKASI EXPIRED

Sistem harus dapat mendeteksi obat yang:

* sudah expired
* akan expired dalam 30 hari
* akan expired dalam 60 hari
* akan expired dalam 90 hari

Contoh:

> 🔴 OBAT EXPIRED
>
> Nama: Obat X
> Batch: ABC123
> Expired: 20 September 2026
> Stok: 10
>
> Segera lakukan tindakan terhadap stok tersebut.

Untuk obat yang akan expired:

> 🟠 PERINGATAN OBAT AKAN EXPIRED
>
> Nama: Obat X
> Batch: ABC123
> Expired: 15 Oktober 2026
> Sisa waktu: 16 hari
> Stok: 25

---

# 24. NOTIFIKASI TAMBAHAN

Tambahkan notifikasi yang relevan tetapi tetap sederhana:

### 1. Pembelian diterima

Ketika pembelian berhasil diterima:

> 🛒 PEMBELIAN DITERIMA
> Supplier: PT ABC
> Nomor: PB-2026-001
> Total Item: 15
> Total Pembelian: Rp xxx

---

### 2. Retur pembelian

Ketika retur dilakukan:

> 🔄 RETUR PEMBELIAN
> Supplier: PT ABC
> Obat: Paracetamol
> Batch: ABC123
> Jumlah: 10

---

### 3. Ringkasan harian

Jika memungkinkan, buat scheduled notification harian.

Contoh:

> 📊 RINGKASAN APOTEK BINTAN 88
>
> Tanggal: 29 September 2026
>
> Penjualan: Rp 2.500.000
> Transaksi: 48
>
> Obat stok kosong: 3
> Obat stok menipis: 8
> Obat akan expired: 5

Gunakan Laravel Scheduler untuk menjalankan proses otomatis.

---

# 25. NOTIFICATION LOG

Buat tabel sederhana untuk menyimpan:

* jenis notifikasi
* pesan
* tanggal
* status
* penerima
* response Telegram jika diperlukan

Tujuannya agar admin dapat melihat apakah notifikasi pernah dikirim.

---

# 26. LARAVEL SCHEDULER

Gunakan Laravel Scheduler untuk melakukan pemeriksaan otomatis.

Contoh:

Setiap hari:

1. Cek stok kosong.
2. Cek stok menipis.
3. Cek obat expired.
4. Cek obat akan expired.
5. Kirim notifikasi Telegram.
6. Simpan log notifikasi.

Hindari pengiriman notifikasi berulang setiap kali scheduler dijalankan.

Implementasikan mekanisme sederhana agar notifikasi untuk kondisi yang sama tidak dikirim terus-menerus.

Contoh:

Jika obat A sudah dikirim notifikasi stok menipis hari ini, jangan mengirim notifikasi yang sama berkali-kali pada hari yang sama.

---

# 27. NOTIFICATION CENTER

Selain Telegram, buat notification center sederhana di dalam aplikasi.

Tampilkan:

* Notifikasi stok
* Notifikasi expired
* Notifikasi pembelian
* Notifikasi retur
* Notifikasi sistem

Tambahkan badge jumlah notifikasi yang belum dibaca.

---

# 28. SEARCH DAN FILTER

Gunakan AJAX/Vanilla JS untuk meningkatkan usability.

Minimal:

* Search obat
* Search supplier
* Search pasien
* Search transaksi
* Filter kategori
* Filter tanggal
* Filter status
* Filter expired
* Filter stok

Tidak perlu membuat SPA.

---

# 29. DATABASE

Buat database relational yang sederhana dan mudah dipahami.

Minimal tabel:

* users
* roles
* permissions jika diperlukan
* medicines
* medicine_categories
* units
* medicine_batches
* suppliers
* patients
* purchases
* purchase_items
* purchase_returns
* purchase_return_items
* sales
* sale_items
* prescriptions
* prescription_items jika diperlukan
* stock_movements
* notifications
* telegram_settings
* activity_logs

Buat relasi Eloquent yang sesuai.

Gunakan:

* migrations
* models
* factories jika diperlukan
* seeders

---

# 30. DATABASE SEEDER

Buat data dummy agar sistem langsung dapat didemonstrasikan.

Minimal:

### Users

* Owner
* Apoteker
* Kasir
* Petugas Gudang

### Obat

Minimal 20 data obat.

Buat variasi:

* stok aman
* stok menipis
* stok kosong
* akan expired
* sudah expired

### Supplier

Minimal 5.

### Pasien

Minimal 10.

### Transaksi

Buat beberapa transaksi dummy.

Gunakan data Indonesia yang realistis.

---

# 31. UI / UX

Gunakan:

**Tailwind CSS**

Desain:

* modern
* sederhana
* bersih
* responsive
* cocok untuk desktop dan tablet
* sidebar navigation
* topbar
* dashboard cards
* table
* modal
* form
* alert
* badge status

Gunakan warna yang identik dengan bidang kesehatan/apotek, tetapi jangan membuat desain terlalu kompleks.

Pastikan:

* tombol jelas
* status mudah dibaca
* tabel mudah digunakan
* form mudah dipahami
* dashboard informatif

---

# 32. SIDEBAR MENU

Buat struktur menu seperti:

Dashboard

MASTER DATA

* Obat
* Kategori Obat
* Satuan
* Supplier
* Pasien

INVENTORY

* Stok
* Batch & Expired
* Riwayat Stok

TRANSAKSI

* POS / Penjualan
* Riwayat Penjualan
* Resep

PENGADAAN

* Pembelian
* Retur Pembelian

LAPORAN

* Penjualan
* Pembelian
* Stok
* Expired
* Obat Terlaris
* Pendapatan

NOTIFIKASI

* Notification Center
* Telegram

ADMINISTRASI

* Pengguna
* Role
* Pengaturan

Tampilkan menu sesuai role pengguna.

---

# 33. BUSINESS RULES

Implementasikan aturan berikut:

1. Kode obat harus unik.
2. Barcode harus unik jika diisi.
3. Stok tidak boleh negatif.
4. Harga beli tidak boleh negatif.
5. Harga jual tidak boleh negatif.
6. Jumlah transaksi harus lebih dari 0.
7. Obat yang stoknya 0 tidak dapat dijual.
8. Sistem harus mengecek stok sebelum transaksi.
9. Penjualan mengurangi stok.
10. Pembelian diterima menambah stok.
11. Retur pembelian mengurangi stok.
12. Gunakan FEFO pada penjualan.
13. Obat expired tidak boleh dijual.
14. User hanya dapat mengakses fitur sesuai role.
15. Transaksi yang sudah selesai tidak boleh dihapus sembarangan.
16. Gunakan status transaksi untuk menjaga riwayat transaksi.

Untuk proyek akademik, tidak perlu membuat workflow approval yang terlalu kompleks.

---

# 34. SOFT DELETE

Gunakan soft delete untuk data master yang relevan seperti:

* Obat
* Supplier
* Kategori
* Pasien

Jangan menghapus data transaksi secara permanen melalui UI.

---

# 35. AUDIT SEDERHANA

Buat activity log sederhana.

Catat:

* user
* aktivitas
* waktu
* modul
* deskripsi

Contoh:

> Selamat Muliyadi menambahkan obat Paracetamol 500mg.

Tidak perlu membuat sistem audit enterprise.

---

# 36. VALIDASI

Gunakan Laravel Form Request atau validasi Laravel standar.

Validasi:

* required
* unique
* numeric
* min
* date
* date comparison
* email
* integer

Tampilkan pesan error dalam Bahasa Indonesia.

---

# 37. AJAX / VANILLA JAVASCRIPT

Gunakan AJAX pada bagian yang memang membutuhkan interaksi cepat.

Contoh:

* Search obat pada POS
* Tambah obat ke keranjang
* Update quantity
* Hitung total
* Search pasien
* Filter tabel
* Check barcode
* Notifikasi tanpa reload jika memungkinkan

Tidak perlu membuat seluruh aplikasi menggunakan AJAX.

Gunakan Blade sebagai rendering utama.

---

# 38. TRANSACTION DATABASE

Gunakan Laravel Database Transaction pada proses penting seperti:

### Penjualan

* Simpan sales
* Simpan sale items
* Kurangi stok
* Simpan stock movement

Jika salah satu proses gagal, rollback seluruh transaksi.

Hal yang sama berlaku untuk:

### Pembelian

* Simpan pembelian
* Simpan detail
* Tambahkan stok
* Simpan stock movement

---

# 39. PRINT / EXPORT

Tambahkan fitur sederhana:

* Print laporan
* Print struk
* Export laporan ke CSV jika mudah dilakukan

Tidak perlu membuat sistem export yang terlalu kompleks.

---

# 40. RESPONSIVE

Aplikasi harus dapat digunakan pada:

* Desktop
* Laptop
* Tablet

Fokus utama adalah desktop/laptop karena sistem digunakan di lingkungan apotek.

---

# 41. DEPLOYMENT

Target deployment:

**Ubuntu VPS / Web Hosting**

Dokumentasikan cara deployment:

1. Clone project
2. Install dependency
3. Setup `.env`
4. Setup database MySQL
5. Migration
6. Seeder
7. Storage link
8. Build frontend
9. Configure web server
10. Setup scheduler
11. Setup queue jika digunakan
12. Setup SSL

Jika menggunakan VPS Ubuntu + Apache/Nginx, dokumentasikan konfigurasi dasarnya.

Tidak perlu Docker.

---

# 42. ENVIRONMENT

Jangan menyimpan credential sensitif langsung dalam source code.

Gunakan `.env`.

Minimal:

APP_NAME=
APP_URL=

DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=

---

# 43. DOCUMENTATION

Buat README.md yang berisi:

1. Nama proyek
2. Deskripsi
3. Tujuan
4. Fitur
5. Teknologi
6. Requirement
7. Installation
8. Environment configuration
9. Database migration
10. Seeder
11. Telegram Bot setup
12. Scheduler setup
13. Login akun demo
14. Struktur modul
15. Deployment sederhana

---

# 44. DOKUMENTASI UNTUK KEPERLUAN TUGAS KULIAH

Karena proyek ini merupakan tugas mata kuliah Manajemen Proyek Sistem Informasi, buat dokumentasi tambahan yang dapat digunakan untuk laporan akademik.

Siapkan:

### A. Identifikasi Masalah

Masalah yang terjadi pada pengelolaan apotek secara manual.

### B. Tujuan Sistem

Tujuan pengembangan sistem.

### C. Ruang Lingkup

Apa yang termasuk dan tidak termasuk dalam sistem.

### D. Analisis Kebutuhan

Pisahkan:

* Functional Requirements
* Non-Functional Requirements

### E. Aktor Sistem

* Owner
* Apoteker
* Kasir
* Petugas Gudang

### F. Use Case

Buat Use Case Diagram.

### G. Activity Diagram

Minimal:

* Login
* Pengelolaan obat
* Pembelian
* Penjualan
* Retur
* Notifikasi

### H. ERD

Buat ERD database.

### I. Flowchart

Minimal:

* Flow penjualan
* Flow pembelian
* Flow notifikasi

### J. DFD

Jika diperlukan oleh dosen:

* Context Diagram
* DFD Level 0
* DFD Level 1

### K. Project Schedule

Buat WBS dan jadwal pengembangan.

### L. Risk Management

Identifikasi risiko proyek seperti:

* keterlambatan pengembangan
* error sistem
* database rusak
* Telegram API gagal
* koneksi internet terputus
* perubahan requirement
* kesalahan input data

---

# 45. WBS

Buat Work Breakdown Structure sederhana:

1. Analisis

   * Identifikasi masalah
   * Identifikasi kebutuhan
   * Analisis proses bisnis

2. Perancangan

   * Use Case
   * Activity Diagram
   * ERD
   * UI Design

3. Development

   * Authentication
   * RBAC
   * Master data
   * Inventory
   * Purchasing
   * POS
   * Prescription
   * Reporting
   * Telegram notification

4. Testing

   * Unit testing sederhana
   * Functional testing
   * Integration testing
   * User Acceptance Testing

5. Deployment

   * Server setup
   * Database
   * Application deployment
   * Telegram setup

6. Documentation

   * User manual
   * Technical documentation
   * Project report

---

# 46. TESTING

Buat test scenario sederhana.

Minimal testing:

### Authentication

* Login valid
* Login invalid
* Logout

### Obat

* Tambah obat
* Edit obat
* Hapus/nonaktifkan obat
* Search obat

### Stok

* Stok masuk
* Stok keluar
* Stok kosong
* Stok minimum

### Pembelian

* Tambah pembelian
* Terima pembelian
* Stok bertambah

### Penjualan

* Tambah item
* Validasi stok
* Transaksi berhasil
* Stok berkurang
* Cetak struk

### Expired

* Obat expired terdeteksi
* Obat mendekati expired terdeteksi

### Telegram

* Telegram berhasil dikirim
* Telegram gagal dikirim
* Log notifikasi tersimpan

---

# 47. PRIORITAS IMPLEMENTASI

Jangan langsung membuat semua fitur sekaligus.

Kerjakan berdasarkan prioritas:

## PRIORITAS 1 — CORE

1. Laravel setup
2. Authentication
3. RBAC
4. Dashboard
5. Master obat
6. Kategori
7. Satuan
8. Supplier
9. Batch
10. Stok

## PRIORITAS 2 — TRANSACTION

11. Pembelian
12. Retur
13. POS
14. Penjualan
15. Pasien
16. Resep
17. Print struk

## PRIORITAS 3 — REPORT

18. Laporan penjualan
19. Laporan pembelian
20. Laporan stok
21. Laporan expired
22. Laporan obat terlaris

## PRIORITAS 4 — NOTIFICATION

23. Telegram Bot
24. Stok menipis
25. Stok kosong
26. Expired
27. Akan expired
28. Daily summary
29. Notification log

## PRIORITAS 5 — DOCUMENTATION

30. README
31. Use Case
32. Activity Diagram
33. ERD
34. DFD
35. WBS
36. Testing documentation

---

# 48. PENDEKATAN DEVELOPMENT

Gunakan pendekatan **incremental development**.

Setiap modul harus:

1. Dibuat database migration.
2. Dibuat model.
3. Dibuat controller.
4. Dibuat route.
5. Dibuat Blade view.
6. Dibuat validation.
7. Dibuat relasi.
8. Dibuat testing sederhana.
9. Dicek integrasinya dengan modul lain.

Jangan membuat seluruh sistem dalam satu file atau satu controller besar.

Tetap gunakan struktur Laravel standar agar mudah dipahami mahasiswa.

---

# 49. ATURAN PENTING UNTUK AI CODING AGENT

Saat mengembangkan project:

1. Jangan menghapus fitur yang sudah bekerja.
2. Jangan melakukan refactor besar tanpa alasan.
3. Jangan menambahkan teknologi yang tidak diperlukan.
4. Jangan menggunakan React/Vue/Livewire.
5. Gunakan Blade + Tailwind + Vanilla JS.
6. Jangan membuat arsitektur yang terlalu kompleks.
7. Prioritaskan fitur yang dapat didemonstrasikan.
8. Jangan membuat dummy UI tanpa backend.
9. Semua tombol utama harus memiliki fungsi nyata.
10. Semua CRUD harus benar-benar terhubung dengan database.
11. Semua transaksi harus mempengaruhi stok.
12. Jangan membuat stok hanya sebagai angka statis.
13. Batch dan expired harus benar-benar terhubung dengan stok.
14. Telegram harus benar-benar menggunakan Telegram Bot API.
15. Jangan hardcode credential Telegram.
16. Gunakan `.env`.
17. Jangan menghapus migration/data existing tanpa alasan.
18. Sebelum membuat perubahan besar, periksa struktur project yang sudah ada.
19. Jika project sudah memiliki implementasi sebagian, lanjutkan dari implementasi yang ada.
20. Jangan mengganti framework atau stack teknologi.

---

# 50. OUTPUT YANG DIHARAPKAN

Pada akhirnya project harus menghasilkan:

### Aplikasi

Sistem Informasi Manajemen Apotek Bintan 88 yang dapat:

* Login
* Mengelola user
* Mengelola obat
* Mengelola kategori
* Mengelola supplier
* Mengelola pasien
* Mengelola batch
* Mengelola stok
* Mengelola pembelian
* Mengelola retur
* Melakukan transaksi POS
* Melakukan transaksi dengan resep
* Mengurangi/menambah stok secara otomatis
* Mendeteksi stok minimum
* Mendeteksi stok kosong
* Mendeteksi obat expired
* Mendeteksi obat akan expired
* Mengirim Telegram notification
* Menampilkan notification center
* Membuat laporan
* Print struk
* Print laporan

### Dokumentasi

Sediakan:

* README
* ERD
* Use Case Diagram
* Activity Diagram
* DFD
* Flowchart
* WBS
* Project Schedule
* Risk Management
* Test Scenario
* User Manual sederhana

---

# 51. CARA MEMULAI DEVELOPMENT

Sebelum melakukan coding:

1. Periksa apakah project Laravel sudah tersedia.
2. Analisis struktur project.
3. Identifikasi fitur yang sudah ada.
4. Jangan menghapus implementasi yang sudah tersedia.
5. Buat rancangan database.
6. Tampilkan daftar migration dan relasi utama.
7. Buat roadmap implementasi.
8. Setelah itu mulai implementasi dari Authentication dan RBAC.
9. Lanjutkan secara bertahap sesuai PRIORITAS IMPLEMENTASI di atas.

Jika project masih kosong, mulai dari instalasi dan konfigurasi Laravel 13.

Setiap selesai satu modul utama, pastikan modul tersebut dapat dijalankan dan diuji sebelum melanjutkan ke modul berikutnya.

---

# 52. KRITERIA SELESAI

Project dianggap selesai apabila:

* Tidak ada error Laravel utama.
* Database migration berhasil.
* Seeder berhasil.
* Login berhasil.
* RBAC berjalan.
* CRUD master data berjalan.
* Pembelian dapat menambah stok.
* Retur dapat mengurangi stok.
* POS dapat melakukan transaksi.
* Penjualan mengurangi stok.
* Sistem menggunakan batch.
* Sistem menggunakan FEFO sederhana.
* Sistem mendeteksi stok minimum.
* Sistem mendeteksi stok kosong.
* Sistem mendeteksi expired.
* Sistem mendeteksi hampir expired.
* Telegram notification berhasil dikirim.
* Laporan dapat ditampilkan.
* Struk dapat dicetak.
* UI responsive.
* Project dapat dijalankan pada VPS/Web Hosting.
* Dokumentasi proyek tersedia.

---

## CATATAN AKHIR

Ingat bahwa ini adalah **proyek akademik untuk mata kuliah Manajemen Proyek Sistem Informasi**, bukan sistem enterprise production.

Prioritaskan:

**Feature Completeness > Architecture Complexity**

dan

**Working Application > Overengineering**

Sistem harus mudah dipahami oleh mahasiswa, dosen, dan pengguna saat demonstrasi.

Jaga scope tetap realistis sehingga seluruh fitur utama dapat selesai dan didemonstrasikan.
