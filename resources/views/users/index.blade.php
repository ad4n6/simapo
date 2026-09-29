@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Manajemen Pengguna (User)</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola akun staf apotek, hak akses role (RBAC), status keaktifan, dan ID Telegram.</p>
    </div>
    <div>
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah User Baru</span>
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, ID..."
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            </div>
            <div>
                <select name="role" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    <option value="">Semua Role</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="apoteker" {{ request('role') === 'apoteker' ? 'selected' : '' }}>Apoteker</option>
                    <option value="kasir" {{ request('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="admin_gudang" {{ request('role') === 'admin_gudang' ? 'selected' : '' }}>Admin Gudang</option>
                </select>
            </div>
            <div>
                <select name="status" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('users.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role Akses</th>
                        <th class="py-3 px-4">Telegram Chat ID</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/60 transition-colors" id="user-row-{{ $u->id }}">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900 block">{{ $u->name }}</span>
                                        @if($u->id === Auth::id())
                                            <span class="text-[10px] text-emerald-600 font-bold">(Akun Anda)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ $u->email }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ 
                                    $u->role === 'owner' ? 'bg-amber-100 text-amber-800' :
                                    ($u->role === 'apoteker' ? 'bg-emerald-100 text-emerald-800' :
                                    ($u->role === 'admin_gudang' ? 'bg-sky-100 text-sky-800' : 'bg-purple-100 text-purple-800'))
                                }}">
                                    {{ strtoupper(str_replace('_', ' ', $u->role)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500 font-mono text-[11px]">
                                {{ $u->telegram_chat_id ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <button type="button" onclick="toggleStatus({{ $u->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors {{ $u->status_aktif ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $u->status_aktif ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span id="status-badge-{{ $u->id }}">{{ $u->status_aktif ? 'Aktif' : 'Non-Aktif' }}</span>
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" onclick="openEditModal({{ $u->id }})" class="p-1.5 rounded-lg text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Edit Pengguna">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if($u->id !== Auth::id())
                                    <button type="button" onclick="deleteUser({{ $u->id }}, '{{ addslashes($u->name) }}')" class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Pengguna">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Form Create / Edit User -->
<div id="user-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all my-8">
        <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
            <h3 id="modal-title" class="text-sm font-bold tracking-wide">Tambah Pengguna Baru</h3>
            <button type="button" onclick="Simapo.modal('user-modal').hide()" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="user-form" onsubmit="handleUserSubmit(event)" class="p-6 space-y-4">
            <input type="hidden" id="user-id" name="id">

            <div id="form-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs"></div>

            <div>
                <label for="modal-name" class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="modal-name" name="name" required placeholder="Contoh: apt. Budi Santoso"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
            </div>

            <div>
                <label for="modal-email" class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email</label>
                <input type="email" id="modal-email" name="email" required placeholder="nama@apotekbintan88.com"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="modal-role" class="block text-xs font-semibold text-gray-700 mb-1">Role / Hak Akses</label>
                    <select id="modal-role" name="role" required
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                        <option value="kasir">Kasir</option>
                        <option value="apoteker">Apoteker</option>
                        <option value="admin_gudang">Admin Gudang</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <div>
                    <label for="modal-status" class="block text-xs font-semibold text-gray-700 mb-1">Status Keaktifan</label>
                    <select id="modal-status" name="status_aktif"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                        <option value="1">Aktif</option>
                        <option value="0">Non-Aktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="modal-telegram" class="block text-xs font-semibold text-gray-700 mb-1">Telegram Chat ID (Opsional)</label>
                <input type="text" id="modal-telegram" name="telegram_chat_id" placeholder="Misal: 987654321"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
            </div>

            <div>
                <label for="modal-password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi <span id="password-hint" class="font-normal text-gray-400">(Minimal 6 karakter)</span></label>
                <input type="password" id="modal-password" name="password" placeholder="••••••••"
                    class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" onclick="Simapo.modal('user-modal').hide()" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Batal</button>
                <button type="submit" id="save-btn" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">Simpan Data</button>
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
        document.getElementById('modal-title').textContent = 'Tambah Pengguna Baru';
        document.getElementById('user-id').value = '';
        document.getElementById('user-form').reset();
        document.getElementById('modal-password').required = true;
        document.getElementById('password-hint').textContent = '(Wajib diisi, min. 6 karakter)';
        document.getElementById('form-error').classList.add('hidden');
        Simapo.modal('user-modal').show();
    }

    async function openEditModal(userId) {
        isEditing = true;
        document.getElementById('modal-title').textContent = 'Edit Data Pengguna';
        document.getElementById('user-id').value = userId;
        document.getElementById('modal-password').required = false;
        document.getElementById('password-hint').textContent = '(Kosongkan jika tidak ingin ganti)';
        document.getElementById('form-error').classList.add('hidden');

        const res = await Simapo.fetch(`/users/${userId}`);
        if (res.ok && res.data.success) {
            const u = res.data.data;
            document.getElementById('modal-name').value = u.name;
            document.getElementById('modal-email').value = u.email;
            document.getElementById('modal-role').value = u.role;
            document.getElementById('modal-status').value = u.status_aktif ? '1' : '0';
            document.getElementById('modal-telegram').value = u.telegram_chat_id || '';
            document.getElementById('modal-password').value = '';
            Simapo.modal('user-modal').show();
        } else {
            Simapo.toast('Gagal memuat data pengguna', 'error');
        }
    }

    async function handleUserSubmit(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('save-btn');
        const formError = document.getElementById('form-error');
        formError.classList.add('hidden');
        saveBtn.disabled = true;

        const id = document.getElementById('user-id').value;
        const payload = {
            name: document.getElementById('modal-name').value,
            email: document.getElementById('modal-email').value,
            role: document.getElementById('modal-role').value,
            status_aktif: document.getElementById('modal-status').value,
            telegram_chat_id: document.getElementById('modal-telegram').value,
            password: document.getElementById('modal-password').value
        };

        const url = isEditing ? `/users/${id}` : '/users';
        const method = isEditing ? 'PUT' : 'POST';

        const res = await Simapo.fetch(url, {
            method: method,
            body: JSON.stringify(payload)
        });

        saveBtn.disabled = false;

        if (res.ok && res.data.success) {
            Simapo.modal('user-modal').hide();
            Simapo.toast(res.data.message, 'success');
            setTimeout(() => window.location.reload(), 600);
        } else {
            const msg = res.data.message || 'Terjadi kesalahan saat menyimpan data.';
            const errors = res.data.errors ? Object.values(res.data.errors).flat().join('<br>') : msg;
            formError.innerHTML = errors;
            formError.classList.remove('hidden');
        }
    }

    async function toggleStatus(userId) {
        const res = await Simapo.fetch(`/users/${userId}/toggle-status`, { method: 'PATCH' });
        if (res.ok && res.data.success) {
            const badge = document.getElementById(`status-badge-${userId}`);
            const btn = badge.parentElement;
            if (res.data.status_aktif) {
                badge.textContent = 'Aktif';
                btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors bg-emerald-50 text-emerald-700 hover:bg-emerald-100';
                btn.querySelector('span').className = 'w-1.5 h-1.5 rounded-full bg-emerald-500';
            } else {
                badge.textContent = 'Non-Aktif';
                btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors bg-rose-50 text-rose-700 hover:bg-rose-100';
                btn.querySelector('span').className = 'w-1.5 h-1.5 rounded-full bg-rose-500';
            }
            Simapo.toast(res.data.message, 'success');
        } else {
            Simapo.toast(res.data.message || 'Gagal mengubah status', 'error');
        }
    }

    function deleteUser(userId, name) {
        Simapo.confirm(`Apakah Anda yakin ingin menghapus akun user "${name}"?`, async () => {
            const res = await Simapo.fetch(`/users/${userId}`, { method: 'DELETE' });
            if (res.ok && res.data.success) {
                Simapo.toast(res.data.message, 'success');
                document.getElementById(`user-row-${userId}`)?.remove();
            } else {
                Simapo.toast(res.data.message || 'Gagal menghapus pengguna', 'error');
            }
        });
    }
</script>
@endpush
