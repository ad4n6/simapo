@php
    $user = Auth::user();
    $role = $user->role ?? 'kasir';
@endphp

<aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0 -translate-x-full">
    <!-- Brand / Logo -->
    <div class="h-16 flex items-center gap-3 px-6 bg-slate-950/60 border-b border-slate-800">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white font-bold shadow-md shadow-emerald-900/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        <div class="leading-tight">
            <span class="text-base font-bold text-white tracking-wide">APOTEK BINTAN 88</span>
            <span class="block text-[10px] text-emerald-400 uppercase tracking-wider font-semibold">SIMAPO v1.0</span>
        </div>
    </div>

    <!-- User Profile Bar -->
    <div class="p-4 mx-3 my-3 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-sm">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium tracking-wide {{ 
                $role === 'owner' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 
                ($role === 'apoteker' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 
                ($role === 'admin_gudang' ? 'bg-sky-500/20 text-sky-300 border border-sky-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30')) 
            }}">
                {{ strtoupper(str_replace('_', ' ', $role)) }}
            </span>
        </div>
    </div>

    <!-- Nav Links (Scrollable) -->
    <nav class="flex-1 px-3 space-y-1 overflow-y-auto custom-scrollbar text-xs font-medium">
        
        <!-- DASHBOARD -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Dashboard</span>
        </a>

        @if(in_array($role, ['owner', 'apoteker', 'kasir']))
        <!-- TRANSAKSI POS -->
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Transaksi Kasir</div>
        <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.*') ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Kasir / POS</span>
        </a>
        <a href="{{ route('penjualan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('penjualan.index') || request()->routeIs('penjualan.show') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Riwayat Penjualan</span>
        </a>
        <a href="{{ route('penjualan.retur.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('penjualan.retur.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"/></svg>
            <span>Retur Penjualan</span>
        </a>
        <a href="{{ route('shift.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('shift.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Shift Kasir</span>
        </a>
        @endif

        <!-- MANAJEMEN STOK -->
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Manajemen Stok</div>
        <a href="{{ route('stok.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('stok.index') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span>Daftar Stok & Batch</span>
        </a>
        @if(in_array($role, ['owner', 'apoteker', 'admin_gudang']))
        <a href="{{ route('stok.mutasi') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('stok.mutasi') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>Kartu Stok / Mutasi</span>
        </a>
        <a href="{{ route('stok.opname.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('stok.opname.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span>Stock Opname</span>
        </a>
        @endif
        @if(in_array($role, ['owner', 'apoteker']))
        <a href="{{ route('stok.pemusnahan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('stok.pemusnahan.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <span>Pemusnahan Obat</span>
        </a>
        @endif

        <!-- PENGADAAN BARANG -->
        @if(in_array($role, ['owner', 'apoteker', 'admin_gudang']))
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengadaan Barang</div>
        <a href="{{ route('pengadaan.rekomendasi') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('pengadaan.rekomendasi') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Rekomendasi Pemesanan</span>
        </a>
        <a href="{{ route('po.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('po.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Purchase Order (PO)</span>
        </a>
        <a href="{{ route('pembelian.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('pembelian.index') || request()->routeIs('pembelian.create') || request()->routeIs('pembelian.show') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8"/></svg>
            <span>Penerimaan / Pembelian</span>
        </a>
        @if(in_array($role, ['owner', 'apoteker']))
        <a href="{{ route('pembelian.hutang') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('pembelian.hutang*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Hutang Suplier</span>
        </a>
        @endif
        <a href="{{ route('pembelian.retur.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('pembelian.retur.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            <span>Retur Pembelian</span>
        </a>
        @endif

        <!-- MASTER DATA -->
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Master Data</div>
        <a href="{{ route('obat.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('obat.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Data Obat</span>
        </a>
        @if(in_array($role, ['owner', 'apoteker']))
        <a href="{{ route('kategori.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('kategori.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            <span>Kategori Obat</span>
        </a>
        <a href="{{ route('satuan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('satuan.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            <span>Satuan Obat</span>
        </a>
        @endif
        @if(in_array($role, ['owner', 'apoteker', 'admin_gudang']))
        <a href="{{ route('suplier.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('suplier.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Suplier</span>
        </a>
        @endif
        <a href="{{ route('pelanggan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('pelanggan.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Pelanggan</span>
        </a>
        <a href="{{ route('dokter.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dokter.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Dokter</span>
        </a>

        <!-- LAPORAN -->
        @if(in_array($role, ['owner', 'apoteker', 'admin_gudang']))
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Laporan</div>
        <a href="{{ route('laporan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('laporan.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Pusat Laporan & Ekspor</span>
        </a>
        @endif

        <!-- PENGATURAN -->
        @if($role === 'owner')
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sistem & Pengaturan</div>
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Manajemen User</span>
        </a>
        <a href="{{ route('pengaturan.notifikasi') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('pengaturan.notifikasi*') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span>Notifikasi Telegram</span>
        </a>
        <a href="{{ route('pengaturan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('pengaturan.index') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Pengaturan Apotek</span>
        </a>
        <a href="{{ route('laporan.aktivitas') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('laporan.aktivitas') ? 'bg-slate-800 text-emerald-400 font-semibold' : 'hover:bg-slate-800/60 text-slate-300 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Log Aktivitas</span>
        </a>
        @endif

        <div class="h-6"></div>
    </nav>

    <!-- Logout footer -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/40">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-800 hover:bg-rose-900/40 text-slate-300 hover:text-rose-300 transition-colors text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Keluar (Logout)</span>
            </button>
        </form>
    </div>
</aside>

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" class="fixed inset-0 z-20 bg-slate-950/60 backdrop-blur-xs hidden md:hidden" onclick="toggleSidebar()"></div>
