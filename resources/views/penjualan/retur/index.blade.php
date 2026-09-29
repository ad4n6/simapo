@extends('layouts.app')

@section('title', 'Retur Penjualan Pelanggan')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Retur Penjualan (Pengembalian Obat dari Pelanggan)</h1>
        <p class="text-xs text-gray-500 mt-1">Pencatatan pengembalian obat dari konsumen, pengembalian dana, dan opsi restok fisik.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('penjualan.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs transition-colors">
            Riwayat Penjualan
        </a>
        <a href="{{ route('penjualan.retur.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Input Retur Baru</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Retur Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Retur</th>
                        <th class="py-3 px-4">Ref. Transaksi</th>
                        <th class="py-3 px-4">Tanggal Retur</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Alasan Retur</th>
                        <th class="py-3 px-4 text-center">Jumlah Item</th>
                        <th class="py-3 px-4 text-right">Total Pengembalian</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($returs as $r)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">{{ $r->no_retur }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">
                                <a href="{{ route('penjualan.show', $r->penjualan_id) }}" class="hover:underline">
                                    {{ $r->penjualan->no_transaksi }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y H:i') }}</td>
                            <td class="py-3.5 px-4 text-gray-800">{{ $r->user->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-gray-700 italic max-w-xs truncate">{{ $r->alasan }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ count($r->details) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600">Rp {{ number_format($r->total_pengembalian, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('penjualan.retur.show', $r->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-50 hover:bg-rose-50 text-gray-700 hover:text-rose-700 rounded-lg font-semibold text-xs transition-colors border border-gray-200">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-semibold text-gray-600">Belum ada transaksi retur penjualan.</p>
                                <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Input Retur Baru" jika ada konsumen yang mengembalikan obat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returs->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $returs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
