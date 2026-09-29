@extends('layouts.app')

@section('title', 'Detail Pemusnahan ' . $pemusnahan->no_pemusnahan)

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold text-gray-900">{{ $pemusnahan->no_pemusnahan }}</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Dimusnahkan</span>
        </div>
        <p class="text-xs text-gray-500 mt-1">Berita Acara Pemusnahan Sediaan Farmasi.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('stok.pemusnahan.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
            &larr; Riwayat
        </a>
        <a href="{{ route('stok.pemusnahan.cetak', $pemusnahan->id) }}" target="_blank" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors flex items-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Berita Acara (BAPO)</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl border border-gray-100 shadow-xs p-8 space-y-6">
        <!-- Letterhead Info -->
        <div class="text-center pb-6 border-b border-gray-100">
            <h2 class="text-base font-black text-gray-900 uppercase tracking-wide">{{ $apotek['nama'] }}</h2>
            <p class="text-xs text-gray-600 mt-1">{{ $apotek['alamat'] }}</p>
            <p class="text-[11px] text-gray-500 font-mono mt-0.5">SIA: {{ $apotek['sia'] }} • SIPA: {{ $apotek['sipa'] }}</p>
            <div class="mt-4 inline-block px-4 py-1.5 rounded-full bg-slate-100 text-slate-800 font-bold text-xs uppercase tracking-wider">
                BERITA ACARA PEMUSNAHAN OBAT
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="space-y-3">
                <div>
                    <span class="text-gray-400 text-[11px] block">Nomor Berita Acara</span>
                    <span class="font-mono font-bold text-gray-900 text-sm">{{ $pemusnahan->no_pemusnahan }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[11px] block">Tanggal Pemusnahan</span>
                    <span class="font-bold text-gray-800">{{ $pemusnahan->tanggal->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[11px] block">Penanggung Jawab Pemusnahan</span>
                    <span class="font-bold text-gray-800">{{ $pemusnahan->user?->name ?? 'Apoteker' }}</span>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <span class="text-gray-400 text-[11px] block">Metode Pemusnahan</span>
                    <span class="font-bold text-gray-800">{{ $pemusnahan->metode_pemusnahan }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[11px] block">Saksi-Saksi Pemusnahan</span>
                    <span class="font-bold text-gray-800">{{ $pemusnahan->saksi }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[11px] block">Alasan Pemusnahan</span>
                    <span class="font-medium text-gray-700">{{ $pemusnahan->alasan }}</span>
                </div>
            </div>
        </div>

        <!-- Medication Item -->
        <div class="border border-gray-100 rounded-2xl overflow-hidden">
            <div class="p-3 bg-slate-50 font-bold text-xs text-gray-700">Rincian Obat yang Dimusnahkan</div>
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/50 text-gray-500 font-semibold border-b border-gray-100">
                    <tr>
                        <th class="p-3">Nama Obat</th>
                        <th class="p-3">No. Batch</th>
                        <th class="p-3">Tanggal Expired</th>
                        <th class="p-3">Suplier Asal</th>
                        <th class="p-3 text-right">Jumlah Dimusnahkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    <tr>
                        <td class="p-3 font-bold text-gray-900">{{ $pemusnahan->obat?->nama }}</td>
                        <td class="p-3 font-mono text-gray-700">{{ $pemusnahan->batchObat?->no_batch }}</td>
                        <td class="p-3 font-mono text-rose-600 font-bold">{{ $pemusnahan->batchObat?->tanggal_kedaluwarsa->format('d/m/Y') }}</td>
                        <td class="p-3 text-gray-600">{{ $pemusnahan->batchObat?->suplier?->nama ?? '-' }}</td>
                        <td class="p-3 text-right font-mono font-black text-rose-700 text-sm">
                            {{ $pemusnahan->jumlah }} {{ $pemusnahan->obat?->satuan?->nama }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($pemusnahan->catatan)
        <div class="p-4 rounded-xl bg-gray-50 text-xs text-gray-600">
            <strong>Catatan Tambahan:</strong> {{ $pemusnahan->catatan }}
        </div>
        @endif
    </div>
</div>
@endsection
