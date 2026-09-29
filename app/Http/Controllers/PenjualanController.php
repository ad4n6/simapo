<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notifikasi;
use App\Models\Penjualan;
use App\Models\User;
use App\Services\StokService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function __construct(
        protected StokService $stokService,
        protected TelegramService $telegramService
    ) {}

    /**
     * Riwayat Penjualan Kasir dengan Filter Lengkap
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $query = Penjualan::with(['user', 'pelanggan', 'resep', 'details.obat'])->latest();

        // Kasir hanya bisa melihat transaksi miliknya sendiri, kecuali jika Owner/Apoteker
        if ($user->role === 'kasir') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($pq) use ($search) {
                        $pq->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhereHas('resep', function ($rq) use ($search) {
                        $rq->where('no_resep', 'like', "%{$search}%")
                            ->orWhere('nama_pasien', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->input('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->input('tanggal_sampai'));
        }

        if ($request->filled('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->input('jenis_transaksi'));
        }

        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->input('metode_pembayaran'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('kasir_id') && in_array($user->role, ['owner', 'apoteker'])) {
            $query->where('user_id', $request->input('kasir_id'));
        }

        $penjualans = $query->paginate(15)->withQueryString();
        $kasirs = User::whereIn('role', ['kasir', 'apoteker', 'owner'])->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $penjualans]);
        }

        return view('penjualan.index', compact('penjualans', 'kasirs'));
    }

    /**
     * Detail Transaksi Penjualan
     */
    public function show(Penjualan $penjualan): View
    {
        $penjualan->load([
            'user',
            'voidUser',
            'pelanggan',
            'resep.dokter',
            'details.obat.satuan',
            'details.batchObat',
            'returs.details.obat',
        ]);

        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Batalkan / Void Transaksi (Hanya Owner & Apoteker)
     * Mengembalikan seluruh stok ke batch asal!
     */
    public function voidTransaction(Request $request, Penjualan $penjualan): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        // Otorisasi ketat
        if (! in_array($user->role, ['owner', 'apoteker'])) {
            abort(403, 'Akses ditolak. Hanya Owner dan Apoteker yang berhak membatalkan (void) transaksi kasir.');
        }

        if ($penjualan->status === 'void') {
            return back()->with('error', 'Transaksi ini sudah pernah dibatalkan (void) sebelumnya.');
        }

        $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'alasan.required' => 'Alasan pembatalan transaksi wajib diisi secara rinci.',
        ]);

        $alasan = $request->input('alasan');

        DB::transaction(function () use ($penjualan, $user, $alasan) {
            // 1. Kembalikan stok obat ke batch masing-masing
            $penjualan->load('details.batchObat');
            foreach ($penjualan->details as $d) {
                if ($d->batch_obat_id) {
                    $this->stokService->kembalikanStokBatch(
                        $d->batch_obat_id,
                        $d->jumlah,
                        'VoidPenjualan',
                        $penjualan->id,
                        $user->id,
                        "Void Transaksi {$penjualan->no_transaksi} ({$alasan})"
                    );
                }
            }

            // 2. Tandai status penjualan menjadi void
            $penjualan->status = 'void';
            $penjualan->void_alasan = $alasan;
            $penjualan->void_user_id = $user->id;
            $penjualan->void_at = now();
            $penjualan->save();

            // 3. Jika shift kasir masih aktif/terkait, sesuaikan rekapan
            if ($penjualan->shiftKasir && $penjualan->shiftKasir->status === 'buka') {
                $shift = $penjualan->shiftKasir;
                if ($penjualan->metode_pembayaran === 'tunai') {
                    $shift->total_penjualan_tunai = max(0, $shift->total_penjualan_tunai - $penjualan->total_akhir);
                } else {
                    $shift->total_penjualan_non_tunai = max(0, $shift->total_penjualan_non_tunai - $penjualan->total_akhir);
                }
                $shift->save();
            }

            // 4. In-App Notification (Trigger #8: Transaksi void/retur)
            Notifikasi::create([
                'role_target' => 'owner,apoteker',
                'judul' => '⚠️ Transaksi Kasir Dibatalkan (VOID)',
                'pesan' => "Transaksi {$penjualan->no_transaksi} senilai Rp ".number_format($penjualan->total_akhir, 0, ',', '.')." telah di-VOID oleh {$user->nama}. Alasan: {$alasan}. Stok telah dikembalikan.",
                'tipe' => 'void_retur',
                'link' => "/penjualan/{$penjualan->id}",
            ]);

            // 5. Telegram Notification (Real-time trigger #8)
            $msgTelegram = "⚠️ <b>TRANSAKSI DI-VOID (BATAL) - Apotek Bintan 88</b>\n";
            $msgTelegram .= "No. Transaksi: {$penjualan->no_transaksi}\n";
            $msgTelegram .= 'Nilai: Rp '.number_format($penjualan->total_akhir, 0, ',', '.')."\n";
            $msgTelegram .= "Dibatalkan oleh: {$user->nama} ({$user->role})\n";
            $msgTelegram .= "Kasir awal: {$penjualan->user->nama}\n";
            $msgTelegram .= "Alasan: {$alasan}\n";
            $msgTelegram .= 'Status: Seluruh stok telah otomatis dikembalikan ke batch asal.';

            $this->telegramService->kirimPesan($msgTelegram, 'void_retur', 'penjualan', $penjualan->id);

            ActivityLog::record(
                'void_transaksi',
                "Membatalkan (VOID) transaksi {$penjualan->no_transaksi} senilai Rp ".number_format($penjualan->total_akhir, 0, ',', '.')." dengan alasan: {$alasan}",
                ['penjualan_id' => $penjualan->id]
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan (void) dan stok batch telah dikembalikan!',
            ]);
        }

        return redirect()->route('penjualan.show', $penjualan->id)->with('success', 'Transaksi berhasil dibatalkan (void) dan stok batch telah dikembalikan!');
    }
}
