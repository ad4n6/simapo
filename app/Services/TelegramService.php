<?php

namespace App\Services;

use App\Models\NotifikasiLog;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Dapatkan Bot Token dari database Pengaturan atau .env
     */
    public function getBotToken(): ?string
    {
        $token = Pengaturan::getValue('telegram_bot_token');
        if (empty($token)) {
            $token = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        }

        return ! empty($token) ? trim($token) : null;
    }

    /**
     * Dapatkan Default Group Chat ID dari database Pengaturan atau .env
     */
    public function getDefaultChatId(): ?string
    {
        $chatId = Pengaturan::getValue('telegram_chat_id');
        if (empty($chatId)) {
            $chatId = config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID'));
        }

        return ! empty($chatId) ? trim($chatId) : null;
    }

    /**
     * Cek apakah jenis notifikasi aktif di pengaturan
     */
    public function isNotificationEnabled(string $jenis): bool
    {
        $key = 'notif_'.$jenis;
        $val = Pengaturan::getValue($key);

        return $val === null || $val === '1' || $val === true || $val === 1;
    }

    /**
     * Kirim pesan Telegram ke chat ID tertentu atau grup apotek default.
     * Menggunakan anti-spam: jenis + entitas sama tidak dikirim berulang pada hari yang sama.
     */
    public function kirimPesan(
        string $pesanHtml,
        string $jenis = 'umum',
        ?string $entitasTipe = null,
        ?int $entitasId = null,
        ?string $chatId = null,
        bool $forceSend = false,
        ?string $botToken = null
    ): array {
        $token = ! empty($botToken) ? trim($botToken) : $this->getBotToken();
        $targetChatId = $chatId ?: $this->getDefaultChatId();

        // Cek switch pengaturan jika bukan tes manual
        if ($jenis !== 'tes' && ! $this->isNotificationEnabled($jenis)) {
            return [
                'success' => false,
                'status' => 'skipped',
                'message' => "Notifikasi jenis '{$jenis}' dinonaktifkan di pengaturan.",
            ];
        }

        // Anti-spam check (hanya kirim 1x per hari untuk entitas yang sama)
        if (! $forceSend && $entitasTipe && $entitasId) {
            $today = Carbon::today();
            $alreadySent = NotifikasiLog::where('jenis', $jenis)
                ->where('entitas_tipe', $entitasTipe)
                ->where('entitas_id', $entitasId)
                ->where('status', 'terkirim')
                ->whereDate('created_at', $today)
                ->exists();

            if ($alreadySent) {
                return [
                    'success' => false,
                    'status' => 'skipped',
                    'message' => 'Notifikasi sudah dikirim hari ini untuk entitas ini (Anti-Spam).',
                ];
            }
        }

        if (empty($token) || empty($targetChatId)) {
            $errMsg = 'Bot Token atau Chat ID Telegram belum dikonfigurasi di Pengaturan Apotek atau .env';

            NotifikasiLog::create([
                'jenis' => $jenis,
                'pesan' => $pesanHtml,
                'tujuan' => $targetChatId ?: 'Belum disetel',
                'status' => 'gagal',
                'response_error' => $errMsg,
                'entitas_tipe' => $entitasTipe,
                'entitas_id' => $entitasId,
            ]);

            return [
                'success' => false,
                'status' => 'gagal',
                'message' => $errMsg,
            ];
        }

        try {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";

            $response = Http::timeout(10)->post($url, [
                'chat_id' => $targetChatId,
                'text' => $pesanHtml,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            $isOk = $response->successful() && ($response->json('ok') === true);
            $errorDetail = $isOk ? null : ($response->json('description') ?? $response->body());

            NotifikasiLog::create([
                'jenis' => $jenis,
                'pesan' => $pesanHtml,
                'tujuan' => $targetChatId,
                'status' => $isOk ? 'terkirim' : 'gagal',
                'response_error' => $errorDetail,
                'entitas_tipe' => $entitasTipe,
                'entitas_id' => $entitasId,
            ]);

            return [
                'success' => $isOk,
                'status' => $isOk ? 'terkirim' : 'gagal',
                'message' => $isOk ? 'Pesan Telegram berhasil terkirim!' : "Telegram API Error: {$errorDetail}",
            ];
        } catch (\Throwable $e) {
            Log::error('Telegram Service Exception: '.$e->getMessage());

            NotifikasiLog::create([
                'jenis' => $jenis,
                'pesan' => $pesanHtml,
                'tujuan' => $targetChatId,
                'status' => 'gagal',
                'response_error' => $e->getMessage(),
                'entitas_tipe' => $entitasTipe,
                'entitas_id' => $entitasId,
            ]);

            return [
                'success' => false,
                'status' => 'gagal',
                'message' => 'Koneksi ke Telegram gagal: '.$e->getMessage(),
            ];
        }
    }
}
