<?php

// Script para probar la creación de recibos con envío de correo
require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture(),
    $type = Illuminate\Http\Request::class
);

echo "=== PRUEBA DE CREACIÓN DE RECIBOS CON CORREO ===\n\n";

// Crear un recibo de prueba
$reciboData = [
    'numero_recibo' => 'TEST-' . time(),
    'periodo' => 'Noviembre 2025',
    'fecha_emision' => date('Y-m-d'),
    'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
    'valor_administracion' => 100.00,
    'valor_mantenimiento' => 50.00,
    'valor_aseo' => 25.00,
    'valor_vigilancia' => 30.00,
    'otros_conceptos' => 15.00,
    'estado' => 'activo',
    'observaciones' => 'Recibo de prueba para verificar envío de correos',
    'enviar_correo' => '1' // Marcar para enviar correo
];

echo "1. DATOS DEL RECIBO DE PRUEBA:\n";
foreach ($reciboData as $key => $value) {
    echo "   $key: $value\n";
}
echo "\n";

// Crear el recibo
echo "2. CREANDO RECIBO...\n";
try {
    // Limpiar logs anteriores
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
    }
    
    $recibo = new \App\Models\ReciboGastoComun($reciboData);
    $recibo->calcularTotal();
    $recibo->save();
    
    echo "   ✓ Recibo creado exitosamente\n";
    echo "   - ID: " . $recibo->id . "\n";
    echo "   - Número: " . $recibo->numero_recibo . "\n";
    echo "   - Total: $" . $recibo->total_recibo . "\n";
    echo "   - Estado: " . $recibo->estado . "\n\n";
    
    // Simular el proceso de asignación
    echo "3. PROCESANDO ASIGNACIÓN A APARTAMENTOS...\n";
    
    $apartamentos = \App\Models\Apartamento::where('estado', 'ocupado')
        ->whereNotNull('email')
        ->where('email', '!=', '')
        ->take(3)
        ->get();
    
    echo "   Apartamentos con email encontrados: " . $apartamentos->count() . "\n\n";
    
    foreach ($apartamentos as $apartamento) {
        echo "   Procesando apartamento " . $apartamento->numero . " (" . $apartamento->email . "):\n";
        
        // Crear pago
        $pago = new \App\Models\Pago([
            'recibo_gasto_comun_id' => $recibo->id,
            'apartamento_id' => $apartamento->id,
            'monto_pagado' => 0,
            'fecha_pago' => null,
            'metodo_pago' => null,
            'numero_comprobante' => null,
            'observaciones' => 'Recibo de prueba asignado automáticamente',
            'estado' => 'pendiente_confirmacion'
        ]);
        $pago->save();
        echo "     ✓ Pago creado (ID: " . $pago->id . ")\n";
        
        // Enviar correo
        try {
            \Mail::to($apartamento->email)->send(new \App\Mail\NuevoRecibo($recibo, $apartamento->propietario));
            echo "     ✓ Correo enviado exitosamente\n";
        } catch (\Exception $e) {
            echo "     ✗ Error enviando correo: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // Verificar logs
    echo "4. VERIFICANDO LOGS:\n";
    sleep(2); // Esperar un momento para que se escriban los logs
    
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        $correoLogs = array_filter($lines, function($line) {
            return stripos($line, 'correo') !== false || stripos($line, 'mail') !== false;
        });
        
        if (!empty($correoLogs)) {
            foreach (array_slice($correoLogs, -10) as $log) {
                if (trim($log)) {
                    echo "   " . $log . "\n";
                }
            }
        } else {
            echo "   No se encontraron logs de correo\n";
        }
    }
    
    // Limpiar - eliminar recibo de prueba
    echo "\n5. LIMPIEZA:\n";
    $recibo->delete();
    echo "   ✓ Recibo de prueba eliminado\n";
    
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";