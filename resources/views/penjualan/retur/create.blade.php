@extends('layouts.app')

@section('title', 'Input Retur Penjualan')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('penjualan.retur.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900">Input Retur Penjualan Pelanggan</h1>
        <p class="text-xs text-gray-500 mt-0.5">Pengembalian obat dari konsumen. Stok dapat dikembalikan ke batch jika kondisi masih baik dan tersegel.</p>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @if(!$selectedPenjualan)
    <!-- Step 1: Pilih Transaksi yang akan Diretur -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Langkah 1: Pilih Transaksi Penjualan yang Akan Diretur</h2>
        
        <form method="GET" action="{{ route('penjualan.retur.create') }}" class="flex flex-col sm:flex-row gap-3">
            <select name="penjualan_id" required class="flex-1 px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-rose-500">
                <option value="">-- Pilih Transaksi Penjualan 30 Hari Terakhir --</option>
                @foreach($recentPenjualans as $trx)
                    <option value="{{ $trx->id }}">
                        {{ $trx->no_transaksi }} - {{ \Carbon\Carbon::parse($trx->created_at)->format('d/m/Y H:i') }} - Total: Rp {{ number_format($trx->total_akhir, 0, ',', '.') }} ({{ $trx->pelanggan?->nama ?? 'Umum' }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                Muat Item Transaksi
            </button>
        </form>
    </div>
    @else

    <!-- Step 2: Form Retur Item Transaksi Terpilih -->
    <form action="{{ route('penjualan.retur.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="penjualan_id" value="{{ $selectedPenjualan->id }}">

        <!-- Info Transaksi Terpilih -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Transaksi Terpilih:</span>
                    <span class="font-mono font-bold text-emerald-700 text-sm">{{ $selectedPenjualan->no_transaksi }}</span>
                    <span class="text-xs text-gray-500">({{ \Carbon\Carbon::parse($selectedPenjualan->created_at)->translatedFormat('d F Y H:i') }})</span>
                </div>
                <a href="{{ route('penjualan.retur.create') }}" class="text-xs text-rose-600 hover:underline font-semibold">Ganti Transaksi</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">No. Dokumen Retur <span class="text-rose-500">*</span></label>
                    <input type="text" name="no_retur" value="{{ old('no_retur', $noRetur) }}" required
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-rose-500 outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Pengembalian / Retur <span class="text-rose-500">*</span></label>
                    <input type="text" name="alasan" value="{{ old('alasan') }}" required placeholder="Contoh: Pasien salah beli dosis, kemasan belum dibuka, reaksi alergi..."
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:ring-2 focus:ring-rose-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Tabel Item Penjualan yang Akan Diretur -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Pilih Item Obat yang Diretur</h2>
                <p class="text-xs text-gray-500 mt-0.5">Centang obat yang dikembalikan oleh pelanggan, tentukan jumlah retur, kondisi fisik, dan apakah obat dimasukkan kembali ke stok rak.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4 w-12 text-center">Pilih</th>
                            <th class="py-3 px-4">Nama Obat</th>
                            <th class="py-3 px-4">Batch</th>
                            <th class="py-3 px-4 text-center">Dibeli</th>
                            <th class="py-3 px-4 w-28 text-center">Jumlah Retur</th>
                            <th class="py-3 px-4 w-32">Kondisi Obat</th>
                            <th class="py-3 px-4 text-center">Kembalikan ke Stok?</th>
                            <th class="py-3 px-4 text-right">Harga Jual</th>
                            <th class="py-3 px-4 text-right">Subtotal Refund</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @foreach($selectedPenjualan->details as $idx => $d)
                            <tr class="hover:bg-slate-50/60 transition-colors" id="row-retur-{{ $idx }}">
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox" id="check-{{ $idx }}" onchange="togglePilihItem({{ $idx }})"
                                        class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500">
                                    <input type="hidden" name="items[{{ $idx }}][obat_id]" value="{{ $d->obat_id }}" disabled id="obat-id-{{ $idx }}">
                                    <input type="hidden" name="items[{{ $idx }}][batch_obat_id]" value="{{ $d->batch_obat_id }}" disabled id="batch-id-{{ $idx }}">
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-gray-900">{{ $d->obat->nama }}</span>
                                    <div class="text-[11px] text-gray-400 font-mono">{{ $d->obat->kode_obat }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded">{{ $d->no_batch ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-gray-700">
                                    {{ $d->jumlah }} {{ $d->obat->satuan->nama ?? 'Pcs' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <input type="number" name="items[{{ $idx }}][jumlah]" id="qty-{{ $idx }}" value="{{ $d->jumlah }}" min="1" max="{{ $d->jumlah }}" disabled
                                        oninput="hitungRefund()"
                                        class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded-lg text-xs text-center font-bold text-gray-900 outline-none">
                                </td>
                                <td class="py-3.5 px-4">
                                    <select name="items[{{ $idx }}][kondisi]" id="kondisi-{{ $idx }}" disabled class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 outline-none">
                                        <option value="baik">Kondisi Baik (Segel Utuh)</option>
                                        <option value="rusak">Kondisi Rusak / Cacat</option>
                                    </select>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <input type="hidden" name="items[{{ $idx }}][kembalikan_ke_stok]" value="0" disabled id="kembalikan-hidden-{{ $idx }}">
                                    <input type="checkbox" name="items[{{ $idx }}][kembalikan_ke_stok]" value="1" checked disabled id="kembalikan-{{ $idx }}"
                                        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-gray-700" id="harga-{{ $idx }}" data-harga="{{ $d->harga_jual }}">
                                    Rp {{ number_format($d->harga_jual, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600" id="subtotal-{{ $idx }}">
                                    Rp 0
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-rose-50/40 border-t-2 border-rose-100 font-bold">
                            <td colspan="8" class="py-3.5 px-4 text-right text-rose-950 uppercase tracking-wider text-xs">Total Dana yang Harus Dikembalikan ke Pelanggan:</td>
                            <td class="py-3.5 px-4 text-right font-mono text-sm text-rose-700" id="labelTotalRefund">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('penjualan.retur.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Retur Penjualan</span>
            </button>
        </div>
    </form>

    <script>
        function togglePilihItem(idx) {
            const isChecked = document.getElementById(`check-${idx}`).checked;
            document.getElementById(`obat-id-${idx}`).disabled = !isChecked;
            document.getElementById(`batch-id-${idx}`).disabled = !isChecked;
            document.getElementById(`qty-${idx}`).disabled = !isChecked;
            document.getElementById(`kondisi-${idx}`).disabled = !isChecked;
            document.getElementById(`kembalikan-${idx}`).disabled = !isChecked;
            document.getElementById(`kembalikan-hidden-${idx}`).disabled = !isChecked;

            hitungRefund();
        }

        function hitungRefund() {
            let grandTotal = 0;
            const rows = document.querySelectorAll('[id^="row-retur-"]');

            rows.forEach((row) => {
                const idx = row.id.replace('row-retur-', '');
                const isChecked = document.getElementById(`check-${idx}`).checked;
                const harga = parseFloat(document.getElementById(`harga-${idx}`).getAttribute('data-harga')) || 0;
                const qty = parseInt(document.getElementById(`qty-${idx}`).value) || 0;

                if (isChecked) {
                    const subtotal = harga * qty;
                    document.getElementById(`subtotal-${idx}`).innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
                    grandTotal += subtotal;
                } else {
                    document.getElementById(`subtotal-${idx}`).innerText = 'Rp 0';
                }
            });

            document.getElementById('labelTotalRefund').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }
    </script>
    @endif

</div>
@endsection
