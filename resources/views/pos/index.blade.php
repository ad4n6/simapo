@extends('layouts.app')

@section('title', 'Point of Sale (Kasir)')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span>Point of Sale (POS Kasir Apotek)</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                FEFO Aktif
            </span>
        </h1>
        <p class="text-xs text-gray-500 mt-0.5">Penjualan obat bebas & berresep dokter dengan pemotongan stok otomatis batch kedaluwarsa terdekat.</p>
    </div>
    <div class="flex items-center gap-2">
        @if($activeShift)
            <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Shift Buka: {{ \Carbon\Carbon::parse($activeShift->waktu_buka)->format('H:i') }}</span>
                <span class="text-gray-400">|</span>
                <span>Modal: Rp {{ number_format($activeShift->modal_awal, 0, ',', '.') }}</span>
            </div>
            <button onclick="bukaModalTutupShift()" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-2xs">
                Tutup Shift
            </button>
        @else
            <div class="flex items-center gap-2">
                <span class="text-xs text-amber-700 font-semibold bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200">
                    ⚠️ Shift Kasir Belum Dibuka
                </span>
                <button onclick="bukaModalBukaShift()" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">
                    Buka Shift Sekarang
                </button>
            </div>
        @endif
        <a href="{{ route('penjualan.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-2xs">
            Riwayat Nota
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 h-[calc(100vh-12rem)] min-h-[650px]">

    <!-- Kolom Kiri: Katalog & Pencarian Obat (7 Kolom) -->
    <div class="lg:col-span-7 flex flex-col bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-gray-100 space-y-3 bg-slate-50/50">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="posSearch" placeholder="Cari nama obat, kode, generik, atau scan barcode... (Tekan F2)" autofocus
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:ring-2 focus:ring-emerald-500 outline-none shadow-2xs">
            </div>
            
            <div class="flex items-center justify-between text-[11px] text-gray-500">
                <span id="labelJumlahObat">Memuat daftar obat aktif...</span>
                <span class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Bebas</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Butuh Resep (Obat Keras)</span>
                </span>
            </div>
        </div>

        <!-- Medicine List Cards Grid -->
        <div class="flex-1 p-4 overflow-y-auto" id="posObatContainer">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3" id="obatGrid">
                <!-- Dynamically rendered by JS -->
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Keranjang & Kasir Checkout (5 Kolom) -->
    <div class="lg:col-span-5 flex flex-col bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <!-- Tab Mode Transaksi: Bebas vs Resep -->
        <div class="p-3 border-b border-gray-100 bg-slate-50/50 flex items-center justify-between gap-2">
            <div class="flex rounded-xl bg-gray-200/80 p-1 w-full">
                <button type="button" id="tabBebas" onclick="setJenisTransaksi('bebas')"
                    class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-emerald-800 shadow-xs">
                    💊 Obat Bebas
                </button>
                <button type="button" id="tabResep" onclick="setJenisTransaksi('resep')"
                    class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-600 hover:text-gray-900">
                    📋 Dengan Resep Dokter
                </button>
            </div>
        </div>

        <!-- Bagian Input Form Khusus Resep (Hidden by default) -->
        <div id="sectionResep" class="p-4 border-b border-amber-200 bg-amber-50/40 space-y-3 hidden text-xs">
            <div class="flex items-center justify-between text-amber-800 font-bold">
                <span>Data Validasi Resep Dokter</span>
                <span class="text-[10px] bg-amber-200 text-amber-900 px-2 py-0.5 rounded">Wajib untuk Obat Keras</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Pasien <span class="text-rose-500">*</span></label>
                    <input type="text" id="resepNamaPasien" placeholder="Contoh: Tn. Bambang"
                        class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">No. Resep</label>
                    <input type="text" id="resepNo" placeholder="Otomatis jika kosong"
                        class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:ring-1 focus:ring-emerald-500 font-mono">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Dokter Pemeriksa</label>
                    <select id="resepDokterId" class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Pilih Dokter Terdaftar --</option>
                        @foreach($dokters as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->nama }} ({{ $doc->spesialis ?? 'Umum' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Atau Nama Dokter Luar</label>
                    <input type="text" id="resepNamaDokterManual" placeholder="dr. ..."
                        class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <!-- Pelanggan & Catatan -->
        <div class="px-4 py-2 border-b border-gray-100 flex items-center gap-2 text-xs">
            <span class="text-gray-400 font-semibold whitespace-nowrap">Pelanggan:</span>
            <select id="pelangganId" class="flex-1 bg-transparent py-1 border-0 text-xs font-semibold text-gray-800 outline-none">
                <option value="">Pelanggan Umum (Walk-in / Tunai)</option>
                @foreach($pelanggans as $pel)
                    <option value="{{ $pel->id }}">{{ $pel->nama }} ({{ $pel->telepon ?? 'No Telp' }})</option>
                @endforeach
            </select>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 p-4 overflow-y-auto space-y-2.5" id="cartContainer">
            <!-- Dynamically populated cart items -->
        </div>

        <!-- Cart Summary & Checkout Footer -->
        <div class="p-4 border-t border-gray-100 bg-slate-50/70 space-y-3">
            <!-- Subtotal & Diskon -->
            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal:</span>
                    <span class="font-mono font-bold text-gray-900" id="cartSubtotal">Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-gray-600 gap-2">
                    <span>Diskon (%):</span>
                    <div class="flex items-center gap-2">
                        <input type="number" id="diskonPersen" value="0" min="0" max="100" oninput="hitungTotalBayar()"
                            class="w-16 px-2 py-0.5 text-right bg-white border border-gray-200 rounded text-xs font-mono outline-none">
                        <span class="font-mono text-rose-600" id="cartDiskonNominal">- Rp 0</span>
                    </div>
                </div>
                <div class="flex justify-between text-sm font-bold text-gray-900 pt-1 border-t border-gray-200">
                    <span>Total Pembayaran:</span>
                    <span class="font-mono text-emerald-700 text-base" id="cartTotalAkhir">Rp 0</span>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Metode Bayar:</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="setMetodeBayar('tunai')" id="btnMetodeTunai"
                        class="py-1.5 rounded-xl text-xs font-bold border border-emerald-500 bg-emerald-50 text-emerald-800 transition-colors">
                        💵 Tunai
                    </button>
                    <button type="button" onclick="setMetodeBayar('transfer')" id="btnMetodeTransfer"
                        class="py-1.5 rounded-xl text-xs font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                        🏦 Transfer
                    </button>
                    <button type="button" onclick="setMetodeBayar('qris')" id="btnMetodeQris"
                        class="py-1.5 rounded-xl text-xs font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                        📱 QRIS
                    </button>
                </div>
            </div>

            <!-- Input Uang Bayar & Kembalian (Khusus Tunai) -->
            <div id="sectionTunai" class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Uang Diterima (Rp):</label>
                        <input type="number" id="inputJumlahBayar" value="0" min="0" oninput="hitungKembalian()"
                            class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Kembalian:</label>
                        <div class="px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-mono font-bold text-emerald-700 text-right truncate" id="labelKembalian">
                            Rp 0
                        </div>
                    </div>
                </div>

                <!-- Quick Cash Presets -->
                <div class="flex flex-wrap gap-1">
                    <button type="button" onclick="isiUangPas()" class="px-2 py-1 rounded bg-white hover:bg-gray-100 border border-gray-200 text-[10px] font-bold text-gray-700">Uang Pas</button>
                    <button type="button" onclick="tambahUang(10000)" class="px-2 py-1 rounded bg-white hover:bg-gray-100 border border-gray-200 text-[10px] font-semibold text-gray-700">+10k</button>
                    <button type="button" onclick="tambahUang(20000)" class="px-2 py-1 rounded bg-white hover:bg-gray-100 border border-gray-200 text-[10px] font-semibold text-gray-700">+20k</button>
                    <button type="button" onclick="tambahUang(50000)" class="px-2 py-1 rounded bg-white hover:bg-gray-100 border border-gray-200 text-[10px] font-semibold text-gray-700">+50k</button>
                    <button type="button" onclick="tambahUang(100000)" class="px-2 py-1 rounded bg-white hover:bg-gray-100 border border-gray-200 text-[10px] font-semibold text-gray-700">+100k</button>
                </div>
            </div>

            <!-- Tombol Proses Transaksi -->
            <button type="button" id="btnCheckout" onclick="prosesTransaksi()"
                class="w-full py-3 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] transition-all shadow-md flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>PROSES TRANSAKSI (F8)</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal Sukses Transaksi -->
<div id="modalSuksesTrx" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-sm w-full p-6 text-center space-y-4 animate-in fade-in zoom-in-95">
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-3xl">
            ✓
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-900">Transaksi Berhasil!</h3>
            <p class="text-xs text-gray-500 font-mono mt-1" id="suksesNoTrx">TRX-00000000-0000</p>
        </div>
        
        <div class="bg-emerald-50 rounded-2xl p-4 text-xs space-y-1">
            <div class="flex justify-between text-emerald-800">
                <span>Total Belanja:</span>
                <span class="font-mono font-bold" id="suksesTotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-800">
                <span>Uang Kembalian:</span>
                <span class="font-mono font-bold text-emerald-900 text-sm" id="suksesKembalian">Rp 0</span>
            </div>
        </div>

        <div class="space-y-2 pt-2">
            <a id="btnCetakStruk" href="#" target="_blank" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 flex items-center justify-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Struk Thermal</span>
            </a>
            <button onclick="tutupModalSukses()" class="w-full py-2.5 rounded-xl text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200">
                Transaksi Baru (Esc)
            </button>
        </div>
    </div>
</div>

<!-- Modal Buka Shift -->
<div id="modalBukaShift" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-sm w-full p-5 space-y-4">
        <h3 class="text-sm font-bold text-gray-900">Buka Shift Kasir Baru</h3>
        <p class="text-xs text-gray-500">Masukkan modal kas awal untuk uang kembalian di laci kasir.</p>
        <form action="{{ route('kasir.shift.buka') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Modal Kas Awal (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="modal_awal" value="100000" min="0" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Shift</label>
                <input type="text" name="catatan" placeholder="Contoh: Shift Pagi, uang receh lengkap"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalBukaShift()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700">Mulai Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tutup Shift -->
@if($activeShift)
<div id="modalTutupShift" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-sm w-full p-5 space-y-4">
        <h3 class="text-sm font-bold text-gray-900">Tutup Shift Kasir & Rekonsiliasi</h3>
        <div class="bg-slate-50 p-3 rounded-xl text-xs space-y-1 text-gray-600">
            <div class="flex justify-between">
                <span>Modal Awal:</span>
                <span class="font-mono font-bold">Rp {{ number_format($activeShift->modal_awal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Penjualan Tunai:</span>
                <span class="font-mono font-bold text-emerald-600">Rp {{ number_format($activeShift->total_penjualan_tunai, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 pt-1 border-t border-gray-200">
                <span>Ekspektasi Uang Tunai:</span>
                <span class="font-mono text-emerald-700">Rp {{ number_format($activeShift->modal_awal + $activeShift->total_penjualan_tunai, 0, ',', '.') }}</span>
            </div>
        </div>

        <form action="{{ route('kasir.shift.tutup', $activeShift->id) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Hitungan Uang Fisik Aktual di Laci (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="uang_fisik_akhir" value="{{ $activeShift->modal_awal + $activeShift->total_penjualan_tunai }}" min="0" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                <span class="text-[10px] text-gray-400 mt-1 block">Sistem akan mencatat selisih jika ada dan mengirim laporan ke Owner.</span>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Penutupan</label>
                <input type="text" name="catatan" placeholder="Kondisi laci kasir, setoran..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalTutupShift()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-900">Rekonsiliasi & Tutup</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    // Master data obat injected dari controller
    let MEDICINES = @json($obats);
    let CART = [];
    let currentJenis = 'bebas';
    let currentMetode = 'tunai';

    function setJenisTransaksi(jenis) {
        currentJenis = jenis;
        const btnBebas = document.getElementById('tabBebas');
        const btnResep = document.getElementById('tabResep');
        const secResep = document.getElementById('sectionResep');

        if (jenis === 'resep') {
            btnResep.className = 'flex-1 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-emerald-800 shadow-xs';
            btnBebas.className = 'flex-1 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-600 hover:text-gray-900';
            secResep.classList.remove('hidden');
        } else {
            // Cek jika keranjang ada obat keras
            const adaObatKeras = CART.some(item => item.butuh_resep);
            if (adaObatKeras) {
                Simapo.toast('Keranjang berisi obat keras. Transaksi wajib menggunakan mode resep!', 'warning');
                setJenisTransaksi('resep');
                return;
            }
            btnBebas.className = 'flex-1 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-emerald-800 shadow-xs';
            btnResep.className = 'flex-1 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-600 hover:text-gray-900';
            secResep.classList.add('hidden');
        }
    }

    function setMetodeBayar(metode) {
        currentMetode = metode;
        const btnTunai = document.getElementById('btnMetodeTunai');
        const btnTransfer = document.getElementById('btnMetodeTransfer');
        const btnQris = document.getElementById('btnMetodeQris');
        const secTunai = document.getElementById('sectionTunai');

        [btnTunai, btnTransfer, btnQris].forEach(b => {
            b.className = 'py-1.5 rounded-xl text-xs font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-colors';
        });

        if (metode === 'tunai') {
            btnTunai.className = 'py-1.5 rounded-xl text-xs font-bold border border-emerald-500 bg-emerald-50 text-emerald-800 transition-colors';
            secTunai.classList.remove('hidden');
        } else if (metode === 'transfer') {
            btnTransfer.className = 'py-1.5 rounded-xl text-xs font-bold border border-emerald-500 bg-emerald-50 text-emerald-800 transition-colors';
            secTunai.classList.add('hidden');
        } else {
            btnQris.className = 'py-1.5 rounded-xl text-xs font-bold border border-emerald-500 bg-emerald-50 text-emerald-800 transition-colors';
            secTunai.classList.add('hidden');
        }
    }

    function renderObatGrid(list) {
        const grid = document.getElementById('obatGrid');
        document.getElementById('labelJumlahObat').innerText = `Menampilkan ${list.length} produk obat aktif`;

        if (list.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full py-12 text-center text-gray-400">
                    <p class="text-sm font-semibold text-gray-600">Obat tidak ditemukan</p>
                    <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain atau periksa stok obat.</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = list.map(m => {
            const hasStock = m.stok > 0;
            const resepBadge = m.butuh_resep 
                ? '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Resep</span>'
                : '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Bebas</span>';

            const edInfo = m.batch_terdekat 
                ? `<span class="text-[10px] text-gray-400 font-mono">ED: ${m.batch_terdekat.expired}</span>`
                : '';

            return `
                <div onclick="tambahKeKeranjang(${m.id})"
                    class="p-3 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between select-none ${hasStock ? 'bg-white border-gray-100 hover:border-emerald-300 hover:shadow-md' : 'bg-gray-50 border-gray-100 opacity-60 pointer-events-none'}">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-1">
                            ${resepBadge}
                            <span class="text-[10px] font-mono text-gray-400">${m.kode}</span>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 leading-snug line-clamp-2">${m.nama}</h4>
                        <div class="text-[11px] text-gray-400 italic line-clamp-1">${m.nama_generik || '-'}</div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-50 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-mono font-bold text-emerald-700">Rp ${m.harga_jual.toLocaleString('id-ID')}</div>
                            ${edInfo}
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full font-bold text-[11px] ${m.stok <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800'}">
                                ${m.stok} ${m.satuan}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function tambahKeKeranjang(obatId) {
        const obat = MEDICINES.find(m => m.id === obatId);
        if (!obat) return;

        if (obat.stok <= 0) {
            Simapo.toast(`Stok obat ${obat.nama} habis.`, 'warning');
            return;
        }

        const existing = CART.find(item => item.id === obatId);
        if (existing) {
            if (existing.qty + 1 > obat.stok) {
                Simapo.toast(`Stok tersedia hanya ${obat.stok} ${obat.satuan}.`, 'warning');
                return;
            }
            existing.qty += 1;
        } else {
            CART.push({
                id: obat.id,
                kode: obat.kode,
                nama: obat.nama,
                satuan: obat.satuan,
                harga: obat.harga_jual,
                stok: obat.stok,
                butuh_resep: obat.butuh_resep,
                qty: 1
            });
        }

        // Jika obat butuh resep, otomatis aktifkan mode resep
        if (obat.butuh_resep && currentJenis !== 'resep') {
            setJenisTransaksi('resep');
            Simapo.toast(`Obat "${obat.nama}" membutuhkan resep dokter. Mode beralih otomatis.`, 'info');
        }

        renderCart();
    }

    function ubahQty(obatId, delta) {
        const item = CART.find(i => i.id === obatId);
        if (!item) return;

        const newQty = item.qty + delta;
        if (newQty <= 0) {
            hapusDariKeranjang(obatId);
            return;
        }

        if (newQty > item.stok) {
            Simapo.toast(`Stok maksimal tersedia: ${item.stok} ${item.satuan}`, 'warning');
            return;
        }

        item.qty = newQty;
        renderCart();
    }

    function hapusDariKeranjang(obatId) {
        CART = CART.filter(i => i.id !== obatId);
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartContainer');
        if (CART.length === 0) {
            container.innerHTML = `
                <div class="h-full flex flex-col items-center justify-center text-gray-400 py-12">
                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-xs font-semibold text-gray-600">Keranjang kasir masih kosong</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Pilih produk obat di katalog atau scan barcode</p>
                </div>
            `;
            hitungTotalBayar();
            return;
        }

        container.innerHTML = CART.map(i => {
            const sub = i.qty * i.harga;
            return `
                <div class="p-3 bg-slate-50/80 rounded-xl border border-gray-100 flex items-center justify-between gap-3 text-xs">
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-gray-900 truncate">${i.nama}</div>
                        <div class="text-[11px] text-gray-400 font-mono">
                            Rp ${i.harga.toLocaleString('id-ID')} / ${i.satuan}
                            ${i.butuh_resep ? '<span class="text-rose-500 font-bold ml-1">● Resep</span>' : ''}
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="ubahQty(${i.id}, -1)" class="w-6 h-6 rounded-md bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-bold flex items-center justify-center">-</button>
                        <span class="w-8 text-center font-mono font-bold text-gray-900">${i.qty}</span>
                        <button type="button" onclick="ubahQty(${i.id}, 1)" class="w-6 h-6 rounded-md bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 font-bold flex items-center justify-center">+</button>
                    </div>
                    <div class="text-right min-w-[70px]">
                        <div class="font-mono font-bold text-gray-900">Rp ${sub.toLocaleString('id-ID')}</div>
                    </div>
                    <button type="button" onclick="hapusDariKeranjang(${i.id})" class="text-gray-400 hover:text-rose-600 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            `;
        }).join('');

        hitungTotalBayar();
    }

    function hitungTotalBayar() {
        const subtotal = CART.reduce((acc, it) => acc + (it.qty * it.harga), 0);
        const diskonPersen = parseFloat(document.getElementById('diskonPersen').value) || 0;
        const diskonNominal = (subtotal * diskonPersen) / 100;
        const totalAkhir = Math.max(0, subtotal - diskonNominal);

        document.getElementById('cartSubtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('cartDiskonNominal').innerText = '- Rp ' + diskonNominal.toLocaleString('id-ID');
        document.getElementById('cartTotalAkhir').innerText = 'Rp ' + totalAkhir.toLocaleString('id-ID');

        hitungKembalian();
    }

    function hitungKembalian() {
        const totalAkhirStr = document.getElementById('cartTotalAkhir').innerText.replace(/[^0-9]/g, '');
        const totalAkhir = parseFloat(totalAkhirStr) || 0;
        const uangBayar = parseFloat(document.getElementById('inputJumlahBayar').value) || 0;
        const kembalian = Math.max(0, uangBayar - totalAkhir);

        document.getElementById('labelKembalian').innerText = 'Rp ' + kembalian.toLocaleString('id-ID');
    }

    function isiUangPas() {
        const totalAkhirStr = document.getElementById('cartTotalAkhir').innerText.replace(/[^0-9]/g, '');
        document.getElementById('inputJumlahBayar').value = totalAkhirStr;
        hitungKembalian();
    }

    function tambahUang(nominal) {
        const cur = parseFloat(document.getElementById('inputJumlahBayar').value) || 0;
        document.getElementById('inputJumlahBayar').value = cur + nominal;
        hitungKembalian();
    }

    async function prosesTransaksi() {
        if (CART.length === 0) {
            Simapo.toast('Keranjang masih kosong.', 'warning');
            return;
        }

        const totalAkhirStr = document.getElementById('cartTotalAkhir').innerText.replace(/[^0-9]/g, '');
        const totalAkhir = parseFloat(totalAkhirStr) || 0;
        let uangBayar = parseFloat(document.getElementById('inputJumlahBayar').value) || 0;

        if (currentMetode !== 'tunai') {
            uangBayar = totalAkhir;
        } else if (uangBayar < totalAkhir) {
            Simapo.toast(`Uang tunai kurang (Total: Rp ${totalAkhir.toLocaleString('id-ID')}).`, 'warning');
            return;
        }

        const payload = {
            jenis_transaksi: currentJenis,
            pelanggan_id: document.getElementById('pelangganId').value || null,
            metode_pembayaran: currentMetode,
            diskon_persen: parseFloat(document.getElementById('diskonPersen').value) || 0,
            diskon_nominal: 0,
            jumlah_bayar: uangBayar,
            items: CART.map(i => ({ obat_id: i.id, jumlah: i.qty })),
            no_resep: document.getElementById('resepNo').value || null,
            dokter_id: document.getElementById('resepDokterId').value || null,
            nama_dokter: document.getElementById('resepNamaDokterManual').value || null,
            nama_pasien: document.getElementById('resepNamaPasien').value || null,
            tanggal_resep: '{{ date("Y-m-d") }}'
        };

        const btn = document.getElementById('btnCheckout');
        btn.disabled = true;
        btn.innerHTML = 'Memproses Transaksi...';

        try {
            const resp = await Simapo.fetch('{{ route("pos.store") }}', {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (resp.success) {
                // Tampilkan Modal Sukses
                document.getElementById('suksesNoTrx').innerText = resp.data.no_transaksi;
                document.getElementById('suksesTotal').innerText = 'Rp ' + resp.data.total_akhir.toLocaleString('id-ID');
                document.getElementById('suksesKembalian').innerText = 'Rp ' + resp.data.kembalian.toLocaleString('id-ID');
                document.getElementById('btnCetakStruk').href = resp.data.cetak_url;
                document.getElementById('modalSuksesTrx').classList.remove('hidden');

                // Buka otomatis cetak struk di popup tab baru
                window.open(resp.data.cetak_url, '_blank', 'width=450,height=650');

                // Reset state kasir
                CART = [];
                renderCart();
                document.getElementById('inputJumlahBayar').value = 0;
                document.getElementById('diskonPersen').value = 0;
                document.getElementById('resepNamaPasien').value = '';
                document.getElementById('resepNo').value = '';
                setJenisTransaksi('bebas');
            } else {
                Simapo.toast(resp.message || 'Transaksi gagal diproses.', 'danger');
            }
        } catch (e) {
            Simapo.toast('Terjadi kesalahan koneksi server.', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>PROSES TRANSAKSI (F8)</span>
            `;
        }
    }

    function tutupModalSukses() {
        document.getElementById('modalSuksesTrx').classList.add('hidden');
        document.getElementById('posSearch').focus();
    }

    function bukaModalBukaShift() {
        document.getElementById('modalBukaShift').classList.remove('hidden');
    }
    function tutupModalBukaShift() {
        document.getElementById('modalBukaShift').classList.add('hidden');
    }

    function bukaModalTutupShift() {
        const el = document.getElementById('modalTutupShift');
        if (el) el.classList.remove('hidden');
    }
    function tutupModalTutupShift() {
        const el = document.getElementById('modalTutupShift');
        if (el) el.classList.add('hidden');
    }

    // Live search debounced
    let searchTimeout;
    document.getElementById('posSearch').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const q = e.target.value.toLowerCase().trim();
        searchTimeout = setTimeout(() => {
            if (!q) {
                renderObatGrid(MEDICINES);
            } else {
                const filtered = MEDICINES.filter(m => 
                    m.nama.toLowerCase().includes(q) ||
                    m.kode.toLowerCase().includes(q) ||
                    (m.nama_generik && m.nama_generik.toLowerCase().includes(q)) ||
                    (m.barcode && m.barcode.toLowerCase().includes(q))
                );
                renderObatGrid(filtered);
            }
        }, 150);
    });

    // Keyboard Shortcuts (F2: Search, F8: Checkout, Esc: Close modal)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2') {
            e.preventDefault();
            document.getElementById('posSearch').focus();
            document.getElementById('posSearch').select();
        } else if (e.key === 'F8') {
            e.preventDefault();
            prosesTransaksi();
        } else if (e.key === 'Escape') {
            tutupModalSukses();
            tutupModalBukaShift();
            tutupModalTutupShift();
        }
    });

    // Render katalog awal
    document.addEventListener('DOMContentLoaded', () => {
        renderObatGrid(MEDICINES);
        renderCart();
    });
</script>
@endsection
