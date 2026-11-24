<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 300;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        // Throttle per username: 5 kegagalan → terkunci 5 menit.
        $key = 'login:'.Str::lower($credentials['username']);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return back()->withInput($request->only('username'))
                ->withErrors(['username' => "terlalu banyak percobaan. coba lagi dalam {$minutes} menit."]);
        }

        if (! Auth::attempt([...$credentials, 'is_active' => true])) {
            RateLimiter::hit($key, self::LOCK_SECONDS);
            $left = RateLimiter::remaining($key, self::MAX_ATTEMPTS);

            return back()->withInput($request->only('username'))
                ->withErrors(['username' => $left > 0
                    ? "username atau password salah. sisa percobaan: {$left}."
                    : '5 kali percobaan gagal. username dikunci selama 5 menit.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $user = $request->user();
        ActivityLog::record('login', 'Login berhasil sebagai '.$user->role);

        return redirect()->to($user->homeUrl());
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record('logout', 'Logout dari panel');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
