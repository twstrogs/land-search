<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Initialize default settings on boot (only in production or when running migrations)
        if ($this->app->runningInConsole()) {
            // Settings will be initialized via artisan command
        }
    }
}
