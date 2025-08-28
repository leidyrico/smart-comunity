<?php
/**
 * Script para configurar Gmail y enviar correo real a xxxx@gmail.com
 * INSTRUCCIONES:
 * 1. Genera una contraseña de aplicación en Gmail
 * 2. Ejecuta este script
 * 3. Ingresa la contraseña cuando se solicite
 */

echo "\n🔧 CONFIGURACIÓN DE GMAIL PARA ENVÍO REAL\n";
echo "==========================================\n\n";

echo "📋 PASOS PREVIOS NECESARIOS:\n";
echo "1. Ve a https://myaccount.google.com/apppasswords\n";
echo "2. Genera una contraseña de aplicación para 'Correo'\n";
echo "3. Copia la contraseña de 16 caracteres\n\n";

echo "¿Ya tienes la contraseña de aplicación? (s/n): ";
$respuesta = trim(fgets(STDIN));

if (strtolower($respuesta) !== 's') {
    echo "\n❌ Por favor, genera primero la contraseña de aplicación.\n";
    echo "Visita: https://myaccount.google.com/apppasswords\n";
    exit(1);
}

echo "\nIngresa la contraseña de aplicación de Gmail (16 caracteres): ";
$passwordApp = trim(fgets(STDIN));

if (strlen($passwordApp) !== 16) {
    echo "\n⚠️  ADVERTENCIA: La contraseña debería tener 16 caracteres.\n";
    echo "¿Continuar de todos modos? (s/n): ";
    $continuar = trim(fgets(STDIN));
    if (strtolower($continuar) !== 's') {
        echo "Operación cancelada.\n";
        exit(1);
    }
}

// Leer archivo .env actual
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

// Actualizar configuración
$envContent = preg_replace('/^MAIL_MAILER=.*/m', 'MAIL_MAILER=smtp', $envContent);
$envContent = preg_replace('/^MAIL_PASSWORD=.*/m', 'MAIL_PASSWORD=' . $passwordApp, $envContent);

// Guardar archivo .env
file_put_contents($envFile, $envContent);

echo "\n✅ Configuración actualizada en .env\n";
echo "\n🧪 Probando envío real...\n";

// Probar envío real
require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\ComprobantePago;
use App\Models\Pago;

try {
    // Crear pago de prueba
    $pagoTest = new Pago();
    $pagoTest->id = 999999;
    $pagoTest->apartamento_id = 1;
    $pagoTest->recibo_gasto_comun_id = 1;
    $pagoTest->monto_pagado = 150.00;
    $pagoTest->fecha_pago = now();
    $pagoTest->metodo_pago = 'transferencia';
    $pagoTest->numero_comprobante = 'GMAIL-REAL-' . time();
    $pagoTest->estado = 'confirmado';
    $pagoTest->observaciones = 'Correo de prueba REAL enviado desde Gmail SMTP';
    
    // Simular relaciones
    $apartamento = new stdClass();
    $apartamento->numero = 'PRUEBA-001';
    $apartamento->email = 'xxxx@gmail';
    $apartamento->propietario = 'dddddxxxxxo';
    
    $recibo = new stdClass();
    $recibo->numero_recibo = 'REC-REAL-2024-001';
    $recibo->periodo = 'Enero 2024';
    
    $pagoTest->apartamento = $apartamento;
    $pagoTest->reciboGastoComun = $recibo;
    
    echo "📤 Enviando correo REAL a xxxx@gmail.com...\n";
    echo "⏳ Esto puede tomar unos segundos...\n\n";
    
    Mail::to('xxx@gmail.com')->send(new ComprobantePago($pagoTest));
    
    echo "🎉 ¡CORREO REAL ENVIADO EXITOSAMENTE!\n";
    echo "\n📧 REVISA TU BANDEJA DE ENTRADA EN GMAIL\n";
    echo "- Destinatario: xxxx@gmail.com\n";
    echo "- Asunto: Confirmación de pago recibido\n";
    echo "- Remitente: noreply@residenciasalfa.com\n";
    echo "- Puede tardar 1-2 minutos en llegar\n";
    echo "- Revisa también la carpeta de SPAM\n\n";
    
    echo "✅ CONFIGURACIÓN GMAIL COMPLETADA\n";
    echo "Ahora todos los pagos en el sistema enviarán correos reales.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR AL ENVIAR CORREO REAL:\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), '535') !== false) {
        echo "🔧 SOLUCIONES:\n";
        echo "1. Verifica que la contraseña de aplicación sea correcta\n";
        echo "2. Asegúrate de que la verificación en 2 pasos esté activada\n";
        echo "3. Intenta generar una nueva contraseña de aplicación\n";
    }
    
    // Revertir a modo log si falla
    $envContent = preg_replace('/^MAIL_MAILER=.*/m', 'MAIL_MAILER=log', $envContent);
    file_put_contents($envFile, $envContent);
    echo "\n🔄 Configuración revertida a modo 'log' por seguridad.\n";
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "Configuración completada - " . date('Y-m-d H:i:s') . "\n";
?>