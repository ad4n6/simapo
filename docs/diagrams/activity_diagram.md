# Activity Diagram - SIMAPO (Apotek Bintan 88)

Dokumen ini memuat Activity Diagram dengan **swimlane (jalur peran/komponen)** untuk proses bisnis utama pada **SIMAPO (Apotek Bintan 88)**. Setiap diagram membagi tanggung jawab ke dalam kolom aktor/komponen:
1. **Pengguna** (Kasir / Petugas Gudang / Apoteker / Owner)
2. **Frontend UI** (Browser / Vanilla JS / DOM)
3. **Backend Service / Controller** (Laravel 13 / StokService / TelegramService)
4. **Database** (MySQL / MariaDB)

---

## 1. Activity Diagram: Transaksi Kasir POS & Pengurangan Stok FEFO

Menggambarkan alur lengkap transaksi penjualan di kasir, mulai dari pemilihan mode transaksi (bebas/resep), validasi stok fisik, pemotongan stok otomatis per batch dengan metode **FEFO (*First Expired, First Out*)**, hingga pencetakan struk kasir termal.

### Diagram Mermaid (Visual Swimlane)

```mermaid
flowchart TD
    %% Definisi Swimlane via Subgraph
    subgraph Kasir ["Jalur: Kasir"]
        K1([Mulai Transaksi]) --> K2[Cari Nama Obat / Scan Barcode]
        K2 --> K3[Input Kuantitas Belanja]
        K4{Obat Perlu Resep?} -->|Ya| K5[Input Resep: No Resep, Dokter, Pasien]
        K4 -->|Tidak| K6[Klik Tambah ke Keranjang]
        K5 --> K6
        K7[Tekan F8 / Klik Bayar] --> K8[Pilih Metode: Tunai/QRIS/Transfer]
        K8 --> K9[Input Jumlah Nominal Uang]
        K9 --> K10[Klik Tombol Konfirmasi Bayar]
        K13[Terima Cetakan Struk Kasir & Serahkan Obat ke Pasien] --> K14([Transaksi Selesai])
    end

    subgraph Frontend ["Jalur: Browser / Vanilla JS (AJAX)"]
        F1[Kirim Request Live Search GET /pos/search-obat]
        F2[Tampilkan Hasil Pencarian Obat]
        F3[Validasi Keranjang di Sisi Klien]
        F4[Hitung Subtotal, Diskon, & Kembalian]
        F5[Kirim Request POST /pos/store via Fetch JSON]
        F6[Terima Response JSON Sukses]
        F7[Buka Window Print Struk: /pos/struk/TRX-ID]
    end

    subgraph Backend ["Jalur: PosController & StokService"]
        B1[Terima Payload Transaksi & Mulai DB::transaction]
        B2[Validasi Validitas Stok & Resep Dokter]
        B3{Stok Total Mencukupi?}
        B4[Panggil StokService::kurangiStokFEFO]
        B5[Query Batch Aktif Obat Terurut: tanggal_kedaluwarsa ASC]
        B6[Hitung Pemotongan Kuantitas per Batch]
        B7[Catat Riwayat ke Tabel stok_mutasi: jenis = KELUAR]
        B8[Simpan Header Penjualan & PenjualanDetail]
        B9[Commit DB::transaction]
        B10[Cek Ambang Batas Stok Minimum Obat]
        B11[Pemicu Notifikasi Stok Menipis/Kosong ke Telegram]
    end

    subgraph Database ["Jalur: MySQL Database"]
        D1[(Baca Data Obat & Stok)]
        D2[(Update Kolom jumlah pada batch_obat)]
        D3[(Insert stok_mutasi)]
        D4[(Insert penjualan & penjualan_detail)]
    end

    %% Relasi Lintas Swimlane
    K2 --> F1
    F1 --> D1
    D1 --> F2
    F2 --> K3
    K3 --> K4
    K6 --> F3
    F3 --> F4
    F4 --> K7
    K10 --> F5
    F5 --> B1
    B1 --> B2
    B2 --> B3
    B3 -->|Tidak Cukup| F3
    B3 -->|Cukup| B4
    B4 --> B5
    B5 --> D1
    B5 --> B6
    B6 --> D2
    B6 --> B7
    B7 --> D3
    B7 --> B8
    B8 --> D4
    D4 --> B9
    B9 --> B10
    B10 -->|Jika Stok <= Min| B11
    B9 --> F6
    F6 --> F7
    F7 --> K13
```

