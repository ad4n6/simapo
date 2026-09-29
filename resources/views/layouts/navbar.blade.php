@php
    $user = Auth::user();
    $activeShift = \App\Models\ShiftKasir::where('user_id', $user->id)->where('status', 'buka')->first();
@endphp

<header class="h-16 bg-white border-b border-gray-200 sticky top-0 z-20 flex items-center justify-between px-4 sm:px-6 shadow-xs">
    <!-- Left: Hamburger button & page context -->
    <div class="flex items-center gap-3">
        <button type="button" onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-medium text-gray-700">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
            <span class="text-gray-300">|</span>
            <span class="text-gray-600 font-medium">Sistem Apotek Bintan 88</span>
        </div>
    </div>

    <!-- Right: Actions, Notifications, Profile -->
    <div class="flex items-center gap-3">
        
        <!-- Shift status indicator for cashiers/pharmacists -->
        @if(in_array($user->role, ['owner', 'apoteker', 'kasir']))
            @if($activeShift)
                <a href="{{ route('shift.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Shift Aktif (Kasir)</span>
                </a>
            @else
                <a href="{{ route('shift.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Buka Shift</span>
                </a>
            @endif
        @endif

        <!-- Quick POS Link -->
        @if(in_array($user->role, ['owner', 'apoteker', 'kasir']))
        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span class="hidden sm:inline">Transaksi Kasir</span>
            <span class="sm:hidden">POS</span>
        </a>
        @endif

        <!-- NOTIFICATION BELL & DROPDOWN -->
        <div class="relative" x-data="{ open: false }" id="notif-dropdown-wrapper">
            <button type="button" onclick="toggleNotifDropdown()" class="relative p-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span id="notif-badge" class="hidden absolute top-1 right-1 px-1.5 py-0.5 text-[10px] font-black rounded-full bg-rose-500 text-white animate-pulse">0</span>
            </button>

            <!-- Dropdown Menu -->
            <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden transform origin-top-right transition-all">
                <div class="p-3 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Notifikasi Sistem</span>
                    </div>
                    <button type="button" onclick="Simapo.markAllRead()" class="text-[11px] text-emerald-300 hover:text-emerald-100 font-medium">Tandai Dibaca</button>
                </div>
                
                <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-gray-100 custom-scrollbar">
                    <div class="p-6 text-center text-xs text-gray-400">Memuat notifikasi...</div>
                </div>

                <div class="p-2.5 bg-gray-50 border-t border-gray-100 text-center">
                    <a href="{{ route('notifikasi.riwayat') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Lihat Semua Riwayat Notifikasi &rarr;</a>
                </div>
            </div>
        </div>

        <!-- PROFILE DROPDOWN -->
        <div class="relative" id="profile-dropdown-wrapper">
            <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-gray-100 transition-colors">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="hidden md:block text-left">
                    <span class="block text-xs font-semibold text-gray-800 leading-tight">{{ $user->name }}</span>
                    <span class="block text-[10px] text-gray-500 leading-none">{{ ucfirst($user->role) }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <!-- Dropdown Menu -->
            <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-800">{{ $user->name }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $user->email }}</p>
                </div>
                <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-800">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profil & Kata Sandi</span>
                </a>
                @if($user->role === 'owner')
                <a href="{{ route('pengaturan.notifikasi') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-800">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                    <span>Pengaturan Notifikasi</span>
                </a>
                @endif
                <div class="border-t border-gray-100 my-1"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-medium text-left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
