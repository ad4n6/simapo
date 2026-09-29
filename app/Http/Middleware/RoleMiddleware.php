<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->status_aktif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun Anda dinonaktifkan. Hubungi Owner.'], 403);
            }

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi Administrator/Owner.']);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.'], 403);
            }

            abort(403, 'Akses ditolak. Halaman ini hanya untuk role: '.implode(', ', $roles).'.');
        }

        return $next($request);
    }
}
