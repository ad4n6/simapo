<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Suplier;
use Illuminate\View\View;

class PengadaanRekomendasiController extends Controller
{
    public function index(): View
    {
        // Ambil semua obat dengan total stok <= stok minimum
        $lowStockObats = Obat::with(['kategori', 'satuan', 'batches.suplier'])
            ->get()
            ->filter(function ($obat) {
                return $obat->total_stok <= $obat->stok_minimum;
            })
            ->map(function ($obat) {
                $stokSekarang = $obat->total_stok;
                $stokMin = $obat->stok_minimum;
                // Rekomendasi pesanan: (stok minimum * 3) - stok sekarang, minimal 10
                $rekomendasiQty = max(10, ($stokMin * 3) - $stokSekarang);
                $estimasiBiaya = $rekomendasiQty * $obat->harga_beli;

                // Cari suplier terakhir dari batch sebelumnya jika ada
                $lastBatch = $obat->batches()->whereNotNull('suplier_id')->latest()->first();

                return [
                    'id' => $obat->id,
                    'kode' => $obat->kode,
                    'nama' => $obat->nama,
                    'nama_generik' => $obat->nama_generik,
                    'kategori' => $obat->kategori?->nama,
                    'satuan' => $obat->satuan?->nama,
                    'stok_sekarang' => $stokSekarang,
                    'stok_minimum' => $stokMin,
                    'rekomendasi_qty' => $rekomendasiQty,
                    'harga_beli' => (float) $obat->harga_beli,
                    'estimasi_biaya' => $estimasiBiaya,
                    'last_suplier' => $lastBatch?->suplier?->nama ?? 'PBF Kalbe / Kimia Farma',
                    'lokasi_rak' => $obat->lokasi_rak,
                ];
            })
            ->values();

        $totalEstimasiBiaya = $lowStockObats->sum('estimasi_biaya');
        $supliers = Suplier::orderBy('nama')->get();

        return view('pengadaan.rekomendasi.index', compact('lowStockObats', 'totalEstimasiBiaya', 'supliers'));
    }
}
