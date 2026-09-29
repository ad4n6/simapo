<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RingkasanHarianCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifikasi:ringkasan-harian {--force : Abaikan anti-spam dan kirim paksa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim laporan ringkasan penjualan harian apotek ke Owner via Telegram';

    public function handle(TelegramService $telegramService): int
    {
        $this->info('Menyiapkan ringkasan penjualan harian...');
        $force = (bool) $this->option('force');
        $today = Carbon::today();

        // 1. Ambil data penjualan selesai hari ini
        $penjualanHariIni = Penjualan::whereDate('created_at', $today)
            ->where('status', 'selesai')
            ->get();

        $totalOmset = $penjualanHariIni->sum('total_akhir');
        $totalTransaksi = $penjualanHariIni->count();
        $trxResep = $penjualanHariIni->where('jenis_transaksi', 'resep')->count();
        $trxBebas = $penjualanHariIni->where('jenis_transaksi', 'bebas')->count();

        // 2. Top obat terlaris hari ini
        $topObats = PenjualanDetail::select('obat_id', DB::raw('SUM(jumlah) as total_qty'), DB::raw('SUM(subtotal) as total_subtotal'))
            ->whereHas('penjualan', function ($q) use ($today) {
                $q->whereDate('created_at', $today)->where('status', 'selesai');
            })
            ->with('obat.satuan')
            ->groupBy('obat_id')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        // 3. Status stok menipis / kosong
        $stokMenipisCount = Obat::all()->filter(fn ($o) => $o->total_stok <= $o->stok_minimum && $o->total_stok > 0)->count();
        $stokKosongCount = Obat::all()->filter(fn ($o) => $o->total_stok == 0)->count();

        // Buat Pesan Telegram Ringkasan Harian (HTML)
        $tglFormat = $today->translatedFormat('l, d F Y');
        $msg = "📊 <b>RINGKASAN PENJUALAN HARIAN - Apotek Bintan 88</b>\n";
        $msg .= "Tanggal: {$tglFormat}\n\n";

        $msg .= "💰 <b>Kinerja Keuangan:</b>\n";
        $msg .= '- Total Omset: <b>Rp '.number_format($totalOmset, 0, ',', '.')."</b>\n";
        $msg .= "- Transaksi: <b>{$totalTransaksi}</b> nota ({$trxBebas} bebas, {$trxResep} resep)\n\n";

        if ($topObats->count() > 0) {
            $msg .= "🏆 <b>Top Obat Terlaris Hari Ini:</b>\n";
            foreach ($topObats as $idx => $t) {
                $satuan = $t->obat->satuan->nama ?? 'Pcs';
                $msg .= ($idx + 1).". {$t->obat->nama}: {$t->total_qty} {$satuan} (Rp ".number_format($t->total_subtotal, 0, ',', '.').")\n";
            }
            $msg .= "\n";
        }

        $msg .= "📦 <b>Status Inventori Apotek:</b>\n";
        $msg .= "- Obat Stok Menipis: {$stokMenipisCount} produk\n";
        $msg .= "- Obat Stok Kosong: {$stokKosongCount} produk\n\n";
        $msg .= 'Terima kasih atas kerja keras seluruh tim Apotek Bintan 88!';

        // In-App Notification untuk Owner & Apoteker
        Notifikasi::create([
            'role_target' => 'owner,apoteker',
            'judul' => '📊 Ringkasan Penjualan Hari Ini',
            'pesan' => "Omset {$tglFormat}: Rp ".number_format($totalOmset, 0, ',', '.')." dari {$totalTransaksi} transaksi. Lihat laporan lengkap di dashboard.",
            'tipe' => 'ringkasan_harian',
            'link' => '/dashboard',
        ]);

        $res = $telegramService->kirimPesan($msg, 'ringkasan_harian', 'digest_harian', date('Ymd'), null, $force);
        $this->line("Telegram Status: {$res['status']} - {$res['message']}");

        return Command::SUCCESS;
    }
}
