<?php

namespace App\Http\Controllers;

use App\Models\BatchObat;
use App\Models\Kategori;
use App\Models\Obat;
use App\Models\StokMutasi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StokController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $today = Carbon::today();
        $filter = $request->input('filter', 'semua');
        $query = Obat::with(['kategori', 'satuan', 'batches'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('lokasi_rak', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->input('kategori_id'));
        }

        // Filter status stok
        if ($filter === 'kosong') {
            $query->whereDoesntHave('batches', function ($q) use ($today) {
                $q->where('tanggal_kedaluwarsa', '>', $today)->where('jumlah_saat_ini', '>', 0);
            });
        } elseif ($filter === 'menipis') {
            // Obats whose non-expired stock sum <= stok_minimum and > 0
            $query->whereHas('batches', function ($q) use ($today) {
                $q->where('tanggal_kedaluwarsa', '>', $today)->where('jumlah_saat_ini', '>', 0);
            });
        } elseif ($filter === 'sudah_expired') {
            $query->whereHas('batches', function ($q) use ($today) {
                $q->where('tanggal_kedaluwarsa', '<=', $today)->where('jumlah_saat_ini', '>', 0);
            });
        } elseif (str_starts_with($filter, 'hampir_expired')) {
            $days = 90;
            if ($filter === 'hampir_expired_30') {
                $days = 30;
            }
            if ($filter === 'hampir_expired_60') {
                $days = 60;
            }

            $query->whereHas('batches', function ($q) use ($today, $days) {
                $q->where('tanggal_kedaluwarsa', '>', $today)
                    ->where('tanggal_kedaluwarsa', '<=', $today->copy()->addDays($days))
                    ->where('jumlah_saat_ini', '>', 0);
            });
        }

        $obats = $query->paginate(15)->withQueryString();

        // Additional filter for 'menipis' in collection if needed
        if ($filter === 'menipis') {
            $filteredItems = $obats->getCollection()->filter(function ($obat) {
                $stok = $obat->total_stok;

                return $stok > 0 && $stok <= $obat->stok_minimum;
            });
            $obats->setCollection($filteredItems->values());
        }

        // Summary counts
        $totalObat = Obat::count();
        $totalKosong = Obat::whereDoesntHave('batches', function ($q) use ($today) {
            $q->where('tanggal_kedaluwarsa', '>', $today)->where('jumlah_saat_ini', '>', 0);
        })->count();

        $totalExpired = BatchObat::where('tanggal_kedaluwarsa', '<=', $today)
            ->where('jumlah_saat_ini', '>', 0)
            ->distinct('obat_id')
            ->count('obat_id');

        $totalHampirExpired = BatchObat::where('tanggal_kedaluwarsa', '>', $today)
            ->where('tanggal_kedaluwarsa', '<=', $today->copy()->addDays(90))
            ->where('jumlah_saat_ini', '>', 0)
            ->distinct('obat_id')
            ->count('obat_id');

        $kategoris = Kategori::orderBy('nama')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $obats,
            ]);
        }

        return view('stok.index', compact(
            'obats',
            'kategoris',
            'filter',
            'totalObat',
            'totalKosong',
            'totalExpired',
            'totalHampirExpired'
        ));
    }

    /**
     * Kartu Stok / Mutasi Riwayat
     */
    public function mutasi(Request $request): View|JsonResponse
    {
        $query = StokMutasi::with(['obat.satuan', 'batchObat', 'user'])->latest();

        if ($request->filled('obat_id')) {
            $query->where('obat_id', $request->input('obat_id'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        if ($request->filled('tgl_mulai')) {
            $query->whereDate('created_at', '>=', $request->input('tgl_mulai'));
        }

        if ($request->filled('tgl_selesai')) {
            $query->whereDate('created_at', '<=', $request->input('tgl_selesai'));
        }

        $mutasis = $query->paginate(20)->withQueryString();
        $obats = Obat::orderBy('nama')->get();

        $selectedObat = $request->filled('obat_id') ? Obat::with('satuan')->find($request->input('obat_id')) : null;

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $mutasis]);
        }

        return view('stok.mutasi', compact('mutasis', 'obats', 'selectedObat'));
    }
}
