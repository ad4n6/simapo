# PROMPT: Sistem Informasi Manajemen Apotek Bintan 88

## PERAN
Kamu adalah Senior Full-Stack Developer (agentic coding). Bangun proyek web lengkap dari nol hingga siap dijalankan, dan kerjakan secara mandiri per tahap tanpa banyak bertanya. Jika ada hal yang ambigu, ambil keputusan yang paling masuk akal, lalu catat di README.

## KONTEKS
Proyek tugas kuliah Manajemen Proyek Sistem Informasi (Prodi Sistem Informasi, Fakultas Ilmu Komputer).
Judul: **Pengembangan Sistem Informasi Manajemen Apotek Berbasis Web Menggunakan Framework Laravel yang Dilengkapi Fitur Notifikasi Menggunakan Telegram pada Apotek Bintan 88**.

## PRINSIP
- Ini tugas kuliah, jadi **prioritaskan kelengkapan fitur, bukan kompleksitas arsitektur**.
- Jangan pakai pola berat (Repository, Service Layer berlapis, DDD, microservice, event sourcing, unit test ekstensif). Cukup MVC standar Laravel, dengan Service class sederhana hanya untuk logika stok dan Telegram.
- Keamanan cukup standar bawaan Laravel: hash password, CSRF, validasi Form Request atau `$request->validate()`, middleware role.
- Kode rapi, diberi komentar singkat berbahasa Indonesia, dan mudah dijelaskan saat presentasi.
- Seluruh UI, label, pesan, dan notifikasi memakai **Bahasa Indonesia**. Zona waktu `Asia/Jakarta`, mata uang Rupiah.

