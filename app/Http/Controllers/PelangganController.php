<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pelanggan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Pelanggan::withCount('penjualans')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $pelanggans = $query->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $pelanggans]);
        }

        return view('pelanggan.index', compact('pelanggans'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
        ]);

        $pelanggan = Pelanggan::create($validated);
        ActivityLog::record('tambah_pelanggan', "Menambah pelanggan: {$pelanggan->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil ditambahkan!',
                'data' => $pelanggan,
            ]);
        }

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function show(Pelanggan $pelanggan): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $pelanggan]);
    }

    public function update(Request $request, Pelanggan $pelanggan): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
        ]);

        $pelanggan->update($validated);
        ActivityLog::record('edit_pelanggan', "Mengubah data pelanggan: {$pelanggan->nama}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diperbarui!',
                'data' => $pelanggan,
            ]);
        }

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui!');
    }

    public function destroy(Pelanggan $pelanggan): JsonResponse|RedirectResponse
    {
        $nama = $pelanggan->nama;
        $pelanggan->delete();
        ActivityLog::record('hapus_pelanggan', "Menghapus pelanggan: {$nama}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Pelanggan {$nama} berhasil dihapus!"]);
        }

        return redirect()->route('pelanggan.index')->with('success', "Pelanggan {$nama} berhasil dihapus!");
    }
}
