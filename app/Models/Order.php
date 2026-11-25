<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUSES = ['Diterima', 'Diproses', 'Siap', 'Selesai', 'Dibatalkan'];

    public const PAYMENT_STATUSES = ['Belum Bayar', 'Menunggu QRIS', 'Lunas', 'Gagal', 'Kedaluwarsa'];

    public const METHODS = ['Kasir', 'QRIS'];

    /** Zona waktu bisnis: dipakai untuk reset nomor antrean harian dan jam ramai. */
    public const TIMEZONE = 'Asia/Jakarta';

    protected $fillable = [
        'kode_pesanan', 'token', 'nomor_antrean', 'tanggal_antrean', 'nama_pelanggan', 'table_id', 'subtotal', 'diskon', 'pajak', 'total',
        'metode_bayar', 'status_bayar', 'status_pesanan', 'expires_at', 'paid_at',
        'xendit_qr_id', 'qr_string', 'xendit_payment_id', 'catatan', 'kasir_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float', 'diskon' => 'float', 'pajak' => 'float', 'total' => 'float',
            'nomor_antrean' => 'integer',
            'tanggal_antrean' => 'date:Y-m-d',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->token ??= Str::random(32);
        });
    }

    /** Nomor antrean tiga digit, contoh: 007. Kosong untuk pesanan lama yang belum bernomor. */
    protected function antreanLabel(): Attribute
    {
        return Attribute::get(fn () => $this->nomor_antrean === null
            ? null
            : str_pad((string) $this->nomor_antrean, 3, '0', STR_PAD_LEFT));
    }

    /** Pesanan QRIS yang belum dibayar dan masih dalam batas waktu. */
    public function isAwaitingPayment(): bool
    {
        return $this->status_bayar === 'Menunggu QRIS'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Pesanan yang boleh muncul di kasir. Pesanan QRIS dari website baru masuk setelah lunas;
     * pesanan "bayar di kasir" dan pesanan POS (kasir_id terisi) langsung terlihat.
     */
    public function scopeVisibleToKasir(Builder $query): Builder
    {
        return $query->where(function (Builder $w) {
            $w->where('metode_bayar', 'Kasir')
                ->orWhere('status_bayar', 'Lunas')
                ->orWhereNotNull('kasir_id');
        });
    }

    /**
     * Beri nomor antrean harian (reset 001 tiap hari, Asia/Jakarta). Aman dipanggil berulang:
     * pesanan yang sudah bernomor tidak diubah. Counter dikunci per baris di dalam transaksi
     * sehingga order yang masuk bersamaan tidak mendapat nomor yang sama.
     */
    public function assignQueueNumber(): int
    {
        return DB::transaction(function () {
            $fresh = static::query()->lockForUpdate()->find($this->id);

            if ($fresh && $fresh->nomor_antrean !== null) {
                $this->nomor_antrean = $fresh->nomor_antrean;
                $this->tanggal_antrean = $fresh->tanggal_antrean;

                return $fresh->nomor_antrean;
            }

            $date = now(self::TIMEZONE)->toDateString();

            DB::table('queue_counters')->insertOrIgnore(['tanggal' => $date, 'terakhir' => 0]);
            DB::table('queue_counters')->where('tanggal', $date)->lockForUpdate()->first();
            DB::table('queue_counters')->where('tanggal', $date)->increment('terakhir');
            $number = (int) DB::table('queue_counters')->where('tanggal', $date)->value('terakhir');

            DB::table('orders')->where('id', $this->id)->update(['nomor_antrean' => $number, 'tanggal_antrean' => $date]);
            $this->nomor_antrean = $number;
            $this->tanggal_antrean = $date;
            $this->syncOriginalAttributes(['nomor_antrean', 'tanggal_antrean']);

            return $number;
        });
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
