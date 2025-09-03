<?php
/**
 * Script simple para probar configuración de correo sin templates complejos
 */

require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

echo "\n🧪 PRUEBA SIMPLE DE CORREO\n";
echo "========================\n\n";

// Mostrar configuración actual
echo "📋 Configuración actual:\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n\n";

try {
    echo "📤 Enviando correo de prueba simple...\n";
    
    $emailDestino = 'jaouking@gmail.com';
    
    Mail::raw('Este es un correo de prueba desde Laravel. Si recibes este mensaje, la configuración SMTP está funcionando correctamente.', function ($message) use ($emailDestino) {
        $message->to($emailDestino)
                ->subject('Prueba de configuración SMTP - Laravel');
    });
    
    echo "✅ ¡CORREO ENVIADO EXITOSAMENTE!\n";
    echo "📧 Revisa la bandeja de entrada de: $emailDestino\n";
    echo "📧 También revisa la carpeta de SPAM\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR AL ENVIAR CORREO:\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    
    echo "🔧 PASOS DE DIAGNÓSTICO:\n";
    echo "1. ✅ Verifica el archivo .env\n";
    echo "2. ✅ Ejecuta: php artisan config:clear\n";
    echo "3. ✅ Revisa los logs: storage/logs/laravel.log\n\n";
}

echo "🔄 Para volver al modo log (sin envío real):\n";
echo "Cambia MAIL_MAILER=log en .env y ejecuta php artisan config:clear\n";