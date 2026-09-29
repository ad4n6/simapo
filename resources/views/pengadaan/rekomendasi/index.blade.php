@extends('layouts.app')

@section('title', 'Rekomendasi Pemesanan Obat')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Rekomendasi Pemesanan Ulang (Auto-Reorder)</h1>
        <p class="text-xs text-gray-500 mt-1">Daftar obat dengan persediaan stok ≤ batas minimum yang perlu segera dibuatkan Purchase Order (PO).</p>
    </div>
    @if($lowStockObats->isNotEmpty() && in_array(Auth::user()->role, ['owner', 'apoteker', 'admin_gudang']))
    <a href="{{ route('po.create', ['source' => 'rekomendasi']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Buat PO dari Rekomendasi Ini</span>
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-gray-100 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                ⚠️
            </div>
            <div>
                <span class="text-gray-400 text-[10px] uppercase font-bold">Obat Perlu Dipesan</span>
                <div class="text-2xl font-black text-gray-900 mt-0.5">{{ $lowStockObats->count() }} Jenis Obat</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-gray-100 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                💰
            </div>
            <div>
                <span class="text-gray-400 text-[10px] uppercase font-bold">Total Estimasi Anggaran</span>
                <div class="text-2xl font-black text-emerald-700 mt-0.5">Rp {{ number_format($totalEstimasiBiaya, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-emerald-600 text-white shadow-md flex items-center justify-between">
            <div>
                <span class="text-emerald-100 text-[10px] uppercase font-bold">Tindakan Cepat</span>
                <div class="text-sm font-bold mt-1">Proses Langsung ke PO</div>
                <p class="text-[11px] text-emerald-100 mt-0.5">Semua obat menipis otomatis terinput.</p>
            </div>
            <a href="{{ route('po.create', ['source' => 'rekomendasi']) }}" class="px-3.5 py-2 rounded-xl bg-white text-emerald-800 text-xs font-bold hover:bg-emerald-50 transition-colors shadow-xs">
                Buat PO &rarr;
            </a>
        </div>
    </div>

    <!-- Recommendations Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50/70 border-b border-gray-100 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">Daftar Kebutuhan Stok Obat</span>
            <span class="text-xs text-gray-500">Kalkulasi: <code>(Stok Min × 3) − Sisa Stok</code></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nama Obat & Kode</th>
                        <th class="py-3 px-4">Kategori & Rak</th>
                        <th class="py-3 px-4 text-center">Sisa Stok Real</th>
                        <th class="py-3 px-4 text-center">Batas Min</th>
                        <th class="py-3 px-4 text-center">Saran Pesan</th>
                        <th class="py-3 px-4 text-right">Harga Beli Acuan</th>
                        <th class="py-3 px-4 text-right">Estimasi Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($lowStockObats as $item)
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-900 block text-xs">{{ $item['nama'] }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $item['kode'] }} • {{ $item['nama_generik'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-gray-800">{{ $item['kategori'] }}</div>
                                <span class="text-[10px] text-gray-400 font-mono">Rak: {{ $item['lokasi_rak'] ?: '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $item['stok_sekarang'] == 0 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $item['stok_sekarang'] }} {{ $item['satuan'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-gray-500">
                                {{ $item['stok_minimum'] }} {{ $item['satuan'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-mono font-black text-xs border border-emerald-200">
                                    +{{ $item['rekomendasi_qty'] }} {{ $item['satuan'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-gray-700">
                                Rp {{ number_format($item['harga_beli'], 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($item['estimasi_biaya'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                🎉 Luar biasa! Seluruh persediaan obat saat ini masih berada di atas ambang stok minimum.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
