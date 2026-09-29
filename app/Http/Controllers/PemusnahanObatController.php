<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BatchObat;
use App\Models\PemusnahanObat;
use App\Models\Pengaturan;
use App\Models\StokMutasi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PemusnahanObatController extends Controller
{
    public function index(): View
    {
        $pemusnahans = PemusnahanObat::with(['user', 'obat.satuan', 'batchObat'])->latest()->paginate(10);

        return view('stok.pemusnahan.index', compact('pemusnahans'));
    }

    public function create(): View
    {
        // Ambil batch yang sudah kedaluwarsa atau mendekati kedaluwarsa yang masih memiliki stok
        $today = Carbon::today();
        $expiredBatches = BatchObat::with(['obat.satuan', 'suplier'])
            ->where('jumlah_saat_ini', '>', 0)
            ->where('tanggal_kedaluwarsa', '<=', $today->copy()->addDays(30))
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->get();

        $noPemusnahan = 'BA-BAST-'.date('Ymd').'-'.str_pad(PemusnahanObat::whereDate('created_at', $today)->count() + 1, 3, '0', STR_PAD_LEFT);

        return view('stok.pemusnahan.create', compact('expiredBatches', 'noPemusnahan'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'no_pemusnahan' => ['required', 'string', 'unique:pemusnahan_obat,no_pemusnahan'],
            'batch_obat_id' => ['required', 'exists:batch_obat,id'],
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'alasan' => ['required', 'string'],
            'saksi' => ['required', 'string', 'max:255'],
            'metode_pemusnahan' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ], [
            'batch_obat_id.required' => 'Batch obat wajib dipilih.',
            'jumlah.required' => 'Jumlah obat yang dimusnahkan wajib diisi.',
            'alasan.required' => 'Alasan pemusnahan wajib diisi.',
            'saksi.required' => 'Nama saksi pemusnahan wajib diisi.',
            'metode_pemusnahan.required' => 'Metode pemusnahan wajib dipilih / diisi.',
        ]);

        $user = Auth::user();

        $pemusnahan = DB::transaction(function () use ($validated, $user) {
            $batch = BatchObat::with('obat')->lockForUpdate()->findOrFail($validated['batch_obat_id']);

            if ($validated['jumlah'] > $batch->jumlah_saat_ini) {
                throw new \Exception("Jumlah pemusnahan ({$validated['jumlah']}) melebihi stok batch ({$batch->jumlah_saat_ini}).");
            }

            $saldoSebelum = $batch->jumlah_saat_ini;
            $batch->jumlah_saat_ini -= $validated['jumlah'];
            $batch->save();

            $record = PemusnahanObat::create([
                'no_pemusnahan' => $validated['no_pemusnahan'],
                'user_id' => $user->id,
                'obat_id' => $batch->obat_id,
                'batch_obat_id' => $batch->id,
                'tanggal' => $validated['tanggal'],
                'jumlah' => $validated['jumlah'],
                'alasan' => $validated['alasan'],
                'saksi' => $validated['saksi'],
                'metode_pemusnahan' => $validated['metode_pemusnahan'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            StokMutasi::create([
                'obat_id' => $batch->obat_id,
                'batch_obat_id' => $batch->id,
                'user_id' => $user->id,
                'jenis' => 'pemusnahan',
                'jumlah' => $validated['jumlah'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $batch->jumlah_saat_ini,
                'referensi_tipe' => 'PemusnahanObat',
                'referensi_id' => $record->id,
                'keterangan' => "Berita Acara Pemusnahan {$record->no_pemusnahan}: {$record->alasan}",
            ]);

            ActivityLog::record(
                'pemusnahan_obat',
                "Pemusnahan obat {$batch->obat->nama} batch {$batch->no_batch} sejumlah {$validated['jumlah']}",
                ['id' => $record->id]
            );

            return $record;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Berita acara pemusnahan obat berhasil dicatat dan stok dikurangi!',
                'redirect' => route('stok.pemusnahan.show', $pemusnahan->id),
            ]);
        }

        return redirect()->route('stok.pemusnahan.show', $pemusnahan->id)->with('success', 'Berita acara pemusnahan obat berhasil dicatat dan stok dikurangi!');
    }

    public function show(PemusnahanObat $pemusnahan): View
    {
        $pemusnahan->load(['user', 'obat.satuan', 'obat.kategori', 'batchObat.suplier']);
        $apotek = [
            'nama' => Pengaturan::getValue('nama_apotek', 'Apotek Bintan 88'),
            'alamat' => Pengaturan::getValue('alamat_apotek', 'Bintan Timur'),
            'sia' => Pengaturan::getValue('sia_apotek', '-'),
            'sipa' => Pengaturan::getValue('sipa_apoteker', '-'),
            'pj' => Pengaturan::getValue('nama_apoteker_pj', 'Apoteker Penanggung Jawab'),
        ];

        return view('stok.pemusnahan.show', compact('pemusnahan', 'apotek'));
    }

    public function cetak(PemusnahanObat $pemusnahan): View
    {
        $pemusnahan->load(['user', 'obat.satuan', 'obat.kategori', 'batchObat.suplier']);
        $apotek = [
            'nama' => Pengaturan::getValue('nama_apotek', 'Apotek Bintan 88'),
            'alamat' => Pengaturan::getValue('alamat_apotek', 'Bintan Timur'),
            'sia' => Pengaturan::getValue('sia_apotek', '-'),
            'sipa' => Pengaturan::getValue('sipa_apoteker', '-'),
            'pj' => Pengaturan::getValue('nama_apoteker_pj', 'Apoteker Penanggung Jawab'),
        ];

        return view('stok.pemusnahan.cetak', compact('pemusnahan', 'apotek'));
    }
}
