<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paksa semua URL dan Form Action menggunakan HTTPS
        if (request()->hasHeader('x-forwarded-proto') || str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
