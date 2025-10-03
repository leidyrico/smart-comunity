<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\URL;

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

        // Configurar URL dinámicamente para funcionar con XAMPP y servidor de desarrollo
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            
            // Detectar si estamos en XAMPP o servidor de desarrollo
            if ($host === 'localhost' && strpos($requestUri, '/smart-comunity/gestion/public') !== false) {
                // Configuración para XAMPP
                URL::forceRootUrl('http://localhost/smart-comunity/gestion/public');
            } elseif ($host === 'localhost:8000') {
                // Configuración para servidor de desarrollo
                URL::forceRootUrl('http://localhost:8000');
            }
        }
    }
}
