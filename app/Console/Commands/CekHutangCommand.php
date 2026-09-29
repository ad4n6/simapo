<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use App\Models\Pembelian;
use App\Models\PurchaseOrder;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CekHutangCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifikasi:cek-hutang {--force : Abaikan anti-spam dan kirim paksa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengecekan hutang suplier jatuh tempo (H-3, H-1, lewat tempo) dan PO terlambat serta kirim Telegram';

    public function handle(TelegramService $telegramService): int
    {
        $this->info('Memulai pengecekan hutang suplier & status PO...');
        $force = (bool) $this->option('force');
        $today = Carbon::today();

        // 1. Cek Hutang Suplier yang LEWAT TEMPO (Overdue)
        $overdueHutangs = Pembelian::with('suplier')
            ->where('jenis_pembayaran', 'kredit')
            ->where('status_pembayaran', 'belum_lunas')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->get();

        if ($overdueHutangs->count() > 0) {
            $totalOverdue = $overdueHutangs->sum('sisa_hutang');
            $this->error("Ditemukan {$overdueHutangs->count()} faktur hutang LEWAT TEMPO!");

            Notifikasi::create([
                'role_target' => 'owner,apoteker',
                'judul' => '⚠️ Hutang Suplier Lewat Tempo!',
                'pesan' => "Terdapat {$overdueHutangs->count()} faktur telah melewati batas jatuh tempo senilai Rp ".number_format($totalOverdue, 0, ',', '.').'. Segera selesaikan pembayaran.',
                'tipe' => 'hutang_jatuh_tempo',
                'link' => '/pengadaan/pembelian/hutang',
            ]);

            $msg = "⚠️ <b>HUTANG SUPLIER LEWAT JATUH TEMPO - Apotek Bintan 88</b>\n";
            $msg .= "PERINGATAN: <b>{$overdueHutangs->count()}</b> faktur distributor telah melewati jatuh tempo:\n";
            foreach ($overdueHutangs->take(5) as $h) {
                $days = (int) $today->diffInDays(Carbon::parse($h->tanggal_jatuh_tempo));
                $tempo = Carbon::parse($h->tanggal_jatuh_tempo)->format('d/m/Y');
                $msg .= "- {$h->no_faktur} ({$h->suplier->nama}): Sisa Rp ".number_format($h->sisa_hutang, 0, ',', '.')." (Lewat {$days} hari / {$tempo})\n";
            }
            if ($overdueHutangs->count() > 5) {
                $msg .= '...dan '.($overdueHutangs->count() - 5)." faktur lainnya.\n";
            }
            $msg .= 'Total Tertunggak: <b>Rp '.number_format($totalOverdue, 0, ',', '.').'</b>';

            $res = $telegramService->kirimPesan($msg, 'hutang_jatuh_tempo', 'hutang_overdue_digest', date('Ymd'), null, $force);
            $this->line("Telegram Status: {$res['status']} - {$res['message']}");
        }

        // 2. Cek Hutang Suplier Menjelang Jatuh Tempo (H-3 dan H-1)
        $h3Date = Carbon::today()->addDays(3);
        $dueSoonHutangs = Pembelian::with('suplier')
            ->where('jenis_pembayaran', 'kredit')
            ->where('status_pembayaran', 'belum_lunas')
            ->whereBetween('tanggal_jatuh_tempo', [$today, $h3Date])
            ->get();

        if ($dueSoonHutangs->count() > 0) {
            $this->warn("Ditemukan {$dueSoonHutangs->count()} faktur hutang mendekati jatuh tempo (<= 3 hari).");

            $msg = "💳 <b>PENGINGAT HUTANG SUPLIER (H-3) - Apotek Bintan 88</b>\n";
            $msg .= "Daftar faktur yang akan jatuh tempo dalam 3 hari ke depan:\n";
            foreach ($dueSoonHutangs->take(5) as $h) {
                $days = (int) $today->diffInDays(Carbon::parse($h->tanggal_jatuh_tempo));
                $tempo = Carbon::parse($h->tanggal_jatuh_tempo)->format('d/m/Y');
                $msg .= "- {$h->no_faktur} ({$h->suplier->nama}): Rp ".number_format($h->sisa_hutang, 0, ',', '.')." ({$days} hari lagi / {$tempo})\n";
            }
            $msg .= 'Harap siapkan dana kas atau transfer perbankan.';

            $res = $telegramService->kirimPesan($msg, 'hutang_jatuh_tempo', 'hutang_soon_digest', date('Ymd'), null, $force);
            $this->line("Telegram Status: {$res['status']} - {$res['message']}");
        }

        // 3. Trigger #6: Cek Purchase Order (PO) belum diterima melebihi estimasi
        $latePOs = PurchaseOrder::with('suplier')
            ->whereIn('status', ['dipesan', 'diterima_sebagian'])
            ->whereNotNull('estimasi_tanggal_terima')
            ->where('estimasi_tanggal_terima', '<', $today)
            ->get();

        if ($latePOs->count() > 0) {
            $this->warn("Ditemukan {$latePOs->count()} PO melebihi estimasi kedatangan!");

            Notifikasi::create([
                'role_target' => 'owner,admin_gudang,apoteker',
                'judul' => '📦 PO Belum Diterima (Terlambat)',
                'pesan' => "Terdapat {$latePOs->count()} Purchase Order telah melewati estimasi pengiriman distributor. Hubungi PBF terkait.",
                'tipe' => 'po_terlambat',
                'link' => '/pengadaan/po',
            ]);

            $msg = "📦 <b>PURCHASE ORDER TERLAMBAT - Apotek Bintan 88</b>\n";
            $msg .= "Terdapat <b>{$latePOs->count()}</b> PO belum diterima melebihi estimasi tanggal kirim:\n";
            foreach ($latePOs->take(5) as $po) {
                $est = Carbon::parse($po->estimasi_tanggal_terima)->format('d/m/Y');
                $msg .= "- {$po->no_po} ({$po->suplier->nama}) - Est: {$est}\n";
            }
            $msg .= 'Harap konfirmasi ke pihak distributor PBF.';

            $res = $telegramService->kirimPesan($msg, 'po_terlambat', 'po_late_digest', date('Ymd'), null, $force);
            $this->line("Telegram Status: {$res['status']} - {$res['message']}");
        }

        return Command::SUCCESS;
    }
}
