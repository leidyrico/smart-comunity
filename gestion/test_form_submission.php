<?php

// Script para simular el envío del formulario web
require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Crear una solicitud simulada como si viniera del formulario web
$request = Illuminate\Http\Request::create('/recibos', 'POST', [
    'numero_recibo' => 'WEB-TEST-' . time(),
    'periodo' => 'Noviembre 2025',
    'fecha_emision' => date('Y-m-d'),
    'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
    'valor_administracion' => '150.00',
    'valor_mantenimiento' => '75.00',
    'valor_aseo' => '35.00',
    'valor_vigilancia' => '40.00',
    'otros_conceptos' => '20.00',
    'estado' => 'activo',
    'observaciones' => 'Recibo de prueba desde formulario web',
    'enviar_correo' => '1' // Simular checkbox marcado
]);

echo "=== PRUEBA DE ENVÍO DE FORMULARIO WEB ===\n\n";

echo "1. DATOS ENVIADOS DESDE FORMULARIO:\n";
foreach ($request->all() as $key => $value) {
    echo "   $key: $value\n";
}
echo "\n";

// Limpiar logs anteriores
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    file_put_contents($logFile, '');
}

// Procesar la solicitud con el controlador
echo "2. PROCESANDO SOLICITUD EN CONTROLADOR...\n";
try {
    $controller = new \App\Http\Controllers\ReciboGastoComunController();
    
    // Validar datos
    $validated = $request->validate([
        'numero_recibo' => 'required|string|max:50|unique:recibo_gasto_comuns,numero_recibo',
        'periodo' => 'required|string|max:50',
        'fecha_emision' => 'required|date',
        'fecha_vencimiento' => 'required|date|after:fecha_emision',
        'valor_administracion' => 'required|numeric|min:0',
        'valor_aseo' => 'nullable|numeric|min:0',
        'valor_vigilancia' => 'nullable|numeric|min:0',
        'valor_mantenimiento' => 'nullable|numeric|min:0',
        'otros_conceptos' => 'nullable|numeric|min:0',
        'observaciones' => 'nullable|string|max:1000',
        'estado' => 'required|in:activo,vencido,anulado',
        'enviar_correo' => 'sometimes|boolean'
    ]);
    
    echo "   ✓ Validación exitosa\n";
    echo "   Datos validados:\n";
    foreach ($validated as $key => $value) {
        echo "     $key: $value\n";
    }
    echo "\n";
    
    // Verificar si el campo enviar_correo está presente
    echo "3. VERIFICANDO CAMPO enviar_correo:\n";
    echo "   ¿Request tiene enviar_correo? " . ($request->has('enviar_correo') ? 'SÍ' : 'NO') . "\n";
    echo "   Valor de enviar_correo: " . ($request->input('enviar_correo', 'no definido')) . "\n";
    echo "   ¿enviar_correo es booleano? " . (is_bool($request->input('enviar_correo')) ? 'SÍ' : 'NO') . "\n";
    echo "   Tipo de dato: " . gettype($request->input('enviar_correo')) . "\n\n";
    
    // Crear recibo
    $recibo = new \App\Models\ReciboGastoComun($validated);
    $recibo->calcularTotal();
    $recibo->save();
    
    echo "4. RECIBO CREADO:\n";
    echo "   - ID: " . $recibo->id . "\n";
    echo "   - Número: " . $recibo->numero_recibo . "\n";
    echo "   - Total: $" . $recibo->total_recibo . "\n";
    echo "   - Estado: " . $recibo->estado . "\n\n";
    
    // Procesar asignación
    if ($recibo->estado === 'activo') {
        $enviarCorreo = $request->has('enviar_correo');
        echo "5. PROCESANDO ASIGNACIÓN:\n";
        echo "   ¿Se enviarán correos? " . ($enviarCorreo ? 'SÍ' : 'NO') . "\n\n";
        
        // Contar apartamentos
        $apartamentos = \App\Models\Apartamento::where('estado', 'ocupado')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
        
        echo "   Apartamentos con email: " . $apartamentos->count() . "\n";
        
        if ($enviarCorreo && $apartamentos->count() > 0) {
            echo "   Enviando correos a los primeros 2 apartamentos...\n";
            
            foreach ($apartamentos->take(2) as $apartamento) {
                echo "   - " . $apartamento->numero . " (" . $apartamento->email . "): ";
                
                // Crear pago
                $pago = new \App\Models\Pago([
                    'recibo_gasto_comun_id' => $recibo->id,
                    'apartamento_id' => $apartamento->id,
                    'monto_pagado' => 0,
                    'fecha_pago' => null,
                    'metodo_pago' => null,
                    'numero_comprobante' => null,
                    'observaciones' => 'Asignación de prueba',
                    'estado' => 'pendiente_confirmacion'
                ]);
                $pago->save();
                
                // Enviar correo
                try {
                    \Mail::to($apartamento->email)->send(new \App\Mail\NuevoRecibo($recibo, $apartamento->propietario));
                    echo "✓ Correo enviado\n";
                } catch (\Exception $e) {
                    echo "✗ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    // Verificar logs
    echo "\n6. LOGS DEL PROCESO:\n";
    sleep(2);
    
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        
        $procesoLogs = array_filter($lines, function($line) {
            return stripos($line, 'recibo') !== false || 
                   stripos($line, 'correo') !== false || 
                   stripos($line, 'asignaci') !== false;
        });
        
        if (!empty($procesoLogs)) {
            foreach (array_slice($procesoLogs, -10) as $log) {
                if (trim($log)) {
                    echo "   " . $log . "\n";
                }
            }
        } else {
            echo "   No se encontraron logs relevantes\n";
        }
    }
    
    // Limpiar
    echo "\n7. LIMPIEZA:\n";
    $recibo->delete();
    echo "   ✓ Recibo de prueba eliminado\n";
    
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";