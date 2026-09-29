// SIMAPO - Sistem Informasi Manajemen Apotek Bintan 88
// Client-side helper library (Vanilla JavaScript AJAX & UI)

window.Simapo = {
    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },

    formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    },

    toast(message, type = 'success', duration = 3500) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium transition-all transform duration-300 translate-y-2 opacity-0 ${
            type === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' :
            type === 'error' ? 'bg-rose-50 text-rose-800 border-rose-200' :
            type === 'warning' ? 'bg-amber-50 text-amber-800 border-amber-200' :
            'bg-sky-50 text-sky-800 border-sky-200'
        }`;

        const iconSvg = type === 'success' 
            ? `<svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
            : type === 'error'
            ? `<svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
            : `<svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;

        toast.innerHTML = `
            ${iconSvg}
            <div class="flex-1">${message}</div>
            <button type="button" class="text-gray-400 hover:text-gray-600 ml-2" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;

        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        });

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    modal(id) {
        const el = document.getElementById(id);
        return {
            show() {
                if (!el) return;
                el.classList.remove('hidden');
                el.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            },
            hide() {
                if (!el) return;
                el.classList.add('hidden');
                el.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        };
    },

    confirm(message, onConfirm, title = 'Konfirmasi Tindakan') {
        const modal = document.getElementById('confirm-modal');
        if (!modal) {
            if (window.confirm(message)) onConfirm();
            return;
        }

        document.getElementById('confirm-modal-title').textContent = title;
        document.getElementById('confirm-modal-message').textContent = message;

        const confirmBtn = document.getElementById('confirm-modal-btn');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', () => {
            Simapo.modal('confirm-modal').hide();
            onConfirm();
        });

        Simapo.modal('confirm-modal').show();
    },

    async fetch(url, options = {}) {
        const defaultHeaders = {
            'X-CSRF-TOKEN': this.csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        if (!(options.body instanceof FormData)) {
            defaultHeaders['Content-Type'] = 'application/json';
        }

        const config = {
            ...options,
            headers: {
                ...defaultHeaders,
                ...(options.headers || {})
            }
        };

        try {
            const res = await window.fetch(url, config);
            const data = await res.json().catch(() => ({}));
            return { ok: res.ok, status: res.status, data };
        } catch (error) {
            console.error('Fetch error:', error);
            return { ok: false, status: 500, data: { message: 'Terjadi kesalahan jaringan atau koneksi terputus.' } };
        }
    },

    // Notifikasi In-App Polling
    initNotifications() {
        this.fetchNotifications();
        setInterval(() => this.fetchNotifications(), 45000);
    },

    async fetchNotifications() {
        const badge = document.getElementById('notif-badge');
        const list = document.getElementById('notif-list');
        if (!badge || !list) return;

        const res = await this.fetch('/notifikasi/unread');
        if (res.ok && res.data.success) {
            const count = res.data.count || 0;
            const items = res.data.items || [];

            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            if (items.length === 0) {
                list.innerHTML = `<div class="p-6 text-center text-sm text-gray-500">Tidak ada notifikasi baru</div>`;
            } else {
                list.innerHTML = items.map(item => `
                    <a href="${item.link || '#'}" class="block p-3 hover:bg-emerald-50/50 border-b border-gray-100 transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-xs font-semibold text-emerald-800">${item.judul}</span>
                            <span class="text-[10px] text-gray-400 whitespace-nowrap">${item.waktu}</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">${item.pesan}</p>
                    </a>
                `).join('');
            }
        }
    },

    async markAllRead() {
        const res = await this.fetch('/notifikasi/mark-all-read', { method: 'POST' });
        if (res.ok) {
            this.fetchNotifications();
            this.toast('Semua notifikasi ditandai dibaca.', 'success');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('notif-badge')) {
        Simapo.initNotifications();
    }
});
