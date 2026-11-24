<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Pembatas akses per role: ->middleware('role:owner') atau 'role:kasir,owner'. */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', 'akun tidak aktif atau sesi berakhir.');
        }

        abort_unless(in_array($user->role, $roles, true), 403, 'Akun kamu tidak memiliki izin untuk halaman ini.');

        return $next($request);
    }
}
