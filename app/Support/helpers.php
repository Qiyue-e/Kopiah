<?php

use App\Models\Setting;

if (! function_exists('rupiah')) {
    /** Format angka menjadi Rupiah, contoh: Rp24.000 */
    function rupiah(mixed $value): string
    {
        return 'Rp'.number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    /** Ambil pengaturan cafe dari tabel settings (di-cache). */
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::read($key, $default);
    }
}

if (! function_exists('site_logo')) {
    /** URL logo cafe: bawaan di public/assets atau hasil upload di storage. */
    function site_logo(): string
    {
        $path = setting('logo_path') ?: 'assets/img/logo-kopiah.png';

        return str_starts_with($path, 'assets/') ? asset($path) : asset('storage/'.$path);
    }
}
