<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\NotifikasiLog;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService
    ) {}

    /**
     * Polling AJAX untuk ikon lonceng navbar (badge & list unread)
     */
    public function unread(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['count' => 0, 'data' => []]);
        }

        $query = Notifikasi::where(function ($q) use ($user) {
            $q->where('role_target', 'like', "%{$user->role}%")
                ->orWhereNull('role_target');
        })
            ->where('dibaca', false)
            ->latest();

        $count = $query->count();
        $notifications = $query->limit(8)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'judul' => $n->judul,
                'pesan' => $n->pesan,
                'tipe' => $n->tipe,
                'link' => $n->link,
                'waktu' => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'count' => $count,
            'data' => $notifications,
        ]);
    }

    /**
     * Tandai semua notifikasi in-app telah dibaca
     */
    public function markAllRead(): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            Notifikasi::where(function ($q) use ($user) {
                $q->where('role_target', 'like', "%{$user->role}%")
                    ->orWhereNull('role_target');
            })
                ->where('dibaca', false)
                ->update(['dibaca' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Halaman Riwayat Log Notifikasi Telegram & In-App (Owner)
     */
    public function riwayat(Request $request): View
    {
        $query = NotifikasiLog::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        $logs = $query->paginate(15)->withQueryString();

        $totalTerkirim = NotifikasiLog::where('status', 'terkirim')->count();
        $totalGagal = NotifikasiLog::where('status', 'gagal')->count();

        return view('notifikasi.riwayat', compact('logs', 'totalTerkirim', 'totalGagal'));
    }

    /**
     * Tombol Kirim Ulang untuk Pesan yang Gagal
     */
    public function kirimUlang(NotifikasiLog $log): JsonResponse|RedirectResponse
    {
        $result = $this->telegramService->kirimPesan(
            $log->pesan,
            $log->jenis,
            $log->entitas_tipe,
            $log->entitas_id,
            $log->tujuan,
            true // Force send
        );

        if ($result['success']) {
            $log->status = 'terkirim';
            $log->response_error = null;
            $log->save();

            $msg = 'Notifikasi Telegram berhasil dikirim ulang!';
        } else {
            $msg = 'Pengiriman ulang gagal: '.$result['message'];
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $msg,
            ]);
        }

        return back()->with($result['success'] ? 'success' : 'error', $msg);
    }
}
