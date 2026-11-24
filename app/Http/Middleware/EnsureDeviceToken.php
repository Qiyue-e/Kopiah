<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identitas anonim per perangkat untuk Kopiah Moments (pelanggan tidak perlu akun).
 * Token disimpan di cookie terenkripsi selama 1 tahun dan dibaca lewat $request->attributes.
 */
class EnsureDeviceToken
{
    public const COOKIE = 'kopiah_device';

    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->cookie(self::COOKIE);

        if (! preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            $token = Str::random(40);
            Cookie::queue(Cookie::make(self::COOKIE, $token, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax'));
        }

        $request->attributes->set('device_token', $token);

        return $next($request);
    }
}