## TECH STACK (WAJIB)
- Laravel 13 (PHP sesuai kebutuhan Laravel 13). Jika belum tersedia lewat `composer create-project`, gunakan versi stabil terbaru dan catat di README.
- MySQL / MariaDB
- Tailwind CSS (via Vite)
- **Vanilla JavaScript** dengan AJAX (`fetch`), tanpa jQuery, Vue, React, atau Livewire
- Blade template (layout + komponen sederhana)
- Telegram Bot API (via `Http` facade Laravel)
- Laravel Scheduler untuk notifikasi otomatis
- Auth: session-based buatan sendiri (tanpa Breeze/Jetstream), agar sederhana
- Library tambahan yang boleh dipakai: `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (Excel), Chart.js (via npm, untuk grafik dashboard)

## ROLE (RBAC)
Gunakan kolom `role` pada tabel `users` dan middleware `role:owner,apoteker,...`.

| Role | Hak Akses |
|---|---|
| **Owner** | Semua fitur, kelola user, semua laporan, pengaturan Telegram/apotek, dashboard analitik |
| **Apoteker** | Master obat, stok & batch, stock opname, pengadaan (PO/pembelian/retur), validasi resep, transaksi POS, laporan stok & penjualan, notifikasi |
| **Kasir** | POS (penjualan), riwayat & cetak struk transaksi miliknya, lihat stok (read-only), data pelanggan |
| **Admin Gudang** | Master suplier, pengadaan (PO, penerimaan barang, retur pembelian), stok & batch, stock opname, laporan stok/pembelian |

Tampilkan menu sidebar sesuai role, dan blokir akses langsung via URL (403).

## MODUL & FITUR

### 1. Autentikasi & Manajemen User
- Login, logout, dan halaman profil (ganti password)
- CRUD user (Owner saja): nama, email, role, status aktif, **Telegram Chat ID**

### 2. Master Data (CRUD dengan pencarian, pagination, AJAX modal/form)
- Kategori obat (mis. obat bebas, bebas terbatas, keras, narkotika/psikotropika, vitamin, alkes)
- Satuan (tablet, strip, botol, box, dll.)
- **Obat**: kode, nama, nama generik, kategori, satuan, harga beli, harga jual, stok minimum, lokasi rak, `butuh_resep` (boolean), deskripsi
- Suplier: nama, alamat, telepon, email, kontak person
- Pelanggan (opsional saat transaksi): nama, telepon, alamat
- Dokter: nama, no. SIP/izin, spesialis

### 3. Manajemen Stok
- Stok dikelola **per batch**: `no_batch`, `tanggal_kedaluwarsa`, `jumlah`, `harga_beli`, suplier
- Total stok obat = jumlah seluruh batch aktif
- Penjualan otomatis mengurangi stok dengan metode **FEFO** (batch yang lebih cepat kedaluwarsa dikurangi lebih dulu). Batch yang sudah kedaluwarsa tidak boleh dijual.
- Halaman daftar stok dengan **indikator warna**: aman (hijau), menipis (kuning, stok ≤ minimum), kosong (merah), hampir kedaluwarsa (oranye), kedaluwarsa (merah gelap)
- Filter: menipis, kosong, hampir expired (≤ 30/60/90 hari), sudah expired
- **Kartu stok** per obat (riwayat masuk/keluar/penyesuaian/retur dengan saldo berjalan) lewat tabel `stok_mutasi`
- **Stock opname / penyesuaian stok**: input stok fisik per batch, selisih tercatat, wajib alasan
- **Pemusnahan obat kedaluwarsa**: catat berita acara sederhana (tanggal, batch, jumlah, alasan, penanggung jawab), stok berkurang

### 4. Transaksi Kasir (POS)
- Halaman POS satu layar, AJAX penuh:
  - Cari obat (nama/kode/generik) dengan live search, atau scan barcode via input teks
  - Keranjang dinamis (tambah, ubah qty, hapus), subtotal, diskon (nominal/persen), total
  - Validasi qty terhadap stok tersedia
  - Pembayaran: tunai (hitung kembalian otomatis), transfer, QRIS (cukup pilihan metode)
- **Dua jenis transaksi**:
  - **Obat bebas**: langsung
  - **Dengan resep**: form tambahan (no. resep, dokter, nama pasien, tanggal resep). Obat `butuh_resep=true` **wajib** memakai mode resep.
- Nomor transaksi otomatis (mis. `TRX-20260929-0001`)
- **Cetak struk** (halaman print thermal 58/80mm dengan CSS `@media print`)
- Riwayat penjualan dengan filter tanggal, kasir, dan jenis, serta detail transaksi
- **Void/batal transaksi** (hanya Owner/Apoteker, dengan alasan; stok dikembalikan ke batch asal)
- **Retur penjualan** sederhana (pelanggan mengembalikan obat, stok kembali bila layak)
- Shift kasir sederhana: buka & tutup kasir dengan modal awal dan rekap uang di akhir shift

### 5. Pengadaan Barang
- **Purchase Order (PO)** ke suplier: status draft → dipesan → diterima sebagian → diterima → batal
- **Penerimaan barang / Pembelian**: input per item beserta `no_batch`, `tanggal_kedaluwarsa`, qty, harga beli. Saat disimpan, batch dibuat dan stok bertambah. Bisa dari PO atau langsung tanpa PO.
- Pembayaran pembelian: tunai atau **kredit dengan jatuh tempo**, status lunas/belum lunas, catat pembayaran hutang
- **Retur pembelian** ke suplier (obat rusak/kedaluwarsa/salah kirim): stok batch berkurang, catat alasan dan status (diajukan/disetujui/selesai)
- **Rekomendasi pemesanan otomatis**: daftar obat dengan stok ≤ minimum, lengkap dengan tombol "Buat PO dari rekomendasi"

### 6. Laporan (filter periode, tampil di layar, ekspor PDF dan Excel)
1. Laporan penjualan (harian/bulanan/per kasir/per metode bayar)
2. Laporan penjualan per obat (terlaris)
3. Laporan penjualan obat resep (untuk pertanggungjawaban)
4. Laporan pembelian & hutang suplier
5. Laporan stok saat ini (nilai persediaan)
6. Laporan obat menipis/kosong
7. Laporan obat kedaluwarsa & hampir kedaluwarsa
8. Laporan retur (penjualan & pembelian)
9. Laporan mutasi/kartu stok
10. Laporan laba rugi sederhana (penjualan − HPP − diskon)
11. Log aktivitas user (Owner)

### 7. Dashboard (per role)
- Kartu ringkasan: penjualan hari ini, jumlah transaksi, obat menipis, obat kosong, obat hampir expired, hutang jatuh tempo
- Grafik penjualan 7/30 hari terakhir dan top 5 obat terlaris (Chart.js)
- Daftar notifikasi terbaru

### 8. NOTIFIKASI (FITUR UTAMA) - Telegram + In-App

**Infrastruktur**
- Service class `TelegramService` (kirim pesan via `https://api.telegram.org/bot{TOKEN}/sendMessage`, `parse_mode=HTML`). Token dan pengaturan disimpan di `.env` dan tabel `pengaturan`.
- Halaman **Pengaturan Notifikasi** (Owner): bot token, Chat ID grup apotek, ambang hari kedaluwarsa (default 30/60/90), jam kirim ringkasan harian, saklar aktif/nonaktif per jenis notifikasi, dan **tombol "Kirim Pesan Tes"** (AJAX).
- Setiap user dapat mengisi Telegram Chat ID di profil. Notifikasi dikirim ke grup dan/atau ke user sesuai role yang relevan.
- Tabel `notifikasi_log`: jenis, pesan, tujuan, status (terkirim/gagal), response error, waktu. Sediakan halaman riwayat notifikasi dengan tombol kirim ulang untuk yang gagal.
- Notifikasi in-app: ikon lonceng di navbar dengan badge jumlah belum dibaca, dropdown daftar (polling AJAX tiap 30–60 detik), tandai sudah dibaca.
- **Hindari spam**: satu notifikasi jenis + entitas yang sama tidak dikirim ulang pada hari yang sama.

