<?php

namespace App\Support;

/**
 * Normalisasi path foto menu lama dan baru.
 * Foto lokal disajikan lewat route Laravel agar tidak bergantung pada
 * APP_URL atau symbolic link public/storage.
 */
final class MenuImagePath
{
    public const PLACEHOLDER_URL = '/assets/img/coffee-placeholder.svg';

    public static function url(?string $storedPath): string
    {
        $value = trim((string) $storedPath);

        if ($value === '') {
            return self::PLACEHOLDER_URL;
        }

        if (str_starts_with($value, '//')) {
            $value = 'https:'.$value;
        }

        $parts = parse_url($value);

        if (is_array($parts) && isset($parts['scheme'])) {
            $scheme = strtolower((string) $parts['scheme']);
            $host = strtolower((string) ($parts['host'] ?? ''));

            if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
                return self::PLACEHOLDER_URL;
            }

            // URL eksternal (contoh URL CDN) dipertahankan apa adanya.
            $urlPath = (string) ($parts['path'] ?? '');
            if (! self::isApplicationHost($host) && ! self::looksLikeStoredMenuImage($urlPath)) {
                return $value;
            }

            // URL lama yang menunjuk ke domain Kopiah/localhost (termasuk
            // domain lama dari APP_URL) dibuat relatif ke host yang sedang dibuka.
            $value = $urlPath;
        }

        $value = rawurldecode(str_replace('\\', '/', $value));
        $value = ltrim($value, '/');

        $assetPath = self::publicAssetPath($value);
        if ($assetPath !== null) {
            return '/'.$assetPath;
        }

        $storagePath = self::storagePath($value);
        if ($storagePath === null) {
            return self::PLACEHOLDER_URL;
        }

        return route('menu.image', ['path' => $storagePath], false);
    }

    /**
     * Mengembalikan path relatif di storage/app/public atau null untuk path
     * yang bukan file storage menu, termasuk URL eksternal dan path berbahaya.
     */
    public static function storagePath(?string $storedPath): ?string
    {
        $value = trim((string) $storedPath);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '//')) {
            $value = 'https:'.$value;
        }

        $parts = parse_url($value);
        if (is_array($parts) && isset($parts['scheme'])) {
            $scheme = strtolower((string) $parts['scheme']);
            $host = strtolower((string) ($parts['host'] ?? ''));

            $urlPath = (string) ($parts['path'] ?? '');
            if (! in_array($scheme, ['http', 'https'], true) || $host === ''
                || (! self::isApplicationHost($host) && ! self::looksLikeStoredMenuImage($urlPath))) {
                return null;
            }

            $value = $urlPath;
        }

        $value = rawurldecode(str_replace('\\', '/', $value));

        // Path absolut lama dari hosting boleh dipulihkan hanya bila berada
        // tepat di bawah folder storage publik Laravel.
        foreach (['/storage/app/public/', '/public/storage/'] as $absoluteMarker) {
            $markerPosition = strrpos($value, $absoluteMarker);
            if ($markerPosition !== false) {
                $value = substr($value, $markerPosition + strlen($absoluteMarker));
                break;
            }
        }

        $value = ltrim($value, '/');
        $value = preg_replace('#/+#', '/', $value) ?? '';

        foreach ([
            'storage/app/public/',
            'app/public/',
            'public/storage/',
            'storage/',
            'public/',
        ] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = substr($value, strlen($prefix));
                break;
            }
        }

        if ($value === '' || preg_match('/[\x00-\x1F\x7F?#:]/', $value)) {
            return null;
        }

        $segments = explode('/', $value);
        if (in_array('', $segments, true) || in_array('.', $segments, true) || in_array('..', $segments, true)) {
            return null;
        }

        // Hanya folder gambar menu yang boleh disajikan/dihapus melalui helper ini.
        $allowedDirectories = ['menus', 'menu', 'menu-images', 'products'];
        if (! in_array($segments[0], $allowedDirectories, true) || count($segments) < 2) {
            return null;
        }

        return $value;
    }

    private static function publicAssetPath(string $value): ?string
    {
        $value = ltrim($value, '/');
        if (str_starts_with($value, 'public/assets/')) {
            $value = substr($value, strlen('public/'));
        }

        if (! str_starts_with($value, 'assets/')) {
            return null;
        }

        $segments = explode('/', $value);
        if (in_array('', $segments, true) || in_array('.', $segments, true) || in_array('..', $segments, true)
            || preg_match('/[\x00-\x1F\x7F?#:]/', $value)) {
            return null;
        }

        return $value;
    }


    private static function looksLikeStoredMenuImage(string $path): bool
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        foreach (['menus', 'menu', 'menu-images', 'products'] as $directory) {
            foreach (["storage/{$directory}/", "public/storage/{$directory}/", "storage/app/public/{$directory}/"] as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function isApplicationHost(string $host): bool
    {
        $hosts = ['localhost', '127.0.0.1', '::1'];

        $configuredHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (is_string($configuredHost) && $configuredHost !== '') {
            $hosts[] = strtolower($configuredHost);
        }

        try {
            $requestHost = request()->getHost();
            if (is_string($requestHost) && $requestHost !== '') {
                $hosts[] = strtolower($requestHost);
            }
        } catch (\Throwable) {
            // Accessor juga dapat dipakai saat CLI/queue tanpa HTTP request.
        }

        return in_array(strtolower($host), array_unique($hosts), true);
    }
}
