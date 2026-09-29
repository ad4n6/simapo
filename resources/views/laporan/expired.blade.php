@extends('layouts.app')

@section('title', 'Laporan Obat Kedaluwarsa & Hampir Expired')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Obat Kedaluwarsa & Mendekati Expired</h1>
        <p class="text-xs text-gray-500 mt-1">Pemantauan tanggal kedaluwarsa seluruh batch obat untuk pencegahan kerugian dan regulasi keamanan BPOM.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors flex items-center gap-1.5">
            <span>Ekspor Excel / CSV</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-1.5">
            <span>Unduh PDF</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('laporan.nav')

    <!-- Bagian 1: Sudah Kedaluwarsa -->
    <div class="bg-white rounded-2xl border border-rose-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-rose-100 bg-rose-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-rose-900 flex items-center gap-2">
                    <span>🛑 Batch Obat Sudah Kedaluwarsa (Wajib Dimusnahkan)</span>
                </h2>
                <p class="text-xs text-rose-700 mt-0.5">Batch berikut telah melewati tanggal ED dan sistem telah memblokirnya dari transaksi kasir POS.</p>
            </div>
            <a href="{{ route('pemusnahan.create') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-2xs">
                + Berita Acara Pemusnahan
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Nama Obat</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4">Distributor</th>
                        <th class="py-3 px-4 text-center">Tanggal ED</th>
                        <th class="py-3 px-4 text-center">Sisa Stok</th>
                        <th class="py-3 px-4 text-right">Harga Beli</th>
                        <th class="py-3 px-4 text-right">Potensi Kerugian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($expiredBatches as $b)
                        @php $loss = $b->jumlah_saat_ini * $b->harga_beli; @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-gray-900">{{ $b->obat->nama }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $b->no_batch }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $b->suplier->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-rose-600 font-mono">
                                {{ \Carbon\Carbon::parse($b->tanggal_kedaluwarsa)->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-rose-700">
                                {{ $b->jumlah_saat_ini }} {{ $b->obat->satuan->nama ?? 'Pcs' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-gray-600">Rp {{ number_format($b->harga_beli, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600">Rp {{ number_format($loss, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">
                                Luar biasa! Tidak ada stok obat yang kedaluwarsa di apotek.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bagian 2: Mendekati Kedaluwarsa (<= 90 Hari) -->
    <div class="bg-white rounded-2xl border border-amber-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-amber-100 bg-amber-50/50">
            <h2 class="text-sm font-bold text-amber-900">⏳ Batch Obat Mendekati Kedaluwarsa (Dalam 90 Hari)</h2>
            <p class="text-xs text-amber-700 mt-0.5">Prioritaskan penjualan dengan FEFO, berikan promo potongan harga, atau ajukan retur ke distributor.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Nama Obat</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4 text-center">Tanggal ED</th>
                        <th class="py-3 px-4 text-center">Sisa Hari</th>
                        <th class="py-3 px-4 text-center">Sisa Stok</th>
                        <th class="py-3 px-4 text-right">Nilai Aset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($nearExpiredBatches as $b)
                        @php
                            $daysLeft = (int) \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($b->tanggal_kedaluwarsa));
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-gray-900">{{ $b->obat->nama }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $b->no_batch }}</td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-gray-800">
                                {{ \Carbon\Carbon::parse($b->tanggal_kedaluwarsa)->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-amber-700">
                                {{ $daysLeft }} Hari Lagi
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ $b->jumlah_saat_ini }} {{ $b->obat->satuan->nama ?? 'Pcs' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($b->jumlah_saat_ini * $b->harga_beli, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">
                                Tidak ada stok obat yang mendekati kedaluwarsa dalam 90 hari.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
