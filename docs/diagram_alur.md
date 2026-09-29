# Diagram Alur Sistem (System Flowcharts) - SIMAPO

Dokumen ini memuat diagram alur proses bisnis inti pada **SIMAPO (Apotek Bintan 88)** menggunakan notasi Mermaid.

---

## 1. Diagram Alur Transaksi Kasir (POS) & Pengurangan Stok FEFO

Proses penjualan kasir dengan validasi stok fisik, aturan resep dokter, pengurangan kuantitas berbasis metode **FEFO (*First Expired, First Out*)**, pencatatan kartu mutasi stok, dan pencetakan struk termal.

```mermaid
flowchart TD
    Start([Mulai Transaksi Kasir]) --> OpenShift{Shift Kasir Terbuka?}
    OpenShift -- Tidak --> BukaModalShift[Buka Modal Masukkan Kas Awal]
    BukaModalShift --> InputShift[Shift Aktif Dimulai]
    InputShift --> ScanObat
    OpenShift -- Ya --> ScanObat[Kasir Cari / Scan Barcode Obat]

    ScanObat --> AddCart[Tambah ke Keranjang Belanja]
    AddCart --> CheckResep{Apakah Obat Butuh Resep?}
    
    CheckResep -- Ya --> ModeResep[Wajib Input Data Resep: No Resep, Dokter, Pasien]
    CheckResep -- Tidak --> CekStok[Validasi Total Stok Fisik Tersedia]
    ModeResep --> CekStok
    
    CekStok -- Stok Kurang --> AlertStok[Tampilkan Peringatan Stok Tidak Cukup]
    AlertStok --> ScanObat
    
    CekStok -- Stok Cukup --> SelesaiPilih{Ada Item Lain?}
    SelesaiPilih -- Ya --> ScanObat
    SelesaiPilih -- Tidak --> Checkout[Tekan F8 / Selesaikan Pembayaran]
    
    Checkout --> PilihMetode[Pilih: Tunai / Transfer / QRIS]
    PilihMetode --> Bayar[Input Jumlah Uang & Hitung Kembalian]
    
    Bayar --> DBTransaction[Mulai DB::transaction]
    DBTransaction --> SimpanPenjualan[Simpan Header Penjualan]
    
    SimpanPenjualan --> LoopItem[Iterasi Item Keranjang]
    LoopItem --> CariBatchFEFO[Ambil Batch Aktif Terurut: tanggal_kedaluwarsa ASC]
    
    CariBatchFEFO --> KurangiBatch[Potong Kuantitas Batch & Simpan Rincian]
    KurangiBatch --> CatatMutasi[Catat Riwayat ke Tabel stok_mutasi]
    CatatMutasi --> CekBatchHabis{Sisa Stok Batch == 0?}
    CekBatchHabis -- Ya --> UpdateStatusBatch[Ubah Status Batch = habis]
    CekBatchHabis -- Tidak --> CekSisaItem{Semua Qty Item Terpenuhi?}
    UpdateStatusBatch --> CekSisaItem
    
    CekSisaItem -- Belum --> CariBatchFEFO
    CekSisaItem -- Sudah --> NextItem{Ada Item Berikutnya?}
    NextItem -- Ya --> LoopItem
    NextItem -- Tidak --> CommitDB[Commit Transaksi Database]
    
    CommitDB --> StrukPrint[Buka Struk Termal Otomatis 58/80mm]
    StrukPrint --> CekAmbangStok{Apakah Total Stok <= Stok Minimum?}
    CekAmbangStok -- Ya --> TriggerNotif[Kirim Peringatan Stok Menipis/Kosong ke Telegram]
    CekAmbangStok -- Tidak --> Selesai([Selesai])
    TriggerNotif --> Selesai
```

---

## 2. Diagram Alur Pengadaan Barang & Penerimaan Batch Baru

Proses pengadaan obat dari rekomendasi stok menipis, penerimaan faktur dari Pedagang Besar Farmasi (PBF), pencatatan nomor batch, dan manajemen hutang suplier.

```mermaid
flowchart TD
    StartPO([Mulai Pengadaan Barang]) --> CekStokMinimum[Sistem Cek Stok Obat <= Minimum]
    CekStokMinimum --> RekomendasiPO[Tampilkan Daftar Rekomendasi Pemesanan]
    
    RekomendasiPO --> BuatPO[Admin Gudang / Apoteker Buat Surat Pesanan PO]
    BuatPO --> KirimSuplier[Kirim PO ke Distributor / PBF & Status = Dipesan]
    
    KirimSuplier --> BarangDatang[Barang & Faktur Tiba di Gudang Apotek]
    BarangDatang --> CekKesesuaian{Sesuai Fisik & Faktur?}
    
    CekKesesuaian -- Rusak / Salah Kirim --> ReturBeli[Buat Dokumen Retur Pembelian ke Suplier]
    ReturBeli --> SelesaiRetur([Proses Retur Selesai])
    
    CekKesesuaian -- Sesuai --> InputFaktur[Input Penerimaan Pembelian di SIMAPO]
    InputFaktur --> InputBatch[Input No Batch, Expired Date, Qty Masuk & Harga Beli]
    
    InputBatch --> SimpanPembelian[Simpan Transaksi Pembelian & Buat Record Batch Baru]
    SimpanPembelian --> TambahStok[Stok Fisik Obat Bertambah]
    TambahStok --> CatatKartuStok[Catat Riwayat ke Kartu Mutasi Stok: jenis = masuk]
    
    CatatKartuStok --> JenisBayar{Jenis Pembayaran?}
    JenisBayar -- Tunai --> StatusLunas[Status Pembelian = LUNAS]
    JenisBayar -- Kredit / Tempo --> StatusHutang[Catat Tanggal Jatuh Tempo & Sisa Hutang]
    
    StatusHutang --> NotifPenerimaan[Kirim Notifikasi Penerimaan Barang ke Telegram]
    StatusLunas --> NotifPenerimaan
    NotifPenerimaan --> Selesai([Selesai])
```

