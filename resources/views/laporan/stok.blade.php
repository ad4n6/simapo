@extends('layouts.app')

@section('title', 'Laporan Nilai Persediaan Stok')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Nilai Persediaan Stok Obat</h1>
        <p class="text-xs text-gray-500 mt-1">Valuasi aset persediaan apotek berdasarkan akumulasi batch aktif, harga beli (HPP), dan estimasi potensi omset.</p>
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

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Nilai Beli (Aset HPP)</span>
            <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">Rp {{ number_format($totalNilaiBeli, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Modal tertanam pada persediaan obat</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Estimasi Nilai Jual (Potensi Omset)</span>
            <div class="text-2xl font-bold font-mono text-gray-900 mt-2">Rp {{ number_format($totalNilaiJual, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Jika seluruh stok fisik habis terjual</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Potensi Laba Kotor Persediaan</span>
            <div class="text-2xl font-bold font-mono text-purple-700 mt-2">Rp {{ number_format($totalNilaiJual - $totalNilaiBeli, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Margin: {{ $totalNilaiJual > 0 ? number_format((($totalNilaiJual - $totalNilaiBeli) / $totalNilaiJual) * 100, 1) : 0 }}%</p>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Kode & Nama Obat</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Stok Fisik</th>
                        <th class="py-3 px-4 text-right">Harga Beli</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-right">Total Nilai HPP</th>
                        <th class="py-3 px-4 text-right">Total Nilai Jual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($obats as $o)
                        @php
                            $stok = $o->total_stok;
                            $valBeli = $o->batches->sum(fn($b) => $b->jumlah_saat_ini * $b->harga_beli);
                            $valJual = $stok * $o->harga_jual;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900">{{ $o->nama }}</div>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $o->kode_obat }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $o->kategori->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $stok <= $o->stok_minimum ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ number_format($stok) }} {{ $o->satuan->nama ?? 'Pcs' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-gray-600">Rp {{ number_format($o->harga_beli, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-gray-800">Rp {{ number_format($o->harga_jual, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($valBeli, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">Rp {{ number_format($valJual, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                Tidak ada data persediaan obat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50/40 border-t-2 border-emerald-100 font-bold">
                        <td colspan="5" class="py-3.5 px-4 text-right text-emerald-950 uppercase tracking-wider text-xs">Total Valuasi Persediaan:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-emerald-700">Rp {{ number_format($totalNilaiBeli, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-gray-900">Rp {{ number_format($totalNilaiJual, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
