@extends('layouts.app')

@section('title', 'Kartu Stok & Mutasi Obat')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Kartu Stok & Riwayat Mutasi</h1>
        <p class="text-xs text-gray-500 mt-1">Pencatatan saldo berjalan keluar-masuk barang, penyesuaian opname, dan retur obat.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('stok.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs transition-colors">
            &larr; Kembali ke Daftar Stok
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('stok.mutasi') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Pilih Obat (Opsional)</label>
                <select name="obat_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Obat</option>
                    @foreach($obats as $o)
                        <option value="{{ $o->id }}" {{ request('obat_id') == $o->id ? 'selected' : '' }}>
                            {{ $o->nama }} ({{ $o->kode }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Jenis Mutasi</label>
                <select name="jenis" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Jenis</option>
                    <option value="masuk" {{ request('jenis') === 'masuk' ? 'selected' : '' }}>Masuk (Penerimaan)</option>
                    <option value="keluar" {{ request('jenis') === 'keluar' ? 'selected' : '' }}>Keluar (Penjualan)</option>
                    <option value="penyesuaian" {{ request('jenis') === 'penyesuaian' ? 'selected' : '' }}>Penyesuaian (Opname)</option>
                    <option value="retur_penjualan" {{ request('jenis') === 'retur_penjualan' ? 'selected' : '' }}>Retur Penjualan</option>
                    <option value="retur_pembelian" {{ request('jenis') === 'retur_pembelian' ? 'selected' : '' }}>Retur Pembelian</option>
                    <option value="pemusnahan" {{ request('jenis') === 'pemusnahan' ? 'selected' : '' }}>Pemusnahan</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
                <div class="flex gap-2">
                    <input type="date" name="tgl_selesai" value="{{ request('tgl_selesai') }}"
                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <button type="submit" class="py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">Filter</button>
                </div>
            </div>
        </form>
    </div>

    @if($selectedObat)
    <!-- Header Ringkasan Obat yang Dipilih -->
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-bold text-emerald-900">{{ $selectedObat->nama }}</h2>
            <p class="text-xs text-emerald-700">Kode: {{ $selectedObat->kode }} • Lokasi Rak: {{ $selectedObat->lokasi_rak ?: '-' }}</p>
        </div>
        <div class="flex items-center gap-4 text-xs">
            <div>
                <span class="text-gray-500 text-[10px] uppercase font-semibold">Stok Aktif Sekarang:</span>
                <span class="font-black text-emerald-900 text-base ml-1">{{ $selectedObat->total_stok }} {{ $selectedObat->satuan?->nama }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Ledger Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Obat & No. Batch</th>
                        <th class="py-3 px-4 text-center">Jenis Mutasi</th>
                        <th class="py-3 px-4 text-center">Qty Perubahan</th>
                        <th class="py-3 px-4 text-center">Saldo Berjalan</th>
                        <th class="py-3 px-4">Keterangan / Referensi</th>
                        <th class="py-3 px-4 text-right">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($mutasis as $m)
                        @php
                            $isPlus = in_array($m->jenis, ['masuk', 'retur_penjualan']) || ($m->jenis === 'penyesuaian' && $m->jumlah > 0);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-gray-500 font-mono text-[11px]">
                                {{ $m->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-900 block">{{ $m->obat?->nama ?? '-' }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $m->batchObat?->no_batch ?? 'Umum' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ 
                                    $m->jenis === 'masuk' ? 'bg-emerald-100 text-emerald-800' :
                                    ($m->jenis === 'keluar' ? 'bg-rose-100 text-rose-800' :
                                    ($m->jenis === 'penyesuaian' ? 'bg-purple-100 text-purple-800' :
                                    ($m->jenis === 'retur_penjualan' ? 'bg-sky-100 text-sky-800' :
                                    ($m->jenis === 'pemusnahan' ? 'bg-red-950 text-white' : 'bg-gray-100 text-gray-800'))))
                                }}">
                                    {{ strtoupper(str_replace('_', ' ', $m->jenis)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono font-bold {{ $isPlus ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $isPlus ? '+' : '-' }}{{ abs($m->jumlah) }} {{ $m->obat?->satuan?->nama }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="text-gray-400">{{ $m->saldo_sebelum }}</span>
                                <span class="text-gray-300 mx-1">&rarr;</span>
                                <span class="font-bold text-gray-900">{{ $m->saldo_sesudah }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 max-w-xs">
                                <div class="truncate text-xs">{{ $m->keterangan }}</div>
                                @if($m->referensi_tipe)
                                    <span class="text-[10px] text-gray-400 font-mono">Ref: {{ $m->referensi_tipe }} #{{ $m->referensi_id }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right text-gray-600">
                                {{ $m->user?->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                Tidak ada catatan mutasi stok untuk kriteria yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutasis->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $mutasis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
