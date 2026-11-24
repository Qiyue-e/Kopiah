<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Memproses foto Moments dari kamera: decode ulang dengan GD, crop persegi,
 * batasi ukuran 1080 px, lalu simpan sebagai JPEG pada disk public.
 */
class MomentImageService
{
    public function store(UploadedFile $file): string
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new RuntimeException('Ekstensi GD belum aktif di PHP. Aktifkan extension=gd lalu restart server.');
        }

        $contents = @file_get_contents($file->getRealPath());

        if ($contents === false || $contents === '') {
            throw new InvalidArgumentException('File foto tidak dapat dibaca. Coba ambil foto lagi.');
        }

        $source = @imagecreatefromstring($contents);

        if (! $source) {
            throw new InvalidArgumentException('Gambar gagal diproses. Coba ambil foto lagi.');
        }

        $square = null;

        try {
            $width = imagesx($source);
            $height = imagesy($source);

            if ($width < 1 || $height < 1) {
                throw new InvalidArgumentException('Ukuran foto tidak valid. Coba ambil foto lagi.');
            }

            $side = min($width, $height);
            $size = min(1080, $side);
            $square = imagecreatetruecolor($size, $size);

            if (! $square) {
                throw new RuntimeException('Foto gagal disiapkan oleh server.');
            }

            if (! imagecopyresampled(
                $square,
                $source,
                0,
                0,
                intdiv($width - $side, 2),
                intdiv($height - $side, 2),
                $size,
                $size,
                $side,
                $side
            )) {
                throw new RuntimeException('Foto gagal diproses oleh server.');
            }

            ob_start();
            $encoded = imagejpeg($square, null, 86);
            $jpeg = (string) ob_get_clean();

            if (! $encoded || $jpeg === '') {
                throw new RuntimeException('Foto gagal dikonversi ke format JPEG.');
            }
        } finally {
            imagedestroy($source);

            if ($square) {
                imagedestroy($square);
            }
        }

        $path = 'moments/'.Str::random(36).'.jpg';
        $disk = Storage::disk('public');

        try {
            $disk->makeDirectory('moments');
            $stored = $disk->put($path, $jpeg);

            // Jangan mengembalikan path ketika Flysystem gagal menyimpan file.
            if (! $stored || ! $disk->exists($path)) {
                throw new RuntimeException('Foto belum berhasil disimpan ke penyimpanan. Cek izin folder storage/app/public.');
            }
        } catch (Throwable $exception) {
            try {
                $disk->delete($path);
            } catch (Throwable) {
                // Abaikan kegagalan pembersihan file parsial; error utama tetap dilaporkan.
            }

            throw new RuntimeException(
                'Foto belum berhasil disimpan. Cek koneksi dan izin folder penyimpanan server.',
                previous: $exception
            );
        }

        return $path;
    }
}
