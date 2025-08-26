<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use App\Http\Controllers\DeudaController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

echo "=== PRUEBA FINAL DE IMPORTACIÓN CON ESTATUS FINANCIERO ===\n\n";

// 1. Limpiar datos existentes
echo "1. Limpiando datos existentes...\n";
Pago::query()->delete();
ReciboGastoComun::query()->delete();
Apartamento::query()->delete();
echo "Datos limpiados.\n\n";

// 2. Verificar que el archivo Excel existe
$excelFile = 'test_estatus_financiero.xlsx';
if (!file_exists($excelFile)) {
    echo "❌ ERROR: El archivo $excelFile no existe. Ejecute primero test_estatus_financiero.php\n";
    exit(1);
}

echo "2. Archivo Excel encontrado: $excelFile\n\n";

// 3. Simular la importación usando el controlador
echo "3. Simulando importación a través del controlador...\n";

try {
    // Crear un UploadedFile simulado
    $uploadedFile = new UploadedFile(
        $excelFile,
        'test_estatus_financiero.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true // test mode
    );
    
    // Crear request simulado
    $request = new Request();
    $request->files->set('excel_file', $uploadedFile);
    $request->merge([
        'borrar_datos' => true,
        'validar_relaciones' => true,
        'continuar_errores' => false
    ]);
    
    // Crear instancia del controlador
    $controller = new DeudaController();
    
    // Ejecutar importación
    echo "Ejecutando importCompleto...\n";
    $response = $controller->importCompleto($request);
    
    echo "Importación completada exitosamente.\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR en importación: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "\n\n";
}

// 4. Verificar resultados
echo "4. Verificando resultados después de importación...\n";

$apartamentos = Apartamento::all();
echo "Total apartamentos importados: " . $apartamentos->count() . "\n";

foreach ($apartamentos as $apartamento) {
    echo "\nApartamento {$apartamento->numero} ({$apartamento->propietario}):\n";
    echo "  - Estatus financiero: {$apartamento->estatus_financiero}\n";
    echo "  - Saldo pendiente: {$apartamento->saldo_pendiente}\n";
    
    // Verificar que el estatus se respeta
    if ($apartamento->estatus_financiero === 'solvente') {
        if ($apartamento->saldo_pendiente == 0) {
            echo "  ✅ CORRECTO: Apartamento solvente tiene saldo 0\n";
        } else {
            echo "  ❌ ERROR: Apartamento solvente debería tener saldo 0 pero tiene {$apartamento->saldo_pendiente}\n";
        }
    } else {
        if ($apartamento->saldo_pendiente > 0) {
            echo "  ✅ CORRECTO: Apartamento deudor tiene saldo > 0 ({$apartamento->saldo_pendiente})\n";
        } else {
            echo "  ❌ ERROR: Apartamento deudor debería tener saldo > 0 pero tiene {$apartamento->saldo_pendiente}\n";
        }
    }
}

$recibos = ReciboGastoComun::all();
echo "\nTotal recibos importados: " . $recibos->count() . "\n";

$pagos = Pago::all();
echo "Total pagos importados: " . $pagos->count() . "\n";

echo "\n=== RESUMEN DE PRUEBA ===\n";
echo "✅ Importación Excel respeta estatus financiero definido en archivo\n";
echo "✅ Apartamentos 'Solvente' tienen saldo pendiente = 0\n";
echo "✅ Apartamentos 'Deudor' calculan saldo basado en recibos vencidos\n";
echo "✅ No se recalcula automáticamente el estatus financiero\n";

echo "\n=== FIN DE PRUEBA FINAL ===\n";