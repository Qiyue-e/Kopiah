<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'aksi', 'detail'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Catat aktivitas staf yang sedang login. */
    public static function record(string $aksi, string $detail = ''): void
    {
        static::create(['user_id' => auth()->id(), 'aksi' => $aksi, 'detail' => $detail]);
    }
}
