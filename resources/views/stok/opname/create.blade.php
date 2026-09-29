@extends('layouts.app')

@section('title', 'Input Stock Opname')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Formulir Stock Opname Baru</h1>
        <p class="text-xs text-gray-500 mt-1">Masukkan hasil penghitungan fisik obat di rak. Sistem akan otomatis menyesuaikan stok dan saldo berjalan.</p>
    </div>
    <a href="{{ route('stok.opname.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
        &larr; Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <form id="opname-form" onsubmit="handleOpnameSubmit(event)" class="space-y-6">
        <!-- Form Header Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Stock Opname</label>
                <input type="text" id="no_opname" name="no_opname" value="{{ $noOpname }}" readonly
                    class="w-full px-3.5 py-2 bg-gray-100 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-700">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pelaksanaan</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan / Keterangan Umum</label>
                <input type="text" id="catatan" name="catatan" placeholder="Opname berkala rak A & B..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <!-- Medication Batch Selector Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
            <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">Pilih Batch Obat untuk Dihitung</h2>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <select id="batch-selector" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Obat & Batch yang Ingin Dihitung --</option>
                        @foreach($obats as $o)
                            @foreach($o->batches as $b)
                                <option value="{{ $b->id }}"
                                    data-obat="{{ $o->nama }}"
                                    data-satuan="{{ $o->satuan?->nama }}"
                                    data-batch="{{ $b->no_batch }}"
                                    data-exp="{{ $b->tanggal_kedaluwarsa->format('d/m/Y') }}"
                                    data-sistem="{{ $b->jumlah_saat_ini }}"
                                    data-harga="{{ $b->harga_beli }}">
                                    {{ $o->nama }} • Batch: {{ $b->no_batch }} (Exp: {{ $b->tanggal_kedaluwarsa->format('d/m/Y') }}) • Stok Sistem: {{ $b->jumlah_saat_ini }} {{ $o->satuan?->nama }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="addBatchRow()" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors shrink-0">
                    + Tambah ke Daftar Hitung
                </button>
            </div>
        </div>

        <!-- Opname Items Table Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50/70 border-b border-gray-100 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">Tabel Penghitungan Fisik</span>
                <span class="text-[11px] text-gray-500">*Wajib mengisi alasan jika terdapat selisih</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Nama Obat & Batch</th>
                            <th class="py-3 px-4 text-center">Stok Sistem</th>
                            <th class="py-3 px-4 text-center">Stok Fisik Real</th>
                            <th class="py-3 px-4 text-center">Selisih</th>
                            <th class="py-3 px-4">Alasan Selisih</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="opname-tbody" class="divide-y divide-gray-100 font-medium">
                        <tr id="empty-row">
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                Belum ada item obat yang dipilih. Silakan pilih dari dropdown di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="opname-error" class="hidden p-4 mx-6 my-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs"></div>

            <div class="p-6 bg-slate-50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-gray-600">
                    Total Item: <span id="total-items-badge" class="font-bold text-gray-900">0</span> batch obat
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('stok.opname.index') }}" class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-100">Batal</a>
                    <button type="submit" id="submit-btn" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Hasil Opname</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const selectedBatches = new Map();

    function addBatchRow() {
        const selector = document.getElementById('batch-selector');
        const batchId = selector.value;
        if (!batchId) return;

        if (selectedBatches.has(batchId)) {
            Simapo.toast('Batch ini sudah ditambahkan ke tabel!', 'warning');
            return;
        }

        const opt = selector.options[selector.selectedIndex];
        const data = {
            id: batchId,
            obat: opt.dataset.obat,
            satuan: opt.dataset.satuan,
            batch: opt.dataset.batch,
            exp: opt.dataset.exp,
            sistem: parseInt(opt.dataset.sistem, 10),
            harga: parseFloat(opt.dataset.harga)
        };

        selectedBatches.set(batchId, data);
        renderTable();
        selector.value = '';
    }

    function removeRow(batchId) {
        selectedBatches.delete(String(batchId));
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('opname-tbody');
        const emptyRow = document.getElementById('empty-row');
        document.getElementById('total-items-badge').textContent = selectedBatches.size;

        if (selectedBatches.size === 0) {
            tbody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="6" class="py-12 text-center text-gray-400">
                        Belum ada item obat yang dipilih. Silakan pilih dari dropdown di atas.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = Array.from(selectedBatches.values()).map(item => `
            <tr id="row-${item.id}" class="hover:bg-gray-50/50">
                <td class="py-3 px-4">
                    <span class="font-bold text-gray-900 block">${item.obat}</span>
                    <span class="text-[10px] text-gray-500 font-mono">Batch: ${item.batch} • Exp: ${item.exp}</span>
                </td>
                <td class="py-3 px-4 text-center font-mono font-bold text-gray-700">
                    ${item.sistem} ${item.satuan}
                </td>
                <td class="py-3 px-4 text-center">
                    <input type="number" min="0" value="${item.sistem}" id="fisik-${item.id}"
                        oninput="calculateDiff('${item.id}', ${item.sistem})"
                        class="w-24 px-2.5 py-1.5 text-center font-bold bg-white border border-gray-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                </td>
                <td class="py-3 px-4 text-center font-mono font-bold" id="diff-${item.id}">
                    <span class="text-gray-400">0</span>
                </td>
                <td class="py-3 px-4">
                    <input type="text" id="alasan-${item.id}" placeholder="Alasan jika ada selisih..." value="Sesuai fisik"
                        class="w-full px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                </td>
                <td class="py-3 px-4 text-center">
                    <button type="button" onclick="removeRow('${item.id}')" class="p-1 rounded text-rose-500 hover:bg-rose-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function calculateDiff(batchId, stokSistem) {
        const inputFisik = document.getElementById(`fisik-${batchId}`);
        const diffEl = document.getElementById(`diff-${batchId}`);
        const alasanInput = document.getElementById(`alasan-${batchId}`);
        const fisikVal = parseInt(inputFisik.value || '0', 10);
        const selisih = fisikVal - stokSistem;

        if (selisih > 0) {
            diffEl.innerHTML = `<span class="text-emerald-600 font-bold">+${selisih}</span>`;
            if (alasanInput.value === 'Sesuai fisik') alasanInput.value = 'Kelebihan fisik saat hitung ulang';
        } else if (selisih < 0) {
            diffEl.innerHTML = `<span class="text-rose-600 font-bold">${selisih}</span>`;
            if (alasanInput.value === 'Sesuai fisik') alasanInput.value = 'Hilang / rusak / selisih kemasan';
        } else {
            diffEl.innerHTML = `<span class="text-gray-400">0</span>`;
            alasanInput.value = 'Sesuai fisik';
        }
    }

    async function handleOpnameSubmit(e) {
        e.preventDefault();
        const errBox = document.getElementById('opname-error');
        errBox.classList.add('hidden');

        if (selectedBatches.size === 0) {
            Simapo.toast('Tambahkan minimal 1 batch obat untuk di-opname!', 'error');
            return;
        }

        const items = [];
        for (const [batchId, data] of selectedBatches) {
            const fisik = parseInt(document.getElementById(`fisik-${batchId}`)?.value || '0', 10);
            const alasan = document.getElementById(`alasan-${batchId}`)?.value || 'Sesuai fisik';
            items.push({
                batch_id: batchId,
                stok_fisik: fisik,
                alasan: alasan
            });
        }

        const payload = {
            no_opname: document.getElementById('no_opname').value,
            tanggal: document.getElementById('tanggal').value,
            catatan: document.getElementById('catatan').value,
            items: items
        };

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;

        const res = await Simapo.fetch('{{ route("stok.opname.store") }}', {
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
            const msg = res.data.message || 'Gagal menyimpan stock opname';
            const errors = res.data.errors ? Object.values(res.data.errors).flat().join('<br>') : msg;
            errBox.innerHTML = errors;
            errBox.classList.remove('hidden');
        }
    }
</script>
@endpush
