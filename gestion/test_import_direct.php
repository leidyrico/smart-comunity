<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DeudaController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

echo "=== PRUEBA DIRECTA DE IMPORTACIÓN EXCEL ===\n";

$filePath = __DIR__ . '/test_import_completo.xlsx';

if (!file_exists($filePath)) {
    die("Error: El archivo Excel no existe: $filePath\n");
}

echo "Archivo encontrado: $filePath\n";
echo "Tamaño: " . filesize($filePath) . " bytes\n\n";

try {
    // Crear un UploadedFile simulado
    $file = new UploadedFile(
        $filePath,
        'test_import_completo.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true // test mode
    );
    
    // Crear una request simulada
    $request = new Request();
    $request->files->set('excel_file', $file);
    $request->merge([
        'borrar_datos' => true,
        'validar_relaciones' => true,
        'continuar_errores' => true
    ]);
    
    echo "Iniciando importación...\n";
    
    // Crear instancia del controlador
    $controller = new DeudaController();
    
    // Ejecutar la importación
    $response = $controller->importCompleto($request);
    
    echo "\n=== RESULTADO ===\n";
    echo "Importación completada\n";
    
    // Verificar los datos importados
    echo "\n=== VERIFICANDO DATOS IMPORTADOS ===\n";
    
    $apartamentos = \App\Models\Apartamento::count();
    $recibos = \App\Models\ReciboGastoComun::count();
    $pagos = \App\Models\Pago::count();
    
    echo "Apartamentos importados: $apartamentos\n";
    echo "Recibos importados: $recibos\n";
    echo "Pagos importados: $pagos\n";
    
    // Mostrar algunos detalles
    if ($apartamentos > 0) {
        echo "\n=== APARTAMENTOS ===\n";
        $apts = \App\Models\Apartamento::all();
        foreach ($apts as $apt) {
            echo "- {$apt->numero}: {$apt->propietario} ({$apt->estatus_financiero})\n";
        }
    }
    
    if ($recibos > 0) {
        echo "\n=== RECIBOS ===\n";
        $recs = \App\Models\ReciboGastoComun::with('apartamento')->take(5)->get();
        foreach ($recs as $rec) {
            echo "- {$rec->numero_recibo}: Apt {$rec->apartamento->numero} - {$rec->periodo} - ${$rec->monto}\n";
        }
    }
    
    if ($pagos > 0) {
        echo "\n=== PAGOS ===\n";
        $pags = \App\Models\Pago::with(['apartamento', 'recibo'])->take(5)->get();
        foreach ($pags as $pag) {
            echo "- Apt {$pag->apartamento->numero}: ${$pag->monto} - {$pag->fecha_pago} ({$pag->estado})\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n=== ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

// Verificar logs
echo "\n=== LOGS ===\n";
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    if (!empty(trim($logs))) {
        echo "Logs encontrados:\n";
        echo $logs;
    } else {
        echo "No hay logs nuevos.\n";
    }
} else {
    echo "Archivo de log no encontrado.\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";

?>