<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use App\Models\Obat;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class CekStokCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifikasi:cek-stok {--force : Abaikan anti-spam dan kirim paksa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengecekan stok obat menipis (<= minimum) dan stok kosong (= 0) serta kirim notifikasi Telegram';

    public function handle(TelegramService $telegramService): int
    {
        $this->info('Memulai pengecekan stok obat...');
        $force = (bool) $this->option('force');

        $obats = Obat::with('satuan')->get();
        $stokKosong = [];
        $stokMenipis = [];

        foreach ($obats as $obat) {
            $totalStok = $obat->total_stok;

            if ($totalStok <= 0) {
                $stokKosong[] = $obat;
            } elseif ($totalStok <= $obat->stok_minimum) {
                $stokMenipis[] = $obat;
            }
        }

        // 1. Notifikasi Stok Kosong
        if (count($stokKosong) > 0) {
            $this->warn('Ditemukan '.count($stokKosong).' obat dengan stok KOSONG.');

            // In-app notification
            Notifikasi::create([
                'role_target' => 'owner,apoteker,admin_gudang',
                'judul' => '🚨 Obat Stok Kosong (0)',
                'pesan' => 'Terdapat '.count($stokKosong).' item obat dengan stok habis (0). Segera buat Purchase Order pengadaan.',
                'tipe' => 'stok_kosong',
                'link' => '/pengadaan/rekomendasi',
            ]);

            // Telegram message (digest)
            $msg = "🚨 <b>STOK KOSONG - Apotek Bintan 88</b>\n";
            $msg .= 'Terdapat <b>'.count($stokKosong)."</b> produk obat dengan stok HABIS (0):\n";
            foreach (array_slice($stokKosong, 0, 7) as $o) {
                $satuan = $o->satuan->nama ?? 'Pcs';
                $msg .= "- {$o->nama} (Rak: {$o->lokasi_rak})\n";
            }
            if (count($stokKosong) > 7) {
                $msg .= '...dan '.(count($stokKosong) - 7)." obat lainnya.\n";
            }
            $msg .= 'Segera buat Purchase Order (PO) ke distributor farmasi.';

            $res = $telegramService->kirimPesan($msg, 'stok_kosong', 'stok_batch_kosong', date('Ymd'), null, $force);
            $this->line("Telegram Status: {$res['status']} - {$res['message']}");
        }

        // 2. Notifikasi Stok Menipis
        if (count($stokMenipis) > 0) {
            $this->warn('Ditemukan '.count($stokMenipis).' obat dengan stok MENIPIS.');

            Notifikasi::create([
                'role_target' => 'owner,apoteker,admin_gudang',
                'judul' => '⚠️ Obat Stok Menipis',
                'pesan' => 'Terdapat '.count($stokMenipis).' item obat mencapai batas stok minimum. Periksa rekomendasi pemesanan.',
                'tipe' => 'stok_menipis',
                'link' => '/pengadaan/rekomendasi',
            ]);

            $msg = "⚠️ <b>STOK MENIPIS - Apotek Bintan 88</b>\n";
            $msg .= 'Terdapat <b>'.count($stokMenipis)."</b> produk obat mendekati batas minimum:\n";
            foreach (array_slice($stokMenipis, 0, 7) as $o) {
                $satuan = $o->satuan->nama ?? 'Pcs';
                $msg .= "- {$o->nama}: sisa {$o->total_stok} {$satuan} (Min: {$o->stok_minimum})\n";
            }
            if (count($stokMenipis) > 7) {
                $msg .= '...dan '.(count($stokMenipis) - 7)." obat lainnya.\n";
            }
            $msg .= 'Rekomendasi pemesanan tersedia di sistem.';

            $res = $telegramService->kirimPesan($msg, 'stok_menipis', 'stok_batch_menipis', date('Ymd'), null, $force);
            $this->line("Telegram Status: {$res['status']} - {$res['message']}");
        }

        if (count($stokKosong) === 0 && count($stokMenipis) === 0) {
            $this->info('Seluruh stok obat berada dalam kondisi aman.');
        }

        return Command::SUCCESS;
    }
}
