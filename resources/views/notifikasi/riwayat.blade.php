@extends('layouts.app')

@section('title', 'Riwayat Log Notifikasi Telegram')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Riwayat Log Notifikasi Telegram</h1>
        <p class="text-xs text-gray-500 mt-1">Audit trail seluruh pengiriman notifikasi bot Telegram beserta status pengiriman dan pesan error.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pengaturan.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs transition-colors">
            &larr; Pengaturan Notifikasi
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Notifikasi Dicatat</span>
            <div class="text-2xl font-bold font-mono text-gray-900 mt-2">{{ $logs->total() }} Pesan</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Berhasil Terkirim</span>
            <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">{{ $totalTerkirim }} Pesan</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Gagal Kirim</span>
            <div class="text-2xl font-bold font-mono text-rose-600 mt-2">{{ $totalGagal }} Pesan</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('notifikasi.riwayat') }}" class="flex-1 flex gap-2 w-full max-w-xl">
            <select name="status" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 outline-none">
                <option value="">Semua Status</option>
                <option value="terkirim" {{ request('status') === 'terkirim' ? 'selected' : '' }}>Terkirim</option>
                <option value="gagal" {{ request('status') === 'gagal' ? 'selected' : '' }}>Gagal</option>
            </select>

            <select name="jenis" class="px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 outline-none">
                <option value="">Semua Jenis Pemicu</option>
                <option value="stok_menipis" {{ request('jenis') === 'stok_menipis' ? 'selected' : '' }}>Stok Menipis</option>
                <option value="stok_kosong" {{ request('jenis') === 'stok_kosong' ? 'selected' : '' }}>Stok Kosong</option>
                <option value="obat_expired" {{ request('jenis') === 'obat_expired' ? 'selected' : '' }}>Obat Expired</option>
                <option value="obat_hampir_expired" {{ request('jenis') === 'obat_hampir_expired' ? 'selected' : '' }}>Hampir Expired</option>
                <option value="hutang_jatuh_tempo" {{ request('jenis') === 'hutang_jatuh_tempo' ? 'selected' : '' }}>Hutang Suplier</option>
                <option value="ringkasan_harian" {{ request('jenis') === 'ringkasan_harian' ? 'selected' : '' }}>Ringkasan Harian</option>
                <option value="void_retur" {{ request('jenis') === 'void_retur' ? 'selected' : '' }}>Void & Retur</option>
                <option value="penerimaan" {{ request('jenis') === 'penerimaan' ? 'selected' : '' }}>Penerimaan Barang</option>
                <option value="selisih_shift" {{ request('jenis') === 'selisih_shift' ? 'selected' : '' }}>Selisih Kas Shift</option>
            </select>

            <button type="submit" class="py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['status', 'jenis']))
                <a href="{{ route('notifikasi.riwayat') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-semibold">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Log Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Jenis Pemicu</th>
                        <th class="py-3 px-4">Tujuan (Chat ID)</th>
                        <th class="py-3 px-4 w-1/3">Isi Pesan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Keterangan Error</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y H:i:s') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-gray-800">
                                <span class="bg-slate-100 px-2 py-0.5 rounded text-[11px] font-mono">
                                    {{ $log->jenis }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-gray-600">
                                {{ $log->tujuan ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-gray-100 text-[11px] leading-relaxed line-clamp-3">
                                    {!! strip_tags($log->pesan, '<b><strong><i>') !!}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($log->status === 'terkirim')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Terkirim
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Gagal
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-rose-600 text-[11px] italic max-w-xs truncate">
                                {{ $log->response_error ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                @if($log->status === 'gagal')
                                    <form action="{{ route('notifikasi.kirimUlang', $log->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors shadow-2xs">
                                            Kirim Ulang
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <p class="text-sm font-semibold text-gray-600">Belum ada catatan riwayat notifikasi Telegram.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
