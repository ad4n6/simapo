# Component & Package Diagram - SIMAPO (Apotek Bintan 88)

Dokumen ini memuat Component & Package Diagram untuk **SIMAPO (Apotek Bintan 88)** yang menggambarkan arsitektur modular perangkat lunak, pembagian paket subsistem, dan ketergantungan (*dependencies*) antar-komponen.

---

## 1. Component Diagram (Arsitektur Multi-Tier Modular)

### Diagram Mermaid

```mermaid
flowchart TB
    %% ==========================================================
    %% PRESENTATION LAYER
    %% ==========================================================
    subgraph PresentationLayer ["🖥️ PRESENTATION TIER (UI & CLIENT SCRIPTS)"]
        BladeViews["📄 Blade Template Engine\n- layouts/app.blade.php\n- sidebar & navbar components\n- Modals & Toast Alerts"]
        ViteAssets["🎨 Frontend Asset Bundle (Vite)\n- Tailwind CSS v4 Engine\n- Vanilla JS AJAX Core (Simapo Helper)\n- Chart.js Analytics Scripts"]
    end

    %% ==========================================================
    %% SECURITY & ROUTING LAYER
    %% ==========================================================
    subgraph RoutingSecurityLayer ["🛡️ ROUTING & SECURITY TIER"]
        WebRouter["🛣️ Web Route Registry (routes/web.php)"]
        RoleMW["🔒 RoleMiddleware (RBAC Guard)\nowner, apoteker, kasir, admin_gudang"]
        CSRF["🛡️ VerifyCsrfToken & Session Auth"]
    end

    %% ==========================================================
    %% APPLICATION CONTROLLER SUBSYSTEMS
    %% ==========================================================
    subgraph ControllerTier ["⚙️ CONTROLLER MODULE SUBSYSTEMS"]
        AuthModule["🔐 Auth & User Package\n- AuthController\n- UserController"]
        MasterModule["📋 Master Data Package\n- ObatController\n- KategoriController\n- SatuanController\n- SuplierController\n- DokterController\n- PelangganController"]
        StokModule["📦 Stok & Farmasi Package\n- StokController\n- StockOpnameController\n- PemusnahanObatController"]
        PosModule["💵 POS Kasir Package\n- PosController\n- PenjualanController\n- ReturPenjualanController\n- ShiftKasirController"]
        PengadaanModule["🚚 Pengadaan Package\n- PurchaseOrderController\n- PembelianController\n- ReturPembelianController\n- PengadaanRekomendasiController"]
        LaporanModule["📊 Laporan & Dashboard\n- LaporanController\n- DashboardController"]
        NotifModule["🤖 Notifikasi & Sistem\n- NotifikasiController\n- PengaturanController"]
    end

    %% ==========================================================
    %% CORE BUSINESS SERVICES
    %% ==========================================================
    subgraph ServiceTier ["🧩 CORE BUSINESS SERVICE TIER"]
        StokSvc["📦 StokService\n- kurangiStokFEFO()\n- tambahStokBatch()\n- kembalikanStokBatch()\n- catatMutasi()"]
        TeleSvc["🤖 TelegramService\n- kirimPesan()\n- isNotificationEnabled()\n- Anti-Spam Engine\n- Bot Token Resolver"]
    end

    %% ==========================================================
    %% PERSISTENCE LAYER (MODELS & DATABASE)
    %% ==========================================================
    subgraph PersistenceTier ["🗄️ PERSISTENCE TIER (ELOQUENT & DB)"]
        EloquentORM["🏛️ Eloquent ORM Entities\n- User, Obat, BatchObat, StokMutasi\n- Penjualan, Pembelian, PurchaseOrder\n- Resep, ShiftKasir, StockOpname\n- Pengaturan, NotifikasiLog"]
        DatabaseEngine[("💾 MySQL Database (29 Tables)")]
    end

    %% ==========================================================
    %% EXTERNAL SERVICES & LIBRARIES
    %% ==========================================================
    subgraph ExternalLibraries ["🌐 EXTERNAL LIBRARIES & APIS"]
        TelegramAPI["🤖 Telegram Bot API (HTTPS)"]
        DomPDF["📄 barryvdh/laravel-dompdf"]
        ExcelLib["📊 maatwebsite/excel"]
        ChartJS["📈 Chart.js Library"]
    end

    %% Hubungan Antar-Layer
    BladeViews <--> ViteAssets
    ViteAssets -->|AJAX Fetch HTTP Request| WebRouter
    WebRouter --> CSRF
    CSRF --> RoleMW
    
    RoleMW --> AuthModule
    RoleMW --> MasterModule
    RoleMW --> StokModule
    RoleMW --> PosModule
    RoleMW --> PengadaanModule
    RoleMW --> LaporanModule
    RoleMW --> NotifModule

    PosModule --> StokSvc
    PengadaanModule --> StokSvc
    StokModule --> StokSvc

    PosModule --> TeleSvc
    PengadaanModule --> TeleSvc
    StokModule --> TeleSvc
    NotifModule --> TeleSvc

    AuthModule --> EloquentORM
    MasterModule --> EloquentORM
    StokModule --> EloquentORM
    PosModule --> EloquentORM
    PengadaanModule --> EloquentORM
    LaporanModule --> EloquentORM
    NotifModule --> EloquentORM
    StokSvc --> EloquentORM
    TeleSvc --> EloquentORM

    EloquentORM <--> DatabaseEngine

    TeleSvc --> TelegramAPI
    LaporanModule --> DomPDF
    LaporanModule --> ExcelLib
    LaporanModule --> ChartJS
```

---

## 2. Deskripsi Hubungan Antar-Paket

1. **Paket POS Kasir & StokService**:
   Ketika kasir menekan tombol bayar, `PosController` memanggil `StokService::kurangiStokFEFO()` untuk memproses batch obat secara sekuensial berdasarkan tanggal kedaluwarsa terdekat.
2. **Paket Pengadaan & StokService**:
   Ketika Admin Gudang menyimpan penerimaan barang faktur, `PembelianController` memanggil `StokService::catatMutasi()` untuk menambah kuantitas batch dan mencatat kartu mutasi secara atomik di dalam `DB::transaction`.
3. **Paket Notifikasi & TelegramService**:
   Seluruh aksi penting (Void transaksi, retur penjualan, penerimaan barang, dan selisih kas shift) menginjeksi `TelegramService` untuk memeriksa status saklar konfigurasi, memfilter anti-spam, dan mengirimkan pesan HTML ke Telegram Bot API.
4. **Paket Laporan & Ekspor**:
   `LaporanController` mengintegrasikan `barryvdh/laravel-dompdf` untuk menghasilkan dokumen PDF resmi dan mengalirkan data CSV/Excel yang kompatibel dengan spreadsheet.
