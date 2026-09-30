# Arsitektur Sistem & Data Flow Diagram (DFD) - SIMAPO

Dokumen ini memuat **Data Flow Diagram (DFD)** dan **Arsitektur Aliran Data Sistem** untuk **SIMAPO (Apotek Bintan 88)** yang menggambarkan bagaimana data bergerak antara entitas eksternal, proses-proses komputasi, dan penyimpan data (*data store*).

---

## 1. DFD Level 0 (Context Diagram / Diagram Konteks)

Diagram Konteks memposisikan SIMAPO sebagai satu proses sentral tunggal yang berinteraksi dengan 4 entitas eksternal manusia (**Owner**, **Apoteker**, **Kasir**, **Admin Gudang**) dan 1 entitas eksternal sistem (**Telegram Bot API**).

### Diagram Mermaid

```mermaid
flowchart LR
    %% Entitas Eksternal
    Owner["👑 Owner / Pemilik"]
    Apoteker["💊 Apoteker"]
    Kasir["💵 Kasir"]
    Gudang["📦 Admin Gudang"]
    Telegram["🤖 Telegram Bot API"]

    %% Sistem Utama
    SIMAPO(("⚙️ SIMAPO\n(Sistem Informasi\nManajemen Apotek\nBintan 88)"))

    %% Aliran Data Owner
    Owner -->|Data Akun Staf, Konfig Bot, Otorisasi Void| SIMAPO
    SIMAPO -->|Laporan Laba Rugi, Dashboard Eksekutif, Alert Kritis| Owner

    %% Aliran Data Apoteker
    Apoteker -->|Master Obat, Verifikasi Resep, Hasil Stock Opname| SIMAPO
    SIMAPO -->|Daftar Obat Expired, Laporan Stok, Notifikasi Farmasi| Apoteker

    %% Aliran Data Kasir
    Kasir -->|Data Belanja POS, Modal Shift, Rekonsiliasi Kas Akhir| SIMAPO
    SIMAPO -->|Cetak Struk Kasir, Info Stok Tersedia, Status Transaksi| Kasir

    %% Aliran Data Admin Gudang
    Gudang -->|Data PBF, Purchase Order, Faktur Pembelian, Batch & Exp| SIMAPO
    SIMAPO -->|Rekomendasi Auto-PO, Bukti Terima Barang, Kartu Stok| Gudang

    %% Aliran Data Telegram
    SIMAPO -->|Payload Pesan Format HTML (Token, Chat ID, Teks Alert)| Telegram
    Telegram -->|Response Status HTTP 200 OK / Error JSON| SIMAPO
```

---

## 2. DFD Level 1 (Diagram Alur Data Tingkat Rinci)

DFD Level 1 memecah SIMAPO menjadi 7 proses bisnis utama beserta data store (*tabel-tabel database*) yang menjadi tempat penyimpanan data permanen.

### Diagram Mermaid

