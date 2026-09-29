@extends('layouts.app')

@section('title', 'Sesi Shift Kasir')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Rekap Sesi Shift Kasir</h1>
        <p class="text-xs text-gray-500 mt-1">Pencatatan modal kas awal laci, rekonsiliasi uang fisik saat pergantian kasir, dan deteksi selisih.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Ke POS Kasir</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Active Shift Status Card -->
    @if($activeShift)
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 rounded-full bg-white/10 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-bold backdrop-blur-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-300 animate-pulse"></span>
                    <span>SHIFT ANDA SEDANG AKTIF</span>
                </div>
                <h2 class="text-2xl font-bold">Kasir: {{ $activeShift->user->nama }}</h2>
                <p class="text-xs text-emerald-100">Dibuka pada {{ \Carbon\Carbon::parse($activeShift->waktu_buka)->translatedFormat('d F Y - H:i:s') }} WIB</p>
            </div>

            <!-- Mini KPI In Shift -->
            <div class="grid grid-cols-3 gap-4 bg-white/10 backdrop-blur-xs p-4 rounded-2xl border border-white/20">
                <div>
                    <div class="text-[10px] text-emerald-200 uppercase font-semibold">Modal Awal</div>
                    <div class="text-sm font-mono font-bold mt-0.5">Rp {{ number_format($activeShift->modal_awal, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-[10px] text-emerald-200 uppercase font-semibold">Tunai Masuk</div>
                    <div class="text-sm font-mono font-bold mt-0.5">Rp {{ number_format($activeShift->total_penjualan_tunai, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-[10px] text-emerald-200 uppercase font-semibold">Non-Tunai</div>
                    <div class="text-sm font-mono font-bold mt-0.5">Rp {{ number_format($activeShift->total_penjualan_non_tunai, 0, ',', '.') }}</div>
                </div>
            </div>

            <div>
                <button onclick="bukaModalTutupShift()" class="w-full md:w-auto px-5 py-3 rounded-2xl bg-white text-emerald-900 font-bold text-xs hover:bg-emerald-50 transition-all shadow-md">
                    Tutup Shift & Rekonsiliasi
                </button>
            </div>
        </div>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-3xl">⚠️</span>
            <div>
                <h3 class="text-sm font-bold text-amber-900">Anda Sedang Tidak Berada Dalam Sesi Shift Aktif</h3>
                <p class="text-xs text-amber-700 mt-0.5">Buka sesi shift kasir baru dengan menginput modal awal kas untuk mulai melayani transaksi penjualan.</p>
            </div>
        </div>
        <button onclick="bukaModalBukaShift()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-sm whitespace-nowrap">
            + Buka Shift Kasir Baru
        </button>
    </div>
    @endif

    <!-- Shift History Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Riwayat Seluruh Sesi Shift Kasir</h2>
            <p class="text-xs text-gray-500 mt-0.5">Rekapitulasi setoran kasir dan pemantauan selisih uang fisik laci.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Waktu Buka</th>
                        <th class="py-3 px-4">Waktu Tutup</th>
                        <th class="py-3 px-4 text-right">Modal Awal</th>
                        <th class="py-3 px-4 text-right">Penjualan Tunai</th>
                        <th class="py-3 px-4 text-right">Penjualan Non-Tunai</th>
                        <th class="py-3 px-4 text-right">Uang Fisik Akhir</th>
                        <th class="py-3 px-4 text-center">Selisih Kas</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($shifts as $s)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-gray-900">
                                {{ $s->user->nama }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">
                                {{ \Carbon\Carbon::parse($s->waktu_buka)->translatedFormat('d M Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">
                                {{ $s->waktu_tutup ? \Carbon\Carbon::parse($s->waktu_tutup)->translatedFormat('d M Y H:i') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-gray-700">
                                Rp {{ number_format($s->modal_awal, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                                Rp {{ number_format($s->total_penjualan_tunai, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-700">
                                Rp {{ number_format($s->total_penjualan_non_tunai, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">
                                {{ $s->status === 'tutup' ? 'Rp ' . number_format($s->uang_fisik_akhir, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                @if($s->status === 'tutup')
                                    @if($s->selisih == 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                                            Pas (Rp 0)
                                        </span>
                                    @elseif($s->selisih > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            +Rp {{ number_format($s->selisih, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            -Rp {{ number_format(abs($s->selisih), 0, ',', '.') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($s->status === 'buka')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        Ditutup
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                <p class="text-sm font-semibold text-gray-600">Belum ada riwayat shift kasir.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Buka Shift -->
<div id="modalBukaShift" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-sm w-full p-5 space-y-4">
        <h3 class="text-sm font-bold text-gray-900">Buka Shift Kasir Baru</h3>
        <p class="text-xs text-gray-500">Masukkan modal kas awal untuk uang kembalian di laci kasir.</p>
        <form action="{{ route('kasir.shift.buka') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Modal Kas Awal (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="modal_awal" value="100000" min="0" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Shift</label>
                <input type="text" name="catatan" placeholder="Contoh: Shift Siang, uang receh siap"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalBukaShift()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700">Mulai Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tutup Shift -->
@if($activeShift)
<div id="modalTutupShift" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-sm w-full p-5 space-y-4">
        <h3 class="text-sm font-bold text-gray-900">Tutup Shift Kasir & Rekonsiliasi</h3>
        <div class="bg-slate-50 p-3 rounded-xl text-xs space-y-1 text-gray-600">
            <div class="flex justify-between">
                <span>Modal Awal:</span>
                <span class="font-mono font-bold">Rp {{ number_format($activeShift->modal_awal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Penjualan Tunai:</span>
                <span class="font-mono font-bold text-emerald-600">Rp {{ number_format($activeShift->total_penjualan_tunai, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 pt-1 border-t border-gray-200">
                <span>Ekspektasi Kas Laci:</span>
                <span class="font-mono text-emerald-700">Rp {{ number_format($activeShift->modal_awal + $activeShift->total_penjualan_tunai, 0, ',', '.') }}</span>
            </div>
        </div>

        <form action="{{ route('kasir.shift.tutup', $activeShift->id) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Hitung Fisik Total Uang di Laci (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="uang_fisik_akhir" value="{{ $activeShift->modal_awal + $activeShift->total_penjualan_tunai }}" min="0" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Penutupan</label>
                <input type="text" name="catatan" placeholder="Catatan laci..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalTutupShift()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-900">Rekonsiliasi & Tutup</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function bukaModalBukaShift() {
        document.getElementById('modalBukaShift').classList.remove('hidden');
    }
    function tutupModalBukaShift() {
        document.getElementById('modalBukaShift').classList.add('hidden');
    }

    function bukaModalTutupShift() {
        const el = document.getElementById('modalTutupShift');
        if (el) el.classList.remove('hidden');
    }
    function tutupModalTutupShift() {
        const el = document.getElementById('modalTutupShift');
        if (el) el.classList.add('hidden');
    }
</script>
@endsection
