<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Moment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/** Pengelolaan Moments khusus owner. */
class MomentController extends Controller
{
    public function index(Request $request): View
    {
        $filter = in_array($request->query('filter'), ['all', 'approved', 'rejected'], true)
            ? $request->query('filter')
            : 'all';

        $moments = Moment::query()
            ->when($filter !== 'all', fn ($query) => $query->where('status', $filter))
            ->withCount('reactions')
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('admin.moments', compact('moments', 'filter'));
    }

    public function destroy(Moment $moment): RedirectResponse
    {
        $disk = Storage::disk('public');
        $path = $moment->file_path;
        $safePath = $this->isSafeMomentPath($path);
        $fileContents = null;
        $fileDeleted = false;

        try {
            if ($safePath && $disk->exists($path)) {
                $fileContents = $disk->get($path);
            }

            DB::transaction(function () use ($moment, $disk, $path, $safePath, $fileContents, &$fileDeleted): void {
                if ($safePath && $fileContents !== null) {
                    if (! $disk->delete($path)) {
                        throw new RuntimeException('File foto gagal dihapus dari storage.');
                    }
                    $fileDeleted = true;
                }

                $momentId = $moment->id;
                $moment->delete(); // moment_reactions terhapus otomatis melalui cascade.
                ActivityLog::record('delete_moment', 'Owner menghapus Moments ID '.$momentId);
            });
        } catch (Throwable $exception) {
            // Jika transaksi database gagal sesudah file terhapus, pulihkan file agar data lama tetap utuh.
            if ($fileDeleted && is_string($fileContents)) {
                try {
                    $disk->put($path, $fileContents);
                } catch (Throwable $restoreException) {
                    Log::critical('Pemulihan file Moments gagal setelah transaksi penghapusan dibatalkan.', [
                        'moment_id' => $moment->id,
                        'path' => $path,
                        'error' => $restoreException->getMessage(),
                    ]);
                }
            }

            Log::error('Owner gagal menghapus Kopiah Moments.', [
                'moment_id' => $moment->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'momen belum berhasil dihapus. coba lagi atau periksa izin folder storage.');
        }

        if (! $safePath) {
            Log::warning('Data Moments dihapus, tetapi file_path lama tidak memenuhi format path Moments yang aman.', [
                'moment_id' => $moment->id,
                'path' => $path,
            ]);
        }

        return back()->with('success', 'momen berhasil dihapus.');
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
