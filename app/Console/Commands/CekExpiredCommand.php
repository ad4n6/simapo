<?php

namespace App\Console\Commands;

use App\Models\BatchObat;
use App\Models\Notifikasi;
use App\Models\Pengaturan;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CekExpiredCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifikasi:cek-expired {--force : Abaikan anti-spam dan kirim paksa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengecekan obat kedaluwarsa dan hampir kedaluwarsa (30/60/90 hari) serta kirim notifikasi Telegram';

    public function handle(TelegramService $telegramService): int
    {
        $this->info('Memulai pengecekan obat kedaluwarsa & hampir kedaluwarsa...');
        $force = (bool) $this->option('force');
        $today = Carbon::today();

        // 1. Cek Batch yang SUDAH Kedaluwarsa (Trigger #4)
        $expiredBatches = BatchObat::with(['obat.satuan', 'suplier'])
            ->where('tanggal_kedaluwarsa', '<=', $today)
            ->where('jumlah_saat_ini', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->get();

        if ($expiredBatches->count() > 0) {
            $this->error('Ditemukan '.$expiredBatches->count().' batch obat SUDAH KEDALUWARSA!');

            Notifikasi::create([
                'role_target' => 'owner,apoteker,admin_gudang',
                'judul' => '🛑 Obat Sudah Kedaluwarsa',
                'pesan' => "Terdapat {$expiredBatches->count()} batch obat kedaluwarsa yang masih memiliki stok fisik. Segera karantina dan musnahkan.",
                'tipe' => 'obat_expired',
                'link' => '/stok?filter=expired',
            ]);

            $msg = "🛑 <b>OBAT KEDALUWARSA - Apotek Bintan 88</b>\n";
            $msg .= "PERHATIAN: Ditemukan <b>{$expiredBatches->count()}</b> batch obat sudah lewat tanggal ED dan TIDAK BOLEH DIJUAL:\n";
            foreach ($expiredBatches->take(6) as $b) {
                $ed = Carbon::parse($b->tanggal_kedaluwarsa)->format('d/m/Y');
                $satuan = $b->obat->satuan->nama ?? 'Pcs';
                $msg .= "- {$b->obat->nama} [Batch: {$b->no_batch}] - {$b->jumlah_saat_ini} {$satuan} (ED: {$ed})\n";
            }
            if ($expiredBatches->count() > 6) {
                $msg .= '...dan '.($expiredBatches->count() - 6)." batch obat lainnya.\n";
            }
            $msg .= 'Segera lakukan pemusnahan obat kedaluwarsa atau retur ke suplier.';

            $res = $telegramService->kirimPesan($msg, 'obat_expired', 'batch_expired_digest', date('Ymd'), null, $force);
            $this->line("Telegram Status: {$res['status']} - {$res['message']}");
        }

        // 2. Cek Batch Hampir Kedaluwarsa (Trigger #3)
        // Ambil ambang hari dari pengaturan, default 30 hari
        $ambangHari = (int) (Pengaturan::getValue('ambang_hari_expired') ?? 30);
        $targetDate = Carbon::today()->addDays($ambangHari);

        $nearExpiredBatches = BatchObat::with(['obat.satuan', 'suplier'])
            ->where('tanggal_kedaluwarsa', '>', $today)
            ->where('tanggal_kedaluwarsa', '<=', $targetDate)
            ->where('jumlah_saat_ini', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->get();

        if ($nearExpiredBatches->count() > 0) {
            $this->warn("Ditemukan {$nearExpiredBatches->count()} batch obat hampir kedaluwarsa (<= {$ambangHari} hari).");

            Notifikasi::create([
                'role_target' => 'owner,apoteker,admin_gudang',
                'judul' => '⏳ Obat Hampir Kedaluwarsa',
                'pesan' => "Terdapat {$nearExpiredBatches->count()} batch obat akan kedaluwarsa dalam {$ambangHari} hari ke depan. Pertimbangkan promo/retur.",
                'tipe' => 'obat_hampir_expired',
                'link' => '/stok?filter=hampir_expired',
            ]);

            $msg = "⏳ <b>OBAT HAMPIR KEDALUWARSA (≤ {$ambangHari} Hari) - Apotek Bintan 88</b>\n";
            $msg .= "Ditemukan <b>{$nearExpiredBatches->count()}</b> batch obat mendekati expired:\n";
            foreach ($nearExpiredBatches->take(6) as $b) {
                $sisaHari = (int) $today->diffInDays(Carbon::parse($b->tanggal_kedaluwarsa));
                $ed = Carbon::parse($b->tanggal_kedaluwarsa)->format('d/m/Y');
                $satuan = $b->obat->satuan->nama ?? 'Pcs';
                $msg .= "- {$b->obat->nama} [Batch: {$b->no_batch}]: sisa {$b->jumlah_saat_ini} {$satuan} ({$sisaHari} hari lagi / {$ed})\n";
            }
            if ($nearExpiredBatches->count() > 6) {
                $msg .= '...dan '.($nearExpiredBatches->count() - 6)." batch obat lainnya.\n";
            }
            $msg .= 'Saran: Terapkan diskon atau ajukan retur ke PBF.';

            $res = $telegramService->kirimPesan($msg, 'obat_hampir_expired', 'batch_near_expired_digest', date('Ymd'), null, $force);
            $this->line("Telegram Status: {$res['status']} - {$res['message']}");
        }

        if ($expiredBatches->count() === 0 && $nearExpiredBatches->count() === 0) {
            $this->info('Tidak ada obat kedaluwarsa atau mendekati kedaluwarsa.');
        }

        return Command::SUCCESS;
    }
}
