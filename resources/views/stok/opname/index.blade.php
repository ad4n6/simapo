@extends('layouts.app')

@section('title', 'Riwayat Stock Opname')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Stock Opname & Penyesuaian Fisik</h1>
        <p class="text-xs text-gray-500 mt-1">Pencocokan stok sistem dengan stok fisik obat di rak apotek secara berkala.</p>
    </div>
    <a href="{{ route('stok.opname.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Mulai Stock Opname Baru</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Opname</th>
                        <th class="py-3 px-4">Tanggal Pelaksanaan</th>
                        <th class="py-3 px-4 text-center">Selisih Fisik (+ / -)</th>
                        <th class="py-3 px-4 text-right">Nilai Selisih Keuangan</th>
                        <th class="py-3 px-4">Petugas Pelaksana</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($opnames as $op)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-gray-900 font-mono">{{ $op->no_opname }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $op->tanggal->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono text-emerald-600 font-bold">+{{ $op->total_selisih_plus }}</span>
                                <span class="text-gray-300 mx-1">/</span>
                                <span class="font-mono text-rose-600 font-bold">-{{ $op->total_selisih_minus }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold {{ $op->total_nilai_selisih >= 0 ? 'text-gray-900' : 'text-rose-600' }}">
                                Rp {{ number_format($op->total_nilai_selisih, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-700">{{ $op->user?->name ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('stok.opname.show', $op->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-gray-700 text-xs font-semibold transition-colors">
                                    Detail Hasil &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                Belum ada riwayat stock opname yang tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $opnames->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
