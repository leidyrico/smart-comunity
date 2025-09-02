<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register PDF facade alias
        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf') && !class_exists('PDF')) {
            class_alias('Barryvdh\\DomPDF\\Facade\\Pdf', 'PDF');
        }
    }
}
