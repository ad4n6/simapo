<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Apotek Bintan 88</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.3);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.6);
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased flex flex-col">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col md:pl-64 overflow-hidden">
            <!-- Navbar -->
            @include('layouts.navbar')

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 custom-scrollbar">
                <!-- Page Title / Breadcrumb Slot -->
                @hasSection('header')
                    <div class="mb-6">
                        @yield('header')
                    </div>
                @endif

                <!-- Yield Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Floating Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-auto"></div>

    <!-- Global Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 p-6 transform transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 id="confirm-modal-title" class="text-base font-bold text-gray-900">Konfirmasi Tindakan</h3>
                    <p id="confirm-modal-message" class="text-xs text-gray-500 mt-1">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" onclick="Simapo.modal('confirm-modal').hide()" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                <button type="button" id="confirm-modal-btn" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-xs">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <!-- Interactive Toggle Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function toggleNotifDropdown() {
            const dropdown = document.getElementById('notif-dropdown');
            dropdown.classList.toggle('hidden');
            document.getElementById('profile-dropdown')?.classList.add('hidden');
        }

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
            document.getElementById('notif-dropdown')?.classList.add('hidden');
        }

        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            const notifWrap = document.getElementById('notif-dropdown-wrapper');
            const profileWrap = document.getElementById('profile-dropdown-wrapper');
            if (notifWrap && !notifWrap.contains(e.target)) {
                document.getElementById('notif-dropdown')?.classList.add('hidden');
            }
            if (profileWrap && !profileWrap.contains(e.target)) {
                document.getElementById('profile-dropdown')?.classList.add('hidden');
            }
        });

        // Flash message from session
        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                Simapo.toast(@json(session('success')), 'success');
            @endif
            @if(session('error'))
                Simapo.toast(@json(session('error')), 'error');
            @endif
            @if(session('warning'))
                Simapo.toast(@json(session('warning')), 'warning');
            @endif
            @if(session('info'))
                Simapo.toast(@json(session('info')), 'info');
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
