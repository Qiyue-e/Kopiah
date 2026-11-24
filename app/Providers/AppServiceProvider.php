<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @rupiah($angka) di Blade → Rp24.000
        Blade::directive('rupiah', fn (string $expression) => "<?php echo rupiah({$expression}); ?>");
    }
}
