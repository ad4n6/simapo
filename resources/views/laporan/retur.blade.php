@extends('layouts.app')

@section('title', 'Laporan Retur')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Retur Penjualan & Pembelian</h1>
        <p class="text-xs text-gray-500 mt-1">Rekapitulasi pengembalian obat dari pelanggan dan retur obat ke distributor farmasi.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-1.5">
            <span>Unduh PDF</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('laporan.nav')

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('laporan.retur') }}" class="flex flex-col sm:flex-row gap-3 text-xs">
            <div class="flex-1">
                <label class="block text-gray-500 font-semibold mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex-1">
                <label class="block text-gray-500 font-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="py-2.5 px-6 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition-colors">
                    Filter Periode
                </button>
            </div>
        </form>
    </div>

    <!-- Bagian 1: Retur Penjualan Pelanggan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">↩️ Retur Penjualan dari Konsumen</h2>
            <span class="text-xs text-rose-600 font-bold font-mono">Total Refund: Rp {{ number_format($returPenjualans->sum('total_pengembalian'), 0, ',', '.') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Retur</th>
                        <th class="py-3 px-4">Ref. Transaksi</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Alasan</th>
                        <th class="py-3 px-4 text-right">Total Refund</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($returPenjualans as $rj)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-4 font-mono font-bold text-gray-900">{{ $rj->no_retur }}</td>
                            <td class="py-3 px-4 font-mono text-emerald-700 font-bold">{{ $rj->penjualan->no_transaksi }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ \Carbon\Carbon::parse($rj->created_at)->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-gray-700 italic">{{ $rj->alasan }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-600">Rp {{ number_format($rj->total_pengembalian, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">Tidak ada retur penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bagian 2: Retur Pembelian ke PBF -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">📦 Retur Pembelian ke Distributor Farmasi (PBF)</h2>
            <span class="text-xs text-rose-600 font-bold font-mono">Total Nilai Retur: Rp {{ number_format($returPembelians->sum('total_nilai'), 0, ',', '.') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Retur</th>
                        <th class="py-3 px-4">Distributor PBF</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Alasan Retur</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Nilai Retur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($returPembelians as $rb)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-4 font-mono font-bold text-gray-900">{{ $rb->no_retur }}</td>
                            <td class="py-3 px-4 font-bold text-gray-800">{{ $rb->suplier->nama }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ \Carbon\Carbon::parse($rb->tanggal_retur)->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-gray-700 italic">{{ $rb->alasan }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700">
                                    {{ ucfirst($rb->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-600">Rp {{ number_format($rb->total_nilai, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">Tidak ada retur pembelian pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
