<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['menu_id', 'nama_opsi', 'tipe', 'harga_tambahan'];

    protected function casts(): array
    {
        return ['harga_tambahan' => 'float'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
