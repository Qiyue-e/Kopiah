<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\MenuImagePath;

class Menu extends Model
{
    protected $fillable = [
        'category_id', 'nama', 'deskripsi', 'harga', 'foto', 'stok', 'is_available', 'is_best_seller',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'float',
            'stok' => 'integer',
            'is_available' => 'boolean',
            'is_best_seller' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(MenuOption::class)->orderBy('tipe')->orderBy('id');
    }

    /** URL foto: link eksternal, aset public, atau file storage melalui route Laravel. */
    protected function fotoUrl(): Attribute
    {
        return Attribute::get(fn (): string => MenuImagePath::url($this->foto));
    }

    public function isOrderable(): bool
    {
        return $this->is_available && $this->stok > 0;
    }

    public function isNew(): bool
    {
        return (bool) $this->created_at?->gte(now()->subDays(7));
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true)->where('stok', '>', 0);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), function (Builder $q) use ($term) {
            $q->where(fn (Builder $w) => $w->where('nama', 'like', "%{$term}%")->orWhere('deskripsi', 'like', "%{$term}%"));
        });
    }
}
