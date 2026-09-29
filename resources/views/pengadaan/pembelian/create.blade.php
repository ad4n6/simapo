@extends('layouts.app')

@section('title', 'Catat Faktur Pembelian Masuk')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('pembelian.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900">Catat Faktur Pembelian (Penerimaan Barang)</h1>
        <p class="text-xs text-gray-500 mt-0.5">Input nomor batch, expired date, dan harga beli faktur. Stok obat fisik otomatis bertambah.</p>
    </div>
</div>
@endsection

@section('content')
<form id="formPembelian" action="{{ route('pembelian.store') }}" method="POST" class="space-y-6">
    @csrf

    @if($selectedPo)
        <input type="hidden" name="purchase_order_id" value="{{ $selectedPo->id }}">
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-center justify-between text-xs">
            <div class="flex items-center gap-3">
                <span class="text-xl">📄</span>
                <div>
                    <span class="font-bold text-blue-900">Mengimpor dari Purchase Order: {{ $selectedPo->no_po }}</span>
                    <p class="text-blue-700 mt-0.5">Item dan suplier telah diisi otomatis sesuai pesanan PO.</p>
                </div>
            </div>
            <a href="{{ route('po.show', $selectedPo->id) }}" class="font-semibold text-blue-800 hover:underline">Lihat Detail PO &rarr;</a>
        </div>
    @endif

    <!-- Header Faktur -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Informasi Faktur & Suplier</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">No. Faktur PBF <span class="text-rose-500">*</span></label>
                <input type="text" name="no_faktur" value="{{ old('no_faktur', $noFaktur) }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Suplier / PBF <span class="text-rose-500">*</span></label>
                <select name="suplier_id" required class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Suplier --</option>
                    @foreach($supliers as $s)
                        <option value="{{ $s->id }}" {{ (old('suplier_id', $selectedPo?->suplier_id) == $s->id) ? 'selected' : '' }}>
                            {{ $s->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Faktur <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_faktur" value="{{ old('tanggal_faktur', date('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Pembayaran <span class="text-rose-500">*</span></label>
                <select name="jenis_pembayaran" id="jenisPembayaran" required onchange="toggleTempo(this.value)"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="tunai" {{ old('jenis_pembayaran') === 'tunai' ? 'selected' : '' }}>Tunai (Cash / Lunas)</option>
                    <option value="kredit" {{ old('jenis_pembayaran') === 'kredit' ? 'selected' : '' }}>Kredit (Tempo / Hutang)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
            <div id="colTempo" class="{{ old('jenis_pembayaran') === 'kredit' ? '' : 'hidden' }}">
                <label class="block text-xs font-semibold text-rose-700 mb-1">Tanggal Jatuh Tempo <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_jatuh_tempo" id="tanggalJatuhTempo" value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+30 days'))) }}"
                    class="w-full px-3.5 py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-900 outline-none focus:ring-2 focus:ring-rose-500">
            </div>

            <div class="{{ old('jenis_pembayaran') === 'kredit' ? '' : 'sm:col-span-2' }}" id="colCatatan">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                <input type="text" name="catatan" value="{{ old('catatan') }}" placeholder="Contoh: Barang diterima kurir, diskon khusus, dll..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>
    </div>

    <!-- Items Penerimaan Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-900">Rincian Obat & Nomor Batch Masuk</h2>
                <p class="text-xs text-gray-500 mt-0.5">Wajib mencatat no batch & tanggal ED untuk setiap produk obat demi regulasi BPOM & FEFO.</p>
            </div>
            <button type="button" onclick="tambahBaris()" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Baris</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="tabelItems">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-1/4">Obat</th>
                        <th class="py-3 px-4 w-36">No. Batch</th>
                        <th class="py-3 px-4 w-36">Expired Date</th>
                        <th class="py-3 px-4 w-28 text-center">Jumlah</th>
                        <th class="py-3 px-4 w-36 text-right">Harga Beli (Rp)</th>
                        <th class="py-3 px-4 w-40 text-right">Subtotal</th>
                        <th class="py-3 px-4 w-12 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium" id="tbodyItems">
                    <!-- Populated dynamically -->
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                        <td colspan="5" class="py-3.5 px-4 text-right text-gray-700 uppercase tracking-wider text-xs">Total Faktur Pembelian:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-sm text-emerald-700" id="labelTotalAkhir">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Submit Bar -->
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('pembelian.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Simpan Faktur & Tambah Stok</span>
        </button>
    </div>
</form>

@php
    $medicinesJson = $obats->map(function($o) {
        return [
            'id' => $o->id,
            'nama' => $o->nama,
            'kode' => $o->kode_obat,
            'satuan' => $o->satuan->nama ?? 'Pcs',
            'harga_beli' => (float) $o->harga_beli,
        ];
    });

    $initialRows = [];
    if ($selectedPo && $selectedPo->details->count() > 0) {
        foreach ($selectedPo->details as $d) {
            $initialRows[] = [
                'obat_id' => $d->obat_id,
                'no_batch' => 'BATCH-'.strtoupper(bin2hex(random_bytes(3))),
                'tanggal_kedaluwarsa' => date('Y-m-d', strtotime('+18 months')),
                'jumlah' => $d->jumlah_dipesan,
                'harga_beli' => (float) $d->harga_satuan_perkiraan,
            ];
        }
    } else {
        $initialRows[] = [
            'obat_id' => $obats->first()?->id ?? '',
            'no_batch' => 'BATCH-'.strtoupper(bin2hex(random_bytes(3))),
            'tanggal_kedaluwarsa' => date('Y-m-d', strtotime('+18 months')),
            'jumlah' => 10,
            'harga_beli' => (float) ($obats->first()?->harga_beli ?? 0),
        ];
    }
@endphp

<script>
    const MEDICINES = @json($medicinesJson);
    const INITIAL_ROWS = @json($initialRows);
    let rowCount = 0;

    function toggleTempo(val) {
        const colTempo = document.getElementById('colTempo');
        const colCatatan = document.getElementById('colCatatan');
        const inputTempo = document.getElementById('tanggalJatuhTempo');

        if (val === 'kredit') {
            colTempo.classList.remove('hidden');
            colCatatan.classList.remove('sm:col-span-2');
            inputTempo.required = true;
        } else {
            colTempo.classList.add('hidden');
            colCatatan.classList.add('sm:col-span-2');
            inputTempo.required = false;
        }
    }

    function createMedicineOptions(selectedId) {
        return MEDICINES.map(m => {
            const isSel = m.id == selectedId ? 'selected' : '';
            return `<option value="${m.id}" data-harga="${m.harga_beli}" data-satuan="${m.satuan}" ${isSel}>${m.nama} (${m.satuan})</option>`;
        }).join('');
    }

    function tambahBaris(data = null) {
        const tbody = document.getElementById('tbodyItems');
        const idx = rowCount++;

        const obatId = data ? data.obat_id : (MEDICINES[0] ? MEDICINES[0].id : '');
        const batch = data ? data.no_batch : ('BATCH-' + Math.random().toString(36).substring(2, 8).toUpperCase());
        const expired = data ? data.tanggal_kedaluwarsa : '';
        const qty = data ? data.jumlah : 10;
        
        let harga = data ? data.harga_beli : 0;
        if (!harga && obatId) {
            const found = MEDICINES.find(m => m.id == obatId);
            if (found) harga = found.harga_beli;
        }

        const tr = document.createElement('tr');
        tr.id = `row-${idx}`;
        tr.className = 'hover:bg-slate-50/60 transition-colors';
        tr.innerHTML = `
            <td class="py-2.5 px-4">
                <select name="items[${idx}][obat_id]" required onchange="onObatChange(${idx}, this)"
                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    ${createMedicineOptions(obatId)}
                </select>
            </td>
            <td class="py-2.5 px-4">
                <input type="text" name="items[${idx}][no_batch]" value="${batch}" required placeholder="Contoh: B1234"
                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 outline-none focus:ring-2 focus:ring-emerald-500">
            </td>
            <td class="py-2.5 px-4">
                <input type="date" name="items[${idx}][tanggal_kedaluwarsa]" value="${expired}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
            </td>
            <td class="py-2.5 px-4">
                <input type="number" name="items[${idx}][jumlah]" id="qty-${idx}" value="${qty}" min="1" required oninput="hitungSubtotal(${idx})"
                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-center font-bold text-gray-900 outline-none focus:ring-2 focus:ring-emerald-500">
            </td>
            <td class="py-2.5 px-4">
                <input type="number" name="items[${idx}][harga_beli]" id="harga-${idx}" value="${harga}" min="0" step="100" required oninput="hitungSubtotal(${idx})"
                    class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-right font-mono font-bold text-gray-900 outline-none focus:ring-2 focus:ring-emerald-500">
            </td>
            <td class="py-2.5 px-4 text-right font-mono font-bold text-gray-900" id="subtotal-${idx}">
                Rp 0
            </td>
            <td class="py-2.5 px-4 text-center">
                <button type="button" onclick="hapusBaris(${idx})" class="p-1 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        hitungSubtotal(idx);
    }

    function onObatChange(idx, selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const harga = opt.getAttribute('data-harga') || 0;
        document.getElementById(`harga-${idx}`).value = harga;
        hitungSubtotal(idx);
    }

    function hapusBaris(idx) {
        const tbody = document.getElementById('tbodyItems');
        if (tbody.children.length <= 1) {
            Simapo.toast('Minimal harus ada 1 item obat dalam faktur', 'warning');
            return;
        }
        const row = document.getElementById(`row-${idx}`);
        if (row) row.remove();
        hitungTotalSemua();
    }

    function hitungSubtotal(idx) {
        const qtyEl = document.getElementById(`qty-${idx}`);
        const hargaEl = document.getElementById(`harga-${idx}`);
        const subtotalEl = document.getElementById(`subtotal-${idx}`);

        if (!qtyEl || !hargaEl || !subtotalEl) return;

        const qty = parseFloat(qtyEl.value) || 0;
        const harga = parseFloat(hargaEl.value) || 0;
        const subtotal = qty * harga;

        subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        hitungTotalSemua();
    }

    function hitungTotalSemua() {
        let grandTotal = 0;
        const tbody = document.getElementById('tbodyItems');
        for (let i = 0; i < tbody.children.length; i++) {
            const row = tbody.children[i];
            const rowId = row.id.replace('row-', '');
            const qty = parseFloat(document.getElementById(`qty-${rowId}`)?.value) || 0;
            const harga = parseFloat(document.getElementById(`harga-${rowId}`)?.value) || 0;
            grandTotal += (qty * harga);
        }

        document.getElementById('labelTotalAkhir').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    // Initialize with initial rows
    document.addEventListener('DOMContentLoaded', () => {
        if (INITIAL_ROWS.length > 0) {
            INITIAL_ROWS.forEach(r => tambahBaris(r));
        } else {
            tambahBaris();
        }
    });
</script>
@endsection
