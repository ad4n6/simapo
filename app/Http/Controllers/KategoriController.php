<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Kategori::withCount('obats')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $kategoris = $query->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $kategoris]);
        }

        return view('kategori.index', compact('kategoris'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:kategori,nama'],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique' => 'Nama kategori sudah ada.',
        ]);

        $kategori = Kategori::create($validated);
        ActivityLog::record('tambah_kategori', "Menambah kategori: {$kategori->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan!',
                'data' => $kategori,
            ]);
        }

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function show(Kategori $kategori): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $kategori]);
    }

    public function update(Request $request, Kategori $kategori): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('kategori', 'nama')->ignore($kategori->id)],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique' => 'Nama kategori sudah digunakan.',
        ]);

        $kategori->update($validated);
        ActivityLog::record('edit_kategori', "Mengubah kategori: {$kategori->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui!',
                'data' => $kategori,
            ]);
        }

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Kategori $kategori): JsonResponse|RedirectResponse
    {
        if ($kategori->obats()->count() > 0) {
            $msg = 'Kategori tidak dapat dihapus karena masih digunakan oleh obat terkait.';
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $nama = $kategori->nama;
        $kategori->delete();
        ActivityLog::record('hapus_kategori', "Menghapus kategori: {$nama}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Kategori {$nama} berhasil dihapus!"]);
        }

        return redirect()->route('kategori.index')->with('success', "Kategori {$nama} berhasil dihapus!");
    }
}
