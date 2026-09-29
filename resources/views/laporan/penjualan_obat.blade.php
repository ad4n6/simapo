@extends('layouts.app')

@section('title', 'Laporan Obat Terlaris')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Penjualan Per Produk Obat (Terlaris)</h1>
        <p class="text-xs text-gray-500 mt-1">Peringkat produk terlaris, total kuantitas terjual, perolehan omset, dan margin keuntungan apotek.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Ekspor Excel / CSV</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Unduh PDF</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('laporan.nav')

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('laporan.penjualanObat') }}" class="flex flex-col sm:flex-row gap-3 text-xs">
            <div class="flex-1">
                <label class="block text-gray-500 font-semibold mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex-1">
                <label class="block text-gray-500 font-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="py-2.5 px-6 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition-colors">
                    Filter Periode
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">Rank</th>
                        <th class="py-3 px-4">Kode & Nama Obat</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Jumlah Terjual</th>
                        <th class="py-3 px-4 text-right">Total Omset</th>
                        <th class="py-3 px-4 text-right">Total HPP</th>
                        <th class="py-3 px-4 text-right">Gross Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($items as $idx => $it)
                        @php
                            $margin = $it->total_omset - $it->total_hpp;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold font-mono">
                                @if($idx === 0)
                                    <span class="text-amber-500 text-base">🥇 1</span>
                                @elseif($idx === 1)
                                    <span class="text-slate-400 text-base">🥈 2</span>
                                @elseif($idx === 2)
                                    <span class="text-amber-700 text-base">🥉 3</span>
                                @else
                                    <span class="text-gray-400">{{ $idx + 1 }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900">{{ $it->obat->nama }}</div>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $it->obat->kode_obat }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $it->obat->kategori->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ number_format($it->total_qty) }} {{ $it->obat->satuan->nama ?? 'Pcs' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($it->total_omset, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-gray-500">
                                Rp {{ number_format($it->total_hpp, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                                Rp {{ number_format($margin, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                Belum ada penjualan obat pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
