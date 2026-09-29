<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BatchObat;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\StokMutasi;
use App\Models\Suplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturPembelianController extends Controller
{
    public function index(): View
    {
        $returs = ReturPembelian::with(['suplier', 'user', 'details.obat.satuan'])->latest()->paginate(10);

        return view('pengadaan.retur.index', compact('returs'));
    }

    public function create(): View
    {
        $today = Carbon::today();
        $noRetur = 'RET-BELI-'.date('Ymd').'-'.str_pad(ReturPembelian::whereDate('created_at', $today)->count() + 1, 3, '0', STR_PAD_LEFT);
        $supliers = Suplier::orderBy('nama')->get();

        $batches = BatchObat::with(['obat.satuan', 'suplier'])
            ->where('jumlah_saat_ini', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->get();

        return view('pengadaan.retur.create', compact('noRetur', 'supliers', 'batches'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'no_retur' => ['required', 'string', 'unique:retur_pembelian,no_retur'],
            'suplier_id' => ['required', 'exists:suplier,id'],
            'tanggal_retur' => ['required', 'date'],
            'alasan' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_id' => ['required', 'exists:batch_obat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'suplier_id.required' => 'Suplier PBF tujuan retur wajib dipilih.',
            'alasan.required' => 'Alasan retur wajib diisi (rusak, salah kirim, expired).',
            'items.required' => 'Pilih minimal satu item batch obat untuk diretur.',
        ]);

        $user = Auth::user();

        $retur = DB::transaction(function () use ($validated, $user) {
            $totalNilai = 0;

            $record = ReturPembelian::create([
                'no_retur' => $validated['no_retur'],
                'suplier_id' => $validated['suplier_id'],
                'user_id' => $user->id,
                'tanggal_retur' => $validated['tanggal_retur'],
                'total_nilai' => 0,
                'status' => 'diajukan',
                'alasan' => $validated['alasan'],
            ]);

            foreach ($validated['items'] as $it) {
                $batch = BatchObat::with('obat')->lockForUpdate()->findOrFail($it['batch_id']);
                $qty = (int) $it['jumlah'];

                if ($qty > $batch->jumlah_saat_ini) {
                    throw new \Exception("Jumlah retur {$qty} melebihi sisa stok batch {$batch->no_batch} ({$batch->jumlah_saat_ini}).");
                }

                $hargaSatuan = (float) $batch->harga_beli;
                $subtotal = $qty * $hargaSatuan;
                $totalNilai += $subtotal;

                // Kurangi stok batch
                $saldoSebelum = $batch->jumlah_saat_ini;
                $batch->jumlah_saat_ini -= $qty;
                $batch->save();

                ReturPembelianDetail::create([
                    'retur_pembelian_id' => $record->id,
                    'obat_id' => $batch->obat_id,
                    'batch_obat_id' => $batch->id,
                    'jumlah' => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                ]);

                // Catat mutasi kartu stok
                StokMutasi::create([
                    'obat_id' => $batch->obat_id,
                    'batch_obat_id' => $batch->id,
                    'user_id' => $user->id,
                    'jenis' => 'retur_pembelian',
                    'jumlah' => $qty,
                    'saldo_sebelum' => $saldoSebelum,
                    'saldo_sesudah' => $batch->jumlah_saat_ini,
                    'referensi_tipe' => 'ReturPembelian',
                    'referensi_id' => $record->id,
                    'keterangan' => "Retur Pembelian {$record->no_retur}: {$record->alasan}",
                ]);
            }

            $record->total_nilai = $totalNilai;
            $record->save();

            ActivityLog::record(
                'retur_pembelian',
                "Retur pembelian {$record->no_retur} ke {$record->suplier->nama} senilai Rp ".number_format($totalNilai, 0, ',', '.'),
                ['id' => $record->id]
            );

            return $record;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Retur pembelian berhasil diajukan dan stok batch dipotong!',
                'redirect' => route('pembelian.retur.show', $retur->id),
            ]);
        }

        return redirect()->route('pembelian.retur.show', $retur->id)->with('success', 'Retur pembelian berhasil diajukan dan stok batch dipotong!');
    }

    public function show(ReturPembelian $retur): View
    {
        $retur->load(['suplier', 'user', 'details.obat.satuan', 'details.batchObat']);

        return view('pengadaan.retur.show', compact('retur'));
    }
}
