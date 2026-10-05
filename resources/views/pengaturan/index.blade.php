@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Notifikasi Telegram')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Pengaturan Sistem & Notifikasi Telegram</h1>
        <p class="text-xs text-gray-500 mt-1">Konfigurasi bot Telegram, ambang batas kedaluwarsa, identitas apotek, dan saklar 12 jenis notifikasi otomatis.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('notifikasi.riwayat') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-gray-200 hover:bg-gray-50 shadow-xs transition-colors flex items-center gap-1.5">
            <span>📋 Riwayat Log Telegram</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Manual Execution / Demo Box -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 text-white shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold mb-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>FITUR DEMO PENGUJIAN</span>
                </div>
                <h2 class="text-lg font-bold">Jalankan Pengecekan & Notifikasi Manual (Tanpa Menunggu Cron)</h2>
                <p class="text-xs text-slate-300 mt-0.5">Jalankan command pengecekan secara on-demand untuk demonstrasi sidang/tugas kuliah.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="runManualCheck('cek-stok')" class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-600">
                    <span>📦 Cek Stok</span>
                </button>
                <button type="button" onclick="runManualCheck('cek-expired')" class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-600">
                    <span>⏳ Cek Expired</span>
                </button>
                <button type="button" onclick="runManualCheck('cek-hutang')" class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-600">
                    <span>💳 Cek Hutang & PO</span>
                </button>
                <button type="button" onclick="runManualCheck('ringkasan-harian')" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                    <span>📊 Ringkasan Harian</span>
                </button>
            </div>
        </div>

        <div class="bg-slate-950/60 p-3 rounded-2xl border border-slate-800 text-[11px] font-mono text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <span>⚙️ Setup VPS Cron: <code>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code></span>
            <span class="text-emerald-400 font-bold">Status Scheduler: Aktif</span>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('pengaturan.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Telegram Bot Configuration -->
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>🤖 Integrasi Telegram Bot API</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Konfigurasi API token bot dan chat ID grup untuk menerima notifikasi real-time.</p>
                </div>
                <button type="button" onclick="testKirimTelegram()" id="btnTestTelegram"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors flex items-center gap-1.5 self-start">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Kirim Pesan Tes Telegram</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Telegram Bot Token <span class="text-rose-500">*</span></label>
                    <input type="text" name="telegram_bot_token" id="inputBotToken" value="{{ $settings['telegram_bot_token'] ?? env('TELEGRAM_BOT_TOKEN') }}" placeholder="123456789:ABCdefGHIjklMNOpqrSTUvwxyz"
                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-medium text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">Dapatkan token dari <strong>@BotFather</strong> di aplikasi Telegram.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Telegram Group Chat ID <span class="text-rose-500">*</span></label>
                    <input type="text" name="telegram_chat_id" id="inputChatId" value="{{ $settings['telegram_chat_id'] ?? env('TELEGRAM_CHAT_ID') }}" placeholder="-100xxxxxxxxxx atau ID user Anda"
                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-medium text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">Chat ID grup/channel penerima notifikasi (bisa didapat lewat bot @userinfobot).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Ambang Peringatan Obat Hampir Kedaluwarsa (Hari)</label>
                    <input type="number" name="ambang_hari_expired" value="{{ $settings['ambang_hari_expired'] ?? 30 }}" min="7" max="180"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-gray-400 mt-1">Peringatan otomatis muncul bila tanggal ED kurang dari jumlah hari ini (Default: 30 hari).</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Pengiriman Ringkasan Harian</label>
                    <input type="time" name="jam_ringkasan_harian" value="{{ $settings['jam_ringkasan_harian'] ?? '21:00' }}"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 outline-none focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-gray-400 mt-1">Waktu pengiriman laporan omset dan top obat ke Telegram Owner setiap malam.</p>
                </div>
            </div>
        </div>

        <!-- Section 2: 12 Saklar Jenis Notifikasi -->
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-xs space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-bold text-gray-900">Saklar Aktif / Nonaktif 12 Pemicu Notifikasi</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pilih notifikasi mana saja yang diizinkan dikirim ke Telegram apotek.</p>
            </div>

            @php
                $triggers = [
                    ['key' => 'notif_stok_menipis', 'title' => '1. Stok Obat Menipis', 'desc' => 'Kirim saat total stok obat <= stok minimum', 'icon' => '⚠️'],
                    ['key' => 'notif_stok_kosong', 'title' => '2. Stok Obat Kosong (Habis)', 'desc' => 'Kirim saat stok obat menyentuh angka 0', 'icon' => '🚨'],
                    ['key' => 'notif_obat_hampir_expired', 'title' => '3. Obat Hampir Kedaluwarsa', 'desc' => 'Peringatan bertingkat (≤ 30/60/90 hari)', 'icon' => '⏳'],
                    ['key' => 'notif_obat_expired', 'title' => '4. Obat Sudah Kedaluwarsa', 'desc' => 'Peringatan keras jika ada batch lewat tanggal ED', 'icon' => '🛑'],
                    ['key' => 'notif_hutang_jatuh_tempo', 'title' => '5. Hutang Suplier Jatuh Tempo', 'desc' => 'Kirim saat H-3, H-1, atau sudah lewat batas tempo', 'icon' => '💳'],
                    ['key' => 'notif_po_terlambat', 'title' => '6. Purchase Order (PO) Terlambat', 'desc' => 'PO belum diterima melebihi estimasi kedatangan', 'icon' => '📦'],
                    ['key' => 'notif_ringkasan_harian', 'title' => '7. Ringkasan Penjualan Harian', 'desc' => 'Laporan omset harian, jumlah transaksi & top obat', 'icon' => '📊'],
                    ['key' => 'notif_void_retur', 'title' => '8. Transaksi VOID & Retur Penjualan', 'desc' => 'Pemberitahuan real-time saat transaksi dibatalkan', 'icon' => '↩️'],
                    ['key' => 'notif_penerimaan', 'title' => '9. Penerimaan Barang Berhasil', 'desc' => 'Pemberitahuan real-time saat faktur masuk & stok bertambah', 'icon' => '🚚'],
                    ['key' => 'notif_opname_selisih', 'title' => '10. Selisih Stock Opname Signifikan', 'desc' => 'Kirim ke Owner saat ditemukan selisih fisik obat', 'icon' => '🔍'],
                    ['key' => 'notif_selisih_shift', 'title' => '11. Selisih Kas Tutup Shift Kasir', 'desc' => 'Kirim saat uang fisik laci tidak cocok dengan sistem', 'icon' => '💵'],
                    ['key' => 'notif_login_gagal', 'title' => '12. Login Gagal / Mencurigakan', 'desc' => 'Peringatan jika ada percobaan login yang tidak valid', 'icon' => '🛡️'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                @foreach($triggers as $t)
                    @php
                        $isActive = isset($settings[$t['key']]) ? ($settings[$t['key']] === '1') : true;
                    @endphp
                    <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-gray-100 hover:bg-slate-50/60 transition-colors cursor-pointer select-none">
                        <input type="checkbox" name="{{ $t['key'] }}" value="1" {{ $isActive ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 mt-1">
                        <div>
                            <div class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                <span>{{ $t['icon'] }}</span>
                                <span>{{ $t['title'] }}</span>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $t['desc'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Section 3: Identitas Apotek (Kop Struk & Laporan) -->
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-xs space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-bold text-gray-900">Identitas Apotek & Informasi Struk</h2>
                <p class="text-xs text-gray-500 mt-0.5">Informasi legalitas dan kontak yang tercetak di struk thermal dan dokumen PDF.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Apotek</label>
                    <input type="text" name="nama_apotek" value="{{ $settings['nama_apotek'] ?? 'Apotek Bintan 88' }}"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon / WhatsApp</label>
                    <input type="text" name="telepon_apotek" value="{{ $settings['telepon_apotek'] ?? '(0771) 888-888' }}"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Apoteker Pengelola Apotek (APA)</label>
                    <input type="text" name="nama_apoteker" value="{{ $settings['nama_apoteker'] ?? 'apt. Hendra Wijaya, S.Farm.' }}"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Surat Izin Apotek (SIA)</label>
                    <input type="text" name="no_sia" value="{{ $settings['no_sia'] ?? '503/SIA-088/DPMPTSP/2024' }}"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Surat Izin Praktik Apoteker (SIPA)</label>
                    <input type="text" name="no_sipa" value="{{ $settings['no_sipa'] ?? '19890515/SIPA_2172/2024/2001' }}"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Apotek</label>
                    <input type="text" name="alamat_apotek" value="{{ $settings['alamat_apotek'] ?? 'Jl. Bintan No. 88, Tanjungpinang, Kepulauan Riau' }}"
                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Seluruh Pengaturan</span>
            </button>
        </div>
    </form>

</div>

<!-- Modal Output Console Execution -->
<div id="modalConsoleOutput" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 text-white rounded-3xl shadow-2xl border border-slate-800 max-w-lg w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-emerald-400 font-mono" id="consoleTitle">Output Eksekusi Command</h3>
            <button onclick="document.getElementById('modalConsoleOutput').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
        </div>
        <pre class="bg-slate-950 p-4 rounded-2xl text-xs font-mono text-emerald-300 overflow-x-auto whitespace-pre-wrap max-h-80" id="consoleBody"></pre>
        <div class="flex justify-end">
            <button onclick="document.getElementById('modalConsoleOutput').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-xl">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    async function testKirimTelegram() {
        const btn = document.getElementById('btnTestTelegram');
        const token = document.getElementById('inputBotToken') ? document.getElementById('inputBotToken').value.trim() : '';
        const chatId = document.getElementById('inputChatId') ? document.getElementById('inputChatId').value.trim() : '';

        btn.disabled = true;
        btn.innerHTML = 'Mengirim Tes...';

        try {
            const resp = await Simapo.fetch('{{ route("pengaturan.testTelegram") }}', {
                method: 'POST',
                body: JSON.stringify({ chat_id: chatId, bot_token: token })
            });

            const isSuccess = resp.success || (resp.ok && resp.data && resp.data.success);
            const message = resp.message || resp.data?.message;

            if (isSuccess) {
                Simapo.toast(message || 'Pesan tes berhasil terkirim ke Telegram!', 'success');
            } else {
                Simapo.toast(message || 'Gagal mengirim pesan tes Telegram.', 'error');
            }
        } catch (e) {
            Simapo.toast('Terjadi kesalahan saat memanggil API Telegram.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Kirim Pesan Tes Telegram</span>
            `;
        }
    }

    async function runManualCheck(command) {
        Simapo.toast(`Menjalankan pengecekan "${command}"...`, 'info');

        try {
            const resp = await Simapo.fetch('{{ route("pengaturan.runCheck") }}', {
                method: 'POST',
                body: JSON.stringify({ command: command })
            });

            const isSuccess = resp.success || (resp.ok && resp.data && resp.data.success);
            const output = resp.output || resp.data?.output;
            const message = resp.message || resp.data?.message;

            if (isSuccess) {
                document.getElementById('consoleTitle').innerText = `Hasil Eksekusi: notifikasi:${command}`;
                document.getElementById('consoleBody').innerText = output || 'Command selesai dieksekusi tanpa output.';
                document.getElementById('modalConsoleOutput').classList.remove('hidden');
                Simapo.toast(`Pengecekan ${command} berhasil dijalankan!`, 'success');
            } else {
                Simapo.toast(message || 'Gagal mengeksekusi perintah.', 'error');
            }
        } catch (e) {
            Simapo.toast('Gagal mengeksekusi artisan command.', 'error');
        }
    }
</script>
@endsection
