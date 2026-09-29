<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = User::query()->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telegram_chat_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status_aktif', $request->input('status') === '1');
        }

        $users = $query->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        }

        return view('users.index', compact('users'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in(['owner', 'apoteker', 'kasir', 'admin_gudang'])],
            'telegram_chat_id' => ['nullable', 'string', 'max:50'],
            'status_aktif' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'telegram_chat_id' => $validated['telegram_chat_id'] ?? null,
            'status_aktif' => $request->has('status_aktif') ? (bool) $request->input('status_aktif') : true,
        ]);

        ActivityLog::record('tambah_user', "Menambahkan user baru: {$user->name} ({$user->role})", ['user_id' => $user->id]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan!',
                'data' => $user,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan!');
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in(['owner', 'apoteker', 'kasir', 'admin_gudang'])],
            'telegram_chat_id' => ['nullable', 'string', 'max:50'],
            'status_aktif' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Kata sandi minimal 6 karakter jika diubah.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->telegram_chat_id = $validated['telegram_chat_id'] ?? null;
        $user->status_aktif = $request->has('status_aktif') ? (bool) $request->input('status_aktif') : $user->status_aktif;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        ActivityLog::record('edit_user', "Memperbarui user: {$user->name} ({$user->role})", ['user_id' => $user->id]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diperbarui!',
                'data' => $user,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function toggleStatus(User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menonaktifkan akun sendiri!',
            ], 422);
        }

        $user->status_aktif = ! $user->status_aktif;
        $user->save();

        $statusText = $user->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::record('toggle_status_user', "Status user {$user->name} {$statusText}", ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => "User berhasil {$statusText}!",
            'status_aktif' => $user->status_aktif,
        ]);
    }

    public function destroy(User $user): JsonResponse|RedirectResponse
    {
        if ($user->id === Auth::id()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri!'], 422);
            }

            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record('hapus_user', "Menghapus user: {$name}", ['user_id' => $user->id]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "User {$name} berhasil dihapus!",
            ]);
        }

        return redirect()->route('users.index')->with('success', "User {$name} berhasil dihapus!");
    }
}
