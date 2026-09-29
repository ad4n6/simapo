<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notifikasi;
use App\Models\Obat;
use App\Models\PembayaranHutang;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PurchaseOrder;
use App\Models\Suplier;
use App\Services\StokService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PembelianController extends Controller
{
    public function __construct(
        protected StokService $stokService,
        protected TelegramService $telegramService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $query = Pembelian::with(['suplier', 'user', 'purchaseOrder'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_faktur', 'like', "%{$search}%")
                    ->orWhereHas('suplier', function ($sq) use ($search) {
                        $sq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->input('status_pembayaran'));
        }

        if ($request->filled('jenis_pembayaran')) {
            $query->where('jenis_pembayaran', $request->input('jenis_pembayaran'));
        }

        $pembelians = $query->paginate(10)->withQueryString();
        $supliers = Suplier::orderBy('nama')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $pembelians]);
        }

        return view('pengadaan.pembelian.index', compact('pembelians', 'supliers'));
    }

    public function create(Request $request): View
    {
        $today = Carbon::today();
        $noFaktur = 'INV-B88-'.date('Ym').'-'.str_pad(Pembelian::whereDate('created_at', $today)->count() + 1, 4, '0', STR_PAD_LEFT);
        $supliers = Suplier::orderBy('nama')->get();
        $obats = Obat::with('satuan')->orderBy('nama')->get();

        $selectedPo = null;
        if ($request->filled('po_id')) {
            $selectedPo = PurchaseOrder::with(['suplier', 'details.obat.satuan'])->find($request->input('po_id'));
        }

        return view('pengadaan.pembelian.create', compact('noFaktur', 'supliers', 'obats', 'selectedPo'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'no_faktur' => ['required', 'string', 'unique:pembelian,no_faktur'],
            'suplier_id' => ['required', 'exists:suplier,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_order,id'],
            'tanggal_faktur' => ['required', 'date'],
            'jenis_pembayaran' => ['required', 'in:tunai,kredit'],
            'tanggal_jatuh_tempo' => ['required_if:jenis_pembayaran,kredit', 'nullable', 'date'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.no_batch' => ['required', 'string', 'max:100'],
            'items.*.tanggal_kedaluwarsa' => ['required', 'date', 'after:today'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.harga_beli' => ['required', 'numeric', 'min:0'],
        ], [
            'items.*.tanggal_kedaluwarsa.after' => 'Tanggal kedaluwarsa harus di masa depan.',
            'tanggal_jatuh_tempo.required_if' => 'Tanggal jatuh tempo wajib diisi jika pembayaran kredit.',
        ]);

        $user = Auth::user();

        $pembelian = DB::transaction(function () use ($validated, $user) {
            $subtotal = 0;
            foreach ($validated['items'] as $it) {
                $subtotal += $it['jumlah'] * $it['harga_beli'];
            }

            $isKredit = $validated['jenis_pembayaran'] === 'kredit';
            $statusBayar = $isKredit ? 'belum_lunas' : 'lunas';
            $totalTerbayar = $isKredit ? 0 : $subtotal;
            $sisaHutang = $isKredit ? $subtotal : 0;

            $record = Pembelian::create([
                'no_faktur' => $validated['no_faktur'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'suplier_id' => $validated['suplier_id'],
                'user_id' => $user->id,
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'tanggal_jatuh_tempo' => $isKredit ? $validated['tanggal_jatuh_tempo'] : null,
                'jenis_pembayaran' => $validated['jenis_pembayaran'],
                'status_pembayaran' => $statusBayar,
                'subtotal' => $subtotal,
                'total_akhir' => $subtotal,
                'total_terbayar' => $totalTerbayar,
                'sisa_hutang' => $sisaHutang,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Process each item and add stock via StokService
            $batchSummaries = [];
            foreach ($validated['items'] as $it) {
                $obat = Obat::findOrFail($it['obat_id']);

                $batch = $this->stokService->tambahStokBatch(
                    $obat,
                    $it['no_batch'],
                    $it['tanggal_kedaluwarsa'],
                    (int) $it['jumlah'],
                    (float) $it['harga_beli'],
                    (int) $validated['suplier_id'],
                    'Pembelian',
                    $record->id,
                    $user->id,
                    "Penerimaan Faktur {$record->no_faktur}"
                );

                PembelianDetail::create([
                    'pembelian_id' => $record->id,
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $batch->id,
                    'no_batch' => $it['no_batch'],
                    'tanggal_kedaluwarsa' => $it['tanggal_kedaluwarsa'],
                    'jumlah' => $it['jumlah'],
                    'harga_beli' => $it['harga_beli'],
                    'subtotal' => $it['jumlah'] * $it['harga_beli'],
                ]);

                $batchSummaries[] = "{$obat->nama} (Batch {$it['no_batch']}: +{$it['jumlah']})";
            }

            // Update PO status if linked
            if (! empty($validated['purchase_order_id'])) {
                $po = PurchaseOrder::find($validated['purchase_order_id']);
                if ($po) {
                    $po->status = 'diterima';
                    $po->save();
                }
            }

            // In-app notification
            $suplierName = Suplier::find($validated['suplier_id'])?->nama ?? 'PBF';
            Notifikasi::create([
                'role_target' => 'owner,apoteker,admin_gudang',
                'judul' => '📦 Penerimaan Barang Berhasil',
                'pesan' => "Faktur {$record->no_faktur} dari {$suplierName} telah diterima. Total: Rp ".number_format($subtotal, 0, ',', '.').'. Stok bertambah.',
                'tipe' => 'penerimaan',
                'link' => "/pengadaan/pembelian/{$record->id}",
            ]);

            // Telegram notification
            $msgTelegram = "📦 <b>PENERIMAAN BARANG - Apotek Bintan 88</b>\n";
            $msgTelegram .= "Faktur: {$record->no_faktur}\n";
            $msgTelegram .= "Suplier: {$suplierName}\n";
            $msgTelegram .= 'Total: Rp '.number_format($subtotal, 0, ',', '.')."\n";
            $msgTelegram .= 'Status: '.strtoupper($record->jenis_pembayaran)."\n";
            $msgTelegram .= "Item:\n- ".implode("\n- ", array_slice($batchSummaries, 0, 5));
            $this->telegramService->kirimPesan($msgTelegram, 'penerimaan', 'pembelian', $record->id);

            ActivityLog::record('pembelian', "Penerimaan faktur {$record->no_faktur} senilai Rp ".number_format($subtotal, 0, ',', '.'));

            return $record;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Penerimaan barang dan faktur pembelian berhasil disimpan!',
                'redirect' => route('pembelian.show', $pembelian->id),
            ]);
        }

        return redirect()->route('pembelian.show', $pembelian->id)->with('success', 'Penerimaan barang dan faktur pembelian berhasil disimpan!');
    }

    public function show(Pembelian $pembelian): View
    {
        $pembelian->load(['suplier', 'user', 'purchaseOrder', 'details.obat.satuan', 'pembayaranHutangs.user']);

        return view('pengadaan.pembelian.show', compact('pembelian'));
    }

    /**
     * Halaman Khusus Manajemen Hutang Suplier
     */
    public function hutang(Request $request): View|JsonResponse
    {
        $today = Carbon::today();
        $query = Pembelian::with(['suplier', 'pembayaranHutangs'])
            ->where('jenis_pembayaran', 'kredit')
            ->where('status_pembayaran', 'belum_lunas')
            ->orderBy('tanggal_jatuh_tempo', 'asc');

        if ($request->filled('suplier_id')) {
            $query->where('suplier_id', $request->input('suplier_id'));
        }

        $hutangs = $query->paginate(10)->withQueryString();

        $totalHutang = Pembelian::where('jenis_pembayaran', 'kredit')
            ->where('status_pembayaran', 'belum_lunas')
            ->sum('sisa_hutang');

        $hutangLewatTempo = Pembelian::where('jenis_pembayaran', 'kredit')
            ->where('status_pembayaran', 'belum_lunas')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->sum('sisa_hutang');

        $supliers = Suplier::orderBy('nama')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $hutangs]);
        }

        return view('pengadaan.pembelian.hutang', compact('hutangs', 'totalHutang', 'hutangLewatTempo', 'supliers'));
    }

    /**
     * Bayar Hutang Faktur
     */
    public function bayarHutang(Request $request, Pembelian $pembelian): JsonResponse|RedirectResponse
    {
        $request->validate([
            'jumlah_bayar' => ['required', 'numeric', 'min:1000', "max:{$pembelian->sisa_hutang}"],
            'tanggal_bayar' => ['required', 'date'],
            'metode_bayar' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
        ], [
            'jumlah_bayar.max' => 'Jumlah bayar tidak boleh melebihi sisa hutang.',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $pembelian, $user) {
            $bayar = (float) $request->input('jumlah_bayar');
            $noBayar = 'PAY-B88-'.date('Ymd').'-'.str_pad(PembayaranHutang::whereDate('created_at', Carbon::today())->count() + 1, 3, '0', STR_PAD_LEFT);

            PembayaranHutang::create([
                'no_pembayaran' => $noBayar,
                'pembelian_id' => $pembelian->id,
                'user_id' => $user->id,
                'tanggal_bayar' => $request->input('tanggal_bayar'),
                'jumlah_bayar' => $bayar,
                'metode_bayar' => $request->input('metode_bayar'),
                'catatan' => $request->input('catatan'),
            ]);

            $pembelian->total_terbayar += $bayar;
            $pembelian->sisa_hutang -= $bayar;

            if ($pembelian->sisa_hutang <= 0) {
                $pembelian->sisa_hutang = 0;
                $pembelian->status_pembayaran = 'lunas';
            }

            $pembelian->save();

            ActivityLog::record(
                'bayar_hutang',
                "Pembayaran hutang faktur {$pembelian->no_faktur} sebesar Rp ".number_format($bayar, 0, ',', '.'),
                ['pembelian_id' => $pembelian->id]
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran hutang berhasil dicatat!',
            ]);
        }

        return back()->with('success', 'Pembayaran hutang berhasil dicatat!');
    }
}
