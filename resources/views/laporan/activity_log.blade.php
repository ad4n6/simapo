@extends('layouts.app')

@section('title', 'Log Aktivitas Pengguna')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Log Aktivitas Pengguna (Audit Trail)</h1>
        <p class="text-xs text-gray-500 mt-1">Rekam jejak seluruh tindakan penting user (login, transaksi kasir, perubahan stok, penerimaan faktur, dan void).</p>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('laporan.nav')

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('laporan.activityLog') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Pilih Pengguna</label>
                <select name="user_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }} ({{ ucfirst($u->role) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-500 font-semibold mb-1">Tipe Tindakan</label>
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Contoh: login, penjualan, buat_po, void..."
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition-colors">
                    Filter Aktivitas
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
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Aksi / Event</th>
                        <th class="py-3 px-4 w-2/5">Deskripsi Aktivitas</th>
                        <th class="py-3 px-4 text-center">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($logs as $l)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($l->created_at)->translatedFormat('d M Y H:i:s') }}</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">{{ $l->user->nama ?? 'Sistem' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ ucfirst($l->user->role ?? 'System') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">{{ $l->action }}</td>
                            <td class="py-3.5 px-4 text-gray-700">{{ $l->deskripsi }}</td>
                            <td class="py-3.5 px-4 text-center font-mono text-gray-400">{{ $l->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-gray-400">Belum ada riwayat aktivitas yang tercatat.</td></tr>
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
