<?php

// Script para verificar el comportamiento del checkbox enviar_correo
require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== PRUEBA DEL CHECKBOX enviar_correo ===\n\n";

// Simular diferentes escenarios de envío del formulario
$escenarios = [
    'checkbox_marcado' => [
        'numero_recibo' => 'TEST-1',
        'periodo' => 'Noviembre 2025',
        'fecha_emision' => date('Y-m-d'),
        'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
        'valor_administracion' => 100,
        'estado' => 'activo',
        'enviar_correo' => '1' // Checkbox marcado
    ],
    'checkbox_no_marcado' => [
        'numero_recibo' => 'TEST-2',
        'periodo' => 'Noviembre 2025',
        'fecha_emision' => date('Y-m-d'),
        'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
        'valor_administracion' => 100,
        'estado' => 'activo'
        // Sin campo enviar_correo = checkbox no marcado
    ]
];

foreach ($escenarios as $nombre => $datos) {
    echo "ESCENARIO: " . strtoupper(str_replace('_', ' ', $nombre)) . "\n";
    
    // Crear request
    $request = Illuminate\Http\Request::create('/recibos', 'POST', $datos);
    
    echo "Datos enviados:\n";
    foreach ($datos as $key => $value) {
        echo "   $key: $value\n";
    }
    
    // Verificar el campo enviar_correo
    echo "\nResultados de verificación:\n";
    echo "   ¿Request tiene enviar_correo? " . ($request->has('enviar_correo') ? 'SÍ' : 'NO') . "\n";
    echo "   Valor de enviar_correo: " . var_export($request->input('enviar_correo'), true) . "\n";
    echo "   ¿enviar_correo es true/booleano? " . ($request->boolean('enviar_correo') ? 'SÍ' : 'NO') . "\n";
    
    // Validar según las reglas del controlador
    try {
        $validated = $request->validate([
            'numero_recibo' => 'required|string|max:50',
            'periodo' => 'required|string|max:50',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_emision',
            'valor_administracion' => 'required|numeric|min:0',
            'estado' => 'required|in:activo,vencido,anulado',
            'enviar_correo' => 'sometimes|boolean'
        ]);
        
        echo "   ✓ Validación exitosa\n";
        echo "   Datos después de validación:\n";
        foreach ($validated as $key => $value) {
            echo "     $key: " . var_export($value, true) . "\n";
        }
        
        // Simular la lógica del controlador
        $enviarCorreo = $request->has('enviar_correo');
        echo "   ¿Se enviarán correos? " . ($enviarCorreo ? 'SÍ' : 'NO') . "\n";
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "   ✗ Error de validación: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

// Verificar cómo Laravel interpreta el checkbox
echo "ANÁLISIS DEL COMPORTAMIENTO DEL CHECKBOX:\n\n";

echo "Cuando el checkbox está MARCADO:\n";
echo "   - El navegador envía: enviar_correo=1\n";
echo "   - Laravel interpreta: enviar_correo = '1' (string)\n";
echo "   - Request::has('enviar_correo') devuelve: true\n";
echo "   - Request::boolean('enviar_correo') devuelve: true\n\n";

echo "Cuando el checkbox NO está marcado:\n";
echo "   - El navegador NO envía el campo enviar_correo\n";
echo "   - Laravel interpreta: enviar_correo = null\n";
echo "   - Request::has('enviar_correo') devuelve: false\n";
echo "   - Request::boolean('enviar_correo') devuelve: false\n\n";

echo "CONCLUSIÓN:\n";
echo "   El problema NO está en el checkbox, ya que Request::has() detecta correctamente\n";
echo "   si el campo está presente o no. El problema debe estar en otra parte.\n";

echo "\n=== FIN DEL ANÁLISIS ===\n";