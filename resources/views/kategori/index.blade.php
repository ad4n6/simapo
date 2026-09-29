@extends('layouts.app')

@section('title', 'Kategori Obat')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Kategori Obat</h1>
        <p class="text-xs text-gray-500 mt-1">Klasifikasi golongan obat (bebas, terbatas, keras, narkotika, sirup, dll.).</p>
    </div>
    @if(in_array(Auth::user()->role, ['owner', 'apoteker']))
    <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah Kategori</span>
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Search Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between gap-4">
        <form method="GET" action="{{ route('kategori.index') }}" class="flex-1 flex gap-2 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kategori..."
                class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            <button type="submit" class="py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">Cari</button>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Nama Kategori</th>
                        <th class="py-3 px-4">Deskripsi / Keterangan</th>
                        <th class="py-3 px-4 text-center">Jumlah Obat</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($kategoris as $k)
                        <tr class="hover:bg-slate-50/60 transition-colors" id="row-{{ $k->id }}">
                            <td class="py-3.5 px-4 font-bold text-gray-900">{{ $k->nama }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $k->deskripsi ?: '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $k->obats_count }} Obat
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if(in_array(Auth::user()->role, ['owner', 'apoteker']))
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" onclick="openEditModal({{ $k->id }})" class="p-1.5 rounded-lg text-gray-500 hover:text-emerald-600 hover:bg-emerald-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" onclick="deleteItem({{ $k->id }}, '{{ addslashes($k->nama) }}')" class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                                @else
                                <span class="text-gray-400 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-400">Tidak ada kategori ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kategoris->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $kategoris->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Create / Edit -->
<div id="item-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden">
        <div class="p-5 bg-slate-900 text-white flex items-center justify-between">
            <h3 id="modal-title" class="text-sm font-bold">Tambah Kategori</h3>
            <button type="button" onclick="Simapo.modal('item-modal').hide()" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="item-form" onsubmit="handleSubmit(event)" class="p-6 space-y-4">
            <input type="hidden" id="item-id">
            <div id="form-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs"></div>

            <div>
                <label for="nama" class="block text-xs font-semibold text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" id="nama" name="nama" required placeholder="Contoh: Obat Keras"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
            </div>

            <div>
                <label for="deskripsi" class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi / Keterangan</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Keterangan singkat golongan obat..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"></textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" onclick="Simapo.modal('item-modal').hide()" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Batal</button>
                <button type="submit" id="save-btn" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isEditing = false;

    function openCreateModal() {
        isEditing = false;
        document.getElementById('modal-title').textContent = 'Tambah Kategori Baru';
        document.getElementById('item-id').value = '';
        document.getElementById('item-form').reset();
        document.getElementById('form-error').classList.add('hidden');
        Simapo.modal('item-modal').show();
    }

    async function openEditModal(id) {
        isEditing = true;
        document.getElementById('modal-title').textContent = 'Edit Kategori';
        document.getElementById('item-id').value = id;
        document.getElementById('form-error').classList.add('hidden');

        const res = await Simapo.fetch(`/kategori/${id}`);
        if (res.ok && res.data.success) {
            document.getElementById('nama').value = res.data.data.nama;
            document.getElementById('deskripsi').value = res.data.data.deskripsi || '';
            Simapo.modal('item-modal').show();
        } else {
            Simapo.toast('Gagal mengambil data', 'error');
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
            nama: document.getElementById('nama').value,
            deskripsi: document.getElementById('deskripsi').value
        };

        const url = isEditing ? `/kategori/${id}` : '/kategori';
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
            const msg = res.data.message || 'Gagal menyimpan data';
            const errors = res.data.errors ? Object.values(res.data.errors).flat().join('<br>') : msg;
            formError.innerHTML = errors;
            formError.classList.remove('hidden');
        }
    }

    function deleteItem(id, nama) {
        Simapo.confirm(`Hapus kategori "${nama}"?`, async () => {
            const res = await Simapo.fetch(`/kategori/${id}`, { method: 'DELETE' });
            if (res.ok && res.data.success) {
                Simapo.toast(res.data.message, 'success');
                document.getElementById(`row-${id}`)?.remove();
            } else {
                Simapo.toast(res.data.message || 'Gagal menghapus kategori', 'error');
            }
        });
    }
</script>
@endpush
