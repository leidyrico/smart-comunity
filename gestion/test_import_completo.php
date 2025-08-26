<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DeudaController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

echo "=== PRUEBA DE IMPORTACIÓN COMPLETA ===\n";
echo "Fecha y hora: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar que el archivo Excel existe
$excelFile = 'test_fechas_correctas.xlsx';
if (!file_exists($excelFile)) {
    echo "ERROR: No se encontró el archivo {$excelFile}\n";
    exit(1);
}

echo "Archivo Excel encontrado: {$excelFile}\n";
echo "Tamaño del archivo: " . filesize($excelFile) . " bytes\n\n";

try {
    // Crear un UploadedFile simulado
    $uploadedFile = new UploadedFile(
        $excelFile,
        'test_fechas_correctas.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true // test mode
    );
    
    // Crear request simulado
    $request = new Request();
    $request->files->set('excel_file', $uploadedFile);
    $request->merge([
        'borrar_datos' => false, // No borrar para ver qué pasa con los datos existentes
        'validar_relaciones' => true,
        'continuar_errores' => true
    ]);
    
    echo "=== INICIANDO IMPORTACIÓN ===\n";
    
    // Limpiar logs anteriores
    Log::info('=== INICIO DE PRUEBA DE IMPORTACIÓN COMPLETA ===');
    
    // Crear instancia del controlador
    $controller = new DeudaController();
    
    // Ejecutar la importación
    $response = $controller->importCompleto($request);
    
    echo "\n=== IMPORTACIÓN COMPLETADA ===\n";
    echo "Tipo de respuesta: " . get_class($response) . "\n";
    
    // Si es una redirección, obtener la URL
    if (method_exists($response, 'getTargetUrl')) {
        echo "URL de redirección: " . $response->getTargetUrl() . "\n";
    }
    
    // Verificar mensajes de sesión
    if (session()->has('success')) {
        echo "Mensaje de éxito: " . session('success') . "\n";
    }
    
    if (session()->has('error')) {
        echo "Mensaje de error: " . session('error') . "\n";
    }
    
    if (session()->has('warning')) {
        echo "Mensaje de advertencia: " . session('warning') . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR DURANTE LA IMPORTACIÓN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== REVISANDO LOGS RECIENTES ===\n";

// Leer los últimos logs
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file($logFile);
        $recentLogs = array_slice($logs, -50); // Últimas 50 líneas
        
        echo "Últimos logs relacionados con parseExcelDate:\n";
        foreach ($recentLogs as $log) {
            if (strpos($log, 'parseExcelDate') !== false) {
                echo $log;
            }
        }
    } else {
        echo "No se encontró el archivo de logs.\n";
    }
} catch (Exception $e) {
    echo "Error al leer logs: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";