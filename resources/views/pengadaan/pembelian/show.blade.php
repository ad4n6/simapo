@extends('layouts.app')

@section('title', 'Detail Faktur Pembelian ' . $pembelian->no_faktur)

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('pembelian.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $pembelian->no_faktur }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $pembelian->status_pembayaran === 'lunas' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                    {{ $pembelian->status_pembayaran === 'lunas' ? 'LUNAS' : 'BELUM LUNAS' }}
                </span>
                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">
                    {{ ucfirst($pembelian->jenis_pembayaran) }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5">Diterima oleh <span class="font-semibold text-gray-700">{{ $pembelian->user->nama ?? 'Petugas' }}</span> pada {{ \Carbon\Carbon::parse($pembelian->created_at)->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="window.print()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Bukti</span>
        </button>

        @if($pembelian->sisa_hutang > 0)
            <button onclick="bukaModalBayar()" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Bayar Hutang Faktur</span>
            </button>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Faktur Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Suplier Details -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Distributor / PBF</span>
            <div>
                <h3 class="text-sm font-bold text-gray-900">{{ $pembelian->suplier->nama }}</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $pembelian->suplier->alamat ?? 'Alamat tidak terdata' }}</p>
                <div class="mt-3 flex items-center gap-4 text-xs text-gray-600">
                    <span>📞 {{ $pembelian->suplier->telepon ?? '-' }}</span>
                    <span>✉️ {{ $pembelian->suplier->email ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Info Faktur -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-2 text-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Detail Dokumen</span>
            <div class="flex justify-between py-1 border-b border-gray-50">
                <span class="text-gray-500">Tanggal Faktur:</span>
                <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($pembelian->tanggal_faktur)->translatedFormat('d F Y') }}</span>
            </div>
            @if($pembelian->purchaseOrder)
                <div class="flex justify-between py-1 border-b border-gray-50">
                    <span class="text-gray-500">Referensi PO:</span>
                    <a href="{{ route('po.show', $pembelian->purchaseOrder->id) }}" class="font-mono font-bold text-emerald-600 hover:underline">
                        {{ $pembelian->purchaseOrder->no_po }}
                    </a>
                </div>
            @endif
            <div class="flex justify-between py-1 border-b border-gray-50">
                <span class="text-gray-500">Jatuh Tempo:</span>
                <span class="font-bold {{ $pembelian->tanggal_jatuh_tempo && \Carbon\Carbon::parse($pembelian->tanggal_jatuh_tempo)->isPast() && $pembelian->sisa_hutang > 0 ? 'text-rose-600' : 'text-gray-800' }}">
                    {{ $pembelian->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($pembelian->tanggal_jatuh_tempo)->translatedFormat('d F Y') : '-' }}
                </span>
            </div>
            <div class="flex justify-between py-1">
                <span class="text-gray-500">Catatan:</span>
                <span class="text-gray-700 italic">{{ $pembelian->catatan ?? '-' }}</span>
            </div>
        </div>

        <!-- Ringkasan Keuangan -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-2">Status Pembayaran</span>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Nilai Faktur:</span>
                    <span class="font-mono font-bold text-gray-900">Rp {{ number_format($pembelian->total_akhir, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Sudah Terbayar:</span>
                    <span class="font-mono font-bold text-emerald-600">Rp {{ number_format($pembelian->total_terbayar, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="text-gray-700 font-bold">Sisa Hutang:</span>
                    <span class="font-mono font-bold text-base {{ $pembelian->sisa_hutang > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                        Rp {{ number_format($pembelian->sisa_hutang, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rincian Batch Obat Masuk -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Item Batch Obat yang Diterima ke Gudang</h2>
            <span class="text-xs text-gray-500 font-semibold">{{ count($pembelian->details) }} Item Obat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama Obat</th>
                        <th class="py-3 px-4">No. Batch</th>
                        <th class="py-3 px-4 text-center">Expired Date</th>
                        <th class="py-3 px-4 text-center">Jumlah Diterima</th>
                        <th class="py-3 px-4 text-right">Harga Beli Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @foreach($pembelian->details as $idx => $d)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-gray-900">{{ $d->obat->nama }}</span>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $d->obat->kode_obat }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">{{ $d->no_batch }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono">
                                {{ \Carbon\Carbon::parse($d->tanggal_kedaluwarsa)->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-emerald-700">
                                +{{ number_format($d->jumlah) }} {{ $d->obat->satuan->nama ?? 'Pcs' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700">
                                Rp {{ number_format($d->harga_beli, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50/40 border-t-2 border-emerald-100 font-bold">
                        <td colspan="6" class="py-3.5 px-4 text-right text-emerald-950 uppercase tracking-wider text-xs">Total Pembelian:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-emerald-700">Rp {{ number_format($pembelian->total_akhir, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Riwayat Pembayaran Hutang -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900">Riwayat Pembayaran Faktur Ini</h3>
            @if($pembelian->sisa_hutang > 0)
                <button onclick="bukaModalBayar()" class="px-3 py-1.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                    + Input Cicilan / Pelunasan
                </button>
            @endif
        </div>

        @if($pembelian->pembayaranHutangs && $pembelian->pembayaranHutangs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase font-semibold">
                            <th class="py-2.5 px-4">No. Pembayaran</th>
                            <th class="py-2.5 px-4">Tanggal Bayar</th>
                            <th class="py-2.5 px-4">Metode Bayar</th>
                            <th class="py-2.5 px-4">Petugas</th>
                            <th class="py-2.5 px-4 text-right">Jumlah Dibayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @foreach($pembelian->pembayaranHutangs as $pay)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3 px-4 font-mono font-bold text-gray-800">{{ $pay->no_pembayaran }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ \Carbon\Carbon::parse($pay->tanggal_bayar)->translatedFormat('d M Y') }}</td>
                                <td class="py-3 px-4"><span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-semibold">{{ ucfirst($pay->metode_bayar) }}</span></td>
                                <td class="py-3 px-4 text-gray-700">{{ $pay->user->nama ?? '-' }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">Rp {{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-gray-400 py-3 italic">
                {{ $pembelian->jenis_pembayaran === 'tunai' ? 'Faktur ini dibayar tunai pada saat penerimaan.' : 'Belum ada pembayaran cicilan yang dicatat.' }}
            </p>
        @endif
    </div>

</div>

<!-- Modal Bayar Hutang -->
<div id="modalBayarHutang" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900">Bayar Hutang Faktur</h3>
            <button onclick="tutupModalBayar()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('pembelian.bayarHutang', $pembelian->id) }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-xs flex justify-between items-center">
                <span class="text-rose-700 font-semibold">Sisa Hutang Faktur:</span>
                <span class="text-rose-900 font-mono font-bold text-sm">Rp {{ number_format($pembelian->sisa_hutang, 0, ',', '.') }}</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Bayar (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="jumlah_bayar" max="{{ $pembelian->sisa_hutang }}" min="1000" value="{{ $pembelian->sisa_hutang }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                <select name="metode_bayar" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="transfer">Transfer Bank</option>
                    <option value="tunai">Tunai / Kas Toko</option>
                    <option value="giro">Giro / Cek</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan</label>
                <input type="text" name="catatan" placeholder="Contoh: Bukti transfer BCA, pelunasan bertahap..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalBayar()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200">
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
    function bukaModalBayar() {
        document.getElementById('modalBayarHutang').classList.remove('hidden');
    }
    function tutupModalBayar() {
        document.getElementById('modalBayarHutang').classList.add('hidden');
    }
</script>
@endsection
