<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'menu_id', 'nama_menu', 'qty', 'harga', 'opsi_json', 'catatan'];

    protected function casts(): array
    {
        return ['qty' => 'integer', 'harga' => 'float', 'opsi_json' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** Nama opsi yang dipilih, contoh: "Large, Ice". */
    protected function opsiNama(): Attribute
    {
        return Attribute::get(fn () => collect($this->opsi_json)->pluck('nama')->implode(', '));
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn () => $this->qty * $this->harga);
    }
}
