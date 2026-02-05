<?php

// Script de diagnóstico para verificar el proceso de creación de recibos
require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture(),
    $type = Illuminate\Http\Request::class
);

echo "=== DIAGNÓSTICO DE CREACIÓN DE RECIBOS ===\n\n";

// 1. Verificar configuración de correo
echo "1. CONFIGURACIÓN DE CORREO:\n";
echo "   MAIL_MAILER: " . env('MAIL_MAILER', 'no configurado') . "\n";
echo "   MAIL_HOST: " . env('MAIL_HOST', 'no configurado') . "\n";
echo "   MAIL_PORT: " . env('MAIL_PORT', 'no configurado') . "\n";
echo "   MAIL_USERNAME: " . env('MAIL_USERNAME', 'no configurado') . "\n";
echo "   MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION', 'no configurado') . "\n";
echo "   MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'no configurado') . "\n\n";

// 2. Verificar apartamentos con email
echo "2. APARTAMENTOS CON EMAIL:\n";
$apartamentos = \App\Models\Apartamento::whereNotNull('email')
    ->where('email', '!=', '')
    ->where('estado', 'ocupado')
    ->get();

echo "   Total apartamentos con email: " . $apartamentos->count() . "\n";
foreach ($apartamentos->take(5) as $apartamento) {
    echo "   - " . $apartamento->numero . ": " . $apartamento->email . "\n";
}
echo "\n";

// 3. Verificar últimos recibos activos
echo "3. ÚLTIMOS RECIBOS ACTIVOS:\n";
$recibos = \App\Models\ReciboGastoComun::where('estado', 'activo')
    ->orderBy('created_at', 'desc')
    ->take(3)
    ->get();

foreach ($recibos as $recibo) {
    echo "   - " . $recibo->numero_recibo . " (" . $recibo->created_at->format('Y-m-d H:i:s') . ")\n";
}
echo "\n";

// 4. Verificar pagos creados recientemente
echo "4. PAGOS CREADOS RECIENTEMENTE:\n";
$pagos = \App\Models\Pago::where('estado', 'pendiente_confirmacion')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

foreach ($pagos as $pago) {
    $reciboInfo = $pago->recibo ? $pago->recibo->numero_recibo : 'Sin recibo';
    $apartamentoInfo = $pago->apartamento ? $pago->apartamento->numero : 'Sin apartamento';
    echo "   - Recibo " . $reciboInfo . " - Apartamento " . $apartamentoInfo . " (" . $pago->created_at->format('Y-m-d H:i:s') . ")\n";
}
echo "\n";

// 5. Verificar logs de correo recientes
echo "5. LOGS DE CORREO RECIENTES:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLogs = array_slice($lines, -50);
    
    $correoLogs = array_filter($recentLogs, function($line) {
        return stripos($line, 'correo') !== false || stripos($line, 'mail') !== false;
    });
    
    if (!empty($correoLogs)) {
        foreach (array_slice($correoLogs, -5) as $log) {
            echo "   " . $log . "\n";
        }
    } else {
        echo "   No se encontraron logs recientes de correo\n";
    }
} else {
    echo "   No se encontró el archivo de logs\n";
}
echo "\n";

// 6. Probar envío de correo con datos simulados
echo "6. PRUEBA DE ENVÍO DE CORREO SIMULADO:\n";
if ($apartamentos->count() > 0 && $recibos->count() > 0) {
    $apartamento = $apartamentos->first();
    $recibo = $recibos->first();
    
    echo "   Enviando correo de prueba...\n";
    echo "   - Recibo: " . $recibo->numero_recibo . "\n";
    echo "   - Apartamento: " . $apartamento->numero . "\n";
    echo "   - Email: " . $apartamento->email . "\n";
    
    try {
        // Limpiar caché de configuración
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        \Mail::to($apartamento->email)->send(new \App\Mail\NuevoRecibo($recibo, $apartamento->propietario));
        echo "   ✓ Correo enviado exitosamente\n";
        
        // Verificar logs
        sleep(2);
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        $recentLogs = array_slice($lines, -10);
        
        foreach ($recentLogs as $log) {
            if (stripos($log, 'correo') !== false) {
                echo "   Log: " . $log . "\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "   ✗ Error enviando correo: " . $e->getMessage() . "\n";
        echo "   Config actual: " . config('mail.default') . "\n";
        echo "   Host actual: " . config('mail.mailers.smtp.host') . "\n";
    }
} else {
    echo "   No hay datos suficientes para la prueba\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";