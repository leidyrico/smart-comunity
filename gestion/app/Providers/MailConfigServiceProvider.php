<?php

namespace App\Providers;

use App\Services\MailConfigService;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Ejecutar configuración de correo solo en producción para evitar latencia en desarrollo
        if (app()->environment('production')) {
            MailConfigService::configurarCorreo();
        }
    }
}