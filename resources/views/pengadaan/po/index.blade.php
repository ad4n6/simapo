@extends('layouts.app')

@section('title', 'Purchase Order (PO)')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Purchase Order (PO) Pengadaan</h1>
        <p class="text-xs text-gray-500 mt-1">Daftar pesanan pembelian resmi apotek ke distributor farmasi (PBF).</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pengadaan.rekomendasi') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-colors">
            ⚠️ Cek Rekomendasi
        </a>
        <a href="{{ route('po.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Buat PO Baru</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('po.index') }}" class="flex-1 flex gap-2 w-full max-w-2xl">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. PO, nama suplier..."
                class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            <select name="status" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="dipesan" {{ request('status') === 'dipesan' ? 'selected' : '' }}>Dipesan</option>
                <option value="diterima" {{ request('status') === 'diterima' ? 'selected' : '' }}>Diterima</option>
                <option value="batal" {{ request('status') === 'batal' ? 'selected' : '' }}>Batal</option>
            </select>
            <button type="submit" class="py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">Cari</button>
        </form>
    </div>

    <!-- PO Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Purchase Order</th>
                        <th class="py-3 px-4">Suplier PBF</th>
                        <th class="py-3 px-4">Tanggal Pesan</th>
                        <th class="py-3 px-4 text-center">Jumlah Item</th>
                        <th class="py-3 px-4 text-right">Perkiraan Biaya</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($pos as $p)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">{{ $p->no_po }}</td>
                            <td class="py-3.5 px-4 font-semibold text-gray-800">{{ $p->suplier?->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-gray-600">
                                <div>{{ $p->tanggal_po->format('d/m/Y') }}</div>
                                @if($p->estimasi_tanggal_terima)
                                    <div class="text-[10px] text-gray-400">Est: {{ $p->estimasi_tanggal_terima->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded font-mono">
                                    {{ $p->details->count() }} Obat
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-700 font-mono">
                                Rp {{ number_format($p->total_perkiraan, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ 
                                    $p->status === 'diterima' ? 'bg-emerald-100 text-emerald-800' :
                                    ($p->status === 'dipesan' ? 'bg-sky-100 text-sky-800' :
                                    ($p->status === 'batal' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-800'))
                                }}">
                                    {{ strtoupper($p->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('po.show', $p->id) }}" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-colors">
                                        Detail
                                    </a>
                                    @if($p->status === 'dipesan')
                                    <a href="{{ route('pembelian.create', ['po_id' => $p->id]) }}" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold transition-colors">
                                        + Terima Barang
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                Tidak ada data Purchase Order ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pos->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $pos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
