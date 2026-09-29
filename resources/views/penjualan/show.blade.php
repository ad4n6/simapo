@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $penjualan->no_transaksi)

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('penjualan.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold font-mono {{ $penjualan->status === 'void' ? 'line-through text-rose-700' : 'text-gray-900' }}">
                    {{ $penjualan->no_transaksi }}
                </h1>
                @if($penjualan->status === 'void')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        VOID (DIBATALKAN)
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        SELESAI
                    </span>
                @endif
                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">
                    {{ ucfirst($penjualan->jenis_transaksi) }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5">Dilayani oleh <span class="font-semibold text-gray-700">{{ $penjualan->user->nama ?? '-' }}</span> pada {{ \Carbon\Carbon::parse($penjualan->created_at)->translatedFormat('d F Y H:i:s') }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('pos.struk', $penjualan->id) }}" target="_blank"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Struk</span>
        </a>

        @if($penjualan->status !== 'void')
            <a href="{{ route('penjualan.retur.create', ['penjualan_id' => $penjualan->id]) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                ↩️ Retur Barang
            </a>

            @if(in_array(auth()->user()->role, ['owner', 'apoteker']))
                <button onclick="bukaModalVoid()"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span>Batalkan Transaksi (VOID)</span>
                </button>
            @endif
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @if($penjualan->status === 'void')
    <!-- Banner Void Info -->
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-center justify-between text-xs text-rose-800">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⚠️</span>
            <div>
                <span class="font-bold text-rose-900">Transaksi Telah Dibatalkan (VOID)</span>
                <p class="mt-0.5">Dibatalkan oleh <strong>{{ $penjualan->voidUser->nama ?? 'Petugas' }}</strong> pada {{ \Carbon\Carbon::parse($penjualan->void_at)->translatedFormat('d F Y H:i') }}.</p>
                <p class="italic mt-0.5">Alasan: "{{ $penjualan->void_alasan }}"</p>
            </div>
        </div>
        <span class="bg-rose-100 text-rose-900 font-bold px-3 py-1 rounded-xl">Stok Telah Dikembalikan</span>
    </div>
    @endif

    <!-- Cards Info Transaksi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Customer / Pasien -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Pelanggan / Pasien</span>
            <div class="text-xs space-y-1.5">
                @if($penjualan->jenis_transaksi === 'resep' && $penjualan->resep)
                    <div class="font-bold text-sm text-gray-900">👤 Pasien: {{ $penjualan->resep->nama_pasien }}</div>
                    <div class="text-gray-600">Dokter: <span class="font-semibold">{{ $penjualan->resep->nama_dokter }}</span></div>
                    <div class="text-gray-600">No. Resep: <span class="font-mono font-bold">{{ $penjualan->resep->no_resep }}</span></div>
                    <div class="text-gray-500">Tgl Resep: {{ \Carbon\Carbon::parse($penjualan->resep->tanggal_resep)->translatedFormat('d M Y') }}</div>
                @elseif($penjualan->pelanggan)
                    <div class="font-bold text-sm text-gray-900">{{ $penjualan->pelanggan->nama }}</div>
                    <div class="text-gray-600">Telepon: {{ $penjualan->pelanggan->telepon ?? '-' }}</div>
                    <div class="text-gray-500">{{ $penjualan->pelanggan->alamat ?? 'Alamat tidak terdata' }}</div>
                @else
                    <div class="text-gray-700 italic font-semibold">Pelanggan Umum (Tanpa Akun)</div>
                @endif
            </div>
        </div>

        <!-- Info Pembayaran -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Metode Pembayaran</span>
            <div class="text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Metode Bayar:</span>
                    <span class="font-bold uppercase text-gray-900">{{ $penjualan->metode_pembayaran }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jumlah Uang Diterima:</span>
                    <span class="font-mono font-bold text-gray-800">Rp {{ number_format($penjualan->jumlah_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Uang Kembalian:</span>
                    <span class="font-mono font-bold text-emerald-600">Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Ringkasan Nilai -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Ringkasan Total</span>
            <div class="text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal:</span>
                    <span class="font-mono font-bold text-gray-800">Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($penjualan->diskon_nominal > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Diskon ({{ (float)$penjualan->diskon_persen }}%):</span>
                    <span class="font-mono font-bold">- Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="text-gray-900 font-bold">Total Belanja:</span>
                    <span class="font-mono font-bold text-base text-emerald-700">Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Detail Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Rincian Obat & Batch FEFO yang Terjual</h2>
            <span class="text-xs text-gray-500 font-semibold">{{ count($penjualan->details) }} Item Batch</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama Obat</th>
                        <th class="py-3 px-4">Nomor Batch (FEFO)</th>
                        <th class="py-3 px-4 text-center">Jumlah</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @foreach($penjualan->details as $idx => $d)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-gray-900">{{ $d->obat->nama }}</span>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $d->obat->kode_obat }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">{{ $d->no_batch ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">
                                {{ $d->jumlah }} {{ $d->obat->satuan->nama ?? 'Pcs' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700">
                                Rp {{ number_format($d->harga_jual, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/50 border-t-2 border-slate-200 font-bold">
                        <td colspan="5" class="py-3.5 px-4 text-right text-gray-700 uppercase tracking-wider text-xs">Total Transaksi:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-emerald-700">Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Riwayat Retur Terkait -->
    @if($penjualan->returs && $penjualan->returs->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-5">
        <h3 class="text-sm font-bold text-gray-900 mb-3">Riwayat Retur Pelanggan dari Transaksi Ini</h3>
        <div class="space-y-2">
            @foreach($penjualan->returs as $r)
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 text-xs">
                    <div>
                        <span class="font-mono font-bold text-rose-700">{{ $r->no_retur }}</span>
                        <span class="text-gray-500 ml-2">Alasan: {{ $r->alasan }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-rose-600">Rp {{ number_format($r->total_pengembalian, 0, ',', '.') }}</span>
                        <a href="{{ route('penjualan.retur.show', $r->id) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold">Lihat Detail Retur &rarr;</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<!-- Modal Konfirmasi Void Transaksi -->
@if(in_array(auth()->user()->role, ['owner', 'apoteker']) && $penjualan->status !== 'void')
<div id="modalVoid" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-rose-600 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Konfirmasi VOID (Batal Transaksi)</span>
            </h3>
            <button onclick="tutupModalVoid()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('penjualan.void', $penjualan->id) }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900 space-y-1">
                <p class="font-bold">Perhatian Penting:</p>
                <p>1. Transaksi {{ $penjualan->no_transaksi }} senilai <strong>Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</strong> akan dibatalkan.</p>
                <p>2. Seluruh stok item obat akan otomatis dikembalikan ke batch asalnya.</p>
                <p>3. Notifikasi real-time akan dikirimkan ke Owner & Apoteker via Telegram.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Pembatalan / VOID <span class="text-rose-500">*</span></label>
                <textarea name="alasan" rows="3" required placeholder="Jelaskan alasan pembatalan transaksi secara rinci (salah input, pelanggan batal bayar, dll)..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-rose-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalVoid()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm">
                    Ya, Batalkan Transaksi Ini
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalVoid() {
        document.getElementById('modalVoid').classList.remove('hidden');
    }
    function tutupModalVoid() {
        document.getElementById('modalVoid').classList.add('hidden');
    }
</script>
@endif
@endsection
