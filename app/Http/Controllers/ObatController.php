<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Satuan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ObatController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Obat::with(['kategori', 'satuan', 'batches'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('nama_generik', 'like', "%{$search}%")
                    ->orWhere('lokasi_rak', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->input('kategori_id'));
        }

        if ($request->filled('butuh_resep')) {
            $query->where('butuh_resep', $request->input('butuh_resep') === '1');
        }

        $obats = $query->paginate(12)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $obats,
            ]);
        }

        $kategoris = Kategori::orderBy('nama')->get();
        $satuans = Satuan::orderBy('nama')->get();

        return view('obat.index', compact('obats', 'kategoris', 'satuans'));
    }

    /**
     * Live search for POS / AJAX dropdowns
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        $results = Obat::with(['satuan', 'kategori', 'availableBatches'])
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%")
                    ->orWhere('nama_generik', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($obat) {
                return [
                    'id' => $obat->id,
                    'kode' => $obat->kode,
                    'nama' => $obat->nama,
                    'nama_generik' => $obat->nama_generik,
                    'satuan' => $obat->satuan?->nama ?? '',
                    'kategori' => $obat->kategori?->nama ?? '',
                    'harga_jual' => (float) $obat->harga_jual,
                    'harga_beli' => (float) $obat->harga_beli,
                    'butuh_resep' => (bool) $obat->butuh_resep,
                    'total_stok' => $obat->total_stok,
                    'lokasi_rak' => $obat->lokasi_rak,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Auto-generate code if empty
        if (! $request->filled('kode')) {
            $count = Obat::withTrashed()->count() + 1;
            $request->merge(['kode' => 'OBT-'.str_pad($count, 3, '0', STR_PAD_LEFT)]);
        }

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:obat,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'nama_generik' => ['nullable', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori,id'],
            'satuan_id' => ['required', 'exists:satuan,id'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'lokasi_rak' => ['nullable', 'string', 'max:100'],
            'butuh_resep' => ['nullable', 'boolean'],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'kode.required' => 'Kode obat wajib diisi.',
            'kode.unique' => 'Kode obat sudah digunakan.',
            'nama.required' => 'Nama obat wajib diisi.',
            'kategori_id.required' => 'Kategori obat wajib dipilih.',
            'satuan_id.required' => 'Satuan obat wajib dipilih.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
        ]);

        $validated['butuh_resep'] = $request->has('butuh_resep') ? (bool) $request->input('butuh_resep') : false;

        $obat = Obat::create($validated);
        ActivityLog::record('tambah_obat', "Menambahkan obat baru: {$obat->nama} ({$obat->kode})");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Obat {$obat->nama} berhasil disimpan!",
                'data' => $obat->load(['kategori', 'satuan']),
            ]);
        }

        return redirect()->route('obat.index')->with('success', "Obat {$obat->nama} berhasil disimpan!");
    }

    public function show(Obat $obat): JsonResponse
    {
        $obat->load([
            'kategori',
            'satuan',
            'batches' => function ($q) {
                $q->with('suplier')->orderBy('tanggal_kedaluwarsa', 'asc');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $obat->id,
                'kode' => $obat->kode,
                'nama' => $obat->nama,
                'nama_generik' => $obat->nama_generik,
                'kategori_id' => $obat->kategori_id,
                'satuan_id' => $obat->satuan_id,
                'kategori' => $obat->kategori?->nama,
                'satuan' => $obat->satuan?->nama,
                'harga_beli' => (float) $obat->harga_beli,
                'harga_jual' => (float) $obat->harga_jual,
                'stok_minimum' => $obat->stok_minimum,
                'lokasi_rak' => $obat->lokasi_rak,
                'butuh_resep' => (bool) $obat->butuh_resep,
                'deskripsi' => $obat->deskripsi,
                'total_stok' => $obat->total_stok,
                'status_stok' => $obat->status_stok,
                'status_kedaluwarsa' => $obat->status_kedaluwarsa,
                'batches' => $obat->batches->map(function ($b) {
                    $today = Carbon::today();
                    $exp = Carbon::parse($b->tanggal_kedaluwarsa);
                    $diff = (int) $today->diffInDays($exp, false);

                    return [
                        'id' => $b->id,
                        'no_batch' => $b->no_batch,
                        'suplier' => $b->suplier?->nama ?? '-',
                        'tanggal_kedaluwarsa' => $b->tanggal_kedaluwarsa->format('d/m/Y'),
                        'sisa_hari' => $diff,
                        'is_expired' => $diff <= 0,
                        'jumlah_masuk' => $b->jumlah_masuk,
                        'jumlah_saat_ini' => $b->jumlah_saat_ini,
                        'harga_beli' => (float) $b->harga_beli,
                    ];
                }),
            ],
        ]);
    }

    public function update(Request $request, Obat $obat): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('obat', 'kode')->ignore($obat->id)],
            'nama' => ['required', 'string', 'max:255'],
            'nama_generik' => ['nullable', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori,id'],
            'satuan_id' => ['required', 'exists:satuan,id'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'lokasi_rak' => ['nullable', 'string', 'max:100'],
            'butuh_resep' => ['nullable', 'boolean'],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'kode.required' => 'Kode obat wajib diisi.',
            'kode.unique' => 'Kode obat sudah digunakan.',
            'nama.required' => 'Nama obat wajib diisi.',
            'kategori_id.required' => 'Kategori obat wajib dipilih.',
            'satuan_id.required' => 'Satuan obat wajib dipilih.',
        ]);

        $validated['butuh_resep'] = $request->has('butuh_resep') ? (bool) $request->input('butuh_resep') : false;

        $obat->update($validated);
        ActivityLog::record('edit_obat', "Mengubah data obat: {$obat->nama} ({$obat->kode})");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data obat {$obat->nama} berhasil diperbarui!",
                'data' => $obat->load(['kategori', 'satuan']),
            ]);
        }

        return redirect()->route('obat.index')->with('success', "Data obat {$obat->nama} berhasil diperbarui!");
    }

    public function destroy(Obat $obat): JsonResponse|RedirectResponse
    {
        $nama = $obat->nama;
        $obat->delete();
        ActivityLog::record('hapus_obat', "Menghapus data obat: {$nama}");

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Obat {$nama} berhasil dihapus!",
            ]);
        }

        return redirect()->route('obat.index')->with('success', "Obat {$nama} berhasil dihapus!");
    }
}
