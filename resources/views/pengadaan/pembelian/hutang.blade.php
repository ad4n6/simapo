@extends('layouts.app')

@section('title', 'Manajemen Hutang PBF / Suplier')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Manajemen Hutang Dagang (PBF)</h1>
        <p class="text-xs text-gray-500 mt-1">Daftar faktur pembelian kredit yang belum lunas beserta tanggal jatuh tempo untuk pencegahan blacklist distributor.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pembelian.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs transition-colors">
            &larr; Kembali ke Faktur Pembelian
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Hutang Berjalan</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">💳</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-gray-900">Rp {{ number_format($totalHutang, 0, ',', '.') }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Kewajiban bayar seluruh distributor</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Hutang Lewat Tempo (Kritis)</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600">⚠️</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-rose-600">Rp {{ number_format($hutangLewatTempo, 0, ',', '.') }}</div>
                <p class="text-[11px] text-rose-400 mt-1">Harus segera diselesaikan</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Faktur Belum Lunas</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">📄</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-gray-900">{{ $hutangs->total() }} Faktur</div>
                <p class="text-[11px] text-gray-400 mt-1">Menunggu cicilan / pelunasan</p>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('pembelian.hutang') }}" class="flex-1 flex gap-2 w-full max-w-xl">
            <select name="suplier_id" class="flex-1 px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 outline-none">
                <option value="">Semua Distributor PBF</option>
                @foreach($supliers as $s)
                    <option value="{{ $s->id }}" {{ request('suplier_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->nama }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                Filter Suplier
            </button>
            @if(request('suplier_id'))
                <a href="{{ route('pembelian.hutang') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-semibold">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Debt Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Faktur</th>
                        <th class="py-3 px-4">Distributor (PBF)</th>
                        <th class="py-3 px-4">Tgl Faktur</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Total Faktur</th>
                        <th class="py-3 px-4 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-4 text-right">Sisa Hutang</th>
                        <th class="py-3 px-4 text-center">Status Tempo</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($hutangs as $h)
                        @php
                            $isOverdue = $h->tanggal_jatuh_tempo && \Carbon\Carbon::parse($h->tanggal_jatuh_tempo)->isPast();
                            $daysLeft = $h->tanggal_jatuh_tempo ? (int) \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($h->tanggal_jatuh_tempo)->startOfDay(), false) : null;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $isOverdue ? 'bg-rose-50/20' : '' }}">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">{{ $h->no_faktur }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-800">{{ $h->suplier->nama }}</span>
                                <div class="text-[11px] text-gray-400">Telp: {{ $h->suplier->telepon ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ \Carbon\Carbon::parse($h->tanggal_faktur)->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 font-bold {{ $isOverdue ? 'text-rose-600' : 'text-gray-800' }}">
                                {{ $h->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($h->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-gray-700">Rp {{ number_format($h->total_akhir, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-emerald-600">Rp {{ number_format($h->total_terbayar, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600 text-sm">
                                Rp {{ number_format($h->sisa_hutang, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($isOverdue)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Lewat {{ abs($daysLeft) }} Hari
                                    </span>
                                @elseif($daysLeft !== null && $daysLeft <= 7)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ $daysLeft }} Hari Lagi
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                        Aman ({{ $daysLeft }} Hari)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="bukaModalBayarQuick({{ $h->id }}, '{{ $h->no_faktur }}', {{ $h->sisa_hutang }})"
                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-2xs transition-colors">
                                        Bayar
                                    </button>
                                    <a href="{{ route('pembelian.show', $h->id) }}" class="p-1 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-emerald-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm font-semibold text-gray-700">Luar Biasa! Tidak ada hutang distributor yang belum lunas.</p>
                                <p class="text-xs text-gray-400 mt-1">Seluruh tagihan faktur telah diselesaikan tepat waktu.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($hutangs->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $hutangs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Bayar Hutang Quick -->
<div id="modalBayarQuick" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900">Bayar Hutang: <span id="quickFaktur" class="font-mono text-emerald-600"></span></h3>
            <button onclick="tutupModalBayarQuick()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="formBayarQuick" method="POST" class="p-5 space-y-4">
            @csrf
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-xs flex justify-between items-center">
                <span class="text-rose-700 font-semibold">Sisa Tagihan Hutang:</span>
                <span id="quickSisaLabel" class="text-rose-900 font-mono font-bold text-sm"></span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Bayar (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" id="quickJumlahBayar" name="jumlah_bayar" min="1000" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Bayar <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Bayar <span class="text-rose-500">*</span></label>
                <select name="metode_bayar" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="transfer">Transfer Bank</option>
                    <option value="tunai">Tunai / Kas Apotek</option>
                    <option value="giro">Giro / Cek</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan</label>
                <input type="text" name="catatan" placeholder="Catatan bukti transfer / no referensi bank"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalBayarQuick()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm">
                    Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalBayarQuick(id, noFaktur, sisaHutang) {
        document.getElementById('formBayarQuick').action = `/pengadaan/pembelian/${id}/bayar-hutang`;
        document.getElementById('quickFaktur').innerText = noFaktur;
        document.getElementById('quickSisaLabel').innerText = 'Rp ' + sisaHutang.toLocaleString('id-ID');
        const inputBayar = document.getElementById('quickJumlahBayar');
        inputBayar.max = sisaHutang;
        inputBayar.value = sisaHutang;

        document.getElementById('modalBayarQuick').classList.remove('hidden');
    }

    function tutupModalBayarQuick() {
        document.getElementById('modalBayarQuick').classList.add('hidden');
    }
</script>
@endsection
