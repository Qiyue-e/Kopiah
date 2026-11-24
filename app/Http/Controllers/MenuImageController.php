<?php

namespace App\Http\Controllers;

use App\Support\MenuImagePath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MenuImageController extends Controller
{
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/avif',
    ];

    public function show(string $path): BinaryFileResponse
    {
        $safePath = MenuImagePath::storagePath($path);
        if ($safePath === null) {
            return $this->placeholder();
        }

        $disk = Storage::disk('public');

        try {
            if (! $disk->exists($safePath)) {
                Log::warning('File foto menu tidak ditemukan; placeholder ditampilkan.', [
                    'path' => $safePath,
                ]);

                return $this->placeholder();
            }

            $absolutePath = $disk->path($safePath);
            $realPath = realpath($absolutePath);
            $storageRoot = realpath($disk->path(''));

            // Defense-in-depth: pastikan file hasil resolusi tetap di dalam
            // storage/app/public, bukan di luar folder melalui traversal/symlink.
            if ($realPath === false || $storageRoot === false
                || ! str_starts_with($realPath, rtrim($storageRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)
                || ! is_file($realPath)) {
                Log::warning('Path foto menu berada di luar storage publik.', ['path' => $safePath]);

                return $this->placeholder();
            }

            $mime = $disk->mimeType($safePath);
            if (! in_array($mime, self::ALLOWED_MIMES, true)) {
                Log::warning('Tipe file foto menu tidak diizinkan.', [
                    'path' => $safePath,
                    'mime' => $mime,
                ]);

                return $this->placeholder();
            }

            return response()->file($realPath, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=604800',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (Throwable $exception) {
            Log::error('Gagal membaca foto menu dari storage.', [
                'path' => $safePath,
                'error' => $exception->getMessage(),
            ]);

            return $this->placeholder();
        }
    }

    private function placeholder(): BinaryFileResponse
    {
        return response()->file(public_path('assets/img/coffee-placeholder.svg'), [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Menu-Image-Fallback' => '1',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
