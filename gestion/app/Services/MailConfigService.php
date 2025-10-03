<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MailConfigService
{
    /**
     * Configurar el sistema de correo según el entorno detectado
     */
    public static function configurarCorreo()
    {
        $entorno = self::detectarEntorno();
        
        Log::info('Configurando correo para entorno', ['entorno' => $entorno]);
        
        switch ($entorno) {
            case 'xampp':
                self::configurarParaXampp();
                break;
            case 'local':
                self::configurarParaLocal();
                break;
            case 'production':
                self::configurarParaProduccion();
                break;
            default:
                self::configurarPorDefecto();
        }
    }
    
    /**
     * Detectar el entorno actual
     */
    private static function detectarEntorno(): string
    {
        // Forzar entorno local si está configurado
        if (env('FORCE_LOCAL_ENV', false)) {
            return 'local';
        }
        
        // Detectar desarrollo local por el puerto 8000 (artisan serve)
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        if (strpos($host, ':8000') !== false || strpos($host, '127.0.0.1:8000') !== false) {
            return 'local';
        }
        
        // Detectar si estamos en CLI con artisan serve
        if (php_sapi_name() === 'cli' && isset($_SERVER['argv'])) {
            $args = implode(' ', $_SERVER['argv']);
            if (strpos($args, 'artisan serve') !== false) {
                return 'local';
            }
        }
        
        // Detectar XAMPP por la estructura de directorios
        if (strpos(__DIR__, 'xampp') !== false || strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false) {
            return 'xampp';
        }
        
        // Detectar producción por variables de entorno
        if (env('APP_ENV') === 'production' || env('APP_DEBUG') === false) {
            return 'production';
        }
        
        return 'local'; // Por defecto
    }
    
    /**
     * Configuración para XAMPP
     */
    private static function configurarParaXampp()
    {
        // En XAMPP, usar configuración SMTP real si está disponible
        if (env('MAIL_USERNAME') && env('MAIL_PASSWORD')) {
            Config::set([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => env('MAIL_HOST', 'smtp.gmail.com'),
                'mail.mailers.smtp.port' => env('MAIL_PORT', 587),
                'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
                'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
                'mail.mailers.smtp.timeout' => 120,
                'mail.mailers.smtp.verify_peer' => false,
            ]);
            
            Log::info('XAMPP: Configurado para envío SMTP real');
        } else {
            // Si no hay credenciales, usar modo log
            Config::set('mail.default', 'log');
            Log::info('XAMPP: Configurado para modo log (sin credenciales SMTP)');
        }
    }
    
    /**
     * Configuración para desarrollo local (artisan serve)
     */
    private static function configurarParaLocal()
    {
        // En desarrollo local, preferir modo log para evitar spam
        if (env('FORCE_SMTP_LOCAL', false)) {
            // Permitir forzar SMTP en local con variable de entorno
            Config::set([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => env('MAIL_HOST', 'smtp.gmail.com'),
                'mail.mailers.smtp.port' => env('MAIL_PORT', 587),
                'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
                'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
                'mail.mailers.smtp.timeout' => 120,
            ]);
            
            Log::info('Local: Configurado para envío SMTP real (forzado)');
        } else {
            Config::set('mail.default', 'log');
            Log::info('Local: Configurado para modo log (desarrollo)');
        }
    }
    
    /**
     * Configuración para producción
     */
    private static function configurarParaProduccion()
    {
        // En producción, siempre usar SMTP real
        Config::set([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'mail.mailers.smtp.port' => env('MAIL_PORT', 587),
            'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
            'mail.mailers.smtp.timeout' => 120,
            'mail.mailers.smtp.verify_peer' => true,
        ]);
        
        Log::info('Producción: Configurado para envío SMTP real');
    }
    
    /**
     * Configuración por defecto
     */
    private static function configurarPorDefecto()
    {
        Config::set('mail.default', 'log');
        Log::info('Por defecto: Configurado para modo log');
    }
    
    /**
     * Verificar si el correo está configurado correctamente
     */
    public static function verificarConfiguracion(): array
    {
        $mailer = Config::get('mail.default');
        $resultado = [
            'mailer' => $mailer,
            'entorno' => self::detectarEntorno(),
            'configurado' => false,
            'mensaje' => ''
        ];
        
        if ($mailer === 'smtp') {
            $host = Config::get('mail.mailers.smtp.host');
            $username = Config::get('mail.mailers.smtp.username');
            
            if ($host && $username) {
                $resultado['configurado'] = true;
                $resultado['mensaje'] = "SMTP configurado correctamente para {$host}";
            } else {
                $resultado['mensaje'] = 'SMTP seleccionado pero faltan credenciales';
            }
        } else {
            $resultado['configurado'] = true;
            $resultado['mensaje'] = "Modo {$mailer} configurado correctamente";
        }
        
        return $resultado;
    }
    
    /**
     * Obtener información del estado actual del correo
     */
    public static function obtenerEstado(): array
    {
        return [
            'entorno_detectado' => self::detectarEntorno(),
            'mailer_actual' => Config::get('mail.default'),
            'host_smtp' => Config::get('mail.mailers.smtp.host'),
            'puerto_smtp' => Config::get('mail.mailers.smtp.port'),
            'encriptacion' => Config::get('mail.mailers.smtp.encryption'),
            'usuario_configurado' => !empty(Config::get('mail.mailers.smtp.username')),
            'from_address' => Config::get('mail.from.address'),
            'from_name' => Config::get('mail.from.name'),
        ];
    }
}