**Jenis notifikasi (implementasikan semuanya)**
1. **Stok menipis**: stok ≤ stok minimum
2. **Stok kosong**: stok = 0
3. **Obat hampir kedaluwarsa**: dalam 90/60/30 hari (bertingkat, sesuai pengaturan)
4. **Obat sudah kedaluwarsa**: masih ada stok pada batch yang sudah lewat tanggal
5. **Hutang suplier jatuh tempo**: H-3, H-1, dan yang sudah lewat
6. **PO belum diterima** melebihi estimasi tanggal (opsional, jika ada kolom estimasi)
7. **Ringkasan penjualan harian** (dikirim ke Owner pada jam tertentu): total penjualan, jumlah transaksi, top obat, obat menipis
8. **Transaksi void/retur** (real-time ke Owner/Apoteker)
9. **Penerimaan barang berhasil** (real-time, info batch dan expired)
10. **Selisih stock opname signifikan** (real-time ke Owner)
11. **Selisih kas saat tutup shift** (jika ada selisih)
12. **Login mencurigakan/gagal berulang** (opsional, ringan saja)

**Penjadwalan**
- Buat Artisan command (mis. `notifikasi:cek-stok`, `notifikasi:cek-expired`, `notifikasi:cek-hutang`, `notifikasi:ringkasan-harian`) dan daftarkan di scheduler Laravel (`routes/console.php`).
- Sediakan tombol **"Jalankan Pengecekan Sekarang"** di halaman pengaturan agar bisa didemokan tanpa cron.
- Dokumentasikan cara pasang cron di VPS/hosting: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`

**Format pesan Telegram** (contoh gaya, pakai emoji dan HTML sederhana):
```
⚠️ <b>STOK MENIPIS - Apotek Bintan 88</b>
Obat : Paracetamol 500mg
Stok : 8 strip (minimum 20)
Rak  : A-03
Segera lakukan pemesanan ulang.
```
Satu pesan boleh berisi gabungan beberapa obat (digest) agar tidak membanjiri chat.

## DESAIN DATABASE (minimal)
Buat migration, model beserta relasi, dan seeder untuk tabel berikut. Kamu boleh menambah kolom yang diperlukan:

`users`, `kategori`, `satuan`, `obat`, `batch_obat`, `stok_mutasi`, `suplier`, `pelanggan`, `dokter`, `penjualan`, `penjualan_detail`, `resep`, `retur_penjualan`, `retur_penjualan_detail`, `shift_kasir`, `purchase_order`, `purchase_order_detail`, `pembelian`, `pembelian_detail`, `pembayaran_hutang`, `retur_pembelian`, `retur_pembelian_detail`, `stock_opname`, `stock_opname_detail`, `pemusnahan_obat`, `notifikasi`, `notifikasi_log`, `pengaturan`, `activity_log`.

Gunakan foreign key, `softDeletes` pada master data, dan pastikan operasi yang menyentuh banyak tabel (penjualan, penerimaan barang, retur, opname) dibungkus `DB::transaction`.

## STRUKTUR UI
- Layout: sidebar (menu sesuai role) + navbar (lonceng notifikasi, profil) + konten, responsif dengan Tailwind
- Komponen Blade reusable: tabel, modal, alert/toast, badge status, form input, pagination
- Semua aksi CRUD dan pencarian memakai **AJAX (fetch)** dengan respons JSON, toast sukses/gagal, dan validasi error tampil di form tanpa reload penuh
- Tampilan bersih bertema apotek (hijau/teal), tabel dengan pencarian dan pagination

## DATA AWAL (SEEDER)
- 4 user demo (satu per role), password `password`
- ±10 kategori/satuan, ±50 obat realistis (mix obat bebas, keras, vitamin) dengan batch berbagai kondisi: aman, menipis, kosong, hampir expired, sudah expired
- 5 suplier, 5 dokter, 10 pelanggan
- ±30 transaksi penjualan dan ±10 pembelian contoh agar dashboard dan laporan langsung terisi

## TAHAPAN KERJA (kerjakan berurutan, jelaskan singkat setiap selesai satu tahap)
1. Inisialisasi proyek Laravel 13, konfigurasi MySQL, Tailwind + Vite, layout dasar
2. Migration, model, relasi, seeder
3. Autentikasi, middleware role, manajemen user
4. Master data (AJAX CRUD)
5. Stok & batch, kartu stok, opname, pemusnahan
6. Pengadaan (PO, penerimaan, hutang, retur pembelian, rekomendasi PO)
7. POS, resep, struk, void, retur penjualan, shift kasir
8. Notifikasi Telegram, in-app, command, scheduler, halaman pengaturan
9. Laporan + ekspor PDF/Excel
10. Dashboard + grafik
11. Pengujian manual alur utama, perbaikan bug, rapikan UI
12. Buat **README.md**

## OUTPUT AKHIR
- Proyek Laravel yang berjalan dengan `composer install`, `npm install && npm run build`, `php artisan migrate --seed`, dan `php artisan serve`
- `.env.example` yang sudah berisi variabel Telegram (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`)
- **README.md** berisi: deskripsi proyek, fitur, tech stack, cara instalasi, akun demo, **cara membuat bot Telegram lewat @BotFather dan mendapatkan Chat ID**, cara setup cron/scheduler di VPS/hosting, dan panduan deploy singkat (Nginx/Apache, `.env` produksi, `php artisan optimize`)
- Dokumen singkat `docs/` untuk lampiran laporan tugas: daftar tabel (kamus data), daftar route/fitur per role, dan diagram alur (Mermaid) untuk alur POS, penerimaan barang, dan notifikasi

## ATURAN TAMBAHAN
- Jangan berhenti di tengah. Jika satu tahap selesai, lanjut ke tahap berikutnya.
- Jika ada error, perbaiki sendiri sampai aplikasi berjalan.
- Jangan membuat placeholder kosong atau "TODO"; setiap menu di sidebar harus berfungsi.
- Sebelum selesai, jalankan `php artisan migrate:fresh --seed` dan cek bahwa semua halaman utama bisa dibuka tanpa error.