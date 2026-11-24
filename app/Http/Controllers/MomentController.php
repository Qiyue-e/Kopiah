<?php

namespace App\Http\Controllers;

use App\Models\Moment;
use App\Services\MomentImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Kopiah Moments gaya Locket: kamera langsung, caption, feed, riwayat, dan reaksi emoji.
 * Pelanggan memakai identitas perangkat anonim tanpa akun.
 */
class MomentController extends Controller
{
    private const MAX_PER_HOUR = 5;

    public function index(Request $request): View
    {
        $device = $this->device($request);

        return view('moments.index', [
            'moments' => Moment::visibleTo($device)->with('reactions')->latest()->limit(48)->get(),
            'device' => $device,
            'lastId' => (int) Moment::approved()->max('id'),
        ]);
    }

    /** Sajikan foto Moments tanpa bergantung pada symbolic link public/storage. */
    public function image(string $filename): StreamedResponse
    {
        abort_unless(
            preg_match('/\\A[A-Za-z0-9][A-Za-z0-9._-]{0,180}\\z/D', $filename) === 1
                && ! str_contains($filename, '..'),
            404
        );

        $path = 'moments/'.$filename;
        $disk = Storage::disk('public');

        try {
            $exists = $disk->exists($path);
            $mime = $exists ? $disk->mimeType($path) : null;
            $stream = $exists ? $disk->readStream($path) : false;
        } catch (Throwable $exception) {
            Log::error('Gagal membaca foto Kopiah Moments.', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);
            abort(404);
        }

        if (! $exists || ! is_resource($stream)) {
            Log::warning('File foto Kopiah Moments tidak ditemukan.', ['path' => $path]);
            abort(404);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (! in_array($mime, $allowedMimes, true)) {
            fclose($stream);
            Log::warning('File Moments memiliki tipe konten yang tidak didukung.', [
                'path' => $path,
                'mime' => $mime,
            ]);
            abort(404);
        }

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(Request $request, MomentImageService $images): JsonResponse
    {
        $device = $this->device($request);
        $limiterKey = 'moment-upload:'.$request->ip().'|'.$device;

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_PER_HOUR)) {
            return response()->json(['ok' => false, 'message' => 'maksimal 5 foto per jam. terima kasih sudah memahami ya.'], 429);
        }

        $data = $request->validate([
            'foto' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:140'],
            'nama' => ['nullable', 'string', 'max:40'],
            'avatar' => ['nullable', 'string', 'max:16'],
            'type' => ['nullable', Rule::in(['Makanan', 'Selfie'])],
        ]);

        try {
            $path = $images->store($request->file('foto'));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['ok' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Upload foto Kopiah Moments gagal.', [
                'ip' => $request->ip(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'foto belum berhasil disimpan. silakan coba lagi beberapa saat.',
            ], 500);
        }

        try {
            $moment = Moment::create([
                'nama_pelanggan' => Str::of($data['nama'] ?? '')->squish()->toString() ?: null,
                'avatar' => $data['avatar'] ?? null,
                'caption' => Str::of($data['caption'] ?? '')->squish()->toString() ?: null,
                'tipe_foto' => $data['type'] ?? 'Makanan',
                'file_path' => $path,
                'status' => 'approved',
                'device_token' => $device,
                'ip_address' => $request->ip(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            Log::error('Data foto Kopiah Moments gagal disimpan ke database.', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'foto belum berhasil dibagikan. silakan coba lagi.',
            ], 500);
        }

        RateLimiter::hit($limiterKey, 3600);

        return response()->json([
            'ok' => true,
            'status' => $moment->status,
            'message' => 'Momen berhasil dibagikan!',
        ]);
    }

    /** Beri/ubah/batalkan reaksi emoji (satu reaksi per perangkat per foto). */
    public function react(Request $request, Moment $moment): JsonResponse
    {
        abort_unless($moment->status === 'approved', 404);

        $data = $request->validate(['emoji' => ['required', Rule::in(Moment::REACTIONS)]]);
        $device = $this->device($request);
        $existing = $moment->reactions()->where('device_token', $device)->first();

        if ($existing && $existing->emoji === $data['emoji']) {
            $existing->delete();
            $mine = null;
        } else {
            $moment->reactions()->updateOrCreate(['device_token' => $device], ['emoji' => $data['emoji']]);
            $mine = $data['emoji'];
        }

        return response()->json([
            'ok' => true,
            'mine' => $mine,
            'counts' => (object) $moment->reactions()->get()->countBy('emoji')->all(),
        ]);
    }

    /** Pelanggan hanya boleh menghapus foto milik perangkatnya sendiri. */
    public function destroy(Request $request, Moment $moment): JsonResponse
    {
        // Endpoint pelanggan anonim tidak boleh dipakai akun staf; owner punya endpoint khusus.
        abort_if($request->user() !== null, 403);
        abort_unless($moment->device_token === $this->device($request), 403);

        $disk = Storage::disk('public');
        $path = $moment->file_path;

        try {
            if ($this->isSafeMomentPath($path) && $disk->exists($path) && ! $disk->delete($path)) {
                throw new \RuntimeException('File foto gagal dihapus dari penyimpanan.');
            }

            $moment->delete(); // reaksi ikut terhapus melalui foreign key cascade.
        } catch (Throwable $exception) {
            Log::error('Pelanggan gagal menghapus foto Moments.', [
                'moment_id' => $moment->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['ok' => false, 'message' => 'momen belum berhasil dihapus.'], 500);
        }

        return response()->json(['ok' => true]);
    }

    /** Dipolling halaman Moments untuk memunculkan "↑ momen baru". */
    public function newCount(Request $request): JsonResponse
    {
        $count = Moment::approved()
            ->where('id', '>', (int) $request->query('after', 0))
            ->where('device_token', '<>', $this->device($request))
            ->count();

        return response()->json(['count' => $count]);
    }

    private function device(Request $request): string
    {
        return (string) $request->attributes->get('device_token');
    }

    private function isSafeMomentPath(string $path): bool
    {
        return str_starts_with($path, 'moments/')
            && ! str_contains($path, '\\')
            && ! str_contains($path, '../')
            && ! str_starts_with($path, '/')
            && basename($path) !== '';
    }
}
