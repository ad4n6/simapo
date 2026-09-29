<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Satuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SatuanController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Satuan::withCount('obats')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $satuans = $query->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $satuans]);
        }

        return view('satuan.index', compact('satuans'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:satuan,nama'],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique' => 'Nama satuan sudah ada.',
        ]);

        $satuan = Satuan::create($validated);
        ActivityLog::record('tambah_satuan', "Menambah satuan: {$satuan->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil ditambahkan!',
                'data' => $satuan,
            ]);
        }

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan!');
    }

    public function show(Satuan $satuan): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $satuan]);
    }

    public function update(Request $request, Satuan $satuan): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('satuan', 'nama')->ignore($satuan->id)],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique' => 'Nama satuan sudah digunakan.',
        ]);

        $satuan->update($validated);
        ActivityLog::record('edit_satuan', "Mengubah satuan: {$satuan->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil diperbarui!',
                'data' => $satuan,
            ]);
        }

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diperbarui!');
    }

    public function destroy(Satuan $satuan): JsonResponse|RedirectResponse
    {
        if ($satuan->obats()->count() > 0) {
            $msg = 'Satuan tidak dapat dihapus karena masih digunakan oleh obat terkait.';
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $nama = $satuan->nama;
        $satuan->delete();
        ActivityLog::record('hapus_satuan', "Menghapus satuan: {$nama}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Satuan {$nama} berhasil dihapus!"]);
        }

        return redirect()->route('satuan.index')->with('success', "Satuan {$nama} berhasil dihapus!");
    }
}
