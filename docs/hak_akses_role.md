# Matriks Hak Akses Peran (Role-Based Access Control) - SIMAPO

Sistem Informasi Manajemen Apotek (SIMAPO) menerapkan pembagian peran yang ketat untuk menjamin keamanan, kepatuhan farmasi (*Good Pharmacy Practice*), serta integritas data keuangan. Pengendalian dilakukan di dua tingkat:
1. **Antarmuka Pengguna (UI)**: Menu sidebar dan tombol aksi hanya ditampilkan sesuai peran aktif.
2. **Lapisan Server (Backend Middleware)**: `RoleMiddleware` mencegat setiap request URL HTTP secara langsung. Jika peran pengguna tidak terdaftar pada rute yang diminta, sistem otomatis mengembalikan status respon `403 Forbidden`.

---

## 1. Definisi Peran (Roles)

| Role | Deskripsi Tugas Pokok & Tanggung Jawab |
|---|---|
| **Owner** | Pemilik sarana apotek (PSA). Memiliki kendali penuh terhadap seluruh sistem, akun pengguna, konfigurasi Telegram, laporan laba rugi, dan otorisasi khusus (misal pembatalan transaksi / VOID). |
| **Apoteker** | Apoteker Pengelola Apotek (APA) / Staf Teknis Farmasi. Bertanggung jawab atas pengelolaan katalog obat, verifikasi resep dokter, pengawasan stok kedaluwarsa, stock opname, peracikan obat, dan penerimaan pengadaan. |
| **Kasir** | Staf pelayanan meja depan (*front desk*). Fokus utama adalah melayani penjualan obat bebas dan resep di kasir (POS), mencetak struk, dan bertanggung jawab atas modal/uang kas pada shift kerja yang diembannya. |
| **Admin Gudang** | Petugas pergudangan / logistik farmasi. Bertanggung jawab atas pencatatan suplier, monitoring persediaan fisik, pembuatan Purchase Order (PO), penerimaan faktur barang masuk beserta nomor batch, dan retur pembelian. |

---

## 2. Matriks Hak Akses Modul & Tindakan

Keterangan simbol:
- **CRUD**: Akses Penuh (Create, Read, Update, Delete)
- **CRU**: Buat, Baca, Ubah (Tanpa Hapus)
- **R**: Hanya Baca (Read-Only)
- **-**: Tidak Memiliki Akses (Diblokir HTTP 403)

| Modul / Fitur | Sub-Fitur / Tindakan | Owner | Apoteker | Kasir | Admin Gudang |
|---|---|:---:|:---:|:---:|:---:|
| **Dashboard** | KPI Penjualan, Tren 7 Hari, Top Obat | Full | Full | Kasir | Gudang |
| **Manajemen User** | CRUD Akun, Toggle Status, Chat ID | CRUD | - | - | - |
| **Master Kategori** | Kelola Kategori Obat | CRUD | CRUD | - | - |
| **Master Satuan** | Kelola Satuan Kemasan | CRUD | CRUD | - | - |
| **Master Suplier** | Kelola PBF / Distributor | CRUD | CRUD | - | CRUD |
| **Master Pelanggan** | Kelola Kontak Pelanggan | CRUD | CRUD | CRUD | - |
| **Master Dokter** | Kelola Dokter Penulis Resep | CRUD | CRUD | - | - |
| **Master Obat** | Katalog Obat, Harga Beli, Harga Jual | CRUD | CRUD | R | R |
| **Stok & Batch** | Monitoring Stok, Multi-Batch, Status | CRUD | CRUD | R | CRUD |
| **Kartu Stok** | Riwayat Saldo Mutasi Obat | CRUD | CRUD | - | CRUD |
| **Stock Opname** | Input Fisik, Penyesuaian, BAP Opname | CRUD | CRUD | - | CRUD |
| **Pemusnahan Obat** | Catat Berita Acara & Cetak BAP | CRUD | CRUD | - | - |
| **Pengadaan: PO** | Buat, Ubah, Batalkan Purchase Order | CRUD | CRUD | - | CRUD |
| **Pengadaan: Rekomendasi**| Kalkulasi Stok Menipis & Auto-PO | CRUD | CRUD | - | CRUD |
| **Penerimaan Pembelian**| Input Faktur, Bikin Batch Baru | CRUD | CRUD | - | CRUD |
| **Hutang Suplier** | Monitoring Jatuh Tempo & Bayar Cicilan| CRUD | CRUD | - | CRUD |
| **Retur Pembelian** | Pengembalian Barang Rusak ke PBF | CRUD | CRUD | - | CRUD |
| **Kasir POS** | Transaksi Kasir & Validasi Resep | CRUD | CRUD | CRUD | - |
| **Cetak Struk POS** | Cetak Struk Printer Termal 58/80mm | CRUD | CRUD | CRUD | - |
| **Riwayat Penjualan** | Lihat Histori Transaksi Kasir | Semua | Semua | Milik Sendiri | - |
| **Void Transaksi POS** | Batalkan Transaksi & Kembalikan Stok| ✅ Otorisasi | ✅ Otorisasi | - | - |
| **Retur Penjualan** | Pengembalian Obat oleh Pasien | CRUD | CRUD | CRUD | - |
| **Shift Kasir** | Buka/Tutup Kasir, Rekonsiliasi Uang | CRUD | CRUD | CRUD | - |
| **Laporan Penjualan** | Ringkasan Transaksi & Metode Bayar | ✅ | ✅ | - | - |
| **Laporan Resep** | Penjualan Obat Resep (BPOM) | ✅ | ✅ | - | - |
| **Laporan Pembelian** | Ringkasan Pembelian & Hutang PBF | ✅ | ✅ | - | ✅ |
| **Laporan Nilai Stok** | Nilai Valuasi Rupiah Persediaan | ✅ | ✅ | - | ✅ |
| **Laporan Expired** | Obat Kadaluwarsa / H-30/60/90 Hari | ✅ | ✅ | - | ✅ |
| **Laporan Laba Rugi** | Analisis Penjualan vs HPP vs Diskon | ✅ | - | - | - |
| **Log Aktivitas User**| Audit Trail Riwayat Staf | ✅ | - | - | - |
| **Pengaturan Apotek** | Nama, Alamat, Info Struk Kasir | CRUD | - | - | - |
| **Pengaturan Telegram**| Bot Token, Chat ID, Uji Pesan Tes | CRUD | - | - | - |
| **Log Notifikasi** | Riwayat Pesan Terkirim & Kirim Ulang| CRUD | - | - | - |

