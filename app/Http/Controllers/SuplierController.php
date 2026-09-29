<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Suplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuplierController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Suplier::withCount('pembelians')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kontak_person', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $supliers = $query->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $supliers]);
        }

        return view('suplier.index', compact('supliers'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kontak_person' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama suplier wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $suplier = Suplier::create($validated);
        ActivityLog::record('tambah_suplier', "Menambah suplier: {$suplier->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Suplier berhasil ditambahkan!',
                'data' => $suplier,
            ]);
        }

        return redirect()->route('suplier.index')->with('success', 'Suplier berhasil ditambahkan!');
    }

    public function show(Suplier $suplier): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $suplier]);
    }

    public function update(Request $request, Suplier $suplier): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kontak_person' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama suplier wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $suplier->update($validated);
        ActivityLog::record('edit_suplier', "Mengubah data suplier: {$suplier->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data suplier berhasil diperbarui!',
                'data' => $suplier,
            ]);
        }

        return redirect()->route('suplier.index')->with('success', 'Data suplier berhasil diperbarui!');
    }

    public function destroy(Suplier $suplier): JsonResponse|RedirectResponse
    {
        $nama = $suplier->nama;
        $suplier->delete();
        ActivityLog::record('hapus_suplier', "Menghapus suplier: {$nama}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Suplier {$nama} berhasil dihapus!"]);
        }

        return redirect()->route('suplier.index')->with('success', "Suplier {$nama} berhasil dihapus!");
    }
}
