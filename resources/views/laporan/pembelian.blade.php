@extends('layouts.app')

@section('title', 'Laporan Pembelian & Hutang Suplier')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Pembelian & Hutang Distributor (PBF)</h1>
        <p class="text-xs text-gray-500 mt-1">Rekapitulasi faktur barang masuk, tagihan hutang dagang, dan status pelunasan.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Ekspor Excel / CSV</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
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
        <form method="GET" action="{{ route('laporan.pembelian') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Distributor PBF</label>
                <select name="suplier_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua PBF</option>
                    @foreach($supliers as $s)
                        <option value="{{ $s->id }}" {{ request('suplier_id') == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Status Pembayaran</label>
                <select name="status_pembayaran" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="belum_lunas" {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas (Hutang)</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition-colors">
                    Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Nilai Faktur Pembelian</span>
            <div class="text-2xl font-bold font-mono text-gray-900 mt-2">Rp {{ number_format($totalNilaiFaktur, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">{{ count($pembelians) }} faktur pada periode ini</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Total Telah Dibayar</span>
            <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Pembayaran tunai & pelunasan hutang</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Sisa Hutang Berjalan</span>
            <div class="text-2xl font-bold font-mono text-rose-600 mt-2">Rp {{ number_format($totalSisaHutang, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Kewajiban bayar distributor</p>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Faktur</th>
                        <th class="py-3 px-4">Tgl Faktur</th>
                        <th class="py-3 px-4">Distributor PBF</th>
                        <th class="py-3 px-4 text-center">Jenis Bayar</th>
                        <th class="py-3 px-4 text-center">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Total Faktur</th>
                        <th class="py-3 px-4 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-4 text-right">Sisa Hutang</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($pembelians as $p)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">
                                <a href="{{ route('pembelian.show', $p->id) }}" class="hover:text-emerald-700 hover:underline">
                                    {{ $p->no_faktur }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ \Carbon\Carbon::parse($p->tanggal_faktur)->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 font-bold text-gray-800">{{ $p->suplier->nama }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $p->jenis_pembayaran === 'tunai' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ ucfirst($p->jenis_pembayaran) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-gray-700">
                                {{ $p->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">Rp {{ number_format($p->total_akhir, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-emerald-600">Rp {{ number_format($p->total_terbayar, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $p->sisa_hutang > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                Rp {{ number_format($p->sisa_hutang, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $p->status_pembayaran === 'lunas' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $p->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                Tidak ada faktur pembelian pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
