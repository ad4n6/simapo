# Kamus Data (Data Dictionary) - SIMAPO

Kamus data ini mendokumentasikan seluruh 29 tabel basis data yang digunakan dalam sistem informasi manajemen apotek **SIMAPO (Apotek Bintan 88)**. Seluruh tabel menggunakan mesin penyimpanan InnoDB pada MySQL/MariaDB dengan pengindeksan foreign key serta dukungan `softDeletes` pada tabel master data utama.

---

## 1. Tabel `users`
Menyimpan data pengguna sistem beserta peran dan Telegram Chat ID.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `name` | VARCHAR(255) | Tidak | - | Nama lengkap staf/karyawan |
| `email` | VARCHAR(255) | Tidak | - | Alamat email (Unique) |
| `email_verified_at` | TIMESTAMP | Ya | NULL | Waktu verifikasi email |
| `password` | VARCHAR(255) | Tidak | - | Password terenkripsi bcrypt |
| `role` | ENUM | Tidak | 'kasir' | Pilihan: `owner`, `apoteker`, `kasir`, `admin_gudang` |
| `telegram_chat_id` | VARCHAR(100) | Ya | NULL | ID Chat Telegram pribadi untuk notifikasi |
| `is_active` | BOOLEAN | Tidak | 1 | Status aktif akun (1 = Aktif, 0 = Nonaktif) |
| `remember_token` | VARCHAR(100) | Ya | NULL | Token Remember Me |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 2. Tabel `kategori`
Menyimpan klasifikasi/golongan obat (misal: Obat Bebas, Obat Keras, Vitamin, dll).

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `nama` | VARCHAR(100) | Tidak | - | Nama kategori obat |
| `deskripsi` | TEXT | Ya | NULL | Penjelasan kategori |
| `deleted_at` | TIMESTAMP | Ya | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 3. Tabel `satuan`
Menyimpan satuan takaran/kemasan obat (Tablet, Strip, Botol, Box, Tube, dll).

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `nama` | VARCHAR(50) | Tidak | - | Nama satuan |
| `deskripsi` | VARCHAR(255) | Ya | NULL | Deskripsi satuan |
| `deleted_at` | TIMESTAMP | Ya | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 4. Tabel `suplier`
Menyimpan data Pedagang Besar Farmasi (PBF) atau pemasok obat.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `nama` | VARCHAR(150) | Tidak | - | Nama distributor / suplier |
| `kontak_person` | VARCHAR(100) | Ya | NULL | Nama narahubung / sales |
| `telepon` | VARCHAR(30) | Ya | NULL | Nomor telepon suplier |
| `email` | VARCHAR(100) | Ya | NULL | Alamat email suplier |
| `alamat` | TEXT | Ya | NULL | Alamat kantor/gudang suplier |
| `deleted_at` | TIMESTAMP | Ya | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 5. Tabel `pelanggan`
Menyimpan data pelanggan/pasien yang bertransaksi di apotek.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `nama` | VARCHAR(150) | Tidak | - | Nama pelanggan / pasien |
| `telepon` | VARCHAR(30) | Ya | NULL | Nomor telepon / WhatsApp |
| `alamat` | TEXT | Ya | NULL | Alamat tempat tinggal |
| `deleted_at` | TIMESTAMP | Ya | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 6. Tabel `dokter`
Menyimpan data dokter penulis resep obat.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `nama` | VARCHAR(150) | Tidak | - | Nama lengkap dokter |
| `no_sip` | VARCHAR(100) | Ya | NULL | Nomor Surat Izin Praktik (SIP) |
| `spesialis` | VARCHAR(100) | Ya | NULL | Bidang spesialisasi dokter |
| `telepon` | VARCHAR(30) | Ya | NULL | Nomor kontak dokter |
| `alamat` | TEXT | Ya | NULL | Alamat tempat praktik / klinik |
| `deleted_at` | TIMESTAMP | Ya | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 7. Tabel `obat`
Menyimpan master data katalog obat dan alat kesehatan.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `kode_obat` | VARCHAR(50) | Tidak | - | Kode unik / Barcode (Unique) |
| `nama_obat` | VARCHAR(200) | Tidak | - | Nama merek dagang obat |
| `nama_generik` | VARCHAR(200) | Ya | NULL | Nama zat aktif generik |
| `kategori_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `kategori.id` |
| `satuan_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `satuan.id` |
| `harga_beli` | DECIMAL(15,2) | Tidak | 0.00 | Estimasi harga beli acuan |
| `harga_jual` | DECIMAL(15,2) | Tidak | 0.00 | Harga jual ke konsumen |
| `stok_minimum` | INT | Tidak | 10 | Batas minimal peringatan stok menipis |
| `lokasi_rak` | VARCHAR(50) | Ya | NULL | Kode lemari/rak penyimpanan obat |
| `butuh_resep` | BOOLEAN | Tidak | 0 | 1 = Obat Keras (wajib resep), 0 = Bebas |
| `deskripsi` | TEXT | Ya | NULL | Indikasi, aturan pakai, atau catatan |
| `deleted_at` | TIMESTAMP | Ya | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 8. Tabel `batch_obat`
Menyimpan stok obat fisik berdasarkan nomor batch dan tanggal kedaluwarsa.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `suplier_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `suplier.id` |
| `no_batch` | VARCHAR(100) | Tidak | - | Nomor batch dari pabrik/suplier |
| `tanggal_kedaluwarsa`| DATE | Tidak | - | Tanggal kedaluwarsa (*expired date*) |
| `jumlah` | INT | Tidak | 0 | Sisa stok fisik saat ini dalam batch |
| `harga_beli` | DECIMAL(15,2) | Tidak | 0.00 | Harga beli per unit pada batch ini |
| `status` | ENUM | Tidak | 'aktif' | Pilihan: `aktif`, `habis`, `expired`, `dimusnahkan` |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 9. Tabel `stok_mutasi`
Kartu stok riwayat mutasi pergerakan kuantitas obat per batch.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `batch_obat_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `batch_obat.id` |
| `user_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `users.id` (operator) |
| `jenis` | ENUM | Tidak | - | `masuk`, `keluar`, `penyesuaian`, `retur_jual`, `retur_beli`, `pemusnahan` |
| `jumlah` | INT | Tidak | - | Jumlah perubahan (+/-) |
| `saldo_sebelum` | INT | Tidak | 0 | Saldo kuantitas sebelum transaksi |
| `saldo_sesudah` | INT | Tidak | 0 | Saldo kuantitas setelah transaksi |
| `referensi` | VARCHAR(100) | Ya | NULL | No referensi (No TRX, No PO, No BAP, dll) |
| `keterangan` | TEXT | Ya | NULL | Catatan/alasan mutasi |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pencatatan mutasi |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 10. Tabel `shift_kasir`
Menyimpan pencatatan pembukaan dan penutupan shift kasir beserta rekonsiliasi kas.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (kasir) |
| `waktu_buka` | TIMESTAMP | Tidak | CURRENT_TIMESTAMP | Waktu kasir membuka shift |
| `waktu_tutup` | TIMESTAMP | Ya | NULL | Waktu kasir menutup shift |
| `modal_awal` | DECIMAL(15,2) | Tidak | 0.00 | Uang modal awal di laci kasir |
| `total_penjualan_tunai` | DECIMAL(15,2) | Tidak | 0.00 | Total penjualan tunai tercatat di sistem |
| `total_penjualan_non_tunai` | DECIMAL(15,2) | Tidak | 0.00 | Total penjualan Transfer / QRIS |
| `kas_akhir_aktual` | DECIMAL(15,2) | Ya | NULL | Uang fisik dihitung kasir saat tutup |
| `selisih_kas` | DECIMAL(15,2) | Ya | NULL | Selisih (kas fisik - uang seharusnya) |
| `status` | ENUM | Tidak | 'buka' | Pilihan: `buka`, `tutup` |
| `catatan` | TEXT | Ya | NULL | Catatan shift kasir |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 11. Tabel `resep`
Menyimpan data lembar resep dokter untuk transaksi penjualan obat resep.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_resep` | VARCHAR(100) | Tidak | - | Nomor resep dokter |
| `dokter_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `dokter.id` |
| `nama_pasien` | VARCHAR(150) | Tidak | - | Nama pasien pemegang resep |
| `umur_pasien` | INT | Ya | NULL | Umur pasien (tahun) |
| `tanggal_resep` | DATE | Tidak | - | Tanggal resep dikeluarkan dokter |
| `keterangan` | TEXT | Ya | NULL | Diagnosa / aturan pemakaian khusus |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 12. Tabel `penjualan`
Menyimpan data header transaksi kasir POS.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_transaksi` | VARCHAR(50) | Tidak | - | Nomor unik transaksi (TRX-YYYYMMDD-XXXX) |
| `shift_kasir_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `shift_kasir.id` |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (kasir) |
| `pelanggan_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `pelanggan.id` |
| `resep_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `resep.id` (jika beresep) |
| `jenis_transaksi`| ENUM | Tidak | 'bebas' | Pilihan: `bebas`, `resep` |
| `tanggal` | TIMESTAMP | Tidak | CURRENT_TIMESTAMP | Waktu transaksi kasir |
| `subtotal` | DECIMAL(15,2) | Tidak | 0.00 | Total belanja sebelum diskon |
| `diskon` | DECIMAL(15,2) | Tidak | 0.00 | Nilai potongan harga |
| `pajak` | DECIMAL(15,2) | Tidak | 0.00 | Nilai PPN (jika ada) |
| `total` | DECIMAL(15,2) | Tidak | 0.00 | Total akhir yang harus dibayar |
| `metode_bayar` | ENUM | Tidak | 'tunai' | Pilihan: `tunai`, `transfer`, `qris` |
| `bayar` | DECIMAL(15,2) | Tidak | 0.00 | Jumlah uang yang diserahkan pelanggan |
| `kembalian` | DECIMAL(15,2) | Tidak | 0.00 | Uang kembalian yang diserahkan kasir |
| `status` | ENUM | Tidak | 'selesai' | Pilihan: `selesai`, `void` |
| `alasan_void` | TEXT | Ya | NULL | Alasan pembatalan transaksi |
| `void_by` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `users.id` yang mem-void |
| `void_at` | TIMESTAMP | Ya | NULL | Waktu pembatalan dilakukan |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 13. Tabel `penjualan_detail`
Menyimpan rincian item obat yang terjual dan batch yang terpotong secara FEFO.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `penjualan_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `penjualan.id` |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `batch_obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `batch_obat.id` |
| `jumlah` | INT | Tidak | - | Kuantitas item terjual dari batch ini |
| `harga_beli` | DECIMAL(15,2) | Tidak | 0.00 | HPP batch saat penjualan berlangsung |
| `harga_jual` | DECIMAL(15,2) | Tidak | 0.00 | Harga satuan yang ditagihkan |
| `diskon` | DECIMAL(15,2) | Tidak | 0.00 | Diskon per item |
| `subtotal` | DECIMAL(15,2) | Tidak | 0.00 | Subtotal item (jumlah * harga_jual) |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 14. Tabel `retur_penjualan`
Menyimpan header transaksi pengembalian barang dari pelanggan.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_retur` | VARCHAR(50) | Tidak | - | Nomor unik retur (RET-YYYYMMDD-XXXX) |
| `penjualan_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `penjualan.id` |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (petugas) |
| `tanggal` | DATE | Tidak | - | Tanggal retur diproses |
| `total_kembali` | DECIMAL(15,2) | Tidak | 0.00 | Total dana yang dikembalikan ke pelanggan |
| `alasan` | TEXT | Ya | NULL | Alasan pengembalian barang |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 15. Tabel `retur_penjualan_detail`
Rincian item obat yang diretur oleh pelanggan.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `retur_penjualan_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `retur_penjualan.id` |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `batch_obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `batch_obat.id` |
| `jumlah` | INT | Tidak | - | Kuantitas item diretur |
| `harga_satuan` | DECIMAL(15,2) | Tidak | 0.00 | Harga saat dibeli |
| `subtotal` | DECIMAL(15,2) | Tidak | 0.00 | Subtotal dana kembali |
| `kembali_ke_stok` | BOOLEAN | Tidak | 1 | 1 = Barang layak dan kembali ke stok |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 16. Tabel `purchase_order`
Menyimpan Surat Pesanan / Purchase Order (PO) pengadaan obat ke PBF/suplier.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_po` | VARCHAR(50) | Tidak | - | Nomor PO (PO-YYYYMMDD-XXXX) |
| `suplier_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `suplier.id` |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (pembuat PO) |
| `tanggal` | DATE | Tidak | - | Tanggal pembuatan pesanan |
| `estimasi_tanggal` | DATE | Ya | NULL | Estimasi tanggal barang tiba |
| `total_estimasi` | DECIMAL(15,2) | Tidak | 0.00 | Perkiraan total biaya PO |
| `status` | ENUM | Tidak | 'draft' | `draft`, `dipesan`, `diterima_sebagian`, `diterima`, `batal` |
| `catatan` | TEXT | Ya | NULL | Instruksi pengiriman / catatan suplier |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 17. Tabel `purchase_order_detail`
Rincian item obat yang dipesan pada Purchase Order.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `purchase_order_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `purchase_order.id` |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `jumlah_pesan` | INT | Tidak | - | Jumlah obat yang dipesan |
| `jumlah_diterima` | INT | Tidak | 0 | Jumlah obat yang telah diterima gudang |
| `harga_satuan` | DECIMAL(15,2) | Tidak | 0.00 | Estimasi harga beli satuan |
| `subtotal` | DECIMAL(15,2) | Tidak | 0.00 | Subtotal estimasi item |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 18. Tabel `pembelian`
Menyimpan transaksi penerimaan faktur pembelian dan pencatatan hutang suplier.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_faktur` | VARCHAR(100) | Tidak | - | Nomor faktur dari suplier |
| `purchase_order_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `purchase_order.id` |
| `suplier_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `suplier.id` |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (penerima gudang) |
| `tanggal` | DATE | Tidak | - | Tanggal faktur pembelian |
| `total` | DECIMAL(15,2) | Tidak | 0.00 | Total nilai faktur |
| `jenis_bayar` | ENUM | Tidak | 'tunai' | Pilihan: `tunai`, `kredit` |
| `status_bayar` | ENUM | Tidak | 'lunas' | Pilihan: `lunas`, `belum_lunas` |
| `jatuh_tempo` | DATE | Ya | NULL | Tanggal jatuh tempo jika pembelian kredit |
| `sisa_hutang` | DECIMAL(15,2) | Tidak | 0.00 | Sisa tagihan hutang yang belum dibayar |
| `catatan` | TEXT | Ya | NULL | Catatan penerimaan barang |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 19. Tabel `pembelian_detail`
Rincian item penerimaan obat dan pembuatan entitas batch fisik baru.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `pembelian_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `pembelian.id` |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `batch_obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `batch_obat.id` (batch baru) |
| `jumlah` | INT | Tidak | - | Kuantitas unit yang masuk ke stok |
| `harga_beli` | DECIMAL(15,2) | Tidak | 0.00 | Harga beli aktual per unit |
| `subtotal` | DECIMAL(15,2) | Tidak | 0.00 | Total item (jumlah * harga_beli) |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 20. Tabel `pembayaran_hutang`
Riwayat pelunasan cicilan hutang suplier atas faktur pembelian kredit.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `pembelian_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `pembelian.id` |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (pembayar) |
| `tanggal` | DATE | Tidak | - | Tanggal pembayaran dilakukan |
| `jumlah_bayar` | DECIMAL(15,2) | Tidak | 0.00 | Nominal uang yang dibayarkan |
| `metode` | ENUM | Tidak | 'transfer'| Pilihan: `tunai`, `transfer` |
| `bukti_bayar` | VARCHAR(255) | Ya | NULL | Path file bukti transfer |
| `catatan` | TEXT | Ya | NULL | Keterangan pelunasan |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 21. Tabel `retur_pembelian`
Header pengembalian obat rusak atau mendekati kedaluwarsa ke suplier.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_retur` | VARCHAR(50) | Tidak | - | Nomor retur (RB-YYYYMMDD-XXXX) |
| `suplier_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `suplier.id` |
| `pembelian_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `pembelian.id` |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (pengaju) |
| `tanggal` | DATE | Tidak | - | Tanggal pengajuan retur |
| `total_nilai` | DECIMAL(15,2) | Tidak | 0.00 | Total nilai barang yang dikembalikan |
| `status` | ENUM | Tidak | 'diajukan'| `diajukan`, `disetujui`, `selesai` |
| `alasan` | TEXT | Ya | NULL | Alasan komplain / retur suplier |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 22. Tabel `retur_pembelian_detail`
Rincian item obat yang dikembalikan ke suplier.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `retur_pembelian_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `retur_pembelian.id` |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `batch_obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `batch_obat.id` |
| `jumlah` | INT | Tidak | - | Kuantitas unit yang dipotong dari stok |
| `harga_beli` | DECIMAL(15,2) | Tidak | 0.00 | Nilai harga beli per unit |
| `subtotal` | DECIMAL(15,2) | Tidak | 0.00 | Total nilai barang yang diretur |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 23. Tabel `stock_opname`
Header dokumen perhitungan fisik stok obat periodik.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_opname` | VARCHAR(50) | Tidak | - | Nomor dokumen (SO-YYYYMMDD-XXXX) |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (petugas opname) |
| `tanggal` | DATE | Tidak | - | Tanggal pelaksanaan stock opname |
| `status` | ENUM | Tidak | 'draft' | Pilihan: `draft`, `selesai` |
| `catatan` | TEXT | Ya | NULL | Catatan pelaksanaan |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 24. Tabel `stock_opname_detail`
Rincian hasil perhitungan fisik per batch obat dan penyesuaian selisihnya.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `stock_opname_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `stock_opname.id` |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `batch_obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `batch_obat.id` |
| `stok_sistem` | INT | Tidak | 0 | Kuantitas stok menurut data komputer |
| `stok_fisik` | INT | Tidak | 0 | Kuantitas stok riil di lemari/rak |
| `selisih` | INT | Tidak | 0 | Selisih (stok_fisik - stok_sistem) |
| `keterangan` | VARCHAR(255) | Ya | NULL | Alasan selisih (rusak, hilang, dll) |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 25. Tabel `pemusnahan_obat`
Pencatatan Berita Acara Pemusnahan (BAP) obat kedaluwarsa atau rusak berat.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `no_pemusnahan` | VARCHAR(50) | Tidak | - | Nomor BAP (BAP-YYYYMMDD-XXXX) |
| `obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `obat.id` |
| `batch_obat_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `batch_obat.id` |
| `user_id` | BIGINT UNSIGNED | Tidak | - | Foreign Key ke `users.id` (penanggung jawab)|
| `tanggal` | DATE | Tidak | - | Tanggal pemusnahan obat |
| `jumlah` | INT | Tidak | - | Jumlah obat yang dimusnahkan |
| `alasan` | TEXT | Tidak | - | Alasan pemusnahan (Expired, Rusak, dll) |
| `penanggung_jawab`| VARCHAR(150) | Tidak | - | Nama Apoteker / Saksi Pemusnahan |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 26. Tabel `notifikasi`
Menyimpan notifikasi in-app untuk pengguna sistem.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `user_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `users.id` (NULL = broadcast) |
| `judul` | VARCHAR(200) | Tidak | - | Judul notifikasi |
| `pesan` | TEXT | Tidak | - | Konten isi pesan notifikasi |
| `tipe` | VARCHAR(50) | Tidak | 'info' | Tipe notifikasi (info, warning, danger) |
| `url` | VARCHAR(255) | Ya | NULL | Link redirect saat notifikasi diklik |
| `is_read` | BOOLEAN | Tidak | 0 | 0 = Belum dibaca, 1 = Sudah dibaca |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 27. Tabel `notifikasi_log`
Log audit pengiriman pesan notifikasi Telegram & In-App serta status sukses/gagalnya.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `jenis` | VARCHAR(50) | Tidak | - | Jenis trigger (stok_menipis, expired, dll) |
| `pesan` | TEXT | Tidak | - | Konten pesan HTML yang dikirim |
| `tujuan` | VARCHAR(100) | Tidak | - | Chat ID atau nomor tujuan |
| `status` | ENUM | Tidak | 'terkirim'| Pilihan: `terkirim`, `gagal` |
| `response_error` | TEXT | Ya | NULL | Pesan error dari Telegram Bot API |
| `waktu` | TIMESTAMP | Tidak | CURRENT_TIMESTAMP | Waktu transmisi pesan |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 28. Tabel `pengaturan`
Menyimpan pasangan kunci-nilai (*key-value*) konfigurasi apotek dan notifikasi.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `key` | VARCHAR(100) | Tidak | - | Kode konfigurasi unik (Unique) |
| `value` | TEXT | Ya | NULL | Nilai konfigurasi sistem |
| `tipe` | VARCHAR(50) | Tidak | 'string' | Tipe data nilai (string, boolean, integer, json) |
| `keterangan` | VARCHAR(255) | Ya | NULL | Deskripsi parameter pengaturan |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---

## 29. Tabel `activity_log`
Audit trail seluruh aksi pengguna sistem untuk akuntabilitas operasional.

| Kolom | Tipe Data | Nullable | Default | Keterangan |
|---|---|:---:|:---:|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto Increment | Primary Key |
| `user_id` | BIGINT UNSIGNED | Ya | NULL | Foreign Key ke `users.id` |
| `aktivitas` | VARCHAR(255) | Tidak | - | Keterangan aksi (Login, Simpan POS, Void, dll) |
| `ip_address` | VARCHAR(45) | Ya | NULL | Alamat IP pengguna |
| `user_agent` | TEXT | Ya | NULL | Browser / Client User-Agent |
| `created_at` | TIMESTAMP | Ya | NULL | Waktu aktivitas dilakukan |
| `updated_at` | TIMESTAMP | Ya | NULL | Waktu pembaruan record |

---
