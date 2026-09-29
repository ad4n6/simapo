@extends('layouts.app')

@section('title', 'Riwayat Transaksi Penjualan')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Riwayat Penjualan & Transaksi Kasir</h1>
        <p class="text-xs text-gray-500 mt-1">Daftar transaksi kasir, nota pembayaran, resep dokter, dan pembatalan (void).</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('penjualan.retur.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors">
            ↩️ Retur Penjualan
        </a>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Buka Kasir POS</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('penjualan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-2.5 text-xs">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. TRX, nama pasien, pelanggan..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            </div>

            <div>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" placeholder="Dari Tanggal"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
            </div>

            <div>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" placeholder="Sampai Tanggal"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
            </div>

            <div>
                <select name="jenis_transaksi" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
                    <option value="">Semua Jenis</option>
                    <option value="bebas" {{ request('jenis_transaksi') === 'bebas' ? 'selected' : '' }}>Obat Bebas</option>
                    <option value="resep" {{ request('jenis_transaksi') === 'resep' ? 'selected' : '' }}>Dengan Resep</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 outline-none">
                    <option value="">Semua Status</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Dibatalkan (VOID)</option>
                </select>
            </div>

            <div class="md:col-span-6 flex items-center justify-end gap-2 pt-1">
                @if(request()->hasAny(['search', 'tanggal_dari', 'tanggal_sampai', 'jenis_transaksi', 'status', 'kasir_id']))
                    <a href="{{ route('penjualan.index') }}" class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-800">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-semibold">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Pelanggan / Pasien</th>
                        <th class="py-3 px-4 text-center">Jenis</th>
                        <th class="py-3 px-4 text-center">Metode</th>
                        <th class="py-3 px-4 text-right">Total Akhir</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($penjualans as $p)
                        @php $isVoid = $p->status === 'void'; @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $isVoid ? 'bg-rose-50/20 opacity-70' : '' }}">
                            <td class="py-3 px-4 font-mono font-bold {{ $isVoid ? 'line-through text-rose-700' : 'text-gray-900' }}">
                                {{ $p->no_transaksi }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ \Carbon\Carbon::parse($p->created_at)->translatedFormat('d M Y H:i') }}
                            </td>
                            <td class="py-3 px-4 text-gray-800">
                                {{ $p->user->nama ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($p->jenis_transaksi === 'resep' && $p->resep)
                                    <div class="font-bold text-gray-900">👤 {{ $p->resep->nama_pasien }}</div>
                                    <div class="text-[10px] text-gray-400">Dr: {{ $p->resep->nama_dokter }}</div>
                                @elseif($p->pelanggan)
                                    <span class="text-gray-800 font-semibold">{{ $p->pelanggan->nama }}</span>
                                @else
                                    <span class="text-gray-400 italic">Pelanggan Umum</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->jenis_transaksi === 'resep' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $p->jenis_transaksi === 'resep' ? 'Resep' : 'Bebas' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ strtoupper($p->metode_pembayaran) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($p->total_akhir, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($isVoid)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        VOID
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('pos.struk', $p->id) }}" target="_blank" title="Cetak Ulang Struk"
                                        class="p-1 text-gray-400 hover:text-emerald-700 rounded-lg hover:bg-emerald-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <a href="{{ route('penjualan.show', $p->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-50 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 rounded-lg font-semibold text-xs transition-colors border border-gray-200">
                                        <span>Detail</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm font-semibold text-gray-700">Tidak ada riwayat transaksi penjualan.</p>
                                <p class="text-xs text-gray-400 mt-1">Coba sesuaikan filter pencarian atau tanggal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($penjualans->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $penjualans->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
