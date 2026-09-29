<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BatchObat;
use App\Models\Dokter;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Resep;
use App\Models\ReturPembelian;
use App\Models\ReturPenjualan;
use App\Models\StokMutasi;
use App\Models\Suplier;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * 1. Laporan Penjualan (Harian/Bulanan/Per Kasir/Per Metode Bayar)
     */
    public function penjualan(Request $request): View|Response|StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = Penjualan::with(['user', 'pelanggan', 'details.obat'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'selesai')
            ->latest();

        if ($request->filled('kasir_id')) {
            $query->where('user_id', $request->input('kasir_id'));
        }

        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->input('metode_pembayaran'));
        }

        if ($request->filled('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->input('jenis_transaksi'));
        }

        $penjualans = $query->get();
        $totalOmset = $penjualans->sum('total_akhir');
        $totalDiskon = $penjualans->sum('diskon_nominal');
        $totalTrx = $penjualans->count();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.penjualan', compact('penjualans', 'startDate', 'endDate', 'totalOmset', 'totalDiskon', 'totalTrx'))
                ->setPaper('a4', 'landscape');

            return $pdf->download("Laporan_Penjualan_{$startDate}_{$endDate}.pdf");
        }

        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            return $this->exportCsv(
                "Laporan_Penjualan_{$startDate}_{$endDate}.csv",
                ['No. Transaksi', 'Tanggal', 'Kasir', 'Pelanggan', 'Jenis', 'Metode Bayar', 'Subtotal', 'Diskon', 'Total Akhir'],
                $penjualans->map(fn ($p) => [
                    $p->no_transaksi,
                    $p->created_at->format('d/m/Y H:i'),
                    $p->user->nama ?? '-',
                    $p->pelanggan->nama ?? 'Umum',
                    ucfirst($p->jenis_transaksi),
                    strtoupper($p->metode_pembayaran),
                    $p->subtotal,
                    $p->diskon_nominal,
                    $p->total_akhir,
                ])->toArray()
            );
        }

        $kasirs = User::whereIn('role', ['owner', 'apoteker', 'kasir'])->get();

        return view('laporan.penjualan', compact('penjualans', 'startDate', 'endDate', 'totalOmset', 'totalDiskon', 'totalTrx', 'kasirs'));
    }

    /**
     * 2. Laporan Penjualan Per Obat (Terlaris)
     */
    public function penjualanObat(Request $request): View|Response|StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = PenjualanDetail::select(
            'obat_id',
            DB::raw('SUM(jumlah) as total_qty'),
            DB::raw('SUM(subtotal) as total_omset'),
            DB::raw('SUM(jumlah * harga_beli) as total_hpp')
        )
            ->whereHas('penjualan', function ($q) use ($startDate, $endDate) {
                $q->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->where('status', 'selesai');
            })
            ->with(['obat.kategori', 'obat.satuan'])
            ->groupBy('obat_id')
            ->orderByDesc('total_qty');

        $items = $query->get();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.penjualan_obat', compact('items', 'startDate', 'endDate'))
                ->setPaper('a4', 'portrait');

            return $pdf->download("Laporan_Obat_Terlaris_{$startDate}_{$endDate}.pdf");
        }

        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            return $this->exportCsv(
                "Laporan_Obat_Terlaris_{$startDate}_{$endDate}.csv",
                ['Kode', 'Nama Obat', 'Kategori', 'Satuan', 'Qty Terjual', 'Total Omset', 'Total HPP', 'Margin Keuntungan'],
                $items->map(fn ($it) => [
                    $it->obat->kode_obat,
                    $it->obat->nama,
                    $it->obat->kategori->nama ?? '-',
                    $it->obat->satuan->nama ?? 'Pcs',
                    $it->total_qty,
                    $it->total_omset,
                    $it->total_hpp,
                    $it->total_omset - $it->total_hpp,
                ])->toArray()
            );
        }

        return view('laporan.penjualan_obat', compact('items', 'startDate', 'endDate'));
    }

    /**
     * 3. Laporan Penjualan Obat Resep (Dokter & Pasien)
     */
    public function resep(Request $request): View|Response|StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = Resep::with(['dokter', 'penjualans.details.obat.satuan', 'penjualans.user'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest();

        if ($request->filled('dokter_id')) {
            $query->where('dokter_id', $request->input('dokter_id'));
        }

        $reseps = $query->get();
        $dokters = Dokter::orderBy('nama')->get();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.resep', compact('reseps', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->download("Laporan_Obat_Resep_{$startDate}_{$endDate}.pdf");
        }

        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            $rows = [];
            foreach ($reseps as $r) {
                foreach ($r->penjualans as $p) {
                    foreach ($p->details as $d) {
                        $rows[] = [
                            $r->no_resep,
                            $r->created_at->format('d/m/Y'),
                            $r->nama_pasien,
                            $r->nama_dokter,
                            $d->obat->nama,
                            $d->no_batch ?? '-',
                            $d->jumlah,
                            $d->obat->satuan->nama ?? 'Pcs',
                            $d->subtotal,
                        ];
                    }
                }
            }

            return $this->exportCsv(
                "Laporan_Obat_Resep_{$startDate}_{$endDate}.csv",
                ['No. Resep', 'Tanggal', 'Nama Pasien', 'Nama Dokter', 'Obat', 'Batch', 'Qty', 'Satuan', 'Nilai'],
                $rows
            );
        }

        return view('laporan.resep', compact('reseps', 'startDate', 'endDate', 'dokters'));
    }

    /**
     * 4. Laporan Pembelian & Hutang Suplier
     */
    public function pembelian(Request $request): View|Response|StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = Pembelian::with(['suplier', 'user', 'details.obat'])
            ->whereDate('tanggal_faktur', '>=', $startDate)
            ->whereDate('tanggal_faktur', '<=', $endDate)
            ->latest('tanggal_faktur');

        if ($request->filled('suplier_id')) {
            $query->where('suplier_id', $request->input('suplier_id'));
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->input('status_pembayaran'));
        }

        $pembelians = $query->get();
        $totalNilaiFaktur = $pembelians->sum('total_akhir');
        $totalTerbayar = $pembelians->sum('total_terbayar');
        $totalSisaHutang = $pembelians->sum('sisa_hutang');
        $supliers = Suplier::orderBy('nama')->get();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.pembelian', compact('pembelians', 'startDate', 'endDate', 'totalNilaiFaktur', 'totalTerbayar', 'totalSisaHutang'))
                ->setPaper('a4', 'landscape');

            return $pdf->download("Laporan_Pembelian_Hutang_{$startDate}_{$endDate}.pdf");
        }

        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            return $this->exportCsv(
                "Laporan_Pembelian_Hutang_{$startDate}_{$endDate}.csv",
                ['No. Faktur', 'Tgl Faktur', 'Distributor PBF', 'Jenis Bayar', 'Jatuh Tempo', 'Total Akhir', 'Terbayar', 'Sisa Hutang', 'Status'],
                $pembelians->map(fn ($p) => [
                    $p->no_faktur,
                    $p->tanggal_faktur,
                    $p->suplier->nama,
                    ucfirst($p->jenis_pembayaran),
                    $p->tanggal_jatuh_tempo ?? '-',
                    $p->total_akhir,
                    $p->total_terbayar,
                    $p->sisa_hutang,
                    ucfirst($p->status_pembayaran),
                ])->toArray()
            );
        }

        return view('laporan.pembelian', compact('pembelians', 'startDate', 'endDate', 'totalNilaiFaktur', 'totalTerbayar', 'totalSisaHutang', 'supliers'));
    }

    /**
     * 5. Laporan Stok Saat Ini & Nilai Persediaan
     */
    public function stok(Request $request): View|Response|StreamedResponse
    {
        $obats = Obat::with(['kategori', 'satuan', 'batches' => function ($q) {
            $q->where('jumlah_saat_ini', '>', 0);
        }])->orderBy('nama')->get();

        $totalNilaiBeli = 0;
        $totalNilaiJual = 0;

        foreach ($obats as $o) {
            foreach ($o->batches as $b) {
                $totalNilaiBeli += ($b->jumlah_saat_ini * $b->harga_beli);
                $totalNilaiJual += ($b->jumlah_saat_ini * $o->harga_jual);
            }
        }

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.stok', compact('obats', 'totalNilaiBeli', 'totalNilaiJual'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('Laporan_Nilai_Persediaan_Stok_'.date('Ymd').'.pdf');
        }

        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            return $this->exportCsv(
                'Laporan_Nilai_Persediaan_Stok_'.date('Ymd').'.csv',
                ['Kode', 'Nama Obat', 'Kategori', 'Total Stok', 'Satuan', 'Harga Beli Rata2', 'Harga Jual', 'Total Nilai Beli (HPP)', 'Total Nilai Jual'],
                $obats->map(function ($o) {
                    $stok = $o->total_stok;
                    $valBeli = $o->batches->sum(fn ($b) => $b->jumlah_saat_ini * $b->harga_beli);
                    $valJual = $stok * $o->harga_jual;

                    return [
                        $o->kode_obat,
                        $o->nama,
                        $o->kategori->nama ?? '-',
                        $stok,
                        $o->satuan->nama ?? 'Pcs',
                        $o->harga_beli,
                        $o->harga_jual,
                        $valBeli,
                        $valJual,
                    ];
                })->toArray()
            );
        }

        return view('laporan.stok', compact('obats', 'totalNilaiBeli', 'totalNilaiJual'));
    }

    /**
     * 6. Laporan Obat Menipis & Kosong
     */
    public function stokMenipis(Request $request): View|Response|StreamedResponse
    {
        $obats = Obat::with(['kategori', 'satuan'])->get()->filter(function ($o) {
            return $o->total_stok <= $o->stok_minimum;
        });

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.stok_menipis', compact('obats'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('Laporan_Stok_Menipis_Kosong_'.date('Ymd').'.pdf');
        }

        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            return $this->exportCsv(
                'Laporan_Stok_Menipis_Kosong_'.date('Ymd').'.csv',
                ['Kode', 'Nama Obat', 'Kategori', 'Stok Saat Ini', 'Stok Minimum', 'Satuan', 'Lokasi Rak', 'Status'],
                $obats->map(fn ($o) => [
                    $o->kode_obat,
                    $o->nama,
                    $o->kategori->nama ?? '-',
                    $o->total_stok,
                    $o->stok_minimum,
                    $o->satuan->nama ?? 'Pcs',
                    $o->lokasi_rak ?? '-',
                    $o->total_stok == 0 ? 'KOSONG' : 'MENIPIS',
                ])->toArray()
            );
        }

        return view('laporan.stok_menipis', compact('obats'));
    }

    /**
     * 7. Laporan Obat Kedaluwarsa & Hampir Kedaluwarsa
     */
    public function expired(Request $request): View|Response|StreamedResponse
    {
        $today = Carbon::today();
        $targetSoon = Carbon::today()->addDays(90);

        $expiredBatches = BatchObat::with(['obat.satuan', 'suplier'])
            ->where('tanggal_kedaluwarsa', '<=', $today)
            ->where('jumlah_saat_ini', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->get();

        $nearExpiredBatches = BatchObat::with(['obat.satuan', 'suplier'])
            ->where('tanggal_kedaluwarsa', '>', $today)
            ->where('tanggal_kedaluwarsa', '<=', $targetSoon)
            ->where('jumlah_saat_ini', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->get();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.expired', compact('expiredBatches', 'nearExpiredBatches'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('Laporan_Obat_Kedaluwarsa_'.date('Ymd').'.pdf');
        }

        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            $rows = [];
            foreach ($expiredBatches as $b) {
                $rows[] = [$b->obat->kode_obat, $b->obat->nama, $b->no_batch, $b->tanggal_kedaluwarsa, $b->jumlah_saat_ini, $b->obat->satuan->nama ?? 'Pcs', 'SUDAH KEDALUWARSA'];
            }
            foreach ($nearExpiredBatches as $b) {
                $rows[] = [$b->obat->kode_obat, $b->obat->nama, $b->no_batch, $b->tanggal_kedaluwarsa, $b->jumlah_saat_ini, $b->obat->satuan->nama ?? 'Pcs', 'HAMPIR KEDALUWARSA'];
            }

            return $this->exportCsv(
                'Laporan_Obat_Kedaluwarsa_'.date('Ymd').'.csv',
                ['Kode', 'Nama Obat', 'No. Batch', 'Tanggal ED', 'Sisa Stok', 'Satuan', 'Status Kedaluwarsa'],
                $rows
            );
        }

        return view('laporan.expired', compact('expiredBatches', 'nearExpiredBatches'));
    }

    /**
     * 8. Laporan Retur (Penjualan & Pembelian)
     */
    public function retur(Request $request): View|Response|StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $returPenjualans = ReturPenjualan::with(['penjualan', 'user', 'details.obat'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->get();

        $returPembelians = ReturPembelian::with(['suplier', 'user', 'details.obat'])
            ->whereDate('tanggal_retur', '>=', $startDate)
            ->whereDate('tanggal_retur', '<=', $endDate)
            ->latest('tanggal_retur')
            ->get();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.retur', compact('returPenjualans', 'returPembelians', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->download("Laporan_Retur_{$startDate}_{$endDate}.pdf");
        }

        return view('laporan.retur', compact('returPenjualans', 'returPembelians', 'startDate', 'endDate'));
    }

    /**
     * 9. Laporan Mutasi Stok / Kartu Stok
     */
    public function mutasiStok(Request $request): View|Response|StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = StokMutasi::with(['obat.satuan', 'batchObat', 'user'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest();

        if ($request->filled('obat_id')) {
            $query->where('obat_id', $request->input('obat_id'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        $mutasis = $query->paginate(20)->withQueryString();
        $obats = Obat::orderBy('nama')->get();

        if ($request->input('export') === 'pdf') {
            $allMutasis = $query->get();
            $pdf = Pdf::loadView('laporan.pdf.mutasi', compact('allMutasis', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->download("Laporan_Mutasi_Stok_{$startDate}_{$endDate}.pdf");
        }

        return view('laporan.mutasi', compact('mutasis', 'obats', 'startDate', 'endDate'));
    }

    /**
     * 10. Laporan Laba Rugi Sederhana (Penjualan - HPP - Diskon)
     */
    public function labaRugi(Request $request): View|Response|StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $penjualans = Penjualan::with('details')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'selesai')
            ->get();

        $grossSales = $penjualans->sum('subtotal');
        $totalDiskon = $penjualans->sum('diskon_nominal');
        $netSales = $grossSales - $totalDiskon;

        $totalHpp = 0;
        foreach ($penjualans as $p) {
            foreach ($p->details as $d) {
                $totalHpp += ($d->jumlah * $d->harga_beli);
            }
        }

        $labaKotor = $netSales - $totalHpp;
        $marginPersen = $netSales > 0 ? ($labaKotor / $netSales) * 100 : 0;

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.laba_rugi', compact('grossSales', 'totalDiskon', 'netSales', 'totalHpp', 'labaKotor', 'marginPersen', 'startDate', 'endDate'))
                ->setPaper('a4', 'portrait');

            return $pdf->download("Laporan_Laba_Rugi_{$startDate}_{$endDate}.pdf");
        }

        return view('laporan.laba_rugi', compact('grossSales', 'totalDiskon', 'netSales', 'totalHpp', 'labaKotor', 'marginPersen', 'startDate', 'endDate'));
    }

    /**
     * 11. Log Aktivitas User (Audit Trail)
     */
    public function activityLog(Request $request): View
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = User::orderBy('nama')->get();

        return view('laporan.activity_log', compact('logs', 'users'));
    }

    /**
     * Helper Ekspor CSV
     */
    protected function exportCsv(string $filename, array $headers, array $data): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            // Write BOM for UTF-8 Excel support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
