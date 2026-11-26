<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Kopiah Moments: foto persegi langsung dari kamera + caption singkat.
 * Pengirim dikenali lewat device_token anonim, sehingga pelanggan tidak perlu akun.
 */
class Moment extends Model
{
    /** Reaksi emoji yang tersedia (seperti reaksi di Locket). */
    public const REACTIONS = ['❤️', '☕', '😋', '🔥', '😂'];

    protected $attributes = [
        'status' => 'approved',
    ];

    protected $fillable = [
        'nama_pelanggan', 'avatar', 'caption', 'tipe_foto', 'file_path', 'status', 'device_token', 'ip_address',
    ];

    public function reactions(): HasMany
    {
        return $this->hasMany(MomentReaction::class);
    }

    /** URL relatif agar foto memakai host/domain yang sedang membuka website. */
    protected function url(): Attribute
    {
        return Attribute::get(function (): string {
            $path = str_replace('\\', '/', (string) $this->file_path);
            $filename = basename($path);

            // Foto Moments disajikan lewat route Laravel agar tidak bergantung pada symlink public/storage.
            if (str_starts_with($path, 'moments/') && $filename !== '' && ! str_contains($filename, '..')) {
                return route('moments.image', ['filename' => $filename], false);
            }

            // Kompatibilitas untuk path lama non-standar yang masih berada pada disk public.
            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        });
    }

    /** Apakah file fotonya benar-benar ada di disk (data lama bisa menunjuk file yang hilang). */
    public function hasFile(): bool
    {
        $path = str_replace('\\', '/', (string) $this->file_path);

        try {
            return $path !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /** Semua foto yang tampil di feed adalah foto approved; unggahan baru langsung approved. */
    public function scopeVisibleTo(Builder $query, ?string $device): Builder
    {
        return $query->approved();
    }

    /** Jumlah per emoji; butuh relasi reactions sudah dimuat. */
    public function reactionCounts(): Collection
    {
        return $this->reactions->countBy('emoji');
    }

    public function reactionOf(?string $device): ?string
    {
        return $this->reactions->firstWhere('device_token', $device)?->emoji;
    }
}