### Kode PlantUML (Alternatif Swimlane Native)

```plantuml
@startuml
|Kasir|
start
:Cari Obat / Scan Barcode;

|Frontend UI|
:Kirim Request Live Search (AJAX);

|Backend (Laravel)|
:Cari Data Obat & Ketersediaan Stok;

|Frontend UI|
:Tampilkan Hasil Pencarian;

|Kasir|
if (Obat Butuh Resep?) then (Ya)
    :Input Nomor Resep, Nama Dokter & Pasien;
else (Tidak)
endif
:Input Qty & Masukkan ke Keranjang;
:Pilih Metode Bayar & Input Uang;
:Klik Tombol Konfirmasi Bayar;

|Frontend UI|
:Kirim Data Transaksi (Fetch JSON);

|Backend (Laravel)|
:Mulai DB::transaction();
:Validasi Stok Fisik Tersedia;
if (Stok Cukup?) then (Ya)
    :Panggil StokService::kurangiStokFEFO();
    |Database|
    :Query Batch Aktif (ORDER BY expired ASC);
    :Potong Kuantitas Batch Obat;
    :Insert Kartu Stok (stok_mutasi);
    :Insert Header & Detail Penjualan;
    |Backend (Laravel)|
    :Commit DB::transaction();
    if (Stok Obat <= Stok Minimum?) then (Ya)
        :Kirim Notifikasi Peringatan ke Telegram;
    else (Tidak)
    endif
    |Frontend UI|
    :Tampilkan Toast Sukses & Cetak Struk;
    |Kasir|
    :Serahkan Struk & Obat ke Pelanggan;
    stop
else (Tidak)
    |Frontend UI|
    :Tampilkan Toast Error "Stok Tidak Cukup";
    |Kasir|
    :Periksa Ulang Kuantitas;
    stop
endif
@enduml
```

---

## 2. Activity Diagram: Penerimaan Barang & Pembuatan Batch Baru

Menggambarkan alur penerimaan obat dari suplier/PBF, baik dari Purchase Order (PO) maupun faktur langsung, input nomor batch dan tanggal kedaluwarsa, pembaruan stok fisik, serta pencatatan hutang tempo.

```mermaid
flowchart TD
    subgraph Gudang ["Jalur: Admin Gudang / Apoteker"]
        G1([Mulai Penerimaan Barang]) --> G2[Buka Halaman Penerimaan Pembelian]
        G2 --> G3{Ada Nomor PO?}
        G3 -->|Ya| G4[Pilih Dokumen PO Terkait]
        G3 -->|Tidak| G5[Pilih Suplier & Input No Faktur PBF]
        G4 --> G6[Verifikasi Fisik Obat & Nomor Faktur]
        G5 --> G6
        G6 --> G7[Input Item: Qty, No Batch, Expired Date & Harga Beli]
        G7 --> G8[Pilih Jenis Bayar: Tunai atau Kredit / Tempo]
        G8 --> G9[Simpan Penerimaan Pembelian]
        G12[Cek Label Batch & Simpan Obat ke Rak Gudang] --> G13([Selesai])
    end

    subgraph Backend ["Jalur: PembelianController & StokService"]
        B1[Validasi Input Faktur & Tanggal Expired]
        B2[Mulai DB::transaction]
        B3[Simpan Record ke Tabel pembelian]
        B4{Pembelian Kredit?}
        B5[Set status_bayar = belum_lunas & Catat Tanggal Jatuh Tempo]
        B6[Set status_bayar = lunas]
        B7[Buat Entitas Baru di Tabel batch_obat]
        B8[Panggil StokService::catatMutasi: jenis = MASUK]
        B9{Berasal dari PO?}
        B10[Update status PO: diterima / diterima_sebagian]
        B11[Commit DB::transaction]
        B12[Kirim Notifikasi Real-time Penerimaan Barang ke Telegram]
    end

    subgraph Database ["Jalur: MySQL Database"]
        D1[(Insert pembelian & pembelian_detail)]
        D2[(Insert batch_obat baru)]
        D3[(Insert stok_mutasi: MASUK)]
        D4[(Update purchase_order)]
    end

    G3 --> G4
    G9 --> B1
    B1 --> B2
    B2 --> B3
    B3 --> D1
    B3 --> B4
    B4 -->|Ya| B5
    B4 -->|Tidak| B6
    B5 --> B7
    B6 --> B7
    B7 --> D2
    B7 --> B8
    B8 --> D3
    B8 --> B9
    B9 -->|Ya| B10
    B10 --> D4
    B9 -->|Tidak| B11
    B10 --> B11
    B11 --> B12
    B12 --> G12
```