---

## 3. Diagram Alur Sistem Notifikasi Telegram & In-App Polling

Mekanisme deteksi kondisi darurat apotek, pencegahan spam, pengiriman pesan Telegram Bot API dengan format HTML, pencatatan log, dan konsumsi in-app.

```mermaid
flowchart TD
    TriggerEvent([Trigger: Jadwal Scheduler Cron / Aksi Kasir / Mutasi]) --> CekJenis{Jenis Pemicu?}
    
    CekJenis -- Scheduler 08:00 --> CekStok[Pindai Stok Menipis & Stok Kosong]
    CekJenis -- Scheduler 08:30 --> CekExpired[Pindai Obat Hampir Expired H-30/60/90 & Sudah Expired]
    CekJenis -- Scheduler 09:00 --> CekHutang[Pindai Faktur Pembelian Jatuh Tempo H-3/H-1]
    CekJenis -- Scheduler 21:00 --> RekapHarian[Kalkulasi Omzet, Transaksi, & Top 5 Obat Hari Ini]
    CekJenis -- Realtime Kasir --> VoidRetur[Otorisasi Void / Retur / Selisih Kas Shift]
    
    CekStok --> BuatDigest[Susun Ringkasan Pesan Format HTML + Emoji]
    CekExpired --> BuatDigest
    CekHutang --> BuatDigest
    RekapHarian --> BuatDigest
    VoidRetur --> BuatDigest
    
    BuatDigest --> CekAntiSpam{Sudah Pernah Terkirim Hari Ini?}
    CekAntiSpam -- Ya (Kecuali Peringatan Kritis) --> SkipKirim[Abaikan untuk Mencegah Spam Chat]
    SkipKirim --> SelesaiNotif([Selesai])
    
    CekAntiSpam -- Belum --> AmbilKonfig[Ambil BOT TOKEN & CHAT ID dari DB / .env]
    AmbilKonfig --> CekToken{Token & Chat ID Valid?}
    
    CekToken -- Tidak Lengkap --> LogGagal[Catat ke notifikasi_log: status = gagal]
    LogGagal --> SimpanInApp
    
    CekToken -- Lengkap --> KirimAPI[Http::post ke Telegram Bot API sendMessage]
    KirimAPI --> ResponTelegram{Respon HTTP 200 OK?}
    
    ResponTelegram -- Ya --> LogSukses[Catat ke notifikasi_log: status = terkirim]
    ResponTelegram -- Gagal / Error --> LogError[Catat ke notifikasi_log: status = gagal + Simpan Pesan Error]
    
    LogSukses --> SimpanInApp[Simpan Notifikasi ke Tabel notifikasi]
    LogError --> SimpanInApp
    
    SimpanInApp --> BrowserPolling[Browser Klien Polling Tiap 45 Detik via Vanilla JS Fetch]
    BrowserPolling --> UpdateBadge[Perbarui Jumlah Badge Merah di Ikon Lonceng Navbar]
    UpdateBadge --> SelesaiNotif
```

---

## 4. Diagram Alur Manajemen Shift Kasir & Rekonsiliasi Kas

Mekanisme penanggungjawaban uang kas harian antara modal awal, akumulasi transaksi tunai, dan perhitungan fisik saat serah terima kasir.

```mermaid
flowchart TD
    StartShift([Kasir Mulai Kerja]) --> ModalAwal[Input Saldo Kas Awal / Uang Kembalian]
    ModalAwal --> SimpanShift[Simpan Shift Kasir: status = BUKA]
    SimpanShift --> LayaniPenjualan[Kasir Melayani Transaksi POS Sepanjang Shift]
    
    LayaniPenjualan --> AkhirShift[Waktu Kerja Berakhir / Tutup Shift]
    AkhirShift --> HitungFisikLaci[Kasir Menghitung Uang Fisik Riil di Laci Kasir]
    HitungFisikLaci --> FormTutupShift[Buka Modal Tutup Shift Kasir]
    
    FormTutupShift --> SubmitTutup[Input Jumlah Kas Fisik Akhir & Catatan]
    SubmitTutup --> HitungSistem[Sistem Hitung: Total Seharusnya = Modal Awal + Penjualan Tunai]
    HitungSistem --> KalkulasiSelisih[Kalkulasi: Selisih = Kas Fisik - Total Seharusnya]
    
    KalkulasiSelisih --> CekSelisih{Apakah Terdapat Selisih Uang Kas?}
    CekSelisih -- Pas (Selisih = 0) --> UpdateShift[Update Shift: status = TUTUP, waktu_tutup = NOW]
    CekSelisih -- Ada Selisih (+/-) --> CatatSelisih[Tandai Selisih Kas pada Record Shift]
    
    CatatSelisih --> AlertOwner[Kirim Peringatan Real-Time ke Telegram Owner Mengenai Selisih Kas]
    AlertOwner --> UpdateShift
    UpdateShift --> LogoutKasir[Cetak Laporan Rekap Shift & Kasir Logout Aman]
    LogoutKasir --> Selesai([Selesai])
```

---
