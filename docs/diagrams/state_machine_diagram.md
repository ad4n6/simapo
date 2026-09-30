# State Machine Diagram - SIMAPO (Apotek Bintan 88)

Dokumen ini memuat State Machine Diagram untuk siklus hidup entitas penting pada **SIMAPO (Apotek Bintan 88)**, yaitu:
1. **Siklus Hidup Status Purchase Order (PO)**
2. **Siklus Hidup Status Batch Obat & Kedaluwarsa**
3. **Siklus Hidup Status Shift Kasir**

---

## 1. State Machine Diagram: Siklus Status Purchase Order (PO)

Menggambarkan transisi status dokumen Purchase Order dari saat dirancang sebagai draft hingga barang diterima tuntas di gudang apotek atau dibatalkan.

### Diagram Mermaid

```mermaid
stateDiagram-v2
    [*] --> Draft : Buat Surat Pesanan Baru
    
    Draft --> Dipesan : Kirim PO ke Suplier / PBF
    Draft --> Batal : Batalkan PO oleh Staf
    
    Dipesan --> DiterimaSebagian : Terima Faktur Barang Sebagian (Qty Masuk < Qty Pesan)
    Dipesan --> Diterima : Terima Seluruh Barang Lengkap (Qty Masuk == Qty Pesan)
    Dipesan --> Batal : Suplier Konfirmasi Stok Kosong / Pembatalan PO
    
    DiterimaSebagian --> Diterima : Terima Pengiriman Susulan Sisa Barang
    
    Diterima --> [*] : PO Ditutup (Selesai Tuntas)
    Batal --> [*] : Dokumen Diarsipkan (Batal)
```

---

## 2. State Machine Diagram: Siklus Hidup Batch Obat (Status & Kedaluwarsa)

Menggambarkan transisi status dan kondisi fisik batch obat di rak apotek, mulai dari penerimaan faktur pembelian, penjualan FEFO, status kedaluwarsa, hingga pemusnahan resmi.

### Diagram Mermaid

```mermaid
stateDiagram-v2
    [*] --> Aktif_Aman : Pembelian Barang Masuk (Batch Dibuat, Exp > 90 Hari)
    
    state Aktif_Aman {
        [*] --> StokAman : Qty > Stok Minimum
        StokAman --> StokMenipis : Penjualan Kasir (Qty <= Stok Minimum)
    }

    Aktif_Aman --> HampirExpired : Sisa Waktu Kedaluwarsa <= 90 / 60 / 30 Hari
    note right of HampirExpired
        Sistem mengirim peringatan berkala 
        ke Telegram & In-App
    end note

    HampirExpired --> Expired : Tanggal Sekarang >= Tanggal Kedaluwarsa
    note right of Expired
        Batch OTOMATIS DIBLOKIR dari POS kasir
        (Tidak boleh dijual demi keselamatan pasien)
    end note

    Aktif_Aman --> Habis : Seluruh Stok Terjual via Kasir (Qty == 0)
    HampirExpired --> Habis : Seluruh Stok Terjual Sebelum Expired (Qty == 0)

    Expired --> Dimusnahkan : Pembuatan Berita Acara Pemusnahan Obat (BAP)
    Expired --> ReturSuplier : Dikembalikan ke PBF (Jika Ada Perjanjian Retur)

    Habis --> [*] : Batch Selesai
    Dimusnahkan --> [*] : Obat Dimusnahkan & Dicatat di BAP
    ReturSuplier --> [*] : Stok Dihapus & Diganti PBF
```

---

## 3. State Machine Diagram: Siklus Status Shift Kasir

Menggambarkan peralihan status kerja kasir dari pembukaan laci kasir hingga rekonsiliasi tutup buku.

### Diagram Mermaid

```mermaid
stateDiagram-v2
    [*] --> Buka : Kasir Input Saldo Modal Awal Laci Kasir
    
    state Buka {
        [*] --> Melayani
        Melayani --> CatatPenjualanTunai : Transaksi POS Tunai (Kas Laci Bertambah)
        CatatPenjualanTunai --> Melayani
        Melayani --> CatatNonTunai : Transaksi Transfer / QRIS (Kas Bank Bertambah)
        CatatNonTunai --> Melayani
    }

    Buka --> Tutup : Kasir Menghitung Fisik Uang & Input Tutup Shift
    
    state Tutup {
        [*] --> VerifikasiSelisih
        VerifikasiSelisih --> KasSesuai : Kas Fisik == Kas Seharusnya
        VerifikasiSelisih --> SelisihTerdeteksi : Kas Fisik != Kas Seharusnya (Selisih +/-)
        SelisihTerdeteksi --> KirimAlertTelegram : Trigger Notifikasi ke Owner
    }

    Tutup --> [*] : Kasir Logout & Cetak Rekap Shift
```

---

## 4. PlantUML Source Code (Alternatif Ekspor ke Word)

```plantuml
@startuml
[*] --> Draft : Buat PO Baru

Draft --> Dipesan : Kirim ke Suplier
Draft --> Batal : Dibatalkan Staf

Dipesan --> DiterimaSebagian : Barang Masuk Sebagian
Dipesan --> Diterima : Barang Masuk Lengkap
Dipesan --> Batal : Suplier Menolak PO

DiterimaSebagian --> Diterima : Pengiriman Susulan Tuntas

Diterima --> [*]
Batal --> [*]
@enduml
```
