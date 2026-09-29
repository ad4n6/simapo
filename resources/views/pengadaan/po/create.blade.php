@extends('layouts.app')

@section('title', 'Buat Purchase Order Baru')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Buat Purchase Order (PO) Baru</h1>
        <p class="text-xs text-gray-500 mt-1">Buat pesanan obat resmi ke Pedagang Besar Farmasi (PBF) rekanan.</p>
    </div>
    <a href="{{ route('po.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
        &larr; Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <form id="po-form" onsubmit="handlePoSubmit(event)" class="space-y-6">
        <!-- PO Header -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Purchase Order</label>
                <input type="text" id="no_po" name="no_po" value="{{ $noPo }}" readonly
                    class="w-full px-3.5 py-2 bg-gray-100 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-700">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Suplier PBF Tujuan</label>
                <select id="suplier_id" name="suplier_id" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Distributor PBF --</option>
                    @foreach($supliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pesan</label>
                <input type="date" id="tanggal_po" name="tanggal_po" value="{{ date('Y-m-d') }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Estimasi Tanggal Kirim</label>
                <input type="date" id="estimasi_tanggal_terima" name="estimasi_tanggal_terima" value="{{ date('Y-m-d', strtotime('+3 days')) }}"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan untuk Suplier (Opsional)</label>
                <input type="text" id="catatan" name="catatan" placeholder="Mohon konfirmasi ketersediaan batch terbaru expired minimal 1.5 tahun..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <!-- Add Obat Bar -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
            <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">Tambah Item Obat ke PO</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2">
                    <select id="obat-selector" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Obat --</option>
                        @foreach($obats as $o)
                            <option value="{{ $o->id }}"
                                data-nama="{{ $o->nama }}"
                                data-satuan="{{ $o->satuan?->nama }}"
                                data-harga="{{ $o->harga_beli }}">
                                {{ $o->nama }} ({{ $o->kode }}) • Hrg Beli: Rp {{ number_format($o->harga_beli, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="number" id="add-qty" min="1" value="10" placeholder="Qty"
                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-center outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <button type="button" onclick="addItemFromSelector()" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors">
                        + Tambahkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50/70 border-b border-gray-100 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">Daftar Item Obat yang Dipesan</span>
                <span class="text-xs font-bold text-emerald-700">Total Estimasi: <span id="grand-total-label">Rp 0</span></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Nama Obat</th>
                            <th class="py-3 px-4 text-center">Jumlah Dipesan</th>
                            <th class="py-3 px-4 text-right">Harga Satuan Acuan</th>
                            <th class="py-3 px-4 text-right">Subtotal Perkiraan</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody" class="divide-y divide-gray-100 font-medium">
                        <tr id="empty-row">
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                Belum ada item yang ditambahkan ke Purchase Order.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="po-error" class="hidden p-4 mx-6 my-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs"></div>

            <div class="p-6 bg-slate-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('po.index') }}" class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-100">Batal</a>
                <button type="submit" id="submit-btn" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Terbitkan PO</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const itemsMap = new Map();

    // Pre-populate if came from rekomendasi
    @if(!empty($prefilledItems))
        const prefilled = @json($prefilledItems);
        prefilled.forEach(it => {
            itemsMap.set(String(it.obat_id), {
                id: String(it.obat_id),
                nama: it.nama,
                satuan: it.satuan,
                qty: it.qty,
                harga: it.harga
            });
        });
        document.addEventListener('DOMContentLoaded', () => renderItems());
    @endif

    function addItemFromSelector() {
        const selector = document.getElementById('obat-selector');
        const obatId = selector.value;
        const qtyInput = document.getElementById('add-qty');
        const qty = parseInt(qtyInput.value || '1', 10);

        if (!obatId || qty <= 0) return;

        const opt = selector.options[selector.selectedIndex];
        if (itemsMap.has(obatId)) {
            itemsMap.get(obatId).qty += qty;
        } else {
            itemsMap.set(obatId, {
                id: obatId,
                nama: opt.dataset.nama,
                satuan: opt.dataset.satuan,
                qty: qty,
                harga: parseFloat(opt.dataset.harga)
            });
        }

        renderItems();
        selector.value = '';
        qtyInput.value = '10';
    }

    function removeItem(id) {
        itemsMap.delete(String(id));
        renderItems();
    }

    function updateItemQty(id, newQty) {
        if (itemsMap.has(String(id))) {
            itemsMap.get(String(id)).qty = Math.max(1, parseInt(newQty || '1', 10));
            renderItems();
        }
    }

    function renderItems() {
        const tbody = document.getElementById('items-tbody');
        let total = 0;

        if (itemsMap.size === 0) {
            tbody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="5" class="py-12 text-center text-gray-400">
                        Belum ada item yang ditambahkan ke Purchase Order.
                    </td>
                </tr>
            `;
            document.getElementById('grand-total-label').textContent = 'Rp 0';
            return;
        }

        tbody.innerHTML = Array.from(itemsMap.values()).map(it => {
            const subtotal = it.qty * it.harga;
            total += subtotal;
            return `
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3 px-4 font-bold text-gray-900">${it.nama}</td>
                    <td class="py-3 px-4 text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <input type="number" min="1" value="${it.qty}" onchange="updateItemQty('${it.id}', this.value)"
                                class="w-20 px-2 py-1 text-center font-bold bg-white border border-gray-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                            <span class="text-[11px] text-gray-500">${it.satuan}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-right font-mono text-gray-700">
                        ${Simapo.formatRupiah(it.harga)}
                    </td>
                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                        ${Simapo.formatRupiah(subtotal)}
                    </td>
                    <td class="py-3 px-4 text-center">
                        <button type="button" onclick="removeItem('${it.id}')" class="p-1 rounded text-rose-500 hover:bg-rose-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('grand-total-label').textContent = Simapo.formatRupiah(total);
    }

    async function handlePoSubmit(e) {
        e.preventDefault();
        const errBox = document.getElementById('po-error');
        errBox.classList.add('hidden');

        if (itemsMap.size === 0) {
            Simapo.toast('Tambahkan minimal 1 item obat ke PO!', 'error');
            return;
        }

        const items = [];
        for (const [id, it] of itemsMap) {
            items.push({
                obat_id: it.id,
                jumlah_dipesan: it.qty,
                harga_satuan: it.harga
            });
        }

        const payload = {
            no_po: document.getElementById('no_po').value,
            suplier_id: document.getElementById('suplier_id').value,
            tanggal_po: document.getElementById('tanggal_po').value,
            estimasi_tanggal_terima: document.getElementById('estimasi_tanggal_terima').value,
            catatan: document.getElementById('catatan').value,
            items: items
        };

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;

        const res = await Simapo.fetch('{{ route("po.store") }}', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        submitBtn.disabled = false;

        if (res.ok && res.data.success) {
            Simapo.toast(res.data.message, 'success');
            setTimeout(() => {
                window.location.href = res.data.redirect;
            }, 600);
        } else {
            const msg = res.data.message || 'Gagal menyimpan Purchase Order';
            const errors = res.data.errors ? Object.values(res.data.errors).flat().join('<br>') : msg;
            errBox.innerHTML = errors;
            errBox.classList.remove('hidden');
        }
    }
</script>
@endpush
