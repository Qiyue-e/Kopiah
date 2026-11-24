<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUSES = ['Diterima', 'Diproses', 'Siap', 'Selesai', 'Dibatalkan'];

    public const PAYMENT_STATUSES = ['Belum Bayar', 'Menunggu QRIS', 'Lunas', 'Gagal'];

    public const METHODS = ['Kasir', 'QRIS'];

    protected $fillable = [
        'kode_pesanan', 'nama_pelanggan', 'table_id', 'subtotal', 'diskon', 'pajak', 'total',
        'metode_bayar', 'status_bayar', 'status_pesanan', 'catatan', 'kasir_id',
    ];

    protected function casts(): array
    {
        return ['subtotal' => 'float', 'diskon' => 'float', 'pajak' => 'float', 'total' => 'float'];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promoUsage(): HasOne
    {
        return $this->hasOne(PromoUsage::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status_pesanan', '<>', 'Dibatalkan');
    }

    /** Pesanan lunas dan tidak dibatalkan (dasar perhitungan omzet). */
    public function scopePaid(Builder $query): Builder
    {
        return $query->notCancelled()->where('status_bayar', 'Lunas');
    }

    /** Kode unik, contoh: CFY261111A1B2C3 */
    public static function generateCode(): string
    {
        do {
            $code = 'CFY'.now()->format('ymd').strtoupper(bin2hex(random_bytes(3)));
        } while (static::where('kode_pesanan', $code)->exists());

        return $code;
    }
}
