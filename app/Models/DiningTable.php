<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Meja cafe (tabel `tables`) beserta token QR untuk pesan dari meja. */
class DiningTable extends Model
{
    protected $table = 'tables';

    protected $fillable = ['nomor_meja', 'qr_token'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
