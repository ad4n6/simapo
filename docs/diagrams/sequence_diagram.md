# Sequence Diagram - SIMAPO (Apotek Bintan 88)

Dokumen ini memuat Sequence Diagram untuk **SIMAPO (Apotek Bintan 88)** yang menggambarkan kronologi pesan dan interaksi antar komponen (*User*, *Browser/View*, *Controller*, *Service*, *Model/Eloquent*, *Database*, dan *External API*).

---

## 1. Sequence Diagram: Transaksi Penjualan POS Kasir (Metode FEFO & Cetak Struk)

Menggambarkan alur eksekusi saat kasir menyelesaikan transaksi belanja obat di kasir POS satu layar menggunakan Vanilla JS Fetch AJAX, validasi resep, kalkulasi FEFO, transaksi database atomik, dan pencetakan struk kasir termal.

### Diagram Mermaid

```mermaid
sequenceDiagram
    autonumber
    actor Kasir as 💵 Kasir
    participant UI as 🖥️ Browser (Vanilla JS)
    participant PosCtrl as ⚙️ PosController
    participant StokSvc as 📦 StokService
    participant Penjualan as 📑 Model Penjualan
    participant DB as 🗄️ MySQL Database
    participant Struk as 🖨️ Cetak Struk View

    Kasir->>UI: Input item obat, qty, & klik "Bayar" (F8)
    Kasir->>UI: Pilih metode bayar & input nominal uang
    Kasir->>UI: Klik "Konfirmasi Bayar"
    
    activate UI
    UI->>PosCtrl: POST /pos/store (JSON Payload, CSRF Token)
    activate PosCtrl

    PosCtrl->>DB: DB::beginTransaction()
    
    loop Setiap Item Belanja
        PosCtrl->>StokSvc: kurangiStokFEFO(obat_id, qty, referensi)
        activate StokSvc
        StokSvc->>DB: SELECT * FROM batch_obat WHERE obat_id = ? AND status = 'aktif' AND jumlah > 0 ORDER BY tanggal_kedaluwarsa ASC
        DB-->>StokSvc: Return daftar batch aktif terurut tanggal
        
        loop Potong Kuantitas Batch
            StokSvc->>DB: UPDATE batch_obat SET jumlah = (jumlah - potong)
            StokSvc->>DB: INSERT INTO stok_mutasi (obat_id, batch_id, jenis='keluar', jumlah, saldo)
        end
        StokSvc-->>PosCtrl: Return detail potongan batch
        deactivate StokSvc
    end

    PosCtrl->>Penjualan: create(no_transaksi, subtotal, diskon, total, metode_bayar, bayar, kembalian)
    Penjualan->>DB: INSERT INTO penjualan
    DB-->>Penjualan: Return penjualan_id
    
    PosCtrl->>DB: INSERT INTO penjualan_detail (penjualan_id, obat_id, batch_obat_id, qty, harga)
    PosCtrl->>DB: DB::commit()
    
    PosCtrl-->>UI: Return JSON { success: true, id: 12, no_transaksi: "TRX-20260929-0001" }
    deactivate PosCtrl

    UI->>Kasir: Tampilkan Toast Sukses "Transaksi Berhasil Disimpan"
    UI->>Struk: window.open('/pos/struk/12', '_blank')
    activate Struk
    Struk-->>Kasir: Tampilkan Dialog Print Struk Kasir Termal 58/80mm
    deactivate Struk
    deactivate UI
```

---

## 2. Sequence Diagram: Pengecekan Otomatis & Notifikasi Telegram (Scheduler Cron)

Menggambarkan alur kerja cron job Laravel yang berjalan terjadwal di latar belakang (*background job*), mengeksekusi Artisan Command, mengecek stok kosong/menipis, memfilter anti-spam, memanggil Telegram Bot API, dan mencatat log transmisi pesan.

### Diagram Mermaid

