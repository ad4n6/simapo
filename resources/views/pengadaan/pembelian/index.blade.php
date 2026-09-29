@extends('layouts.app')

@section('title', 'Penerimaan Barang & Faktur Pembelian')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Penerimaan Barang (Faktur Pembelian)</h1>
        <p class="text-xs text-gray-500 mt-1">Pencatatan faktur masuk dari PBF, penambahan batch stok obat baru, dan manajemen hutang dagang.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pembelian.hutang') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors flex items-center gap-1.5">
            <span>💳 Manajemen Hutang PBF</span>
        </a>
        <a href="{{ route('pembelian.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Catat Faktur Masuk</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('pembelian.index') }}" class="flex-1 flex flex-wrap gap-2 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Faktur atau Suplier..."
                class="flex-1 min-w-[200px] px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            
            <select name="status_pembayaran" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
                <option value="">Semua Status Bayar</option>
                <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                <option value="belum_lunas" {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas (Hutang)</option>
            </select>

            <select name="jenis_pembayaran" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
                <option value="">Semua Jenis Bayar</option>
                <option value="tunai" {{ request('jenis_pembayaran') === 'tunai' ? 'selected' : '' }}>Tunai / Cash</option>
                <option value="kredit" {{ request('jenis_pembayaran') === 'kredit' ? 'selected' : '' }}>Kredit (Tempo)</option>
            </select>

            <button type="submit" class="py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status_pembayaran', 'jenis_pembayaran']))
                <a href="{{ route('pembelian.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-semibold">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Faktur Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Faktur</th>
                        <th class="py-3 px-4">Suplier PBF</th>
                        <th class="py-3 px-4">Tanggal Faktur</th>
                        <th class="py-3 px-4 text-center">Jenis Bayar</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Total Akhir</th>
                        <th class="py-3 px-4 text-right">Sisa Hutang</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($pembelians as $p)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">
                                <div>{{ $p->no_faktur }}</div>
                                @if($p->purchaseOrder)
                                    <span class="text-[10px] text-gray-400 font-mono">PO: {{ $p->purchaseOrder->no_po }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-800">{{ $p->suplier->nama }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">
                                {{ \Carbon\Carbon::parse($p->tanggal_faktur)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $p->jenis_pembayaran === 'tunai' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ ucfirst($p->jenis_pembayaran) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->status_pembayaran === 'lunas')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lunas
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Belum Lunas
                                    </span>
                                    @if($p->tanggal_jatuh_tempo && \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->isPast())
                                        <div class="text-[10px] text-rose-600 font-bold mt-0.5">Lewat Tempo!</div>
                                    @elseif($p->tanggal_jatuh_tempo)
                                        <div class="text-[10px] text-gray-400 mt-0.5">Tempo: {{ \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d/m/y') }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($p->total_akhir, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold {{ $p->sisa_hutang > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                Rp {{ number_format($p->sisa_hutang, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('pembelian.show', $p->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-50 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 rounded-lg font-semibold text-xs transition-colors border border-gray-200">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-semibold text-gray-600">Belum ada faktur pembelian yang tercatat.</p>
                                <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Catat Faktur Masuk" untuk menginput faktur dari distributor.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pembelians->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $pembelians->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
