@extends('layouts.app')

@section('title', 'Detail Purchase Order ' . $po->no_po)

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('po.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $po->no_po }}</h1>
                @php
                    $badgeCls = match($po->status) {
                        'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                        'dipesan' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'diterima_sebagian' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'diterima' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'batal' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-gray-100 text-gray-700 border-gray-200'
                    };
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeCls }}">
                    {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5">Dibuat oleh <span class="font-semibold text-gray-700">{{ $po->user->nama ?? 'Sistem' }}</span> pada {{ \Carbon\Carbon::parse($po->created_at)->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="window.print()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak PO</span>
        </button>

        @if($po->status !== 'diterima' && $po->status !== 'batal')
            <a href="{{ route('pembelian.create', ['po_id' => $po->id]) }}" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Proses Faktur Penerimaan</span>
            </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- PO Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Suplier Info -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Suplier / PBF</span>
                <span class="text-xs font-mono text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md font-semibold">Tujuan Pesanan</span>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">{{ $po->suplier->nama }}</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $po->suplier->alamat ?? 'Alamat tidak tercatat' }}</p>
                <div class="mt-3 flex items-center gap-4 text-xs text-gray-600 font-medium">
                    <span>📞 {{ $po->suplier->telepon ?? '-' }}</span>
                    <span>✉️ {{ $po->suplier->email ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- PO Detail Info -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Tanggal</span>
                <span class="text-xs font-mono text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md font-semibold">Jadwal</span>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-gray-50">
                    <span class="text-gray-500">Tanggal Pemesanan:</span>
                    <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($po->tanggal_po)->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-50">
                    <span class="text-gray-500">Estimasi Tiba:</span>
                    <span class="font-bold text-gray-800">{{ $po->estimasi_tanggal_terima ? \Carbon\Carbon::parse($po->estimasi_tanggal_terima)->translatedFormat('d F Y') : '-' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Catatan Khusus:</span>
                    <span class="text-gray-700 italic">{{ $po->catatan ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Status Management -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status & Tindakan</span>
                <span class="text-xs font-mono text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md font-semibold">Kontrol</span>
            </div>
            <div>
                <form action="{{ route('po.updateStatus', $po->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Ubah Status PO</label>
                        <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="draft" {{ $po->status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="dipesan" {{ $po->status === 'dipesan' ? 'selected' : '' }}>Dipesan (Menunggu Pengiriman)</option>
                            <option value="diterima_sebagian" {{ $po->status === 'diterima_sebagian' ? 'selected' : '' }}>Diterima Sebagian</option>
                            <option value="diterima" {{ $po->status === 'diterima' ? 'selected' : '' }}>Diterima Lengkap</option>
                            <option value="batal" {{ $po->status === 'batal' ? 'selected' : '' }}>Batalkan PO</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                        Simpan Perubahan Status
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Daftar Item Obat yang Dipesan</h2>
            <span class="text-xs text-gray-500 font-semibold">{{ count($po->details) }} Produk Obat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama Obat / Deskripsi</th>
                        <th class="py-3 px-4">Satuan</th>
                        <th class="py-3 px-4 text-center">Jumlah Dipesan</th>
                        <th class="py-3 px-4 text-center">Sudah Diterima</th>
                        <th class="py-3 px-4 text-right">Harga Satuan (Est.)</th>
                        <th class="py-3 px-4 text-right">Subtotal Perkiraan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @php $totalEst = 0; @endphp
                    @foreach($po->details as $idx => $d)
                        @php
                            $subtotal = $d->jumlah_dipesan * $d->harga_satuan_perkiraan;
                            $totalEst += $subtotal;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-gray-900">{{ $d->obat->nama }}</span>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $d->obat->kode_obat }}</div>
                            </td>
                            <td class="py-3 px-4 text-gray-600">{{ $d->obat->satuan->nama ?? 'Pcs' }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ number_format($d->jumlah_dipesan) }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($d->jumlah_diterima >= $d->jumlah_dipesan)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">{{ $d->jumlah_diterima }} (Lengkap)</span>
                                @elseif($d->jumlah_diterima > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">{{ $d->jumlah_diterima }} / {{ $d->jumlah_dipesan }}</span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700">Rp {{ number_format($d->harga_satuan_perkiraan, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50/40 border-t-2 border-emerald-100 font-bold">
                        <td colspan="6" class="py-3.5 px-4 text-right text-emerald-950 uppercase tracking-wider text-xs">Total Estimasi Nilai Pengadaan:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-emerald-700">Rp {{ number_format($totalEst, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Riwayat Faktur Pembelian Terkait -->
    @if($po->pembelians && $po->pembelians->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-5">
        <h3 class="text-sm font-bold text-gray-900 mb-3">Faktur Pembelian / Penerimaan Terkait PO Ini</h3>
        <div class="space-y-2">
            @foreach($po->pembelians as $p)
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-emerald-700">{{ $p->no_faktur }}</span>
                        <span class="text-gray-500">{{ \Carbon\Carbon::parse($p->tanggal_faktur)->translatedFormat('d M Y') }}</span>
                        <span class="px-2 py-0.5 rounded-md font-bold {{ $p->status_pembayaran === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ strtoupper(str_replace('_', ' ', $p->status_pembayaran)) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-gray-800">Rp {{ number_format($p->total_akhir, 0, ',', '.') }}</span>
                        <a href="{{ route('pembelian.show', $p->id) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold">Lihat Faktur &rarr;</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
