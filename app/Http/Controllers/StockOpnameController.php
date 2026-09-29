<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BatchObat;
use App\Models\Notifikasi;
use App\Models\Obat;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StokMutasi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function index(): View
    {
        $opnames = StockOpname::with('user')->latest()->paginate(10);

        return view('stok.opname.index', compact('opnames'));
    }

    public function create(): View
    {
        $obats = Obat::with(['satuan', 'batches' => function ($q) {
            $q->orderBy('tanggal_kedaluwarsa', 'asc');
        }])->orderBy('nama')->get();

        $noOpname = 'OPN-'.date('Ymd').'-'.str_pad(StockOpname::whereDate('created_at', Carbon::today())->count() + 1, 3, '0', STR_PAD_LEFT);

        return view('stok.opname.create', compact('obats', 'noOpname'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'no_opname' => ['required', 'string', 'unique:stock_opname,no_opname'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_id' => ['required', 'exists:batch_obat,id'],
            'items.*.stok_fisik' => ['required', 'integer', 'min:0'],
            'items.*.alasan' => ['required', 'string', 'max:255'],
        ], [
            'items.required' => 'Minimal satu item batch obat harus diinput.',
            'items.*.alasan.required' => 'Alasan selisih opname wajib diisi untuk setiap item.',
        ]);

        $user = Auth::user();

        $result = DB::transaction(function () use ($request, $user) {
            $totalPlus = 0;
            $totalMinus = 0;
            $totalNilaiSelisih = 0;

            $opname = StockOpname::create([
                'no_opname' => $request->input('no_opname'),
                'user_id' => $user->id,
                'tanggal' => $request->input('tanggal'),
                'status' => 'selesai',
                'catatan' => $request->input('catatan'),
            ]);

            foreach ($request->input('items') as $item) {
                $batch = BatchObat::with('obat')->lockForUpdate()->find($item['batch_id']);
                $stokSistem = $batch->jumlah_saat_ini;
                $stokFisik = (int) $item['stok_fisik'];
                $selisih = $stokFisik - $stokSistem;
                $hargaBeli = (float) $batch->harga_beli;
                $nilaiSelisih = $selisih * $hargaBeli;

                if ($selisih > 0) {
                    $totalPlus += $selisih;
                } elseif ($selisih < 0) {
                    $totalMinus += abs($selisih);
                }

                $totalNilaiSelisih += $nilaiSelisih;

                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'obat_id' => $batch->obat_id,
                    'batch_obat_id' => $batch->id,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $selisih,
                    'harga_beli' => $hargaBeli,
                    'nilai_selisih' => $nilaiSelisih,
                    'alasan' => $item['alasan'],
                ]);

                // Update stok fisik di batch jika ada selisih
                if ($selisih !== 0) {
                    $batch->jumlah_saat_ini = $stokFisik;
                    $batch->save();

                    StokMutasi::create([
                        'obat_id' => $batch->obat_id,
                        'batch_obat_id' => $batch->id,
                        'user_id' => $user->id,
                        'jenis' => 'penyesuaian',
                        'jumlah' => $selisih,
                        'saldo_sebelum' => $stokSistem,
                        'saldo_sesudah' => $stokFisik,
                        'referensi_tipe' => 'StockOpname',
                        'referensi_id' => $opname->id,
                        'keterangan' => "Stock Opname {$opname->no_opname}: {$item['alasan']}",
                    ]);
                }
            }

            $opname->total_selisih_plus = $totalPlus;
            $opname->total_selisih_minus = $totalMinus;
            $opname->total_nilai_selisih = $totalNilaiSelisih;
            $opname->save();

            // Notifikasi real-time jika ada selisih signifikan (nilai > 50rb atau selisih > 5)
            if (abs($totalNilaiSelisih) >= 50000 || ($totalPlus + $totalMinus) >= 5) {
                Notifikasi::create([
                    'role_target' => 'owner',
                    'judul' => '⚖️ Selisih Stock Opname Tercatat',
                    'pesan' => "Opname {$opname->no_opname} selesai oleh {$user->name}. Total selisih: +{$totalPlus} / -{$totalMinus} (Nilai: Rp ".number_format($totalNilaiSelisih, 0, ',', '.').').',
                    'tipe' => 'opname',
                    'link' => "/stok/opname/{$opname->id}",
                ]);
            }

            ActivityLog::record('stock_opname', "Melakukan stock opname {$opname->no_opname}", ['id' => $opname->id]);

            return $opname;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock opname berhasil disimpan dan stok disesuaikan!',
                'redirect' => route('stok.opname.show', $result->id),
            ]);
        }

        return redirect()->route('stok.opname.show', $result->id)->with('success', 'Stock opname berhasil disimpan dan stok disesuaikan!');
    }

    public function show(StockOpname $opname): View
    {
        $opname->load(['user', 'details.obat.satuan', 'details.batchObat']);

        return view('stok.opname.show', compact('opname'));
    }
}