---

## 3. Activity Diagram: Pengelolaan Retur (Retur Penjualan & Retur Pembelian)

Menggambarkan penanganan retur obat rusak/keliru: baik dari sisi pembeli (pasien) maupun pengembalian ke suplier farmasi (PBF).

```mermaid
flowchart TD
    subgraph Pemohon ["Jalur: Kasir / Staf Apotek"]
        P1([Mulai Retur Barang]) --> P2{Jenis Retur?}
        P2 -->|Retur Penjualan| P3[Cari No Transaksi Penjualan Asal]
        P2 -->|Retur Pembelian| P4[Pilih Suplier & No Faktur Asal]
        P3 --> P5[Pilih Item & Jumlah Obat yang Diretur]
        P4 --> P6[Pilih Item Batch & Jumlah yang Rusak/Expired]
        P5 --> P7{Kondisi Obat Masih Layak?}
        P7 -->|Ya| P8[Centang 'Kembali ke Stok']
        P7 -->|Tidak| P9[Jangan Centang Kembali ke Stok]
        P8 --> P10[Input Alasan Retur & Submit]
        P9 --> P10
        P6 --> P11[Input Alasan Komplain & Submit Retur ke PBF]
        P14[Cetak Bukti Retur & Selesaikan Pembukuan] --> P15([Selesai])
    end

    subgraph Controller ["Jalur: Retur Controller & StokService"]
        C1[Mulai Transaksi DB::transaction]
        C2[Simpan Record retur_penjualan & Detail]
        C3{Stok Dikembalikan?}
        C4[Tambah Kuantitas Batch Asal via StokService]
        C5[Catat stok_mutasi: jenis = RETUR_JUAL]
        C6[Simpan Record retur_pembelian & Detail]
        C7[Kurangi Kuantitas Batch Terkait via StokService]
        C8[Catat stok_mutasi: jenis = RETUR_BELI]
        C9[Commit Database Transaction]
        C10[Kirim Alert Retur ke Telegram Owner/Apoteker]
    end

    subgraph Database ["Jalur: Database MySQL"]
        D1[(Tabel retur_penjualan & retur_penjualan_detail)]
        D2[(Update batch_obat)]
        D3[(Insert stok_mutasi)]
        D4[(Tabel retur_pembelian & retur_pembelian_detail)]
    end

    P10 --> C1
    C1 --> C2
    C2 --> D1
    C2 --> C3
    C3 -->|Ya| C4
    C4 --> D2
    C4 --> C5
    C5 --> D3
    C3 -->|Tidak| C9
    C5 --> C9
    P11 --> C1
    C1 --> C6
    C6 --> D4
    C6 --> C7
    C7 --> D2
    C7 --> C8
    C8 --> D3
    C8 --> C9
    C9 --> C10
    C10 --> P14
```

