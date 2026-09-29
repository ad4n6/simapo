<?php

namespace App\Http\Controllers;

use App\Models\BatchObat;
use App\Models\Notifikasi;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PurchaseOrder;
use App\Models\ShiftKasir;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 1. KPI Metrik Utama
        $penjualanHariIniQuery = Penjualan::whereDate('created_at', $today)->where('status', 'selesai');
        if ($user->role === 'kasir') {
            $penjualanHariIniQuery->where('user_id', $user->id);
        }
        $omsetHariIni = $penjualanHariIniQuery->sum('total_akhir');
        $transaksiHariIni = $penjualanHariIniQuery->count();

        // 2. Metrik Inventori
        $allObats = Obat::all();
        $obatMenipis = $allObats->filter(fn ($o) => $o->total_stok <= $o->stok_minimum && $o->total_stok > 0)->count();
        $obatKosong = $allObats->filter(fn ($o) => $o->total_stok == 0)->count();

        // 3. Metrik Kedaluwarsa
        $obatHampirExpired = BatchObat::where('tanggal_kedaluwarsa', '>', $today)
            ->where('tanggal_kedaluwarsa', '<=', Carbon::today()->addDays(30))
            ->where('jumlah_saat_ini', '>', 0)
            ->count();

        $obatExpired = BatchObat::where('tanggal_kedaluwarsa', '<=', $today)
            ->where('jumlah_saat_ini', '>', 0)
            ->count();

        // 4. Metrik Pengadaan & Hutang
        $hutangJatuhTempo = Pembelian::where('jenis_pembayaran', 'kredit')
            ->where('status_pembayaran', 'belum_lunas')
            ->where('tanggal_jatuh_tempo', '<=', Carbon::today()->addDays(3))
            ->sum('sisa_hutang');

        $poMenunggu = PurchaseOrder::whereIn('status', ['dipesan', 'diterima_sebagian'])->count();

        // 5. Data Grafik Tren Penjualan 7 Hari Terakhir
        $chartDates = [];
        $chartSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartDates[] = $date->translatedFormat('d M');

            $dailyTotal = Penjualan::whereDate('created_at', $date)
                ->where('status', 'selesai')
                ->sum('total_akhir');
            $chartSales[] = (float) $dailyTotal;
        }

        // 6. Data Top 5 Obat Terlaris
        $topObats = PenjualanDetail::select('obat_id', DB::raw('SUM(jumlah) as total_qty'))
            ->whereHas('penjualan', function ($q) {
                $q->where('status', 'selesai');
            })
            ->with('obat')
            ->groupBy('obat_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topObatLabels = $topObats->map(fn ($t) => $t->obat->nama ?? 'Unknown')->toArray();
        $topObatValues = $topObats->pluck('total_qty')->toArray();

        // 7. Notifikasi Terbaru
        $recentNotif = Notifikasi::where(function ($q) use ($user) {
            $q->where('role_target', 'like', "%{$user->role}%")
                ->orWhereNull('role_target');
        })
            ->latest()
            ->limit(5)
            ->get();

        // 8. Transaksi Kasir Terbaru
        $recentPenjualans = Penjualan::with(['user', 'pelanggan', 'details'])
            ->latest()
            ->limit(5)
            ->get();

        // 9. Status Shift Aktif Kasir
        $activeShift = null;
        if ($user->role === 'kasir') {
            $activeShift = ShiftKasir::where('user_id', $user->id)
                ->where('status', 'buka')
                ->latest('waktu_buka')
                ->first();
        }

        return view('dashboard.index', compact(
            'omsetHariIni',
            'transaksiHariIni',
            'obatMenipis',
            'obatKosong',
            'obatHampirExpired',
            'obatExpired',
            'hutangJatuhTempo',
            'poMenunggu',
            'chartDates',
            'chartSales',
            'topObatLabels',
            'topObatValues',
            'recentNotif',
            'recentPenjualans',
            'activeShift'
        ));
    }
}
