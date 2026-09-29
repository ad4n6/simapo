@extends('layouts.app')

@section('title', 'Ajukan Retur Pembelian ke PBF')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('pembelian.retur.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900">Ajukan Retur Obat ke Distributor (PBF)</h1>
        <p class="text-xs text-gray-500 mt-0.5">Memotong stok batch fisik dan mencatat kartu stok mutasi jenis retur pembelian.</p>
    </div>
</div>
@endsection

@section('content')
<form id="formRetur" action="{{ route('pembelian.retur.store') }}" method="POST" class="space-y-6">
    @csrf

    <!-- Header Dokumen Retur -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Informasi Pengajuan Retur</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">No. Retur Dokumen <span class="text-rose-500">*</span></label>
                <input type="text" name="no_retur" value="{{ old('no_retur', $noRetur) }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-rose-500 focus:bg-white outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Distributor / PBF Penerima <span class="text-rose-500">*</span></label>
                <select name="suplier_id" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">-- Pilih Distributor --</option>
                    @foreach($supliers as $s)
                        <option value="{{ $s->id }}" {{ old('suplier_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Retur <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_retur" value="{{ old('tanggal_retur', date('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-rose-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Retur <span class="text-rose-500">*</span></label>
            <input type="text" name="alasan" value="{{ old('alasan') }}" required placeholder="Contoh: Kemasan obat pecah saat pengiriman, mendekati kedaluwarsa < 3 bulan, salah kirim jenis obat..."
                class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-rose-500">
        </div>
    </div>

    <!-- Items Batch Retur -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-900">Pilih Batch Obat yang Diretur</h2>
                <p class="text-xs text-gray-500 mt-0.5">Hanya batch dengan sisa stok aktif yang dapat dipilih.</p>
            </div>
            <button type="button" onclick="tambahBarisRetur()" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Batch Obat</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="tabelRetur">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-2/5">Pilih Obat & Batch (Sisa Stok / Expired)</th>
                        <th class="py-3 px-4 w-32 text-center">Jumlah Retur</th>
                        <th class="py-3 px-4 w-36 text-right">Harga Beli Satuan</th>
                        <th class="py-3 px-4 w-40 text-right">Subtotal Nilai</th>
                        <th class="py-3 px-4 w-12 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium" id="tbodyRetur">
                    <!-- Populated dynamically -->
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                        <td colspan="3" class="py-3.5 px-4 text-right text-gray-700 uppercase tracking-wider text-xs">Total Nilai Retur:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-rose-600" id="labelTotalRetur">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('pembelian.retur.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Simpan & Potong Stok Batch</span>
        </button>
    </div>
</form>

@php
    $batchesJson = $batches->map(function($b) {
        return [
            'id' => $b->id,
            'obat_nama' => $b->obat->nama ?? 'Unknown',
            'no_batch' => $b->no_batch,
            'sisa_stok' => $b->jumlah_saat_ini,
            'satuan' => $b->obat->satuan->nama ?? 'Pcs',
            'expired' => \Carbon\Carbon::parse($b->tanggal_kedaluwarsa)->format('d/m/Y'),
            'harga_beli' => (float) $b->harga_beli,
        ];
    });
@endphp

<script>
    const BATCHES = @json($batchesJson);
    let rowCount = 0;

    function createBatchOptions(selectedId) {
        return BATCHES.map(b => {
            const isSel = b.id == selectedId ? 'selected' : '';
            return `<option value="${b.id}" data-stok="${b.sisa_stok}" data-harga="${b.harga_beli}" ${isSel}>
                ${b.obat_nama} [Batch: ${b.no_batch}] - Sisa: ${b.sisa_stok} ${b.satuan} (ED: ${b.expired})
            </option>`;
        }).join('');
    }

    function tambahBarisRetur() {
        if (BATCHES.length === 0) {
            Simapo.toast('Tidak ada stok batch aktif di apotek untuk diretur.', 'warning');
            return;
        }

        const tbody = document.getElementById('tbodyRetur');
        const idx = rowCount++;
        const defaultBatch = BATCHES[0];

        const tr = document.createElement('tr');
        tr.id = `retur-row-${idx}`;
        tr.className = 'hover:bg-slate-50/60 transition-colors';
        tr.innerHTML = `
            <td class="py-2.5 px-4">
                <select name="items[${idx}][batch_id]" required onchange="onBatchChange(${idx}, this)"
                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-rose-500">
                    ${createBatchOptions(defaultBatch.id)}
                </select>
            </td>
            <td class="py-2.5 px-4 text-center">
                <input type="number" name="items[${idx}][jumlah]" id="qty-retur-${idx}" value="1" min="1" max="${defaultBatch.sisa_stok}" required
                    oninput="hitungSubtotalRetur(${idx})"
                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-rose-500">
            </td>
            <td class="py-2.5 px-4 text-right font-mono text-gray-700" id="harga-retur-${idx}">
                Rp ${defaultBatch.harga_beli.toLocaleString('id-ID')}
            </td>
            <td class="py-2.5 px-4 text-right font-mono font-bold text-rose-600" id="subtotal-retur-${idx}">
                Rp 0
            </td>
            <td class="py-2.5 px-4 text-center">
                <button type="button" onclick="hapusBarisRetur(${idx})" class="p-1 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        hitungSubtotalRetur(idx);
    }

    function onBatchChange(idx, selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const stok = parseInt(opt.getAttribute('data-stok')) || 1;
        const harga = parseFloat(opt.getAttribute('data-harga')) || 0;

        const qtyInput = document.getElementById(`qty-retur-${idx}`);
        qtyInput.max = stok;
        if (parseInt(qtyInput.value) > stok) {
            qtyInput.value = stok;
        }

        document.getElementById(`harga-retur-${idx}`).innerText = 'Rp ' + harga.toLocaleString('id-ID');
        hitungSubtotalRetur(idx);
    }

    function hitungSubtotalRetur(idx) {
        const tr = document.getElementById(`retur-row-${idx}`);
        if (!tr) return;

        const selectEl = tr.querySelector('select');
        const opt = selectEl.options[selectEl.selectedIndex];
        const harga = parseFloat(opt.getAttribute('data-harga')) || 0;
        const qty = parseInt(document.getElementById(`qty-retur-${idx}`).value) || 0;

        const subtotal = qty * harga;
        document.getElementById(`subtotal-retur-${idx}`).innerText = 'Rp ' + subtotal.toLocaleString('id-ID');

        hitungTotalSemuaRetur();
    }

    function hapusBarisRetur(idx) {
        const tbody = document.getElementById('tbodyRetur');
        if (tbody.children.length <= 1) {
            Simapo.toast('Minimal harus ada 1 batch obat dalam pengajuan retur.', 'warning');
            return;
        }
        document.getElementById(`retur-row-${idx}`).remove();
        hitungTotalSemuaRetur();
    }

    function hitungTotalSemuaRetur() {
        let total = 0;
        const tbody = document.getElementById('tbodyRetur');
        for (let i = 0; i < tbody.children.length; i++) {
            const tr = tbody.children[i];
            const selectEl = tr.querySelector('select');
            const opt = selectEl.options[selectEl.selectedIndex];
            const harga = parseFloat(opt.getAttribute('data-harga')) || 0;
            const idx = tr.id.replace('retur-row-', '');
            const qty = parseInt(document.getElementById(`qty-retur-${idx}`)?.value) || 0;
            total += (qty * harga);
        }

        document.getElementById('labelTotalRetur').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    document.addEventListener('DOMContentLoaded', () => {
        tambahBarisRetur();
    });
</script>
@endsection
