@extends('layouts.app')

@section('title', 'Retur Pembelian (PBF)')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Retur Pembelian Obat ke PBF</h1>
        <p class="text-xs text-gray-500 mt-1">Pengembalian obat rusak, mendekati expired, atau salah kirim ke distributor farmasi.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pembelian.retur.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Ajukan Retur Baru</span>
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
                        <th class="py-3 px-4">Distributor (PBF)</th>
                        <th class="py-3 px-4">Tanggal Pengajuan</th>
                        <th class="py-3 px-4">Alasan Retur</th>
                        <th class="py-3 px-4 text-center">Jumlah Item</th>
                        <th class="py-3 px-4 text-right">Nilai Retur</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($returs as $r)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">{{ $r->no_retur }}</td>
                            <td class="py-3.5 px-4 font-bold text-gray-800">{{ $r->suplier->nama }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ \Carbon\Carbon::parse($r->tanggal_retur)->translatedFormat('d M Y') }}</td>
                            <td class="py-3.5 px-4 text-gray-700 italic max-w-xs truncate">{{ $r->alasan }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ count($r->details) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600">Rp {{ number_format($r->total_nilai, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('pembelian.retur.show', $r->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-50 hover:bg-rose-50 text-gray-700 hover:text-rose-700 rounded-lg font-semibold text-xs transition-colors border border-gray-200">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <p class="text-sm font-semibold text-gray-600">Belum ada pengajuan retur pembelian.</p>
                                <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Ajukan Retur Baru" untuk mengembalikan obat yang rusak / expired ke PBF.</p>
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
