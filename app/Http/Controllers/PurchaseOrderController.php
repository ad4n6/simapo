<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Suplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = PurchaseOrder::with(['suplier', 'user', 'details.obat'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('suplier_id')) {
            $query->where('suplier_id', $request->input('suplier_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_po', 'like', "%{$search}%")
                    ->orWhereHas('suplier', function ($sq) use ($search) {
                        $sq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $pos = $query->paginate(10)->withQueryString();
        $supliers = Suplier::orderBy('nama')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $pos]);
        }

        return view('pengadaan.po.index', compact('pos', 'supliers'));
    }

    public function create(Request $request): View
    {
        $today = Carbon::today();
        $noPo = 'PO-B88-'.date('Ymd').'-'.str_pad(PurchaseOrder::whereDate('created_at', $today)->count() + 1, 3, '0', STR_PAD_LEFT);
        $supliers = Suplier::orderBy('nama')->get();
        $obats = Obat::with('satuan')->orderBy('nama')->get();

        // Check if pre-populated from rekomendasi
        $prefilledItems = [];
        if ($request->input('source') === 'rekomendasi') {
            $lowStockObats = Obat::with('satuan')->get()->filter(function ($o) {
                return $o->total_stok <= $o->stok_minimum;
            });

            foreach ($lowStockObats as $o) {
                $recommendedQty = max(10, ($o->stok_minimum * 3) - $o->total_stok);
                $prefilledItems[] = [
                    'obat_id' => $o->id,
                    'nama' => $o->nama,
                    'satuan' => $o->satuan?->nama,
                    'qty' => $recommendedQty,
                    'harga' => (float) $o->harga_beli,
                ];
            }
        }

        return view('pengadaan.po.create', compact('noPo', 'supliers', 'obats', 'prefilledItems'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'no_po' => ['required', 'string', 'unique:purchase_order,no_po'],
            'suplier_id' => ['required', 'exists:suplier,id'],
            'tanggal_po' => ['required', 'date'],
            'estimasi_tanggal_terima' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.jumlah_dipesan' => ['required', 'integer', 'min:1'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Minimal 1 obat harus dipesan dalam PO.',
            'suplier_id.required' => 'Suplier PBF tujuan wajib dipilih.',
        ]);

        $user = Auth::user();

        $po = DB::transaction(function () use ($validated, $user) {
            $totalPerkiraan = 0;
            foreach ($validated['items'] as $item) {
                $totalPerkiraan += $item['jumlah_dipesan'] * $item['harga_satuan'];
            }

            $order = PurchaseOrder::create([
                'no_po' => $validated['no_po'],
                'suplier_id' => $validated['suplier_id'],
                'user_id' => $user->id,
                'tanggal_po' => $validated['tanggal_po'],
                'estimasi_tanggal_terima' => $validated['estimasi_tanggal_terima'] ?? null,
                'total_perkiraan' => $totalPerkiraan,
                'status' => 'dipesan',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $order->id,
                    'obat_id' => $item['obat_id'],
                    'jumlah_dipesan' => $item['jumlah_dipesan'],
                    'jumlah_diterima' => 0,
                    'harga_satuan_perkiraan' => $item['harga_satuan'],
                    'subtotal_perkiraan' => $item['jumlah_dipesan'] * $item['harga_satuan'],
                ]);
            }

            ActivityLog::record('buat_po', "Membuat Purchase Order: {$order->no_po} ke {$order->suplier->nama}");

            return $order;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase Order berhasil dibuat!',
                'redirect' => route('po.show', $po->id),
            ]);
        }

        return redirect()->route('po.show', $po->id)->with('success', 'Purchase Order berhasil dibuat!');
    }

    public function show(PurchaseOrder $po): View
    {
        $po->load(['suplier', 'user', 'details.obat.satuan', 'pembelians']);

        return view('pengadaan.po.show', compact('po'));
    }

    public function updateStatus(Request $request, PurchaseOrder $po): JsonResponse|RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:draft,dipesan,diterima_sebagian,diterima,batal'],
        ]);

        $po->status = $request->input('status');
        $po->save();

        ActivityLog::record('update_status_po', "Status PO {$po->no_po} diubah menjadi {$po->status}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status PO berhasil diubah menjadi {$po->status}!",
            ]);
        }

        return back()->with('success', "Status PO berhasil diubah menjadi {$po->status}!");
    }
}
