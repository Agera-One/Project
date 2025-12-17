<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        // Cek apakah role user termasuk dalam role yang diizinkan
        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        // PESAN YANG LEBIH JELAS & DINAMIS
        $allowedRoles = implode(', ', $roles);
        $currentRole = $user->role ?? 'tidak ada role';

        abort(403, "Akses ditolak! Halaman ini hanya untuk: {$allowedRoles}.
                   Role kamu: {$currentRole}.");
    }
}
