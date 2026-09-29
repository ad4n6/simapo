@extends('layouts.app')

@section('title', 'Laporan Obat Resep Dokter')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan Penjualan Obat Resep Dokter</h1>
        <p class="text-xs text-gray-500 mt-1">Pertanggungjawaban penyerahan obat keras, narkotika/psikotropika, dokter penulis resep, dan identitas pasien.</p>
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
        <form method="GET" action="{{ route('laporan.resep') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Dokter Pemeriksa</label>
                <select name="dokter_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Dokter</option>
                    @foreach($dokters as $doc)
                        <option value="{{ $doc->id }}" {{ request('dokter_id') == $doc->id ? 'selected' : '' }}>{{ $doc->nama }}</option>
                    @endforeach
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
                        <th class="py-3 px-4">No. Resep</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Nama Pasien</th>
                        <th class="py-3 px-4">Dokter</th>
                        <th class="py-3 px-4">Rincian Obat & Batch Diserahkan</th>
                        <th class="py-3 px-4 text-right">Nilai Resep</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($reseps as $r)
                        @php
                            $totalResep = 0;
                            foreach($r->penjualans as $p) {
                                $totalResep += $p->total_akhir;
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">{{ $r->no_resep }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 font-bold text-gray-800">{{ $r->nama_pasien }}</td>
                            <td class="py-3.5 px-4 text-gray-700">{{ $r->nama_dokter }}</td>
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @foreach($r->penjualans as $p)
                                        @foreach($p->details as $d)
                                            <div class="text-[11px] flex items-center justify-between text-gray-600 bg-gray-50 px-2 py-0.5 rounded">
                                                <span>{{ $d->obat->nama }} (Batch: {{ $d->no_batch ?? '-' }})</span>
                                                <span class="font-bold text-slate-800">{{ $d->jumlah }} {{ $d->obat->satuan->nama ?? 'Pcs' }}</span>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">
                                Rp {{ number_format($totalResep, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                Tidak ada transaksi obat resep pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