```mermaid
flowchart TD
    %% ==========================================
    %% ENTITAS EKSTERNAL
    %% ==========================================
    E_Owner["👑 Owner"]
    E_Apoteker["💊 Apoteker"]
    E_Kasir["💵 Kasir"]
    E_Gudang["📦 Admin Gudang"]
    E_Telegram["🤖 Telegram Bot API"]

    %% ==========================================
    %% PROSES BISNIS (BUBBLES)
    %% ==========================================
    P1(("1.0\nAutentikasi &\nManajemen User"))
    P2(("2.0\nPengelolaan\nMaster Data"))
    P3(("3.0\nManajemen Stok,\nBatch & Opname"))
    P4(("4.0\nPengadaan Barang\n& Hutang PBF"))
    P5(("5.0\nTransaksi Kasir\nPOS & Shift"))
    P6(("6.0\nPelaporan &\nAnalitik Dashboard"))
    P7(("7.0\nSistem Notifikasi\nTelegram & In-App"))

    %% ==========================================
    %% DATA STORES
    %% ==========================================
    DS1[("D1: users")]
    DS2[("D2: master_data\n(kategori, satuan, suplier, dokter, pelanggan)")]
    DS3[("D3: obat & batch_obat")]
    DS4[("D4: stok_mutasi")]
    DS5[("D5: shift_kasir & penjualan")]
    DS6[("D6: purchase_order & pembelian")]
    DS7[("D7: stock_opname & pemusnahan")]
    DS8[("D8: notifikasi & log")]
    DS9[("D9: pengaturan")]

    %% ==========================================
    %% ALUR PROSES 1.0 (USER & AUTH)
    %% ==========================================
    E_Owner -->|Input Data User & Role| P1
    P1 -->|Simpan Akun| DS1
    DS1 -->|Validasi Kredensial & Role| P1
    P1 -->|Izin Akses Menu| E_Owner
    P1 -->|Izin Akses Menu| E_Apoteker
    P1 -->|Izin Akses Menu| E_Kasir
    P1 -->|Izin Akses Menu| E_Gudang

    %% ==========================================
    %% ALUR PROSES 2.0 (MASTER DATA)
    %% ==========================================
    E_Apoteker -->|Katalog Obat, Kategori, Satuan, Dokter| P2
    E_Gudang -->|Kontak PBF / Suplier| P2
    P2 -->|CRUD Data Kontak & Obat| DS2
    P2 -->|Metadata Obat| DS3

    %% ==========================================
    %% ALUR PROSES 3.0 (STOK & BATCH)
    %% ==========================================
    E_Apoteker -->|Input Hasil Hitung Fisik Opname & BAP Pemusnahan| P3
    E_Gudang -->|Cek Posisi Stok Rak| P3
    P3 <-->|Baca & Update Kuantitas Batch| DS3
    P3 -->|Catat Riwayat Masuk/Keluar/Penyesuaian| DS4
    P3 -->|Simpan Dokumen Opname & BAP| DS7
    P3 -.->|Trigger Alert Selisih Opname| P7

    %% ==========================================
    %% ALUR PROSES 4.0 (PENGADAAN)
    %% ==========================================
    DS3 -->|Deteksi Stok <= Minimum| P4
    P4 -->|Daftar Rekomendasi Pemesanan| E_Gudang
    E_Gudang -->|Buat PO & Input Faktur Pembelian Masuk| P4
    P4 -->|Simpan PO & Faktur Pembelian| DS6
    P4 -->|Generate Batch Baru & Tambah Qty| DS3
    P4 -->|Catat Mutasi Masuk| DS4
    P4 -.->|Trigger Alert Barang Masuk & Hutang| P7

    %% ==========================================
    %% ALUR PROSES 5.0 (POS KASIR)
    %% ==========================================
    E_Kasir -->|Modal Awal & Hitung Kas Akhir| P5
    E_Kasir -->|Item Belanja, Resep Dokter, Pembayaran| P5
    P5 <-->|Validasi & Potong Batch FEFO| DS3
    P5 -->|Catat Pengurangan Stok| DS4
    P5 -->|Simpan Header & Detail Transaksi| DS5
    P5 -->|Cetak Struk Kasir & Rekap Shift| E_Kasir
    P5 -.->|Trigger Alert Void / Selisih Kas| P7

    %% ==========================================
    %% ALUR PROSES 6.0 (LAPORAN)
    %% ==========================================
    DS3 & DS4 & DS5 & DS6 -->|Agregasi Data Finansial & Operasional| P6
    P6 -->|Laporan Laba Rugi, Valuasi Stok, Tren Penjualan| E_Owner
    P6 -->|Laporan Obat Terlaris & Resep| E_Apoteker
    P6 -->|Laporan Pembelian & Hutang Suplier| E_Gudang

    %% ==========================================
    %% ALUR PROSES 7.0 (NOTIFIKASI)
    %% ==========================================
    E_Owner -->|Konfigurasi Bot Token & Saklar Trigger| P7
    P7 <-->|Baca Konfigurasi Notifikasi| DS9
    P7 -->|Simpan Log Pesan & In-App Alerts| DS8
    P7 -->|Kirim Pesan HTML via HTTPS API| E_Telegram
    E_Telegram -->|Push Notification Alert| E_Owner
    E_Telegram -->|Push Notification Alert| E_Apoteker
```

---

## 3. Matriks Aliran Data Antara Entitas & Proses

| Entitas | Input ke Proses | Output dari Proses |
|---|---|---|
| **Owner** | - Kredensial login<br>- Data akun karyawan & role<br>- Pengaturan token bot Telegram<br>- Otorisasi void transaksi | - Dashboard analitik & grafik omzet<br>- Laporan laba rugi apotek<br>- Ringkasan penjualan harian via Telegram<br>- Log audit aktivitas pengguna |
| **Apoteker** | - Master data obat & aturan pakai<br>- Verifikasi resep dokter<br>- Berita acara stock opname & pemusnahan | - Alert obat expired & hampir expired<br>- Laporan penjualan obat keras beresep<br>- Rekomendasi stok menipis |
| **Kasir** | - Nominal modal awal shift<br>- Item belanja obat bebas & resep<br>- Pembayaran tunai/QRIS/transfer<br>- Input nominal fisik kas saat tutup shift | - Struk kasir termal 58/80mm<br>- Lembar rekonsiliasi kas shift<br>- Pencarian obat & cek stok |
| **Admin Gudang**| - Data Pedagang Besar Farmasi (PBF)<br>- Pengajuan Purchase Order (PO)<br>- Input faktur barang masuk (No Batch, Exp Date)<br>- Pengajuan retur suplier | - Lembar Surat Pesanan PO resmi<br>- Daftar rekomendasi pemesanan otomatis<br>- Pengingat jatuh tempo hutang suplier |
| **Telegram API**| - Response status HTTP 200 / Error JSON | - Pesan broadcast HTML (Stok kosong, expired, hutang, rekap) |
