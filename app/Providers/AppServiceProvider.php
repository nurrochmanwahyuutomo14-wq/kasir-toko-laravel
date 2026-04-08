<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- 1. Tambahkan baris ini di atas

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // <-- 2. Tambahkan blok kode ini di dalam fungsi boot
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
}
