@extends('layouts.app')

@section('title', 'Laporan Obat Menipis & Kosong')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Obat Menipis & Kosong (Defekta)</h1>
        <p class="text-xs text-gray-500 mt-1">Daftar obat yang telah mencapai batas stok minimum atau habis total untuk dasar pemesanan kembali ke PBF.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pengadaan.rekomendasi') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 transition-colors">
            ⚠️ Buka Rekomendasi PO
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors flex items-center gap-1.5">
            <span>Ekspor Excel / CSV</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-1.5">
            <span>Unduh PDF</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('laporan.nav')

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Kode & Nama Obat</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                        <th class="py-3 px-4 text-center">Stok Minimum</th>
                        <th class="py-3 px-4 text-center">Satuan</th>
                        <th class="py-3 px-4 text-center">Lokasi Rak</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Rekomendasi Pesan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($obats as $o)
                        @php
                            $stok = $o->total_stok;
                            $rekomendasi = max(10, ($o->stok_minimum * 3) - $stok);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900">{{ $o->nama }}</div>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $o->kode_obat }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $o->kategori->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-sm {{ $stok == 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                {{ number_format($stok) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-gray-600">
                                {{ number_format($o->stok_minimum) }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-gray-600">{{ $o->satuan->nama ?? 'Pcs' }}</td>
                            <td class="py-3.5 px-4 text-center font-mono text-gray-700">{{ $o->lokasi_rak ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($stok == 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        KOSONG (0)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        MENIPIS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">
                                +{{ number_format($rekomendasi) }} {{ $o->satuan->nama ?? 'Pcs' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                Seluruh stok obat berada di atas batas minimum.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