---

## 4. Activity Diagram: Stock Opname & Penyesuaian Selisih Stok

Menggambarkan tahapan audit berkala stok fisik apotek, pencatatan kuantitas riil per batch, kalkulasi selisih otomatis, dan penyesuaian saldo kartu stok.

```mermaid
flowchart TD
    subgraph Apoteker ["Jalur: Apoteker / Petugas Opname"]
        A1([Mulai Stock Opname]) --> A2[Buka Menu Stock Opname & Buat Dokumen Baru]
        A2 --> A3[Pilih Kategori / Seluruh Obat yang Akan Dihitung]
        A3 --> A4[Cetak Lembar Kerja Perhitungan Fisik]
        A4 --> A5[Hitung Kuantitas Fisik Riil Obat di Rak/Lemari]
        A5 --> A6[Input Kuantitas Fisik Riil per Batch ke Sistem]
        A6 --> A7{Terdapat Selisih Stok?}
        A7 -->|Ya| A8[Wajib Mengisi Keterangan/Alasan Selisih]
        A7 -->|Tidak| A9[Verifikasi Hasil Perhitungan]
        A8 --> A9
        A9 --> A10[Klik Tombol 'Selesaikan Stock Opname']
        A13[Cetak Berita Acara Hasil Stock Opname] --> A14([Selesai])
    end

    subgraph Backend ["Jalur: StockOpnameController & StokService"]
        B1[Terima Data Opname & Buka DB::transaction]
        B2[Kalkulasi Selisih = (stok_fisik - stok_sistem)]
        B3[Simpan Record ke stock_opname & Detail]
        B4[Update Kuantitas Fisik pada batch_obat]
        B5[Catat Selisih ke stok_mutasi: jenis = PENYESUAIAN]
        B6[Kunci Status Dokumen: status = SELESAI]
        B7{Selisih Signifikan / Obat Hilang?}
        B8[Kirim Peringatan Selisih Opname ke Telegram Owner]
        B9[Commit DB::transaction]
    end

    subgraph Database ["Jalur: MySQL Database"]
        D1[(Tabel stock_opname & detail)]
        D2[(Update tabel batch_obat)]
        D3[(Insert tabel stok_mutasi)]
    end

    A10 --> B1
    B1 --> B2
    B2 --> B3
    B3 --> D1
    B3 --> B4
    B4 --> D2
    B4 --> B5
    B5 --> D3
    B5 --> B6
    B6 --> B7
    B7 -->|Ya| B8
    B7 -->|Tidak| B9
    B8 --> B9
    B9 --> A13
```

---

## 5. Activity Diagram: Pengecekan Otomatis & Notifikasi Telegram

Menggambarkan eksekusi cron job/scheduler di server untuk memeriksa stok menipis, kedaluwarsa, hutang jatuh tempo, dan pengiriman rekap penjualan malam hari.