```mermaid
sequenceDiagram
    autonumber
    actor Cron as ⏰ Linux Cron / Scheduler
    participant Cmd as 🖥️ CekStokCommand
    participant TeleSvc as 🤖 TelegramService
    participant DB as 🗄️ MySQL Database
    participant TeleAPI as 🌐 Telegram Bot API
    participant Group as 👥 Grup Telegram Apotek

    Cron->>Cmd: php artisan notifikasi:cek-stok
    activate Cmd
    
    Cmd->>DB: SELECT * FROM pengaturan WHERE key LIKE 'telegram_%'
    DB-->>Cmd: Return Bot Token, Group Chat ID, Status Saklar
    
    Cmd->>DB: SELECT * FROM obat WHERE total_stok <= stok_minimum
    DB-->>Cmd: Return daftar obat stok menipis / kosong
    
    alt Tidak Ada Obat Menipis
        Cmd-->>Cron: Log "Semua stok dalam kondisi aman" (Exit 0)
    else Ditemukan Obat Menipis
        Cmd->>TeleSvc: kirimPesan(pesanHtml, jenis='stok_menipis', entitas='obat', entitasId=1)
        activate TeleSvc
        
        TeleSvc->>DB: SELECT COUNT(*) FROM notifikasi_log WHERE jenis='stok_menipis' AND entitas_id=1 AND DATE(waktu)=CURDATE()
        DB-->>TeleSvc: Return 0 (Belum pernah terkirim hari ini)
        
        TeleSvc->>TeleAPI: HTTP POST https://api.telegram.org/bot{TOKEN}/sendMessage (chat_id, parse_mode=HTML, text)
        activate TeleAPI
        TeleAPI-->>Group: Kirim Pesan Alert Peringatan Stok
        TeleAPI-->>TeleSvc: Return HTTP 200 { ok: true, result: {...} }
        deactivate TeleAPI
        
        TeleSvc->>DB: INSERT INTO notifikasi_log (jenis, pesan, tujuan, status='terkirim')
        TeleSvc->>DB: INSERT INTO notifikasi (judul, pesan, tipe='warning', is_read=0)
        TeleSvc-->>Cmd: Return { success: true }
        deactivate TeleSvc
        
        Cmd-->>Cron: Log "Pengecekan stok selesai & pesan terkirim" (Exit 0)
    end
    deactivate Cmd
```

---

## 3. Sequence Diagram: Autentikasi Login & Otorisasi Hak Akses (RoleMiddleware)

Menggambarkan alur login pengguna, pembuatan sesi terenkripsi, pencegatan URL langsung oleh `RoleMiddleware`, dan pengalihan jika pengguna tidak memiliki izin (HTTP 403).

### Diagram Mermaid

```mermaid
sequenceDiagram
    autonumber
    actor User as 👤 Pengguna Staf
    participant LoginView as 🖥️ Halaman Login (/login)
    participant AuthCtrl as ⚙️ AuthController
    participant Middleware as 🛡️ RoleMiddleware
    participant DB as 🗄️ MySQL Database
    participant Dashboard as 📊 Dashboard / Halaman Tujuan

    User->>LoginView: Input Email & Password, Submit Form
    activate LoginView
    LoginView->>AuthCtrl: POST /login (email, password, _token)
    activate AuthCtrl
    
    AuthCtrl->>DB: SELECT * FROM users WHERE email = ? AND is_active = 1
    DB-->>AuthCtrl: Return User Record (dengan password hash bcrypt)
    
    alt Kredensial Salah
        AuthCtrl-->>LoginView: Redirect back with error "Email atau password salah."
        LoginView-->>User: Tampilkan Pesan Error
    else Kredensial Benar
        AuthCtrl->>DB: INSERT INTO sessions (user_id, ip_address, user_agent, payload)
        AuthCtrl->>DB: INSERT INTO activity_log (user_id, aksi='login')
        AuthCtrl-->>LoginView: Redirect to /dashboard
        deactivate AuthCtrl
        
        User->>Middleware: Akses Halaman Tertentu (e.g. GET /pengaturan)
        activate Middleware
        Middleware->>Middleware: Cek auth()->check() & auth()->user()->role
        
        alt Role Pengguna Cocok (e.g. Role = 'owner')
            Middleware->>Dashboard: Teruskan Request ke Controller Tujuan
            activate Dashboard
            Dashboard-->>User: Tampilkan Halaman Pengaturan (HTTP 200 OK)
            deactivate Dashboard
        else Role Tidak Cocok (e.g. Kasir mencoba akses /pengaturan)
            Middleware-->>User: Abort HTTP 403 Forbidden ("Akses ditolak.")
        end
        deactivate Middleware
    end
    deactivate LoginView
```