---

## 3. Matriks Hak Akses Berdasarkan Rute (Prefix URL)

Setiap grup rute diproteksi secara deklaratif dalam file [`routes/web.php`](file:///c:/Users/Admin/Herd/simapo/routes/web.php) menggunakan middleware `role:...`:

### 3.1. Rute Khusus Owner
- `users.*` &rarr; `role:owner`
- `pengaturan.*` &rarr; `role:owner`
- `notifikasi.riwayat`, `notifikasi.kirim-ulang` &rarr; `role:owner`
- `laporan.labaRugi` &rarr; `role:owner`
- `laporan.activityLog` &rarr; `role:owner`

### 3.2. Rute Owner & Apoteker
- `kategori.*` &rarr; `role:owner,apoteker`
- `satuan.*` &rarr; `role:owner,apoteker`
- `dokter.*` &rarr; `role:owner,apoteker`
- `pemusnahan.*` &rarr; `role:owner,apoteker`
- `penjualan.void` &rarr; `role:owner,apoteker`
- `laporan.penjualan*`, `laporan.resep` &rarr; `role:owner,apoteker`

### 3.3. Rute Logistik & Gudang (Owner, Apoteker, Admin Gudang)
- `suplier.*` &rarr; `role:owner,apoteker,admin_gudang`
- `obat.store`, `obat.update`, `obat.destroy` &rarr; `role:owner,apoteker,admin_gudang`
- `stok.mutasi` &rarr; `role:owner,apoteker,admin_gudang`
- `stok.opname.*` &rarr; `role:owner,apoteker,admin_gudang`
- `pengadaan.*` &rarr; `role:owner,apoteker,admin_gudang`
- `laporan.pembelian`, `laporan.stok*`, `laporan.expired`, `laporan.mutasi` &rarr; `role:owner,apoteker,admin_gudang`

### 3.4. Rute Kasir & Pelayanan (Owner, Apoteker, Kasir)
- `pos.*` &rarr; `role:owner,apoteker,kasir`
- `kasir.shift.*` &rarr; `role:owner,apoteker,kasir`
- `penjualan.index`, `penjualan.show` &rarr; `role:owner,apoteker,kasir`
- `penjualan.retur.*` &rarr; `role:owner,apoteker,kasir`
- `pelanggan.*` &rarr; `role:owner,apoteker,kasir`

### 3.5. Rute Terbuka untuk Semua Staf Aktif
- `dashboard` &rarr; Menyesuaikan widget statistik dengan peran pengguna.
- `profile`, `profile.update` &rarr; Pembaruan nama, password, dan Telegram Chat ID pribadi.
- `notifikasi.unread`, `notifikasi.mark-all-read` &rarr; Polling lonceng notifikasi in-app.
- `obat.index` (Pencarian & Daftar Obat) &rarr; Kasir dan Admin Gudang dapat membaca data katalog obat.
- `stok.index` (Daftar Stok Fisik) &rarr; Kasir dapat membaca ketersediaan stok fisik di rak obat.

---
