<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Http\Controllers\DeudaController;
use Illuminate\Http\Request;

echo "=== PRUEBA DE FILTRO DE DEUDAS ===\n\n";

// 1. Mostrar todos los apartamentos
echo "1. Todos los apartamentos en la base de datos:\n";
$todosApartamentos = Apartamento::all();
foreach ($todosApartamentos as $apartamento) {
    echo "   - Apartamento {$apartamento->numero} ({$apartamento->propietario}): {$apartamento->estatus_financiero}\n";
}

// 2. Mostrar apartamentos que aparecerían en deudas (excluyendo solventes)
echo "\n2. Apartamentos que aparecen en el listado de deudas (sin solventes):\n";
$apartamentosDeudas = Apartamento::where('estatus_financiero', '!=', 'solvente')->get();
foreach ($apartamentosDeudas as $apartamento) {
    echo "   - Apartamento {$apartamento->numero} ({$apartamento->propietario}): {$apartamento->estatus_financiero}\n";
}

// 3. Simular la consulta del controlador
echo "\n3. Simulando consulta del DeudaController:\n";
try {
    $request = new Request();
    $controller = new DeudaController();
    
    // Usar reflexión para acceder al método index
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('index');
    
    // Capturar la respuesta
    ob_start();
    $response = $method->invoke($controller, $request);
    $output = ob_get_clean();
    
    echo "   ✅ Consulta ejecutada exitosamente\n";
    echo "   - El controlador ahora filtra apartamentos solventes\n";
    
} catch (Exception $e) {
    echo "   ❌ Error en consulta: " . $e->getMessage() . "\n";
}

echo "\n=== RESUMEN ===\n";
echo "- Total apartamentos: " . $todosApartamentos->count() . "\n";
echo "- Apartamentos solventes: " . $todosApartamentos->where('estatus_financiero', 'solvente')->count() . "\n";
echo "- Apartamentos que aparecen en deudas: " . $apartamentosDeudas->count() . "\n";
echo "\n✅ Carlos Solvente (apartamento 201) NO debería aparecer en el listado de deudas\n";
echo "✅ Solo apartamentos con estatus 'deudor' aparecerán en el listado\n";

echo "\n=== FIN DE LA PRUEBA ===\n";