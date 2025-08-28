<?php
/**
 * Script simple para probar configuración de Gmail
 * Ejecutar después de configurar las credenciales en .env
 */

require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\ComprobantePago;
use App\Models\Pago;

echo "\n🧪 PRUEBA DE CONFIGURACIÓN GMAIL\n";
echo "===============================\n\n";

// Mostrar configuración actual
echo "📋 Configuración actual:\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption') . "\n\n";

// Verificar que esté configurado para SMTP
if (config('mail.default') !== 'smtp') {
    echo "⚠️  ADVERTENCIA: MAIL_MAILER no está configurado como 'smtp'\n";
    echo "Actual: " . config('mail.default') . "\n";
    echo "Cambia MAIL_MAILER=smtp en el archivo .env\n\n";
}

// Verificar credenciales
if (empty(config('mail.mailers.smtp.username')) || empty(config('mail.mailers.smtp.password'))) {
    echo "❌ ERROR: Faltan credenciales SMTP\n";
    echo "Configura MAIL_USERNAME y MAIL_PASSWORD en .env\n";
    exit(1);
}

try {
    echo "📤 Creando correo de prueba...\n";
    
    // Crear un pago de prueba
    $pagoTest = new Pago();
    $pagoTest->id = 999999;
    $pagoTest->apartamento = "PRUEBA-001";
    $pagoTest->inquilino_nombre = "Usuario de Prueba";
    $pagoTest->monto = 150.00;
    $pagoTest->fecha_pago = now();
    $pagoTest->metodo_pago = "Transferencia Bancaria";
    $pagoTest->referencia = "GMAIL-TEST-" . time();
    
    // Email de destino (cambiar por el tuyo)
    $emailDestino = config('mail.mailers.smtp.username'); // Enviar al mismo email configurado
    
    echo "📧 Enviando correo de prueba a: $emailDestino\n";
    echo "⏳ Esto puede tomar unos segundos...\n\n";
    
    // Enviar el correo
    Mail::to($emailDestino)->send(new ComprobantePago($pagoTest));
    
    echo "✅ ¡CORREO ENVIADO EXITOSAMENTE!\n";
    echo "\n📋 Detalles del envío:\n";
    echo "- Destinatario: $emailDestino\n";
    echo "- Asunto: Confirmación de pago recibido\n";
    echo "- Apartamento: PRUEBA-001\n";
    echo "- Monto: $150.00\n";
    echo "- Referencia: " . $pagoTest->referencia . "\n\n";
    
    echo "📧 REVISA TU BANDEJA DE ENTRADA\n";
    echo "- Puede tardar 1-2 minutos en llegar\n";
    echo "- Revisa también la carpeta de SPAM\n";
    echo "- El remitente será: " . config('mail.from.address') . "\n\n";
    
    echo "🎉 ¡CONFIGURACIÓN CORRECTA!\n";
    echo "Ahora los pagos en el sistema enviarán correos reales.\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR AL ENVIAR CORREO:\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    
    // Diagnóstico específico
    $errorMsg = $e->getMessage();
    
    if (strpos($errorMsg, '535') !== false) {
        echo "🔧 PROBLEMA: Credenciales Gmail incorrectas\n";
        echo "\nSOLUCIONES:\n";
        echo "1. ✅ Verifica que uses CONTRASEÑA DE APLICACIÓN (16 caracteres)\n";
        echo "2. ✅ NO uses tu contraseña normal de Gmail\n";
        echo "3. ✅ Activa verificación en 2 pasos: https://myaccount.google.com/security\n";
        echo "4. ✅ Genera contraseña de aplicación: https://myaccount.google.com/apppasswords\n\n";
        
    } elseif (strpos($errorMsg, 'Connection') !== false || strpos($errorMsg, 'timeout') !== false) {
        echo "🔧 PROBLEMA: Error de conexión\n";
        echo "\nSOLUCIONES:\n";
        echo "1. ✅ Verifica tu conexión a internet\n";
        echo "2. ✅ Verifica que el puerto 587 no esté bloqueado\n";
        echo "3. ✅ Verifica la configuración del firewall\n\n";
        
    } elseif (strpos($errorMsg, 'authentication') !== false) {
        echo "🔧 PROBLEMA: Error de autenticación\n";
        echo "\nSOLUCIONES:\n";
        echo "1. ✅ Verifica MAIL_USERNAME (debe ser tu email completo)\n";
        echo "2. ✅ Verifica MAIL_PASSWORD (contraseña de aplicación)\n";
        echo "3. ✅ Ejecuta: php artisan config:clear\n\n";
    } else {
        echo "🔧 ERROR GENERAL\n";
        echo "\nPASOS DE DIAGNÓSTICO:\n";
        echo "1. ✅ Verifica el archivo .env\n";
        echo "2. ✅ Ejecuta: php artisan config:clear\n";
        echo "3. ✅ Revisa los logs: storage/logs/laravel.log\n\n";
    }
    
    echo "📋 CONFIGURACIÓN ACTUAL EN .ENV:\n";
    echo "MAIL_MAILER=smtp\n";
    echo "MAIL_HOST=smtp.gmail.com\n";
    echo "MAIL_PORT=587\n";
    echo "MAIL_USERNAME=tu_email@gmail.com\n";
    echo "MAIL_PASSWORD=tu_contraseña_de_aplicacion_16_chars\n";
    echo "MAIL_ENCRYPTION=tls\n\n";
}

echo "\n🔄 Para volver al modo log (sin envío real):\n";
echo "Cambia MAIL_MAILER=log en .env y ejecuta php artisan config:clear\n";
?>