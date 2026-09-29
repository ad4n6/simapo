@extends('layouts.app')

@section('title', 'Laporan Mutasi Kartu Stok')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Mutasi & Kartu Stok Obat</h1>
        <p class="text-xs text-gray-500 mt-1">Audit log pergerakan stok (masuk, keluar, penyesuaian opname, retur, pemusnahan) dengan saldo berjalan.</p>
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
        <form method="GET" action="{{ route('laporan.mutasiStok') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Filter Obat</label>
                <select name="obat_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Obat</option>
                    @foreach($obats as $o)
                        <option value="{{ $o->id }}" {{ request('obat_id') == $o->id ? 'selected' : '' }}>{{ $o->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Jenis Mutasi</label>
                <select name="jenis" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Jenis</option>
                    <option value="masuk" {{ request('jenis') === 'masuk' ? 'selected' : '' }}>Masuk (Penerimaan)</option>
                    <option value="keluar" {{ request('jenis') === 'keluar' ? 'selected' : '' }}>Keluar (Penjualan)</option>
                    <option value="penyesuaian" {{ request('jenis') === 'penyesuaian' ? 'selected' : '' }}>Penyesuaian (Opname)</option>
                    <option value="pemusnahan" {{ request('jenis') === 'pemusnahan' ? 'selected' : '' }}>Pemusnahan</option>
                    <option value="retur_pembelian" {{ request('jenis') === 'retur_pembelian' ? 'selected' : '' }}>Retur Pembelian</option>
                    <option value="retur_penjualan" {{ request('jenis') === 'retur_penjualan' ? 'selected' : '' }}>Retur Penjualan</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition-colors">
                    Filter Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Nama Obat</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4 text-center">Jenis</th>
                        <th class="py-3 px-4 text-center">Saldo Awal</th>
                        <th class="py-3 px-4 text-center">Mutasi</th>
                        <th class="py-3 px-4 text-center">Saldo Akhir</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($mutasis as $m)
                        @php
                            $isPlus = in_array($m->jenis, ['masuk', 'retur_penjualan']) || ($m->jenis === 'penyesuaian' && $m->saldo_sesudah >= $m->saldo_sebelum);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">{{ $m->obat->nama }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $m->batchObat->no_batch ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $isPlus ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ strtoupper(str_replace('_', ' ', $m->jenis)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-gray-600">{{ number_format($m->saldo_sebelum) }}</td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $isPlus ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $isPlus ? '+' : '-' }}{{ number_format($m->jumlah) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-gray-900">{{ number_format($m->saldo_sesudah) }}</td>
                            <td class="py-3.5 px-4 text-gray-700">{{ $m->user->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-gray-500 italic max-w-xs truncate">{{ $m->keterangan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                Tidak ada mutasi stok pada filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutasis->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $mutasis->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