```mermaid
flowchart TD
    subgraph Scheduler ["Jalur: Cron Daemon / Scheduler"]
        S1([Waktu Cron Tiba: e.g. 08:00 / 21:00]) --> S2[Jalankan php artisan schedule:run]
        S2 --> S3{Command yang Terjadwal?}
        S3 -->|08:00 WIB| S4[Artisan notifikasi:cek-stok]
        S3 -->|08:30 WIB| S5[Artisan notifikasi:cek-expired]
        S3 -->|09:00 WIB| S6[Artisan notifikasi:cek-hutang]
        S3 -->|21:00 WIB| S7[Artisan notifikasi:ringkasan-harian]
    end

    subgraph Service ["Jalur: TelegramService & Command Logic"]
        T1[Pindai Database Berdasarkan Parameter Kondisi]
        T2{Ditemukan Data Anomali/Peringatan?}
        T3[Susun Pesan Format HTML Lengkap dengan Emoji]
        T4[Cek Status Saklar Notifikasi di Tabel pengaturan]
        T5{Jenis Notifikasi Diaktifkan Owner?}
        T6[Cek Anti-Spam: Apakah Entitas Sama Sudah Dikirim Hari Ini?]
        T7{Sudah Pernah Terkirim?}
        T8[Ambil Bot Token & Chat ID Grup/User]
        T9[Http::post ke Telegram Bot API sendMessage]
        T10[Catat Status Pengiriman ke notifikasi_log]
        T11[Simpan Record Peringatan ke Tabel notifikasi In-App]
        T12[Abaikan Pesan untuk Mencegah Spam]
        T13[Skip Pengiriman]
    end

    subgraph TelegramServer ["Jalur: Telegram Cloud API"]
        API1[Terima Permintaan HTTP POST]
        API2{Token & Chat ID Valid?}
        API3[Teruskan Pesan ke Aplikasi Telegram User/Grup]
        API4[Kembalikan HTTP 200 OK]
        API5[Kembalikan Error Response JSON]
    end

    S4 --> T1
    S5 --> T1
    S6 --> T1
    S7 --> T1
    T1 --> T2
    T2 -->|Tidak Ada| T13
    T2 -->|Ada Data| T3
    T3 --> T4
    T4 --> T5
    T5 -->|Tidak| T13
    T5 -->|Ya| T6
    T6 --> T7
    T7 -->|Ya| T12
    T7 -->|Belum| T8
    T8 --> T9
    T9 --> API1
    API1 --> API2
    API2 -->|Valid| API3
    API3 --> API4
    API2 -->|Invalid| API5
    API4 --> T10
    API5 --> T10
    T10 --> T11
    T11 --> S8([Proses Selesai])
    T12 --> S8
    T13 --> S8
```

---

## 6. Activity Diagram: Buka & Tutup Shift Kasir (Rekonsiliasi Kas Fisik)

Menggambarkan alur pertanggungjawaban uang kasir dari awal shift hingga tutup buku harian.

```mermaid
flowchart TD
    subgraph Kasir ["Jalur: Kasir Staf"]
        K1([Kasir Masuk Kerja]) --> K2[Buka Menu Kasir / Shift]
        K2 --> K3[Input Saldo Kas Modal Awal Laci]
        K3 --> K4[Mulai Melayani Transaksi POS Kasir]
        K4 --> K5[Waktu Kerja Shift Selesai]
        K5 --> K6[Hitung Total Uang Tunai Fisik di Laci Kasir]
        K6 --> K7[Buka Modal Tutup Shift Kasir]
        K7 --> K8[Input Nominal Kas Fisik & Catatan Shift]
        K8 --> K9[Submit Tutup Shift Kasir]
        K12[Cetak Lembar Rekonsiliasi Shift & Logout] --> K13([Shift Selesai])
    end

    subgraph Backend ["Jalur: ShiftKasirController"]
        B1[Buat Record Baru di shift_kasir: status = BUKA]
        B2[Akumulasi Transaksi Tunai & Non-Tunai Selama Shift Aktif]
        B3[Hitung Total Kas Seharusnya = Modal Awal + Penjualan Tunai]
        B4[Hitung Selisih Kas = Kas Fisik - Kas Seharusnya]
        B5[Update Record shift_kasir: status = TUTUP, waktu_tutup = NOW]
        B6{Apakah Ada Selisih Uang Kas?}
        B7[Kirim Peringatan Selisih Kas Shift ke Telegram Owner]
        B8[Generate Rekapitulasi Shift Kasir]
    end

    subgraph Database ["Jalur: Database MySQL"]
        D1[(Insert shift_kasir)]
        D2[(Query total penjualan shift)]
        D3[(Update shift_kasir)]
    end

    K3 --> B1
    B1 --> D1
    D1 --> K4
    K9 --> B2
    B2 --> D2
    D2 --> B3
    B3 --> B4
    B4 --> B5
    B5 --> D3
    B5 --> B6
    B6 -->|Ya| B7
    B6 -->|Tidak| B8
    B7 --> B8
    B8 --> K12
```
