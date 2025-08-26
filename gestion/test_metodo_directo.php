<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRUEBA DIRECTA DEL MÉTODO ===\n";

try {
    // Crear una instancia del controlador
    $controller = new App\Http\Controllers\PagoController();
    
    // Crear una request simulada
    $request = new Illuminate\Http\Request();
    $request->merge(['apartamento_id' => 10]);
    
    echo "Probando getRecibosPorApartamento con apartamento_id=10\n";
    
    // Llamar al método
    $response = $controller->getRecibosPorApartamento($request);
    
    echo "Código de respuesta: " . $response->getStatusCode() . "\n";
    echo "Contenido de respuesta:\n";
    echo $response->getContent() . "\n";
    
    // Decodificar JSON
    $data = json_decode($response->getContent(), true);
    if ($data !== null) {
        echo "\nRecibos encontrados: " . count($data) . "\n";
        foreach ($data as $recibo) {
            echo "- ID: {$recibo['id']}, Número: {$recibo['numero_recibo']}, Estado: {$recibo['estado']}, Total: {$recibo['total_recibo']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";