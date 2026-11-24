<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Pengaturan cafe berbentuk key-value, di-cache agar tidak query berulang. */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'settings.all';

    public static function read(string $key, ?string $default = null): ?string
    {
        try {
            $all = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
        } catch (\Throwable) {
            return $default; // mis. tabel belum dimigrasi
        }

        $value = $all[$key] ?? null;

        return ($value === null || $value === '') ? $default : (string) $value;
    }

    /** @param array<string, string|null> $values */
    public static function write(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
