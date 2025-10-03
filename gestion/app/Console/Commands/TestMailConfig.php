<?php

namespace App\Console\Commands;

use App\Services\MailConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestMailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-config {--send : Enviar un correo de prueba}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Probar y verificar la configuración de correo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 VERIFICACIÓN DE CONFIGURACIÓN DE CORREO');
        $this->newLine();
        
        // Obtener estado actual
        $estado = MailConfigService::obtenerEstado();
        
        $this->info("📍 Entorno detectado: " . $estado['entorno_detectado']);
        $this->info("📧 Mailer actual: " . $estado['mailer_actual']);
        
        if ($estado['mailer_actual'] === 'smtp') {
            $this->info("🌐 Host SMTP: " . $estado['host_smtp']);
            $this->info("🔌 Puerto: " . $estado['puerto_smtp']);
            $this->info("🔒 Encriptación: " . $estado['encriptacion']);
            $this->info("👤 Usuario configurado: " . ($estado['usuario_configurado'] ? 'Sí' : 'No'));
        }
        
        $this->info("📤 From Address: " . $estado['from_address']);
        $this->info("📝 From Name: " . $estado['from_name']);
        $this->newLine();
        
        // Verificar configuración
        $verificacion = MailConfigService::verificarConfiguracion();
        
        if ($verificacion['configurado']) {
            $this->info("✅ " . $verificacion['mensaje']);
        } else {
            $this->error("❌ " . $verificacion['mensaje']);
        }
        
        $this->newLine();
        
        // Enviar correo de prueba si se solicita
        if ($this->option('send')) {
            $this->enviarCorreoPrueba();
        } else {
            $this->info("💡 Para enviar un correo de prueba, usa: php artisan mail:test-config --send");
        }
        
        return 0;
    }
    
    /**
     * Enviar un correo de prueba
     */
    private function enviarCorreoPrueba()
    {
        $this->info("📧 ENVIANDO CORREO DE PRUEBA...");
        $this->newLine();
        
        $email = $this->ask('¿A qué email enviar la prueba?', 'admin@sc.com');
        
        try {
            $tiempoInicio = microtime(true);
            
            Mail::raw('Este es un correo de prueba del sistema Smart Community.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Prueba de Configuración de Correo - ' . now()->format('Y-m-d H:i:s'));
            });
            
            $tiempoFin = microtime(true);
            $tiempoTotal = round($tiempoFin - $tiempoInicio, 2);
            
            $mailer = Config::get('mail.default');
            
            if ($mailer === 'log') {
                $this->info("✅ Correo guardado en logs (modo desarrollo)");
                $this->info("📁 Revisa: storage/logs/laravel.log");
            } else {
                $this->info("✅ Correo enviado exitosamente a: {$email}");
                $this->info("⏱️ Tiempo de envío: {$tiempoTotal} segundos");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar correo: " . $e->getMessage());
            $this->newLine();
            
            $this->warn("🔧 POSIBLES SOLUCIONES:");
            $this->line("1. Verificar credenciales SMTP en .env");
            $this->line("2. Comprobar conexión a internet");
            $this->line("3. Revisar configuración de firewall");
            $this->line("4. Usar modo log para desarrollo: MAIL_MAILER=log");
        }
    }
}