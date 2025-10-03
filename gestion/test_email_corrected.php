<?php
/**
 * Script corregido para probar configuración de Gmail
 * Simula correctamente la estructura de datos esperada
 */

require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\ComprobantePago;
use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

echo "\n🧪 PRUEBA DE CONFIGURACIÓN GMAIL (CORREGIDA)\n";
echo "==========================================\n\n";

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
    
    // Crear objetos de prueba que simulen la estructura real
    $apartamentoTest = new Apartamento();
    $apartamentoTest->numero = "PRUEBA-001";
    $apartamentoTest->propietario = "Usuario de Prueba";
    
    $reciboTest = new ReciboGastoComun();
    $reciboTest->numero_recibo = "REC-TEST-001";
    $reciboTest->periodo = "2025-09";
    
    $pagoTest = new Pago();
    $pagoTest->id = 999999;
    $pagoTest->monto_pagado = 150.00;
    $pagoTest->fecha_pago = now();
    $pagoTest->metodo_pago = "transferencia_bancaria";
    $pagoTest->numero_comprobante = "TEST-" . time();
    $pagoTest->estado = "confirmado";
    $pagoTest->observaciones = "Pago de prueba para verificar configuración de correo";
    
    // Simular las relaciones
    $pagoTest->setRelation('apartamento', $apartamentoTest);
    $pagoTest->setRelation('reciboGastoComun', $reciboTest);
    
    // Email de destino
    $emailDestino = config('mail.mailers.smtp.username');
    
    echo "📧 Enviando correo de prueba a: $emailDestino\n";
    echo "⏳ Esto puede tomar unos segundos...\n\n";
    
    // Enviar el correo
    Mail::to($emailDestino)->send(new ComprobantePago($pagoTest));
    
    echo "✅ ¡CORREO ENVIADO EXITOSAMENTE!\n";
    echo "\n📋 Detalles del envío:\n";
    echo "- Destinatario: $emailDestino\n";
    echo "- Asunto: Confirmación de pago recibido\n";
    echo "- Apartamento: PRUEBA-001\n";
    echo "- Propietario: Usuario de Prueba\n";
    echo "- Monto: $150.00\n";
    echo "- Referencia: " . $pagoTest->numero_comprobante . "\n\n";
    
    echo "📧 REVISA TU BANDEJA DE ENTRADA\n";
    echo "- Puede tardar 1-2 minutos en llegar\n";
    echo "- Revisa también la carpeta de SPAM\n";
    echo "- El remitente será: " . config('mail.from.address') . "\n\n";
    
    echo "🎉 ¡CONFIGURACIÓN CORRECTA!\n";
    echo "Ahora los pagos en el sistema enviarán correos reales.\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR AL ENVIAR CORREO:\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    
    echo "🔧 POSIBLES SOLUCIONES:\n";
    echo "1. Verifica que MAIL_PASSWORD sea la contraseña de aplicación (16 caracteres)\n";
    echo "2. Asegúrate de que la verificación en 2 pasos esté activada en Gmail\n";
    echo "3. Verifica que el email en MAIL_USERNAME sea correcto\n";
    echo "4. Revisa que no haya espacios extra en las variables del .env\n\n";
    
    echo "📋 CONFIGURACIÓN ACTUAL EN .ENV:\n";
    echo "MAIL_MAILER=smtp\n";
    echo "MAIL_HOST=smtp.gmail.com\n";
    echo "MAIL_PORT=587\n";
    echo "MAIL_USERNAME=tu_email@gmail.com\n";
    echo "MAIL_PASSWORD=tu_contraseña_de_aplicacion_16_chars\n";
    echo "MAIL_ENCRYPTION=tls\n\n";
    
    echo "🔄 Para volver al modo log (sin envío real):\n";
    echo "Cambia MAIL_MAILER=log en .env y ejecuta php artisan config:clear\n";
}