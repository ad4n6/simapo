@extends('layouts.app')

@section('title', 'Master Data Obat')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Master Data Obat & Sediaan</h1>
        <p class="text-xs text-gray-500 mt-1">Daftar katalog obat, harga jual, lokasi rak, kebutuhan resep, dan ketersediaan stok aktif.</p>
    </div>
    @if(in_array(Auth::user()->role, ['owner', 'apoteker']))
    <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah Obat Baru</span>
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('obat.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, generik, rak..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            </div>
            <div>
                <select name="kategori_id" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="butuh_resep" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    <option value="">Semua Jenis (Resep / Bebas)</option>
                    <option value="1" {{ request('butuh_resep') === '1' ? 'selected' : '' }}>Wajib Resep Dokter</option>
                    <option value="0" {{ request('butuh_resep') === '0' ? 'selected' : '' }}>Bebas Tanpa Resep</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('obat.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Kode & Nama Obat</th>
                        <th class="py-3 px-4">Kategori & Satuan</th>
                        <th class="py-3 px-4">Harga Beli / Jual</th>
                        <th class="py-3 px-4 text-center">Stok Aktif</th>
                        <th class="py-3 px-4 text-center">Lokasi Rak</th>
                        <th class="py-3 px-4 text-center">Jenis Obat</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($obats as $o)
                        @php
                            $stok = $o->total_stok;
                            $statusStok = $o->status_stok;
                            $statusExp = $o->status_kedaluwarsa;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors" id="row-{{ $o->id }}">
                            <td class="py-3.5 px-4">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs border border-emerald-100">
                                        💊
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900 block hover:text-emerald-600 cursor-pointer" onclick="showDetailModal({{ $o->id }})">
                                            {{ $o->nama }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-[10px] text-gray-400 font-semibold">{{ $o->kode }}</span>
                                            @if($o->nama_generik)
                                                <span class="text-[10px] text-gray-500 italic">({{ $o->nama_generik }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-gray-800 font-semibold">{{ $o->kategori?->nama ?? '-' }}</div>
                                <span class="text-[10px] text-gray-400">Satuan: {{ $o->satuan?->nama ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-emerald-700">Rp {{ number_format($o->harga_jual, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-gray-400">Beli: Rp {{ number_format($o->harga_beli, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ 
                                        $statusStok === 'kosong' ? 'bg-rose-100 text-rose-800' :
                                        ($statusStok === 'menipis' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')
                                    }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ 
                                            $statusStok === 'kosong' ? 'bg-rose-500' :
                                            ($statusStok === 'menipis' ? 'bg-amber-500' : 'bg-emerald-500')
                                        }}"></span>
                                        <span>{{ $stok }} {{ $o->satuan?->nama }}</span>
                                    </span>
                                    <span class="text-[9px] text-gray-400 mt-0.5">Min: {{ $o->stok_minimum }}</span>

                                    @if($statusExp === 'kedaluwarsa')
                                        <span class="mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-900 text-white">Ada Expired!</span>
                                    @elseif($statusExp === 'hampir_kedaluwarsa')
                                        <span class="mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500 text-white">Hampir Expired</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-mono text-[11px]">
                                    {{ $o->lokasi_rak ?: 'Rak Umum' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($o->butuh_resep)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        🔴 Wajib Resep
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        🟢 Bebas
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" onclick="showDetailModal({{ $o->id }})" class="p-1.5 rounded-lg text-gray-500 hover:text-emerald-600 hover:bg-emerald-50" title="Lihat Batch & Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    @if(in_array(Auth::user()->role, ['owner', 'apoteker']))
                                    <button type="button" onclick="openEditModal({{ $o->id }})" class="p-1.5 rounded-lg text-gray-500 hover:text-emerald-600 hover:bg-emerald-50" title="Edit Obat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" onclick="deleteItem({{ $o->id }}, '{{ addslashes($o->nama) }}')" class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50" title="Hapus Obat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                Tidak ada data obat yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($obats->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $obats->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Form Create / Edit Obat -->
<div id="item-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-gray-100 overflow-hidden my-8">
        <div class="p-5 bg-slate-900 text-white flex items-center justify-between">
            <h3 id="modal-title" class="text-sm font-bold">Tambah Obat Baru</h3>
            <button type="button" onclick="Simapo.modal('item-modal').hide()" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="item-form" onsubmit="handleSubmit(event)" class="p-6 space-y-4">
            <input type="hidden" id="item-id">
            <div id="form-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs"></div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="kode" class="block text-xs font-semibold text-gray-700 mb-1">Kode Obat</label>
                    <input type="text" id="kode" name="kode" placeholder="Otomatis jika kosong"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>
                <div class="sm:col-span-2">
                    <label for="nama" class="block text-xs font-semibold text-gray-700 mb-1">Nama Dagang Obat</label>
                    <input type="text" id="nama" name="nama" required placeholder="Contoh: Paracetamol 500mg"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="nama_generik" class="block text-xs font-semibold text-gray-700 mb-1">Nama Generik (Kandungan)</label>
                    <input type="text" id="nama_generik" name="nama_generik" placeholder="Paracetamol"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>
                <div>
                    <label for="kategori_id" class="block text-xs font-semibold text-gray-700 mb-1">Kategori Golongan</label>
                    <select id="kategori_id" name="kategori_id" required
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                        <option value="">Pilih Kategori</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="satuan_id" class="block text-xs font-semibold text-gray-700 mb-1">Satuan Kemasan</label>
                    <select id="satuan_id" name="satuan_id" required
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                        <option value="">Pilih Satuan</option>
                        @foreach($satuans as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label for="harga_beli" class="block text-xs font-semibold text-gray-700 mb-1">Harga Beli Acuan (Rp)</label>
                    <input type="number" id="harga_beli" name="harga_beli" required min="0" step="100" placeholder="5000"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>
                <div>
                    <label for="harga_jual" class="block text-xs font-semibold text-gray-700 mb-1">Harga Jual (Rp)</label>
                    <input type="number" id="harga_jual" name="harga_jual" required min="0" step="100" placeholder="7500"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>
                <div>
                    <label for="stok_minimum" class="block text-xs font-semibold text-gray-700 mb-1">Stok Minimum Peringatan</label>
                    <input type="number" id="stok_minimum" name="stok_minimum" required min="1" placeholder="10"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>
                <div>
                    <label for="lokasi_rak" class="block text-xs font-semibold text-gray-700 mb-1">Lokasi Rak Obat</label>
                    <input type="text" id="lokasi_rak" name="lokasi_rak" placeholder="Contoh: A-01"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-gray-200 flex items-center gap-3">
                <input type="checkbox" id="butuh_resep" name="butuh_resep" value="1"
                    class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                <label for="butuh_resep" class="text-xs font-semibold text-gray-800 cursor-pointer">
                    Obat ini <strong>Wajib Resep Dokter</strong> (Hanya bisa dijual di POS via transaksi resep)
                </label>
            </div>

            <div>
                <label for="deskripsi" class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi & Indikasi</label>
                <textarea id="deskripsi" name="deskripsi" rows="2" placeholder="Catatan indikasi pemakaian atau aturan simpan..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"></textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" onclick="Simapo.modal('item-modal').hide()" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Batal</button>
                <button type="submit" id="save-btn" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">Simpan Obat</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Obat & Daftar Batch -->
<div id="detail-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-gray-100 overflow-hidden my-8">
        <div class="p-5 bg-slate-900 text-white flex items-center justify-between">
            <div>
                <h3 id="detail-title" class="text-sm font-bold">Detail Obat & Daftar Batch (FEFO)</h3>
                <p id="detail-subtitle" class="text-[11px] text-gray-400 mt-0.5"></p>
            </div>
            <button type="button" onclick="Simapo.modal('detail-modal').hide()" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-5">
            <!-- Summary Info -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-gray-50 border border-gray-100 text-xs">
                <div>
                    <span class="block text-gray-400 text-[10px] uppercase font-semibold">Harga Jual</span>
                    <span id="detail-harga-jual" class="font-bold text-emerald-700 text-sm"></span>
                </div>
                <div>
                    <span class="block text-gray-400 text-[10px] uppercase font-semibold">Stok Tersedia</span>
                    <span id="detail-stok" class="font-bold text-gray-900 text-sm"></span>
                </div>
                <div>
                    <span class="block text-gray-400 text-[10px] uppercase font-semibold">Lokasi Rak</span>
                    <span id="detail-rak" class="font-bold text-gray-800"></span>
                </div>
                <div>
                    <span class="block text-gray-400 text-[10px] uppercase font-semibold">Ketentuan Resep</span>
                    <span id="detail-resep" class="font-bold text-gray-800"></span>
                </div>
            </div>

            <!-- Batches Table -->
            <div>
                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2 flex items-center justify-between">
                    <span>Batch Aktif (Urutan Penjualan FEFO)</span>
                    <span class="text-[10px] font-normal text-gray-400">*Expired lebih dulu dikurangi terlebih dahulu</span>
                </h4>
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-100">
                            <tr>
                                <th class="p-2.5">No. Batch</th>
                                <th class="p-2.5">Suplier</th>
                                <th class="p-2.5">Kedaluwarsa</th>
                                <th class="p-2.5 text-right">Stok Sisa</th>
                                <th class="p-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="detail-batches-body" class="divide-y divide-gray-100 font-medium"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" onclick="Simapo.modal('detail-modal').hide()" class="px-5 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isEditing = false;

    function openCreateModal() {
        isEditing = false;
        document.getElementById('modal-title').textContent = 'Tambah Obat Baru';
        document.getElementById('item-id').value = '';
        document.getElementById('item-form').reset();
        document.getElementById('form-error').classList.add('hidden');
        Simapo.modal('item-modal').show();
    }

    async function openEditModal(id) {
        isEditing = true;
        document.getElementById('modal-title').textContent = 'Edit Data Obat';
        document.getElementById('item-id').value = id;
        document.getElementById('form-error').classList.add('hidden');

        const res = await Simapo.fetch(`/obat/${id}`);
        if (res.ok && res.data.success) {
            const d = res.data.data;
            document.getElementById('kode').value = d.kode;
            document.getElementById('nama').value = d.nama;
            document.getElementById('nama_generik').value = d.nama_generik || '';
            document.getElementById('kategori_id').value = d.kategori_id;
            document.getElementById('satuan_id').value = d.satuan_id;
            document.getElementById('harga_beli').value = d.harga_beli;
            document.getElementById('harga_jual').value = d.harga_jual;
            document.getElementById('stok_minimum').value = d.stok_minimum;
            document.getElementById('lokasi_rak').value = d.lokasi_rak || '';
            document.getElementById('butuh_resep').checked = d.butuh_resep;
            document.getElementById('deskripsi').value = d.deskripsi || '';
            Simapo.modal('item-modal').show();
        } else {
            Simapo.toast('Gagal mengambil data obat', 'error');
        }
    }

    async function showDetailModal(id) {
        const res = await Simapo.fetch(`/obat/${id}`);
        if (res.ok && res.data.success) {
            const d = res.data.data;
            document.getElementById('detail-title').textContent = d.nama;
            document.getElementById('detail-subtitle').textContent = `${d.kode} • ${d.kategori} • ${d.satuan}`;
            document.getElementById('detail-harga-jual').textContent = Simapo.formatRupiah(d.harga_jual);
            document.getElementById('detail-stok').textContent = `${d.total_stok} ${d.satuan}`;
            document.getElementById('detail-rak').textContent = d.lokasi_rak || 'Rak Umum';
            document.getElementById('detail-resep').textContent = d.butuh_resep ? 'Wajib Resep (K)' : 'Bebas';

            const tbody = document.getElementById('detail-batches-body');
            if (d.batches.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="p-6 text-center text-gray-400">Belum ada batch obat yang terdaftar.</td></tr>`;
            } else {
                tbody.innerHTML = d.batches.map(b => {
                    let statusBadge = '';
                    if (b.is_expired) {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-900 text-white">Kedaluwarsa</span>`;
                    } else if (b.sisa_hari <= 30) {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">${b.sisa_hari} hari lagi</span>`;
                    } else if (b.sisa_hari <= 90) {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">${b.sisa_hari} hari</span>`;
                    } else {
                        statusBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aman</span>`;
                    }

                    return `
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-2.5 font-mono font-bold text-gray-800">${b.no_batch}</td>
                            <td class="p-2.5 text-gray-600">${b.suplier}</td>
                            <td class="p-2.5 text-gray-700">${b.tanggal_kedaluwarsa}</td>
                            <td class="p-2.5 text-right font-bold text-gray-900">${b.jumlah_saat_ini} ${d.satuan}</td>
                            <td class="p-2.5 text-center">${statusBadge}</td>
                        </tr>
                    `;
                }).join('');
            }

            Simapo.modal('detail-modal').show();
        } else {
            Simapo.toast('Gagal memuat detail obat', 'error');
        }
    }

    async function handleSubmit(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('save-btn');
        const formError = document.getElementById('form-error');
        formError.classList.add('hidden');
        saveBtn.disabled = true;

        const id = document.getElementById('item-id').value;
        const payload = {
            kode: document.getElementById('kode').value,
            nama: document.getElementById('nama').value,
            nama_generik: document.getElementById('nama_generik').value,
            kategori_id: document.getElementById('kategori_id').value,
            satuan_id: document.getElementById('satuan_id').value,
            harga_beli: document.getElementById('harga_beli').value,
            harga_jual: document.getElementById('harga_jual').value,
            stok_minimum: document.getElementById('stok_minimum').value,
            lokasi_rak: document.getElementById('lokasi_rak').value,
            butuh_resep: document.getElementById('butuh_resep').checked ? 1 : 0,
            deskripsi: document.getElementById('deskripsi').value
        };

        const url = isEditing ? `/obat/${id}` : '/obat';
        const method = isEditing ? 'PUT' : 'POST';

        const res = await Simapo.fetch(url, {
            method: method,
            body: JSON.stringify(payload)
        });

        saveBtn.disabled = false;

        if (res.ok && res.data.success) {
            Simapo.modal('item-modal').hide();
            Simapo.toast(res.data.message, 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            const msg = res.data.message || 'Gagal menyimpan obat';
            const errors = res.data.errors ? Object.values(res.data.errors).flat().join('<br>') : msg;
            formError.innerHTML = errors;
            formError.classList.remove('hidden');
        }
    }

    function deleteItem(id, nama) {
        Simapo.confirm(`Apakah Anda yakin ingin menghapus data obat "${nama}"?`, async () => {
            const res = await Simapo.fetch(`/obat/${id}`, { method: 'DELETE' });
            if (res.ok && res.data.success) {
                Simapo.toast(res.data.message, 'success');
                document.getElementById(`row-${id}`)?.remove();
            } else {
                Simapo.toast(res.data.message || 'Gagal menghapus obat', 'error');
            }
        });
    }
</script>
@endpush
