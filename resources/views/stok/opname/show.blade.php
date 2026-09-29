@extends('layouts.app')

@section('title', 'Detail Stock Opname ' . $opname->no_opname)

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold text-gray-900">Hasil Stock Opname: {{ $opname->no_opname }}</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Selesai</span>
        </div>
        <p class="text-xs text-gray-500 mt-1">Dilakukan pada {{ $opname->tanggal->format('d/m/Y') }} oleh {{ $opname->user?->name ?? 'Staf' }}.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('stok.opname.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
            &larr; Riwayat Opname
        </a>
        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-800 bg-gray-100 hover:bg-gray-200 transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Hasil</span>
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Summary KPI -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs">
            <span class="text-gray-400 text-[10px] uppercase font-bold">Total Batch Dihitung</span>
            <div class="text-xl font-bold text-gray-900 mt-1">{{ $opname->details->count() }} Batch</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs">
            <span class="text-gray-400 text-[10px] uppercase font-bold">Selisih Fisik Lebih (+)</span>
            <div class="text-xl font-bold text-emerald-600 mt-1">+{{ $opname->total_selisih_plus }} Unit</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs">
            <span class="text-gray-400 text-[10px] uppercase font-bold">Selisih Fisik Kurang (-)</span>
            <div class="text-xl font-bold text-rose-600 mt-1">-{{ $opname->total_selisih_minus }} Unit</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs">
            <span class="text-gray-400 text-[10px] uppercase font-bold">Total Nilai Selisih HPP</span>
            <div class="text-xl font-black mt-1 {{ $opname->total_nilai_selisih >= 0 ? 'text-gray-900' : 'text-rose-600' }}">
                Rp {{ number_format($opname->total_nilai_selisih, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50/70 border-b border-gray-100 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">Rincian Item Obat Stock Opname</span>
            <span class="text-xs text-gray-500">Catatan: {{ $opname->catatan ?: '-' }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nama Obat & Batch</th>
                        <th class="py-3 px-4 text-center">Stok Sistem</th>
                        <th class="py-3 px-4 text-center">Stok Fisik Real</th>
                        <th class="py-3 px-4 text-center">Selisih Unit</th>
                        <th class="py-3 px-4 text-right">Nilai Selisih (Rp)</th>
                        <th class="py-3 px-4">Keterangan / Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @foreach($opname->details as $d)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-900 block">{{ $d->obat?->nama ?? '-' }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">Batch: {{ $d->batchObat?->no_batch ?? '-' }} • Exp: {{ $d->batchObat?->tanggal_kedaluwarsa->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-gray-700">
                                {{ $d->stok_sistem }} {{ $d->obat?->satuan?->nama }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-gray-900">
                                {{ $d->stok_fisik }} {{ $d->obat?->satuan?->nama }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold">
                                @if($d->selisih > 0)
                                    <span class="text-emerald-600">+{{ $d->selisih }}</span>
                                @elseif($d->selisih < 0)
                                    <span class="text-rose-600">{{ $d->selisih }}</span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold {{ $d->nilai_selisih < 0 ? 'text-rose-600' : 'text-gray-800' }}">
                                Rp {{ number_format($d->nilai_selisih, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">
                                {{ $d->alasan }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
