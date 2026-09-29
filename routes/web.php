<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PemusnahanObatController;
use App\Http\Controllers\PengadaanRekomendasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\ReturPenjualanController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\ShiftKasirController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\SuplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/**
 * =====================================================================
 * GUEST / PUBLIC ROUTES
 * =====================================================================
 */
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/**
 * =====================================================================
 * AUTHENTICATED ROUTES
 * =====================================================================
 */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Polling Notifikasi In-App
    Route::get('/notifikasi/unread', [NotifikasiController::class, 'unread'])->name('notifikasi.unread');
    Route::post('/notifikasi/mark-all-read', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.markAllRead');

    /**
     * -----------------------------------------------------------------
     * OWNER ONLY ROUTES
     * -----------------------------------------------------------------
     */
    Route::middleware('role:owner')->group(function () {
        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

        // Pengaturan Sistem & Telegram
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::get('/pengaturan/notifikasi', [PengaturanController::class, 'index'])->name('pengaturan.notifikasi');
        Route::post('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::post('/pengaturan/test-telegram', [PengaturanController::class, 'testTelegram'])->name('pengaturan.testTelegram');
        Route::post('/pengaturan/run-check', [PengaturanController::class, 'runCheck'])->name('pengaturan.runCheck');

        // Riwayat & Kirim Ulang Notifikasi
        Route::get('/notifikasi/riwayat', [NotifikasiController::class, 'riwayat'])->name('notifikasi.riwayat');
        Route::post('/notifikasi/{log}/kirim-ulang', [NotifikasiController::class, 'kirimUlang'])->name('notifikasi.kirimUlang');

        // Laporan Laba Rugi & Log Aktivitas (Owner Saja)
        Route::get('/laporan/laba-rugi', [LaporanController::class, 'labaRugi'])->name('laporan.labaRugi');
        Route::get('/laporan/activity-log', [LaporanController::class, 'activityLog'])->name('laporan.activityLog');
        Route::get('/laporan/aktivitas', [LaporanController::class, 'activityLog'])->name('laporan.aktivitas');
    });

    /**
     * -----------------------------------------------------------------
     * POS & TRANSAKSI KASIR (owner, apoteker, kasir)
     * -----------------------------------------------------------------
     */
    Route::middleware('role:owner,apoteker,kasir')->group(function () {
        // POS Register
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search-obat', [PosController::class, 'searchObat'])->name('pos.searchObat');
        Route::post('/pos/store', [PosController::class, 'store'])->name('pos.store');
        Route::get('/pos/struk/{penjualan}', [PosController::class, 'struk'])->name('pos.struk');

        // Riwayat Penjualan Kasir
        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/{penjualan}', [PenjualanController::class, 'show'])->name('penjualan.show');

        // Retur Penjualan
        Route::get('/penjualan-retur', [ReturPenjualanController::class, 'index'])->name('penjualan.retur.index');
        Route::get('/penjualan-retur/create', [ReturPenjualanController::class, 'create'])->name('penjualan.retur.create');
        Route::post('/penjualan-retur', [ReturPenjualanController::class, 'store'])->name('penjualan.retur.store');
        Route::get('/penjualan-retur/{retur}', [ReturPenjualanController::class, 'show'])->name('penjualan.retur.show');

        // Sesi Shift Kasir
        Route::get('/kasir/shift', [ShiftKasirController::class, 'index'])->name('kasir.shift.index');
        Route::get('/shift', [ShiftKasirController::class, 'index'])->name('shift.index');
        Route::post('/kasir/shift/buka', [ShiftKasirController::class, 'buka'])->name('kasir.shift.buka');
        Route::post('/kasir/shift/{shift}/tutup', [ShiftKasirController::class, 'tutup'])->name('kasir.shift.tutup');
    });

    /**
     * -----------------------------------------------------------------
     * VOID TRANSAKSI (Hanya Owner & Apoteker)
     * -----------------------------------------------------------------
     */
    Route::middleware('role:owner,apoteker')->group(function () {
        Route::post('/penjualan/{penjualan}/void', [PenjualanController::class, 'voidTransaction'])->name('penjualan.void');
    });

    /**
     * -----------------------------------------------------------------
     * MASTER DATA OBAT & STOK
     * -----------------------------------------------------------------
     */
    // Obat & Stok (kasir can read, owner/apoteker can manage)
    Route::middleware('role:owner,apoteker,kasir,admin_gudang')->group(function () {
        Route::get('/obat', [ObatController::class, 'index'])->name('obat.index');
        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
    });

    Route::middleware('role:owner,apoteker')->group(function () {
        Route::post('/obat', [ObatController::class, 'store'])->name('obat.store');
        Route::put('/obat/{obat}', [ObatController::class, 'update'])->name('obat.update');
        Route::delete('/obat/{obat}', [ObatController::class, 'destroy'])->name('obat.destroy');

        // Kategori & Satuan
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        Route::get('/satuan', [SatuanController::class, 'index'])->name('satuan.index');
        Route::post('/satuan', [SatuanController::class, 'store'])->name('satuan.store');
        Route::put('/satuan/{satuan}', [SatuanController::class, 'update'])->name('satuan.update');
        Route::delete('/satuan/{satuan}', [SatuanController::class, 'destroy'])->name('satuan.destroy');

        // Dokter
        Route::get('/dokter', [DokterController::class, 'index'])->name('dokter.index');
        Route::post('/dokter', [DokterController::class, 'store'])->name('dokter.store');
        Route::put('/dokter/{dokter}', [DokterController::class, 'update'])->name('dokter.update');
        Route::delete('/dokter/{dokter}', [DokterController::class, 'destroy'])->name('dokter.destroy');

        // Pemusnahan Obat
        Route::get('/stok/pemusnahan', [PemusnahanObatController::class, 'index'])->name('stok.pemusnahan.index');
        Route::get('/stok/pemusnahan/create', [PemusnahanObatController::class, 'create'])->name('stok.pemusnahan.create');
        Route::post('/stok/pemusnahan', [PemusnahanObatController::class, 'store'])->name('stok.pemusnahan.store');
        Route::get('/stok/pemusnahan/{pemusnahan}', [PemusnahanObatController::class, 'show'])->name('stok.pemusnahan.show');
        Route::get('/stok/pemusnahan/{pemusnahan}/cetak', [PemusnahanObatController::class, 'cetak'])->name('stok.pemusnahan.cetak');
    });

    // Pelanggan (owner, apoteker, kasir)
    Route::middleware('role:owner,apoteker,kasir')->group(function () {
        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
        Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
        Route::put('/pelanggan/{pelanggan}', [PelangganController::class, 'update'])->name('pelanggan.update');
        Route::delete('/pelanggan/{pelanggan}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');
    });

    // Suplier, Kartu Stok, Opname (owner, apoteker, admin_gudang)
    Route::middleware('role:owner,apoteker,admin_gudang')->group(function () {
        Route::get('/suplier', [SuplierController::class, 'index'])->name('suplier.index');
        Route::post('/suplier', [SuplierController::class, 'store'])->name('suplier.store');
        Route::put('/suplier/{suplier}', [SuplierController::class, 'update'])->name('suplier.update');
        Route::delete('/suplier/{suplier}', [SuplierController::class, 'destroy'])->name('suplier.destroy');

        // Kartu Stok
        Route::get('/stok/mutasi', [StokController::class, 'mutasi'])->name('stok.mutasi');

        // Stock Opname
        Route::get('/stok/opname', [StockOpnameController::class, 'index'])->name('stok.opname.index');
        Route::get('/stok/opname/create', [StockOpnameController::class, 'create'])->name('stok.opname.create');
        Route::post('/stok/opname', [StockOpnameController::class, 'store'])->name('stok.opname.store');
        Route::get('/stok/opname/{opname}', [StockOpnameController::class, 'show'])->name('stok.opname.show');

        /**
         * -----------------------------------------------------------------
         * PENGADAAN BARANG (PO, Pembelian, Hutang, Retur Pembelian)
         * -----------------------------------------------------------------
         */
        Route::get('/pengadaan/rekomendasi', [PengadaanRekomendasiController::class, 'index'])->name('pengadaan.rekomendasi');

        // Purchase Order
        Route::get('/pengadaan/po', [PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('/pengadaan/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
        Route::post('/pengadaan/po', [PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/pengadaan/po/{po}', [PurchaseOrderController::class, 'show'])->name('po.show');
        Route::post('/pengadaan/po/{po}/status', [PurchaseOrderController::class, 'updateStatus'])->name('po.updateStatus');

        // Faktur Pembelian / Penerimaan Barang
        Route::get('/pengadaan/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
        Route::get('/pengadaan/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
        Route::post('/pengadaan/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');
        Route::get('/pengadaan/pembelian/hutang', [PembelianController::class, 'hutang'])->name('pembelian.hutang');
        Route::post('/pengadaan/pembelian/{pembelian}/bayar-hutang', [PembelianController::class, 'bayarHutang'])->name('pembelian.bayarHutang');
        Route::get('/pengadaan/pembelian/{pembelian}', [PembelianController::class, 'show'])->name('pembelian.show');

        // Retur Pembelian
        Route::get('/pengadaan/retur', [ReturPembelianController::class, 'index'])->name('pembelian.retur.index');
        Route::get('/pengadaan/retur/create', [ReturPembelianController::class, 'create'])->name('pembelian.retur.create');
        Route::post('/pengadaan/retur', [ReturPembelianController::class, 'store'])->name('pembelian.retur.store');
        Route::get('/pengadaan/retur/{retur}', [ReturPembelianController::class, 'show'])->name('pembelian.retur.show');

        // Laporan Stok, Pembelian, Retur, Mutasi (Akses Gudang & Apoteker/Owner)
        Route::get('/laporan/pembelian', [LaporanController::class, 'pembelian'])->name('laporan.pembelian');
        Route::get('/laporan/stok', [LaporanController::class, 'stok'])->name('laporan.stok');
        Route::get('/laporan/stok-menipis', [LaporanController::class, 'stokMenipis'])->name('laporan.stokMenipis');
        Route::get('/laporan/expired', [LaporanController::class, 'expired'])->name('laporan.expired');
        Route::get('/laporan/retur', [LaporanController::class, 'retur'])->name('laporan.retur');
        Route::get('/laporan/mutasi-stok', [LaporanController::class, 'mutasiStok'])->name('laporan.mutasiStok');
    });

    /**
     * -----------------------------------------------------------------
     * LAPORAN PENJUALAN & RESEP (owner, apoteker)
     * -----------------------------------------------------------------
     */
    Route::middleware('role:owner,apoteker')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'penjualan'])->name('laporan.index');
        Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan-obat', [LaporanController::class, 'penjualanObat'])->name('laporan.penjualanObat');
        Route::get('/laporan/resep', [LaporanController::class, 'resep'])->name('laporan.resep');
    });
});
