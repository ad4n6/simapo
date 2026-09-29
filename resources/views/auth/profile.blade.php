@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Profil & Keamanan Akun</h1>
            <p class="text-xs text-gray-500 mt-1">Perbarui data diri, kata sandi, dan hubungkan ID Telegram untuk notifikasi pribadi.</p>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Profil Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-4 pb-6 border-b border-gray-100">
                <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-md">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Role: {{ strtoupper(str_replace('_', ' ', $user->role)) }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            Status: Aktif
                        </span>
                    </div>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        <p class="font-bold mb-1">Gagal memperbarui profil:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email (Tidak dapat diubah)</label>
                        <input type="email" value="{{ $user->email }}" disabled
                            class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-xs font-medium text-gray-500 cursor-not-allowed">
                    </div>
                </div>

                <!-- Telegram Integration -->
                <div class="p-4 rounded-xl bg-sky-50/60 border border-sky-200">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs font-bold text-sky-900">Integrasi Telegram Pribadi</h3>
                            <p class="text-[11px] text-sky-700 mt-0.5">
                                Masukkan <strong>Chat ID Telegram</strong> Anda untuk menerima notifikasi operasional langsung ke akun Telegram Anda.
                            </p>
                            <div class="mt-3 max-w-sm">
                                <label for="telegram_chat_id" class="block text-[11px] font-semibold text-sky-900 mb-1">Telegram Chat ID</label>
                                <input type="text" name="telegram_chat_id" id="telegram_chat_id" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}"
                                    placeholder="Contoh: 123456789"
                                    class="w-full px-3 py-2 bg-white border border-sky-300 rounded-lg text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                <span class="block text-[10px] text-sky-600 mt-1">
                                    💡 Cara cari ID: buka Telegram, cari bot <code>@userinfobot</code> lalu kirim <code>/start</code>.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ganti Password -->
                <div class="pt-6 border-t border-gray-100">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4">Ganti Kata Sandi (Opsional)</h3>
                    <p class="text-xs text-gray-500 mb-4">Biarkan kosong jika Anda tidak ingin mengubah kata sandi.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="current_password" class="block text-xs font-semibold text-gray-700 mb-1.5">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" id="current_password"
                                placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        </div>

                        <div>
                            <label for="new_password" class="block text-xs font-semibold text-gray-700 mb-1.5">Kata Sandi Baru</label>
                            <input type="password" name="new_password" id="new_password"
                                placeholder="Minimal 6 karakter"
                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1.5">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
