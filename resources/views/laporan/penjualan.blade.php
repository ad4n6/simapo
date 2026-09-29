@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Penjualan Apotek</h1>
        <p class="text-xs text-gray-500 mt-1">Rekapitulasi transaksi penjualan kasir, omset kotor, diskon, dan metode pembayaran.</p>
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
        <form method="GET" action="{{ route('laporan.penjualan') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Petugas Kasir</label>
                <select name="kasir_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kasir</option>
                    @foreach($kasirs as $k)
                        <option value="{{ $k->id }}" {{ request('kasir_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Metode Bayar</label>
                <select name="metode_pembayaran" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Metode</option>
                    <option value="tunai" {{ request('metode_pembayaran') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ request('metode_pembayaran') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="qris" {{ request('metode_pembayaran') === 'qris' ? 'selected' : '' }}>QRIS</option>
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
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Omset Bersih</span>
            <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">Rp {{ number_format($totalOmset, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Jumlah Transaksi Selesai</span>
            <div class="text-2xl font-bold font-mono text-gray-900 mt-2">{{ number_format($totalTrx) }} Nota</div>
            <p class="text-[11px] text-gray-400 mt-1">Rata-rata: Rp {{ $totalTrx > 0 ? number_format($totalOmset / $totalTrx, 0, ',', '.') : 0 }} / transaksi</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Total Diskon Diberikan</span>
            <div class="text-2xl font-bold font-mono text-rose-600 mt-2">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Potongan harga promo kasir</p>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-center">Jenis</th>
                        <th class="py-3 px-4 text-center">Metode</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4 text-right">Diskon</th>
                        <th class="py-3 px-4 text-right">Total Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($penjualans as $p)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-gray-900">
                                <a href="{{ route('penjualan.show', $p->id) }}" class="hover:text-emerald-700 hover:underline">
                                    {{ $p->no_transaksi }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-gray-600">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-gray-800">{{ $p->user->nama ?? '-' }}</td>
                            <td class="py-3 px-4 text-gray-700">{{ $p->pelanggan->nama ?? 'Pelanggan Umum' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->jenis_transaksi === 'resep' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ ucfirst($p->jenis_transaksi) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-[10px] text-gray-600">
                                {{ strtoupper($p->metode_pembayaran) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700">Rp {{ number_format($p->subtotal, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono text-rose-600">Rp {{ number_format($p->diskon_nominal, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">Rp {{ number_format($p->total_akhir, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                Tidak ada transaksi pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50/40 border-t-2 border-emerald-100 font-bold">
                        <td colspan="6" class="py-3.5 px-4 text-right text-emerald-950 uppercase tracking-wider text-xs">Total:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-xs text-gray-900">Rp {{ number_format($penjualans->sum('subtotal'), 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-xs text-rose-600">- Rp {{ number_format($totalDiskon, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-emerald-700">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
