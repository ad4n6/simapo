<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pengaturan;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService
    ) {}

    /**
     * Halaman Pengaturan Sistem & Notifikasi Telegram
     */
    public function index(): View
    {
        $settings = Pengaturan::pluck('nilai', 'kunci')->toArray();

        return view('pengaturan.index', compact('settings'));
    }

    /**
     * Simpan Pengaturan
     */
    public function update(Request $request): RedirectResponse
    {
        $inputs = $request->except(['_token']);

        // Handle checkboxes for notification toggles (if unchecked in form, set to '0')
        $notifKeys = [
            'notif_stok_menipis',
            'notif_stok_kosong',
            'notif_obat_hampir_expired',
            'notif_obat_expired',
            'notif_hutang_jatuh_tempo',
            'notif_po_terlambat',
            'notif_ringkasan_harian',
            'notif_void_retur',
            'notif_penerimaan',
            'notif_opname_selisih',
            'notif_selisih_shift',
            'notif_login_gagal',
        ];

        foreach ($notifKeys as $key) {
            $inputs[$key] = $request->has($key) ? '1' : '0';
        }

        foreach ($inputs as $key => $val) {
            Pengaturan::setValue($key, (string) $val);
        }

        ActivityLog::record('update_pengaturan', 'Memperbarui konfigurasi sistem dan notifikasi Telegram apotek.');

        return back()->with('success', 'Pengaturan sistem & notifikasi berhasil disimpan!');
    }

    /**
     * Kirim Pesan Tes Telegram (AJAX)
     */
    public function testTelegram(Request $request): JsonResponse
    {
        $testMsg = "🤖 <b>TES KONEKSI BOT TELEGRAM - SIMAPO Bintan 88</b>\n";
        $testMsg .= 'Waktu Kirim: '.now()->translatedFormat('d F Y - H:i:s')." WIB\n";
        $testMsg .= "Status Koneksi: <b>BERHASIL TERHUBUNG</b>\n";
        $testMsg .= 'Sistem notifikasi otomatis siap memberikan informasi operasional apotek Anda.';

        $chatId = $request->input('chat_id');
        $result = $this->telegramService->kirimPesan($testMsg, 'tes', 'test_ping', null, $chatId, true);

        return response()->json($result);
    }

    /**
     * Jalankan Pengecekan Manual Tanpa Cron (AJAX / Demo Mode)
     */
    public function runCheck(Request $request): JsonResponse
    {
        $command = $request->input('command', 'cek-stok');

        $allowed = [
            'cek-stok' => 'notifikasi:cek-stok',
            'cek-expired' => 'notifikasi:cek-expired',
            'cek-hutang' => 'notifikasi:cek-hutang',
            'ringkasan-harian' => 'notifikasi:ringkasan-harian',
        ];

        if (! isset($allowed[$command])) {
            return response()->json(['success' => false, 'message' => 'Perintah tidak dikenal.'], 400);
        }

        $artisanCmd = $allowed[$command];
        Artisan::call($artisanCmd, ['--force' => true]);
        $output = Artisan::output();

        ActivityLog::record('manual_run_check', "Menjalankan pengecekan notifikasi manual: {$artisanCmd}");

        return response()->json([
            'success' => true,
            'message' => "Pengecekan {$command} berhasil dieksekusi!",
            'output' => $output,
        ]);
    }
}
