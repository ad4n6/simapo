<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Dokter;
use App\Models\Notifikasi;
use App\Models\Obat;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Resep;
use App\Models\ShiftKasir;
use App\Services\StokService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected StokService $stokService,
        protected TelegramService $telegramService
    ) {}

    /**
     * Halaman Utama POS Kasir (Satu Layar Full AJAX)
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Cek shift kasir aktif untuk user ini
        $activeShift = ShiftKasir::where('user_id', $user->id)
            ->where('status', 'buka')
            ->latest('waktu_buka')
            ->first();

        $dokters = Dokter::orderBy('nama')->get();
        $pelanggans = Pelanggan::orderBy('nama')->get();

        // Ambil data obat awal (aktif, belum kedaluwarsa, memiliki stok)
        $today = Carbon::today();
        $obats = Obat::with(['kategori', 'satuan', 'batches' => function ($q) use ($today) {
            $q->where('tanggal_kedaluwarsa', '>', $today)
                ->where('jumlah_saat_ini', '>', 0)
                ->orderBy('tanggal_kedaluwarsa', 'asc');
        }])->orderBy('nama')->get()->map(function ($o) {
            $stokTersedia = $o->batches->sum('jumlah_saat_ini');
            $nearestBatch = $o->batches->first();

            return [
                'id' => $o->id,
                'kode' => $o->kode_obat,
                'barcode' => $o->barcode ?? $o->kode_obat,
                'nama' => $o->nama,
                'nama_generik' => $o->nama_generik,
                'kategori' => $o->kategori?->nama ?? 'Umum',
                'satuan' => $o->satuan?->nama ?? 'Pcs',
                'harga_jual' => (float) $o->harga_jual,
                'stok' => $stokTersedia,
                'butuh_resep' => (bool) $o->butuh_resep,
                'rak' => $o->lokasi_rak ?? '-',
                'batch_terdekat' => $nearestBatch ? [
                    'no_batch' => $nearestBatch->no_batch,
                    'expired' => Carbon::parse($nearestBatch->tanggal_kedaluwarsa)->format('d/m/Y'),
                ] : null,
            ];
        });

        return view('pos.index', compact('activeShift', 'dokters', 'pelanggans', 'obats'));
    }

    /**
     * Live Search Obat untuk POS
     */
    public function searchObat(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $today = Carbon::today();

        $obats = Obat::with(['kategori', 'satuan', 'batches' => function ($q) use ($today) {
            $q->where('tanggal_kedaluwarsa', '>', $today)
                ->where('jumlah_saat_ini', '>', 0)
                ->orderBy('tanggal_kedaluwarsa', 'asc');
        }])
            ->where(function ($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%")
                    ->orWhere('kode_obat', 'like', "%{$query}%")
                    ->orWhere('nama_generik', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(25)
            ->get()
            ->map(function ($o) {
                $stokTersedia = $o->batches->sum('jumlah_saat_ini');
                $nearestBatch = $o->batches->first();

                return [
                    'id' => $o->id,
                    'kode' => $o->kode_obat,
                    'barcode' => $o->barcode ?? $o->kode_obat,
                    'nama' => $o->nama,
                    'nama_generik' => $o->nama_generik,
                    'kategori' => $o->kategori?->nama ?? 'Umum',
                    'satuan' => $o->satuan?->nama ?? 'Pcs',
                    'harga_jual' => (float) $o->harga_jual,
                    'stok' => $stokTersedia,
                    'butuh_resep' => (bool) $o->butuh_resep,
                    'rak' => $o->lokasi_rak ?? '-',
                    'batch_terdekat' => $nearestBatch ? [
                        'no_batch' => $nearestBatch->no_batch,
                        'expired' => Carbon::parse($nearestBatch->tanggal_kedaluwarsa)->format('d/m/Y'),
                    ] : null,
                ];
            });

        return response()->json(['success' => true, 'data' => $obats]);
    }

    /**
     * Simpan Transaksi Penjualan POS (AJAX)
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validasi shift kasir
        $activeShift = ShiftKasir::where('user_id', $user->id)
            ->where('status', 'buka')
            ->latest('waktu_buka')
            ->first();

        $validated = $request->validate([
            'jenis_transaksi' => ['required', 'in:bebas,resep'],
            'pelanggan_id' => ['nullable', 'exists:pelanggan,id'],
            'metode_pembayaran' => ['required', 'in:tunai,transfer,qris'],
            'diskon_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'diskon_nominal' => ['nullable', 'numeric', 'min:0'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],

            // Kolom khusus resep
            'no_resep' => ['nullable', 'string', 'max:100'],
            'dokter_id' => ['nullable', 'exists:dokter,id'],
            'nama_dokter' => ['nullable', 'string', 'max:255'],
            'nama_pasien' => ['nullable', 'string', 'max:255'],
            'tanggal_resep' => ['nullable', 'date'],
        ]);

        try {
            $penjualan = DB::transaction(function () use ($validated, $user, $activeShift) {
                // 1. Cek validasi obat keras / butuh_resep
                $items = $validated['items'];
                $obatIds = array_column($items, 'obat_id');
                $obats = Obat::whereIn('id', $obatIds)->get()->keyBy('id');

                $adaObatKeras = false;
                $subtotal = 0;

                foreach ($items as $it) {
                    $obat = $obats->get($it['obat_id']);
                    if (! $obat) {
                        throw new Exception('Data obat tidak ditemukan.');
                    }
                    if ($obat->butuh_resep) {
                        $adaObatKeras = true;
                    }
                    $subtotal += $obat->harga_jual * $it['jumlah'];
                }

                // Jika ada obat yang butuh resep, transaksi wajib bertipe 'resep'
                if ($adaObatKeras && $validated['jenis_transaksi'] !== 'resep') {
                    throw new Exception('Keranjang memuat obat keras / bertanda resep dokter. Ubah jenis transaksi menjadi "Dengan Resep" dan lengkapi data dokter serta pasien!');
                }

                if ($validated['jenis_transaksi'] === 'resep') {
                    if (empty($validated['nama_pasien'])) {
                        throw new Exception('Nama pasien wajib diisi untuk transaksi dengan resep dokter.');
                    }
                }

                // 2. Hitung diskon & total
                $diskonPersen = (float) ($validated['diskon_persen'] ?? 0);
                $diskonNominal = (float) ($validated['diskon_nominal'] ?? 0);

                if ($diskonPersen > 0) {
                    $diskonNominal = ($subtotal * $diskonPersen) / 100;
                }

                $totalAkhir = max(0, $subtotal - $diskonNominal);
                $jumlahBayar = (float) $validated['jumlah_bayar'];

                if ($validated['metode_pembayaran'] === 'tunai' && $jumlahBayar < $totalAkhir) {
                    $msgKurang = sprintf('Uang pembayaran tunai (Rp %s) kurang dari total tagihan (Rp %s).', number_format($jumlahBayar, 0, ',', '.'), number_format($totalAkhir, 0, ',', '.'));
                    throw new Exception($msgKurang);
                }

                $kembalian = ($validated['metode_pembayaran'] === 'tunai') ? max(0, $jumlahBayar - $totalAkhir) : 0;

                // 3. Simpan Resep jika mode resep
                $resepId = null;
                if ($validated['jenis_transaksi'] === 'resep') {
                    $namaDokter = $validated['nama_dokter'] ?? null;
                    if (! empty($validated['dokter_id'])) {
                        $dokter = Dokter::find($validated['dokter_id']);
                        $namaDokter = $dokter ? $dokter->nama : $namaDokter;
                    }

                    $noResep = $validated['no_resep'] ?: ('RSP-'.date('Ymd').'-'.str_pad(Resep::whereDate('created_at', Carbon::today())->count() + 1, 4, '0', STR_PAD_LEFT));

                    $resep = Resep::create([
                        'no_resep' => $noResep,
                        'dokter_id' => $validated['dokter_id'] ?? null,
                        'nama_dokter' => $namaDokter ?? 'Dokter Umum',
                        'nama_pasien' => $validated['nama_pasien'] ?? 'Pasien',
                        'tanggal_resep' => $validated['tanggal_resep'] ?? date('Y-m-d'),
                        'catatan' => $validated['catatan'] ?? null,
                    ]);
                    $resepId = $resep->id;
                }

                // 4. Generate No Transaksi otomatis: TRX-YYYYMMDD-XXXX
                $today = Carbon::today();
                $countToday = Penjualan::whereDate('created_at', $today)->count() + 1;
                $noTransaksi = 'TRX-'.date('Ymd').'-'.str_pad($countToday, 4, '0', STR_PAD_LEFT);

                // 5. Buat Record Penjualan
                $penjualan = Penjualan::create([
                    'no_transaksi' => $noTransaksi,
                    'user_id' => $user->id,
                    'shift_kasir_id' => $activeShift?->id,
                    'pelanggan_id' => $validated['pelanggan_id'] ?? null,
                    'resep_id' => $resepId,
                    'jenis_transaksi' => $validated['jenis_transaksi'],
                    'subtotal' => $subtotal,
                    'diskon_persen' => $diskonPersen,
                    'diskon_nominal' => $diskonNominal,
                    'total_akhir' => $totalAkhir,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'jumlah_bayar' => $jumlahBayar,
                    'kembalian' => $kembalian,
                    'status' => 'selesai',
                    'catatan' => $validated['catatan'] ?? null,
                ]);

                // 6. Kurangi stok setiap item obat via FEFO dan catat detail
                $lowStockAlerts = [];
                foreach ($items as $it) {
                    $obat = $obats->get($it['obat_id']);
                    $qtyDipesan = (int) $it['jumlah'];

                    // Panggil FEFO reduction service
                    $deductions = $this->stokService->kurangiStokFEFO(
                        $obat,
                        $qtyDipesan,
                        'Penjualan',
                        $penjualan->id,
                        $user->id,
                        "Penjualan {$penjualan->no_transaksi}"
                    );

                    foreach ($deductions as $ded) {
                        PenjualanDetail::create([
                            'penjualan_id' => $penjualan->id,
                            'obat_id' => $obat->id,
                            'batch_obat_id' => $ded['batch_id'],
                            'no_batch' => $ded['no_batch'],
                            'harga_beli' => $ded['harga_beli'],
                            'harga_jual' => (float) $obat->harga_jual,
                            'jumlah' => $ded['qty'],
                            'subtotal' => $ded['qty'] * $obat->harga_jual,
                        ]);
                    }

                    // Cek jika stok obat menjadi menipis / kosong setelah transaksi
                    $obatFresh = $obat->fresh();
                    if ($obatFresh->total_stok <= $obatFresh->stok_minimum) {
                        $lowStockAlerts[] = $obatFresh;
                    }
                }

                // 7. Update akumulasi shift kasir jika aktif
                if ($activeShift) {
                    if ($validated['metode_pembayaran'] === 'tunai') {
                        $activeShift->total_penjualan_tunai += $totalAkhir;
                    } else {
                        $activeShift->total_penjualan_non_tunai += $totalAkhir;
                    }
                    $activeShift->save();
                }

                // 8. Kirim notifikasi stok menipis secara instan jika ada
                foreach ($lowStockAlerts as $lowObat) {
                    if ($lowObat->total_stok == 0) {
                        $this->telegramService->kirimPesan(
                            "🚨 <b>STOK KOSONG - Apotek Bintan 88</b>\nObat: {$lowObat->nama}\nStok: 0 {$lowObat->satuan?->nama}\nRak: {$lowObat->lokasi_rak}\nSegera lakukan pengadaan!",
                            'stok_kosong',
                            'obat',
                            $lowObat->id
                        );
                    } else {
                        $this->telegramService->kirimPesan(
                            "⚠️ <b>STOK MENIPIS - Apotek Bintan 88</b>\nObat: {$lowObat->nama}\nSisa Stok: {$lowObat->total_stok} {$lowObat->satuan?->nama} (Min: {$lowObat->stok_minimum})\nRak: {$lowObat->lokasi_rak}\nPesanan ulang direkomendasikan.",
                            'stok_menipis',
                            'obat',
                            $lowObat->id
                        );
                    }
                }

                ActivityLog::record(
                    'penjualan',
                    "Transaksi kasir {$penjualan->no_transaksi} ({$penjualan->metode_pembayaran}) senilai Rp ".number_format($totalAkhir, 0, ',', '.')
                );

                return $penjualan;
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses!',
                'data' => [
                    'id' => $penjualan->id,
                    'no_transaksi' => $penjualan->no_transaksi,
                    'total_akhir' => $penjualan->total_akhir,
                    'kembalian' => $penjualan->kembalian,
                    'cetak_url' => route('pos.struk', $penjualan->id),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Struk Thermal POS 58mm / 80mm
     */
    public function struk(Penjualan $penjualan): View
    {
        $penjualan->load(['user', 'pelanggan', 'resep.dokter', 'details.obat.satuan']);

        return view('pos.struk', compact('penjualan'));
    }
}
