<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Dokter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokterController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Dokter::withCount('reseps')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_sip', 'like', "%{$search}%")
                    ->orWhere('spesialis', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        $dokters = $query->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $dokters]);
        }

        return view('dokter.index', compact('dokters'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_sip' => ['nullable', 'string', 'max:100'],
            'spesialis' => ['nullable', 'string', 'max:100'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama dokter wajib diisi.',
        ]);

        $dokter = Dokter::create($validated);
        ActivityLog::record('tambah_dokter', "Menambah dokter: {$dokter->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokter berhasil ditambahkan!',
                'data' => $dokter,
            ]);
        }

        return redirect()->route('dokter.index')->with('success', 'Dokter berhasil ditambahkan!');
    }

    public function show(Dokter $dokter): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $dokter]);
    }

    public function update(Request $request, Dokter $dokter): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_sip' => ['nullable', 'string', 'max:100'],
            'spesialis' => ['nullable', 'string', 'max:100'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama dokter wajib diisi.',
        ]);

        $dokter->update($validated);
        ActivityLog::record('edit_dokter', "Mengubah data dokter: {$dokter->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data dokter berhasil diperbarui!',
                'data' => $dokter,
            ]);
        }

        return redirect()->route('dokter.index')->with('success', 'Data dokter berhasil diperbarui!');
    }

    public function destroy(Dokter $dokter): JsonResponse|RedirectResponse
    {
        $nama = $dokter->nama;
        $dokter->delete();
        ActivityLog::record('hapus_dokter', "Menghapus dokter: {$nama}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Dokter {$nama} berhasil dihapus!"]);
        }

        return redirect()->route('dokter.index')->with('success', "Dokter {$nama} berhasil dihapus!");
    }
}
