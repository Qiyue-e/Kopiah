<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentReaction extends Model
{
    protected $fillable = ['moment_id', 'device_token', 'emoji'];

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class);
    }
}
