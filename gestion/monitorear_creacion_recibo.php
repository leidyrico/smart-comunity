<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevoRecibo;
use App\Models\ReciboGastoComun;
use App\Models\Apartamento;

echo "=== MONITOREO DE CREACIÓN DE RECIBO ===\n\n";

// Configurar el entorno de Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Entorno Laravel configurado.\n\n";

// Función para mostrar logs recientes
echo "1. ÚLTIMOS LOGS ANTES DE LA PRUEBA:\n";
$logs = shell_exec('powershell -Command "Get-Content storage\\logs\\laravel.log -Tail 20 | Select-String -Pattern \'(recibo|correo|email|mail)\'"');
echo $logs ? $logs : "No hay logs relacionados\n";

echo "\n" . str_repeat('=', 60) . "\n";

// Verificar configuración actual
echo "\n2. CONFIGURACIÓN ACTUAL:\n";
echo "   MAIL_MAILER: " . config('mail.default') . "\n";
echo "   MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "   MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "   MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "   MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n\n";

// Verificar apartamentos con emails
echo "3. APARTAMENTOS CON EMAILS:\n";
$apartamentos = DB::table('apartamentos')
    ->whereNotNull('email')
    ->where('email', '!=', '')
    ->select('id', 'numero', 'email')
    ->limit(5)
    ->get();

if ($apartamentos->isEmpty()) {
    echo "   No hay apartamentos con emails configurados\n";
} else {
    foreach ($apartamentos as $apto) {
    echo "   - {$apto->numero}: {$apto->email}\n";
}
}

echo "\n" . str_repeat('=', 60) . "\n";

// Crear un recibo de prueba con enviar_correo activado
echo "\n4. CREANDO RECIBO DE PRUEBA CON enviar_correo = 1:\n";

try {
    // Datos del recibo
    $datosRecibo = [
        'numero_recibo' => 'TEST-MONITOR-' . time(),
        'periodo' => 'Noviembre 2025',
        'fecha_emision' => now()->format('Y-m-d'),
        'fecha_vencimiento' => now()->addDays(30)->format('Y-m-d'),
        'valor_administracion' => 150.00,
        'valor_mantenimiento' => 0.00,
        'otros_conceptos' => 0.00,
        'total_recibo' => 150.00,
        'observaciones' => 'Recibo de prueba para monitoreo',
        'estado' => 'activo',
        'enviar_correo' => '1' // Checkbox marcado
    ];
    
    echo "   Datos del recibo: " . json_encode($datosRecibo) . "\n";
    
    // Crear el recibo
    $reciboId = DB::table('recibo_gasto_comuns')->insertGetId([
        'numero_recibo' => $datosRecibo['numero_recibo'],
        'periodo' => $datosRecibo['periodo'],
        'fecha_emision' => $datosRecibo['fecha_emision'],
        'fecha_vencimiento' => $datosRecibo['fecha_vencimiento'],
        'valor_administracion' => $datosRecibo['valor_administracion'],
        'valor_mantenimiento' => $datosRecibo['valor_mantenimiento'],
        'otros_conceptos' => $datosRecibo['otros_conceptos'],
        'total_recibo' => $datosRecibo['total_recibo'],
        'observaciones' => $datosRecibo['observaciones'],
        'estado' => $datosRecibo['estado'],
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    // Obtener el modelo Eloquent del recibo creado
    $recibo = \App\Models\ReciboGastoComun::find($reciboId);
    
    echo "   ✓ Recibo creado con ID: {$recibo->id}\n";
    
    // Verificar si se debe enviar correo (simulando la lógica del controlador)
    $enviarCorreo = isset($datosRecibo['enviar_correo']);
    echo "   ¿Enviar correo? " . ($enviarCorreo ? 'SÍ' : 'NO') . "\n";
    
    if ($enviarCorreo && $datosRecibo['estado'] === 'activo') {
        echo "   → Procesando envío de correos...\n";
        
        // Obtener apartamentos activos (ocupados o en arriendo)
        $apartamentos = Apartamento::whereIn('estado', ['ocupado', 'en_arriendo'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
        
        echo "   → Apartamentos activos con email: " . $apartamentos->count() . "\n";
        
        foreach ($apartamentos as $apartamento) {
            echo "   - Procesando apartamento {$apartamento->numero}...\n";
            
            // Crear pago
            $pagoId = DB::table('pagos')->insertGetId([
                'recibo_gasto_comun_id' => $recibo->id,
                'apartamento_id' => $apartamento->id,
                'monto_pagado' => 0,
                'estado' => 'pendiente_confirmacion',
                'fecha_pago' => null,
                'metodo_pago' => null,
                'observaciones' => 'Recibo asignado automáticamente',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "     ✓ Pago creado: {$pagoId}\n";
            
            // Enviar correo
            try {
                Mail::to($apartamento->email)->send(new \App\Mail\NuevoRecibo($recibo));
                
                echo "     ✓ Correo enviado a {$apartamento->email}\n";
                
            } catch (\Exception $e) {
                echo "     ✗ Error al enviar correo: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n   ✓ Proceso completado\n";
    
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";

// Verificar logs después de la prueba
echo "\n5. ÚLTIMOS LOGS DESPUÉS DE LA PRUEBA:\n";
$logs = shell_exec('powershell -Command "Get-Content storage\\logs\\laravel.log -Tail 20 | Select-String -Pattern \'(recibo|correo|email|mail|TEST-MONITOR)\'"');
echo $logs ? $logs : "No hay logs relacionados\n";

echo "\n=== FIN DEL MONITOREO ===\n";