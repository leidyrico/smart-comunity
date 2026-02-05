<?php

// Simular el proceso de creación de recibo con envío de correo
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Mail\NuevoRecibo;
use Illuminate\Support\Facades\Mail;

// Configurar el entorno de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNÓSTICO DE ENVÍO DE CORREOS EN CREACIÓN DE RECIBOS ===\n\n";

// 1. Verificar configuración actual
echo "1. CONFIGURACIÓN DE CORREO ACTUAL:\n";
echo "   MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "   MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "   MAIL_PORT: " . env('MAIL_PORT') . "\n";
echo "   MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
echo "   MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "   MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// 2. Verificar si hay apartamentos con email
echo "2. APARTAMENTOS CON EMAIL:\n";
$apartamentosConEmail = Apartamento::whereNotNull('email')
    ->where('email', '!=', '')
    ->where('estado', 'ocupado')
    ->get();

echo "   Total apartamentos con email: " . $apartamentosConEmail->count() . "\n";
foreach ($apartamentosConEmail as $apartamento) {
    echo "   - {$apartamento->numero}: {$apartamento->email} (Propietario: {$apartamento->propietario})\n";
}
echo "\n";

// 3. Verificar últimos recibos activos
echo "3. ÚLTIMOS RECIBOS ACTIVOS:\n";
$recibosActivos = ReciboGastoComun::where('estado', 'activo')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

foreach ($recibosActivos as $recibo) {
    echo "   - {$recibo->numero_recibo} (ID: {$recibo->id}, Creado: {$recibo->created_at})\n";
}
echo "\n";

// 4. Probar envío de correo con el último recibo
echo "4. PRUEBA DE ENVÍO DE CORREO:\n";
if ($recibosActivos->isNotEmpty() && $apartamentosConEmail->isNotEmpty()) {
    $recibo = $recibosActivos->first();
    $apartamento = $apartamentosConEmail->first();
    
    echo "   Enviando correo para recibo {$recibo->numero_recibo} a {$apartamento->email}...\n";
    
    try {
        // Limpiar cache de configuración
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        
        Mail::to($apartamento->email)->send(new NuevoRecibo($recibo, $apartamento->propietario));
        echo "   ✓ Correo enviado exitosamente\n";
        
        // Verificar si el correo fue logueado
        echo "   Verificando logs...\n";
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            if (strpos($logs, 'Correo de nuevo recibo enviado') !== false) {
                echo "   ✓ Se encontró registro de correo enviado en los logs\n";
            } else {
                echo "   ⚠ No se encontró registro de correo enviado en los logs recientes\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Error al enviar correo: " . $e->getMessage() . "\n";
        echo "   Tipo de error: " . get_class($e) . "\n";
    }
} else {
    echo "   ❌ No hay recibos activos o apartamentos con email disponibles\n";
}

echo "\n=== RECOMENDACIONES ===\n";
echo "1. Verificar que el cache esté limpio: php artisan config:clear\n";
echo "2. Verificar que las variables de entorno estén cargadas correctamente\n";
echo "3. Revisar que el mailer esté configurado como 'smtp' y no 'log'\n";
echo "4. Verificar los logs en tiempo real mientras se crea un recibo\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";