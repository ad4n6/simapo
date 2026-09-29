@extends('layouts.app')

@section('title', 'Daftar Stok & Batch')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Manajemen Stok & Batch Obat</h1>
        <p class="text-xs text-gray-500 mt-1">Pemantauan persediaan obat, indikator FEFO, peringatan stok menipis dan obat kedaluwarsa.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('stok.mutasi') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs transition-colors">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>Kartu Stok</span>
        </a>
        @if(in_array(Auth::user()->role, ['owner', 'apoteker', 'admin_gudang']))
        <a href="{{ route('stok.opname.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Stock Opname</span>
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('stok.index', ['filter' => 'semua']) }}" class="p-4 rounded-2xl border transition-all {{ $filter === 'semua' ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-white text-gray-800 border-gray-100 hover:border-gray-200 shadow-xs' }}">
            <div class="text-[10px] font-bold uppercase tracking-wider opacity-70">Total Master Obat</div>
            <div class="text-2xl font-black mt-1">{{ $totalObat }}</div>
            <div class="text-[11px] opacity-80 mt-1">Semua jenis sediaan</div>
        </a>

        <a href="{{ route('stok.index', ['filter' => 'menipis']) }}" class="p-4 rounded-2xl border transition-all {{ $filter === 'menipis' ? 'bg-amber-500 text-white border-amber-500 shadow-md' : 'bg-white text-gray-800 border-amber-200 hover:border-amber-300 shadow-xs' }}">
            <div class="text-[10px] font-bold uppercase tracking-wider {{ $filter === 'menipis' ? 'opacity-90' : 'text-amber-700' }}">Stok Menipis</div>
            <div class="text-2xl font-black mt-1 {{ $filter === 'menipis' ? 'text-white' : 'text-amber-600' }}">≤ Min. Stok</div>
            <div class="text-[11px] mt-1 {{ $filter === 'menipis' ? 'opacity-90' : 'text-amber-600' }}">Perlu dibuat PO</div>
        </a>

        <a href="{{ route('stok.index', ['filter' => 'kosong']) }}" class="p-4 rounded-2xl border transition-all {{ $filter === 'kosong' ? 'bg-rose-600 text-white border-rose-600 shadow-md' : 'bg-white text-gray-800 border-rose-200 hover:border-rose-300 shadow-xs' }}">
            <div class="text-[10px] font-bold uppercase tracking-wider {{ $filter === 'kosong' ? 'opacity-90' : 'text-rose-700' }}">Stok Habis / Kosong</div>
            <div class="text-2xl font-black mt-1 {{ $filter === 'kosong' ? 'text-white' : 'text-rose-600' }}">{{ $totalKosong }}</div>
            <div class="text-[11px] mt-1 {{ $filter === 'kosong' ? 'opacity-90' : 'text-rose-600' }}">Stok = 0 strip/botol</div>
        </a>

        <a href="{{ route('stok.index', ['filter' => 'sudah_expired']) }}" class="p-4 rounded-2xl border transition-all {{ $filter === 'sudah_expired' ? 'bg-red-950 text-white border-red-950 shadow-md' : 'bg-white text-gray-800 border-red-200 hover:border-red-300 shadow-xs' }}">
            <div class="text-[10px] font-bold uppercase tracking-wider {{ $filter === 'sudah_expired' ? 'opacity-90' : 'text-rose-900' }}">Sudah Kedaluwarsa</div>
            <div class="text-2xl font-black mt-1 {{ $filter === 'sudah_expired' ? 'text-white' : 'text-rose-950' }}">{{ $totalExpired }}</div>
            <div class="text-[11px] mt-1 {{ $filter === 'sudah_expired' ? 'opacity-90' : 'text-rose-800' }}">Harus dimusnahkan</div>
        </a>
    </div>

    <!-- Quick Filter Tabs -->
    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
        <span class="text-gray-400 text-[11px] uppercase mr-1">Filter Cepat:</span>
        <a href="{{ route('stok.index', ['filter' => 'semua']) }}" class="px-3 py-1.5 rounded-xl transition-colors {{ $filter === 'semua' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            Semua Stok
        </a>
        <a href="{{ route('stok.index', ['filter' => 'menipis']) }}" class="px-3 py-1.5 rounded-xl transition-colors {{ $filter === 'menipis' ? 'bg-amber-500 text-white' : 'bg-white text-amber-800 hover:bg-amber-50 border border-amber-200' }}">
            ⚠️ Stok Menipis
        </a>
        <a href="{{ route('stok.index', ['filter' => 'kosong']) }}" class="px-3 py-1.5 rounded-xl transition-colors {{ $filter === 'kosong' ? 'bg-rose-600 text-white' : 'bg-white text-rose-800 hover:bg-rose-50 border border-rose-200' }}">
            ⛔ Stok Kosong
        </a>
        <a href="{{ route('stok.index', ['filter' => 'hampir_expired_30']) }}" class="px-3 py-1.5 rounded-xl transition-colors {{ $filter === 'hampir_expired_30' ? 'bg-amber-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            ⏰ Expired ≤ 30 Hari
        </a>
        <a href="{{ route('stok.index', ['filter' => 'hampir_expired_60']) }}" class="px-3 py-1.5 rounded-xl transition-colors {{ $filter === 'hampir_expired_60' ? 'bg-amber-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            ⏰ Expired ≤ 60 Hari
        </a>
        <a href="{{ route('stok.index', ['filter' => 'hampir_expired_90']) }}" class="px-3 py-1.5 rounded-xl transition-colors {{ $filter === 'hampir_expired_90' ? 'bg-amber-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            ⏰ Expired ≤ 90 Hari
        </a>
        <a href="{{ route('stok.index', ['filter' => 'sudah_expired']) }}" class="px-3 py-1.5 rounded-xl transition-colors {{ $filter === 'sudah_expired' ? 'bg-red-950 text-white' : 'bg-white text-rose-950 hover:bg-rose-50 border border-rose-300' }}">
            ☠️ Sudah Expired
        </a>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('stok.index') }}" class="flex-1 flex gap-2 w-full max-w-xl">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama obat, kode, lokasi rak..."
                class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            <select name="kategori_id" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $k)
                    <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                @endforeach
            </select>
            <button type="submit" class="py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">Cari</button>
        </form>
        @if(request()->anyFilled(['search', 'kategori_id']))
            <a href="{{ route('stok.index', ['filter' => $filter]) }}" class="text-xs text-gray-500 hover:text-gray-700">Reset Filter</a>
        @endif
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Obat & Kode</th>
                        <th class="py-3 px-4">Kategori & Rak</th>
                        <th class="py-3 px-4 text-center">Status Stok</th>
                        <th class="py-3 px-4 text-center">Batch Aktif (FEFO)</th>
                        <th class="py-3 px-4 text-center">Status Kedaluwarsa</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($obats as $o)
                        @php
                            $stok = $o->total_stok;
                            $statusStok = $o->status_stok;
                            $statusExp = $o->status_kedaluwarsa;
                            $batches = $o->batches;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-900 block text-xs">{{ $o->nama }}</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[10px] text-gray-400 font-semibold">{{ $o->kode }}</span>
                                    @if($o->butuh_resep)
                                        <span class="text-[9px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200">Resep</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-gray-800">{{ $o->kategori?->nama }}</div>
                                <span class="text-[10px] text-gray-400 font-mono">Rak: {{ $o->lokasi_rak ?: '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ 
                                    $statusStok === 'kosong' ? 'bg-rose-100 text-rose-800' :
                                    ($statusStok === 'menipis' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')
                                }}">
                                    <span class="w-2 h-2 rounded-full {{ 
                                        $statusStok === 'kosong' ? 'bg-rose-500' :
                                        ($statusStok === 'menipis' ? 'bg-amber-500' : 'bg-emerald-500')
                                    }}"></span>
                                    <span>{{ $stok }} {{ $o->satuan?->nama }}</span>
                                </span>
                                <div class="text-[9px] text-gray-400 mt-0.5">Min: {{ $o->stok_minimum }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[11px]">
                                    {{ $batches->where('jumlah_saat_ini', '>', 0)->count() }} Batch
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($statusExp === 'kedaluwarsa')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-950 text-white">
                                        ☠️ Ada Kedaluwarsa!
                                    </span>
                                @elseif($statusExp === 'hampir_kedaluwarsa')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        ⏰ Hampir Kedaluwarsa
                                    </span>
                                @elseif($stok > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                        ✓ Aman
                                    </span>
                                @else
                                    <span class="text-gray-400 text-[10px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('stok.mutasi', ['obat_id' => $o->id]) }}" class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-semibold transition-colors" title="Lihat Kartu Stok">
                                        Kartu Stok
                                    </a>
                                    @if($stok <= $o->stok_minimum && in_array(Auth::user()->role, ['owner', 'apoteker', 'admin_gudang']))
                                    <a href="{{ route('pengadaan.rekomendasi') }}" class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-[11px] font-bold transition-colors">
                                        + PO
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                Tidak ada data stok yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($obats->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $obats->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
