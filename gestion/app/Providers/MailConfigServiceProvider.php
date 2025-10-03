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
        // Configurar el correo automáticamente al iniciar la aplicación
        MailConfigService::configurarCorreo();
    }
}