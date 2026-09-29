@extends('layouts.app')

@section('title', 'Pemusnahan Obat Kedaluwarsa')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Pemusnahan Obat Kedaluwarsa</h1>
        <p class="text-xs text-gray-500 mt-1">Pencatatan Berita Acara Pemusnahan Obat (BAPO) sesuai standar kefarmasian BBPOM/Dinkes.</p>
    </div>
    @if(in_array(Auth::user()->role, ['owner', 'apoteker']))
    <a href="{{ route('stok.pemusnahan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        <span>Buat Berita Acara Pemusnahan</span>
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Berita Acara</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Obat & Batch</th>
                        <th class="py-3 px-4 text-center">Jumlah Dimusnahkan</th>
                        <th class="py-3 px-4">Metode & Saksi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($pemusnahans as $p)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">{{ $p->no_pemusnahan }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $p->tanggal->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-900 block">{{ $p->obat?->nama ?? '-' }}</span>
                                <span class="text-[10px] text-gray-500 font-mono">Batch: {{ $p->batchObat?->no_batch ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full text-xs">
                                    {{ $p->jumlah }} {{ $p->obat?->satuan?->nama }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">
                                <div class="font-semibold text-gray-800">{{ $p->metode_pemusnahan }}</div>
                                <div class="text-[10px] text-gray-400">Saksi: {{ $p->saksi ?: '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('stok.pemusnahan.show', $p->id) }}" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-colors">
                                        Detail
                                    </a>
                                    <a href="{{ route('stok.pemusnahan.cetak', $p->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>Cetak BA</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                Belum ada catatan pemusnahan obat kedaluwarsa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pemusnahans->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $pemusnahans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
