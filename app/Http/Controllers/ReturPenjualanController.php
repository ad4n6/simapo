<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notifikasi;
use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Services\StokService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturPenjualanController extends Controller
{
    public function __construct(
        protected StokService $stokService,
        protected TelegramService $telegramService
    ) {}

    public function index(): View
    {
        $returs = ReturPenjualan::with(['penjualan', 'user', 'details.obat.satuan'])->latest()->paginate(10);

        return view('penjualan.retur.index', compact('returs'));
    }

    public function create(Request $request): View
    {
        $today = Carbon::today();
        $noRetur = 'RET-JUAL-'.date('Ymd').'-'.str_pad(ReturPenjualan::whereDate('created_at', $today)->count() + 1, 3, '0', STR_PAD_LEFT);

        $selectedPenjualan = null;
        if ($request->filled('penjualan_id')) {
            $selectedPenjualan = Penjualan::with(['pelanggan', 'details.obat.satuan', 'details.batchObat'])
                ->where('status', 'selesai')
                ->find($request->input('penjualan_id'));
        }

        // Ambil riwayat penjualan selesai 30 hari terakhir untuk pilihan transaksi
        $recentPenjualans = Penjualan::with(['pelanggan', 'details.obat'])
            ->where('status', 'selesai')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
            ->latest()
            ->limit(50)
            ->get();

        return view('penjualan.retur.create', compact('noRetur', 'selectedPenjualan', 'recentPenjualans'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'no_retur' => ['required', 'string', 'unique:retur_penjualan,no_retur'],
            'penjualan_id' => ['required', 'exists:penjualan,id'],
            'alasan' => ['required', 'string', 'min:3'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.batch_obat_id' => ['required', 'exists:batch_obat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.kembalikan_ke_stok' => ['required', 'boolean'],
            'items.*.kondisi' => ['required', 'string'],
        ], [
            'alasan.required' => 'Alasan retur pelanggan wajib dicatat.',
            'items.required' => 'Pilih minimal satu item obat yang diretur oleh pelanggan.',
        ]);

        $user = Auth::user();
        $penjualan = Penjualan::with('details')->findOrFail($validated['penjualan_id']);

        if ($penjualan->status === 'void') {
            return back()->with('error', 'Transaksi yang telah di-void tidak dapat diretur lagi.');
        }

        try {
            $retur = DB::transaction(function () use ($validated, $user, $penjualan) {
                $totalPengembalian = 0;

                $record = ReturPenjualan::create([
                    'no_retur' => $validated['no_retur'],
                    'penjualan_id' => $penjualan->id,
                    'user_id' => $user->id,
                    'total_pengembalian' => 0,
                    'alasan' => $validated['alasan'],
                ]);

                foreach ($validated['items'] as $it) {
                    // Cari detail penjualan asal untuk mendapatkan harga jual
                    $detailAsal = $penjualan->details
                        ->where('obat_id', $it['obat_id'])
                        ->where('batch_obat_id', $it['batch_obat_id'])
                        ->first();

                    if (! $detailAsal) {
                        throw new Exception('Item obat/batch tidak cocok dengan riwayat penjualan transaksi ini.');
                    }

                    if ($it['jumlah'] > $detailAsal->jumlah) {
                        throw new Exception("Jumlah retur ({$it['jumlah']}) melebihi jumlah yang dibeli ({$detailAsal->jumlah}).");
                    }

                    $hargaSatuan = (float) $detailAsal->harga_jual;
                    $subtotal = $it['jumlah'] * $hargaSatuan;
                    $totalPengembalian += $subtotal;

                    // Buat detail retur
                    ReturPenjualanDetail::create([
                        'retur_penjualan_id' => $record->id,
                        'obat_id' => $it['obat_id'],
                        'batch_obat_id' => $it['batch_obat_id'],
                        'jumlah' => $it['jumlah'],
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $subtotal,
                        'kembalikan_ke_stok' => (bool) $it['kembalikan_ke_stok'],
                        'kondisi' => $it['kondisi'],
                    ]);

                    // Jika obat layak dan dicentang kembalikan ke stok fisik:
                    if ($it['kembalikan_ke_stok']) {
                        $this->stokService->kembalikanStokBatch(
                            (int) $it['batch_obat_id'],
                            (int) $it['jumlah'],
                            'ReturPenjualan',
                            $record->id,
                            $user->id,
                            "Retur Penjualan {$record->no_retur}: {$validated['alasan']}"
                        );
                    }
                }

                $record->total_pengembalian = $totalPengembalian;
                $record->save();

                // Notifikasi In-App & Telegram (Trigger #8: Transaksi void/retur real-time)
                Notifikasi::create([
                    'role_target' => 'owner,apoteker',
                    'judul' => '↩️ Retur Penjualan Pelanggan',
                    'pesan' => "Retur {$record->no_retur} untuk TRX {$penjualan->no_transaksi} dicatat oleh {$user->nama}. Pengembalian: Rp ".number_format($totalPengembalian, 0, ',', '.'),
                    'tipe' => 'void_retur',
                    'link' => "/penjualan/retur/{$record->id}",
                ]);

                $msgTelegram = "↩️ <b>RETUR PENJUALAN - Apotek Bintan 88</b>\n";
                $msgTelegram .= "No. Retur: {$record->no_retur}\n";
                $msgTelegram .= "Ref TRX: {$penjualan->no_transaksi}\n";
                $msgTelegram .= 'Dana Dikembalikan: Rp '.number_format($totalPengembalian, 0, ',', '.')."\n";
                $msgTelegram .= "Petugas: {$user->nama}\n";
                $msgTelegram .= "Alasan: {$validated['alasan']}";

                $this->telegramService->kirimPesan($msgTelegram, 'void_retur', 'retur_penjualan', $record->id);

                ActivityLog::record(
                    'retur_penjualan',
                    "Mencatat retur penjualan {$record->no_retur} senilai Rp ".number_format($totalPengembalian, 0, ',', '.'),
                    ['id' => $record->id]
                );

                return $record;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Retur penjualan berhasil dicatat dan diproses!',
                    'redirect' => route('penjualan.retur.show', $retur->id),
                ]);
            }

            return redirect()->route('penjualan.retur.show', $retur->id)->with('success', 'Retur penjualan berhasil dicatat dan diproses!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(ReturPenjualan $retur): View
    {
        $retur->load(['penjualan.pelanggan', 'user', 'details.obat.satuan', 'details.batchObat']);

        return view('penjualan.retur.show', compact('retur'));
    }
}
