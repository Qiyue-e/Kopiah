<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promo extends Model
{
    protected $fillable = ['kode', 'tipe', 'nilai', 'min_belanja', 'kuota', 'mulai', 'selesai', 'is_active'];

    protected function casts(): array
    {
        return [
            'nilai' => 'float',
            'min_belanja' => 'float',
            'kuota' => 'integer',
            'mulai' => 'datetime',
            'selesai' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoUsage::class);
    }

    /** Promo aktif dan berada di dalam periodenya. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('mulai')->orWhere('mulai', '<=', now()))
            ->where(fn ($w) => $w->whereNull('selesai')->orWhere('selesai', '>=', now()));
    }

    /** Promo aktif yang kuotanya belum habis (kuota 0 = tanpa batas). */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->active()->withCount('usages')->where(function ($w) {
            $w->where('kuota', 0)->orWhereRaw(
                'kuota > (select count(*) from promo_usages where promo_usages.promo_id = promos.id)'
            );
        });
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->tipe === 'persen' ? $subtotal * $this->nilai / 100 : $this->nilai;

        return min($subtotal, (float) $discount);
    }

    /** Dipakai di daftar admin; butuh withCount('usages'). */
    public function isCurrentlyValid(): bool
    {
        return $this->is_active
            && (! $this->mulai || $this->mulai->lte(now()))
            && (! $this->selesai || $this->selesai->gte(now()))
            && ($this->kuota === 0 || $this->usages_count < $this->kuota);
    }
}
