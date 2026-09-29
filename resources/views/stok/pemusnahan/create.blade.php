@extends('layouts.app')

@section('title', 'Buat Berita Acara Pemusnahan Obat')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Berita Acara Pemusnahan Obat (BAPO)</h1>
        <p class="text-xs text-gray-500 mt-1">Formulir resmi pemusnahan sediaan farmasi kedaluwarsa atau rusak sesuai regulasi kefarmasian.</p>
    </div>
    <a href="{{ route('stok.pemusnahan.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
        &larr; Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider">Input Berita Acara Pemusnahan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Stok pada batch yang dipilih akan otomatis dipotong permanen.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-rose-950 text-rose-300 border border-rose-800">
                Pemusnahan Farmasi
            </span>
        </div>

        <form id="pemusnahan-form" onsubmit="handleSubmit(event)" class="p-6 space-y-5">
            <div id="form-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Berita Acara</label>
                    <input type="text" id="no_pemusnahan" name="no_pemusnahan" value="{{ $noPemusnahan }}" readonly
                        class="w-full px-3.5 py-2 bg-gray-100 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pemusnahan</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Batch Obat Kedaluwarsa / Rusak</label>
                <select id="batch_obat_id" name="batch_obat_id" required onchange="handleBatchChange()"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Batch Obat yang Akan Dimusnahkan --</option>
                    @foreach($expiredBatches as $b)
                        <option value="{{ $b->id }}"
                            data-stok="{{ $b->jumlah_saat_ini }}"
                            data-satuan="{{ $b->obat?->satuan?->nama }}"
                            data-exp="{{ $b->tanggal_kedaluwarsa->format('d/m/Y') }}">
                            {{ $b->obat?->nama }} • Batch: {{ $b->no_batch }} (Exp: {{ $b->tanggal_kedaluwarsa->format('d/m/Y') }}) • Sisa: {{ $b->jumlah_saat_ini }} {{ $b->obat?->satuan?->nama }}
                        </option>
                    @endforeach
                </select>
                <span class="block text-[11px] text-gray-400 mt-1">*Menampilkan batch yang sudah kedaluwarsa atau mendekati kedaluwarsa (≤ 30 hari).</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah yang Dimusnahkan</label>
                    <div class="flex items-center gap-2">
                        <input type="number" id="jumlah" name="jumlah" min="1" required placeholder="0"
                            class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span id="satuan-label" class="text-xs font-semibold text-gray-500 shrink-0">Unit</span>
                    </div>
                    <span id="stok-tersedia-hint" class="block text-[11px] text-gray-400 mt-1">Maksimal stok batch saat ini: -</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pemusnahan</label>
                    <select id="metode_pemusnahan" name="metode_pemusnahan" required
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Dikeluarkan dari kemasan & dilarutkan ke saluran IPAL">Dilarutkan & dibuang ke IPAL resmi</option>
                        <option value="Dibakar dalam insinerator limbah medis B3">Insinerator limbah medis B3</option>
                        <option value="Dihancurkan padatan & ditimbun sesuai SOP Dinkes">Dihancurkan & ditimbun tanah sesuai SOP</option>
                        <option value="Diserahkan ke pihak ketiga pengolah limbah B3 berizin">Diserahkan ke pihak ketiga pengolah limbah B3</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Saksi Pemusnahan</label>
                <input type="text" id="saksi" name="saksi" required placeholder="Contoh: apt. Siti Rahma (PJ) dan Rian Hidayat (Staf Gudang)"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Pemusnahan</label>
                <textarea id="alasan" name="alasan" rows="2" required placeholder="Obat telah melewati tanggal kedaluwarsa dan tidak layak konsumsi..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">Obat telah melewati batas tanggal kedaluwarsa dan tidak layak konsumsi.</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Dokumentasi foto terlampir di lemari arsip..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('stok.pemusnahan.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold">Batal</a>
                <button type="submit" id="submit-btn" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Simpan & Eksekusi Pemusnahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function handleBatchChange() {
        const select = document.getElementById('batch_obat_id');
        const opt = select.options[select.selectedIndex];
        const stokInput = document.getElementById('jumlah');
        const hint = document.getElementById('stok-tersedia-hint');
        const satuanLabel = document.getElementById('satuan-label');

        if (!opt.value) {
            hint.textContent = 'Maksimal stok batch saat ini: -';
            satuanLabel.textContent = 'Unit';
            stokInput.max = 0;
            return;
        }

        const maxStok = parseInt(opt.dataset.stok, 10);
        stokInput.max = maxStok;
        stokInput.value = maxStok; // default all
        satuanLabel.textContent = opt.dataset.satuan;
        hint.textContent = `Maksimal stok batch saat ini: ${maxStok} ${opt.dataset.satuan}`;
    }

    async function handleSubmit(e) {
        e.preventDefault();
        const errBox = document.getElementById('form-error');
        errBox.classList.add('hidden');
        const submitBtn = document.getElementById('submit-btn');

        const payload = {
            no_pemusnahan: document.getElementById('no_pemusnahan').value,
            tanggal: document.getElementById('tanggal').value,
            batch_obat_id: document.getElementById('batch_obat_id').value,
            jumlah: parseInt(document.getElementById('jumlah').value, 10),
            metode_pemusnahan: document.getElementById('metode_pemusnahan').value,
            saksi: document.getElementById('saksi').value,
            alasan: document.getElementById('alasan').value,
            catatan: document.getElementById('catatan').value
        };

        submitBtn.disabled = true;

        const res = await Simapo.fetch('{{ route("stok.pemusnahan.store") }}', {
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
            const msg = res.data.message || 'Gagal menyimpan berita acara';
            const errors = res.data.errors ? Object.values(res.data.errors).flat().join('<br>') : msg;
            errBox.innerHTML = errors;
            errBox.classList.remove('hidden');
        }
    }
</script>
@endpush