---

## 4. Sequence Diagram: Penerimaan Barang & Pembuatan Batch Baru

Menggambarkan interaksi saat Admin Gudang menerima barang dari suplier, mencatat faktur, membuat batch obat baru, memperbarui saldo kartu stok, dan mengupdate status Purchase Order (PO).

### Diagram Mermaid

```mermaid
sequenceDiagram
    autonumber
    actor Gudang as 📦 Admin Gudang
    participant Browser as 🖥️ Form Penerimaan
    participant BeliCtrl as ⚙️ PembelianController
    participant BatchModel as 💊 Model BatchObat
    participant MutasiModel as 📈 Model StokMutasi
    participant DB as 🗄️ Database MySQL
    participant Telegram as 🤖 TelegramService

    Gudang->>Browser: Pilih PO, input No Faktur, Tanggal, No Batch, Exp Date, Qty, Harga Beli
    Gudang->>Browser: Klik Simpan Penerimaan Barang
    activate Browser
    Browser->>BeliCtrl: POST /pengadaan/pembelian (FormData)
    activate BeliCtrl

    BeliCtrl->>DB: DB::beginTransaction()
    BeliCtrl->>DB: INSERT INTO pembelian (no_faktur, po_id, suplier_id, total, status_bayar)
    DB-->>BeliCtrl: Return pembelian_id
    
    loop Setiap Item Obat Masuk
        BeliCtrl->>BatchModel: create(obat_id, suplier_id, no_batch, tanggal_kedaluwarsa, jumlah, harga_beli, status='aktif')
        BatchModel->>DB: INSERT INTO batch_obat
        DB-->>BatchModel: Return batch_obat_id
        
        BeliCtrl->>MutasiModel: create(obat_id, batch_obat_id, jenis='masuk', jumlah, referensi=no_faktur)
        MutasiModel->>DB: INSERT INTO stok_mutasi
        
        alt Berasal dari Purchase Order (PO)
            BeliCtrl->>DB: UPDATE purchase_order_detail SET jumlah_diterima = (jumlah_diterima + qty)
        end
    end

    alt Semua Item PO Terpenuhi
        BeliCtrl->>DB: UPDATE purchase_order SET status = 'diterima'
    else Sebagian Terpenuhi
        BeliCtrl->>DB: UPDATE purchase_order SET status = 'diterima_sebagian'
    end

    BeliCtrl->>DB: DB::commit()
    
    BeliCtrl->>Telegram: kirimPesan("🚚 PENERIMAAN BARANG DARI SUPLIER...", jenis='penerimaan')
    activate Telegram
    Telegram->>DB: INSERT INTO notifikasi_log
    Telegram-->>BeliCtrl: Sukses
    deactivate Telegram

    BeliCtrl-->>Browser: Redirect with success "Faktur penerimaan barang berhasil disimpan!"
    deactivate BeliCtrl
    Browser-->>Gudang: Tampilkan Detail Faktur & Kartu Stok Terupdate
    deactivate Browser
```

---

## 5. PlantUML Source Code (Alternatif Ekspor ke Word)

```plantuml
@startuml
autonumber
actor "Kasir" as kasir
boundary "Browser / UI" as ui
control "PosController" as ctrl
entity "StokService" as svc
database "MySQL Database" as db

kasir -> ui: Klik Tombol Bayar (F8)
activate ui
ui -> ctrl: POST /pos/store (JSON Payload)
activate ctrl

ctrl -> db: DB::beginTransaction()

loop Setiap Item Obat Belanja
    ctrl -> svc: kurangiStokFEFO(obat_id, qty)
    activate svc
    svc -> db: SELECT batch_obat ORDER BY exp ASC
    db --> svc: List Batch Aktif
    svc -> db: UPDATE batch_obat SET jumlah
    svc -> db: INSERT INTO stok_mutasi
    svc --> ctrl: Selesai Potong Batch
    deactivate svc
end

ctrl -> db: INSERT INTO penjualan
ctrl -> db: INSERT INTO penjualan_detail
ctrl -> db: DB::commit()

ctrl --> ui: Return JSON { success: true, id: 12 }
deactivate ctrl

ui -> kasir: Buka Modal Cetak Struk Kasir
deactivate ui
@enduml
```
