<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notifikasi;
use App\Models\ShiftKasir;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShiftKasirController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = ShiftKasir::with('user')->latest('waktu_buka');

        // Jika kasir biasa, batasi ke shift miliknya sendiri
        if ($user->role === 'kasir') {
            $query->where('user_id', $user->id);
        }

        $shifts = $query->paginate(15);

        // Ambil shift aktif user saat ini
        $activeShift = ShiftKasir::where('user_id', $user->id)
            ->where('status', 'buka')
            ->first();

        return view('kasir.shift.index', compact('shifts', 'activeShift'));
    }

    /**
     * Buka Shift Kasir Baru
     */
    public function buka(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        // Cek apakah kasir ini sudah punya shift buka
        $existing = ShiftKasir::where('user_id', $user->id)->where('status', 'buka')->first();
        if ($existing) {
            return back()->with('error', 'Anda masih memiliki sesi shift aktif. Harap tutup shift sebelumnya terlebih dahulu.');
        }

        $request->validate([
            'modal_awal' => ['required', 'numeric', 'min:0'],
        ], [
            'modal_awal.required' => 'Modal uang kembalian awal wajib diisi (minimal Rp 0).',
        ]);

        $shift = ShiftKasir::create([
            'user_id' => $user->id,
            'waktu_buka' => now(),
            'modal_awal' => $request->input('modal_awal'),
            'total_penjualan_tunai' => 0,
            'total_penjualan_non_tunai' => 0,
            'uang_fisik_akhir' => 0,
            'selisih' => 0,
            'status' => 'buka',
            'catatan' => $request->input('catatan'),
        ]);

        ActivityLog::record(
            'buka_shift',
            'Membuka shift kasir dengan modal awal Rp '.number_format($shift->modal_awal, 0, ',', '.')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shift kasir berhasil dibuka! Selamat melayani pelanggan.',
                'shift' => $shift,
            ]);
        }

        return redirect()->route('pos.index')->with('success', 'Shift kasir berhasil dibuka! Selamat bertugas.');
    }

    /**
     * Tutup Shift Kasir & Rekonsiliasi Kas Laci
     */
    public function tutup(Request $request, ShiftKasir $shift): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if ($shift->user_id !== $user->id && ! in_array($user->role, ['owner', 'apoteker'])) {
            abort(403, 'Anda tidak berhak menutup shift kasir pengguna lain.');
        }

        if ($shift->status === 'tutup') {
            return back()->with('error', 'Shift ini sudah ditutup sebelumnya.');
        }

        $request->validate([
            'uang_fisik_akhir' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ], [
            'uang_fisik_akhir.required' => 'Hitung fisik total uang tunai di laci kasir dan masukkan nominalnya.',
        ]);

        $uangFisik = (float) $request->input('uang_fisik_akhir');
        $ekspektasiKasTunai = (float) $shift->modal_awal + (float) $shift->total_penjualan_tunai;
        $selisih = $uangFisik - $ekspektasiKasTunai;

        $shift->waktu_tutup = now();
        $shift->uang_fisik_akhir = $uangFisik;
        $shift->selisih = $selisih;
        $shift->status = 'tutup';
        if ($request->filled('catatan')) {
            $shift->catatan = ($shift->catatan ? $shift->catatan.' | ' : '').$request->input('catatan');
        }
        $shift->save();

        // Trigger Notifikasi #11: Selisih kas saat tutup shift
        if ($selisih != 0) {
            $tandaSelisih = $selisih > 0 ? 'LEBIH (Rp '.number_format($selisih, 0, ',', '.').')' : 'KURANG (Rp '.number_format(abs($selisih), 0, ',', '.').')';

            Notifikasi::create([
                'role_target' => 'owner',
                'judul' => '⚠️ Selisih Kas Tutup Shift Kasir',
                'pesan' => "Shift {$shift->user->nama} ditutup dengan selisih kas fisik {$tandaSelisih}. Modal: Rp ".number_format($shift->modal_awal, 0, ',', '.').', Penjualan Tunai: Rp '.number_format($shift->total_penjualan_tunai, 0, ',', '.').', Uang Fisik: Rp '.number_format($uangFisik, 0, ',', '.'),
                'tipe' => 'selisih_shift',
                'link' => '/kasir/shift',
            ]);

            $msgTelegram = "⚠️ <b>SELISIH KAS TUTUP SHIFT - Apotek Bintan 88</b>\n";
            $msgTelegram .= "Kasir: {$shift->user->nama}\n";
            $msgTelegram .= 'Modal Awal: Rp '.number_format($shift->modal_awal, 0, ',', '.')."\n";
            $msgTelegram .= 'Penjualan Tunai: Rp '.number_format($shift->total_penjualan_tunai, 0, ',', '.')."\n";
            $msgTelegram .= 'Total Seharusnya: Rp '.number_format($ekspektasiKasTunai, 0, ',', '.')."\n";
            $msgTelegram .= 'Uang Fisik Laci: Rp '.number_format($uangFisik, 0, ',', '.')."\n";
            $msgTelegram .= "<b>Status Selisih: {$tandaSelisih}</b>\n";
            if ($request->filled('catatan')) {
                $msgTelegram .= "Catatan: {$request->input('catatan')}";
            }

            $this->telegramService->kirimPesan($msgTelegram, 'selisih_shift', 'shift_kasir', $shift->id);
        }

        ActivityLog::record(
            'tutup_shift',
            'Menutup shift kasir. Uang fisik: Rp '.number_format($uangFisik, 0, ',', '.').', Selisih: Rp '.number_format($selisih, 0, ',', '.')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shift kasir berhasil ditutup.',
                'selisih' => $selisih,
            ]);
        }

        return redirect()->route('kasir.shift.index')->with('success', 'Shift kasir berhasil ditutup dan direkonsiliasi.');
    }
}
