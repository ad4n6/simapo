@extends('layouts.app')

@section('title', 'Detail Retur Penjualan ' . $retur->no_retur)

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('penjualan.retur.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $retur->no_retur }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border bg-rose-50 text-rose-700 border-rose-200">
                    RETUR KONSUMEN
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5">Dicatat oleh <span class="font-semibold text-gray-700">{{ $retur->user->nama ?? '-' }}</span> pada {{ \Carbon\Carbon::parse($retur->created_at)->translatedFormat('d F Y H:i:s') }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="window.print()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Tanda Terima Retur</span>
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Cards Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Referensi Transaksi -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Referensi Penjualan</span>
            <div class="text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-gray-500">No. Transaksi:</span>
                    <a href="{{ route('penjualan.show', $retur->penjualan_id) }}" class="font-mono font-bold text-emerald-600 hover:underline">
                        {{ $retur->penjualan->no_transaksi }}
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Pelanggan:</span>
                    <span class="font-semibold text-gray-800">{{ $retur->penjualan->pelanggan->nama ?? 'Pelanggan Umum' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tgl Beli:</span>
                    <span class="text-gray-700">{{ \Carbon\Carbon::parse($retur->penjualan->created_at)->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Alasan -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-2 text-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Alasan Retur</span>
            <div class="py-1">
                <span class="font-semibold text-rose-700 bg-rose-50 p-2.5 rounded-xl block leading-relaxed border border-rose-100">
                    "{{ $retur->alasan }}"
                </span>
            </div>
        </div>

        <!-- Total Dana Refund -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Total Dana Refund</span>
            <div class="mt-2">
                <div class="text-2xl font-bold font-mono text-rose-600">Rp {{ number_format($retur->total_pengembalian, 0, ',', '.') }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Uang dikembalikan tunai ke pelanggan.</p>
            </div>
        </div>
    </div>

    <!-- Items Detail Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Rincian Obat yang Dikembalikan</h2>
            <span class="text-xs text-gray-500 font-semibold">{{ count($retur->details) }} Item Obat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama Obat</th>
                        <th class="py-3 px-4">Nomor Batch</th>
                        <th class="py-3 px-4 text-center">Jumlah Diretur</th>
                        <th class="py-3 px-4 text-center">Kondisi Obat</th>
                        <th class="py-3 px-4 text-center">Restok Fisik?</th>
                        <th class="py-3 px-4 text-right">Harga Beli Asal</th>
                        <th class="py-3 px-4 text-right">Subtotal Refund</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @foreach($retur->details as $idx => $d)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-gray-900">{{ $d->obat->nama }}</span>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $d->obat->kode_obat }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">{{ $d->batchObat->no_batch ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-rose-600">
                                {{ $d->jumlah }} {{ $d->obat->satuan->nama ?? 'Pcs' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $d->kondisi === 'baik' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ ucfirst($d->kondisi) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($d->kembalikan_ke_stok)
                                    <span class="text-emerald-600 font-bold">✓ Ya (Stok bertambah)</span>
                                @else
                                    <span class="text-gray-400">✗ Tidak (Rusak/Musnah)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700">
                                Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-600">
                                Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-rose-50/40 border-t-2 border-rose-100 font-bold">
                        <td colspan="7" class="py-3.5 px-4 text-right text-rose-950 uppercase tracking-wider text-xs">Total Dana Dikembalikan:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-rose-700">Rp {{ number_format($retur->total_pengembalian, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
