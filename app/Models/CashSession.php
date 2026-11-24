<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashSession extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'modal_awal', 'kas_akhir', 'catatan', 'dibuka_pada', 'ditutup_pada'];

    protected function casts(): array
    {
        return [
            'modal_awal' => 'float',
            'kas_akhir' => 'float',
            'dibuka_pada' => 'datetime',
            'ditutup_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('ditutup_pada');
    }
}
