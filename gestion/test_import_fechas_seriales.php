<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DeudaController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== PRUEBA DE IMPORTACIÓN COMPLETA CON FECHAS SERIALES ===\n\n";

// Limpiar datos previos de prueba
echo "Limpiando datos de prueba previos...\n";
Pago::where('observaciones', 'LIKE', '%Prueba serial%')->delete();
ReciboGastoComun::where('observaciones', 'LIKE', '%Prueba serial%')->delete();
Apartamento::where('numero', 'LIKE', 'TEST-%')->delete();

// Verificar que el archivo existe
$filePath = 'test_fechas_seriales.xlsx';
if (!file_exists($filePath)) {
    echo "ERROR: El archivo {$filePath} no existe.\n";
    exit(1);
}

echo "Archivo encontrado: {$filePath}\n";
echo "Tamaño del archivo: " . filesize($filePath) . " bytes\n\n";

// Crear un UploadedFile simulado
$uploadedFile = new UploadedFile(
    $filePath,
    'test_fechas_seriales.xlsx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    null,
    true // test mode
);

// Crear request simulado
$request = new Request();
$request->files->set('excel_file', $uploadedFile);
$request->merge([
    'borrar_datos' => false,
    'validar_relaciones' => false,
    'continuar_errores' => true
]);

echo "Iniciando importación completa...\n\n";

try {
    $controller = new DeudaController();
    $response = $controller->importCompleto($request);
    
    echo "Respuesta del controlador:\n";
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Datos: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "Tipo de respuesta: " . get_class($response) . "\n";
        echo "Contenido: " . $response->getContent() . "\n\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR durante la importación: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n\n";
}

// Verificar datos importados
echo "=== VERIFICACIÓN DE DATOS IMPORTADOS ===\n\n";

// Verificar apartamentos
$apartamentos = Apartamento::where('numero', 'LIKE', 'TEST-%')->get();
echo "Apartamentos importados: " . $apartamentos->count() . "\n";
foreach ($apartamentos as $apt) {
    echo "  - {$apt->numero}: {$apt->propietario}\n";
}
echo "\n";

// Verificar recibos
$recibos = ReciboGastoComun::where('observaciones', 'LIKE', '%Prueba serial%')->get();
echo "Recibos importados: " . $recibos->count() . "\n";
foreach ($recibos->take(5) as $recibo) {
    echo "  - Recibo {$recibo->numero_recibo}: Emisión {$recibo->fecha_emision->format('d-m-Y')}, Vencimiento {$recibo->fecha_vencimiento->format('d-m-Y')}\n";
}
if ($recibos->count() > 5) {
    echo "  ... y " . ($recibos->count() - 5) . " más\n";
}
echo "\n";

// Verificar pagos
$pagos = Pago::whereHas('apartamento', function($q) {
    $q->where('numero', 'LIKE', 'TEST-%');
})->get();
echo "Pagos importados: " . $pagos->count() . "\n";
foreach ($pagos->take(5) as $pago) {
    echo "  - Pago {$pago->numero_comprobante}: Fecha {$pago->fecha_pago->format('d-m-Y')}, Monto {$pago->monto_pagado}\n";
}
if ($pagos->count() > 5) {
    echo "  ... y " . ($pagos->count() - 5) . " más\n";
}

echo "\n=== VERIFICACIÓN DE LOGS RECIENTES ===\n";

// Buscar logs recientes de parseExcelDate
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50); // Últimas 50 líneas
    
    $parseExcelDateLines = array_filter($recentLines, function($line) {
        return strpos($line, 'parseExcelDate') !== false;
    });
    
    if (!empty($parseExcelDateLines)) {
        echo "Logs recientes de parseExcelDate:\n";
        foreach (array_slice($parseExcelDateLines, -10) as $line) {
            echo "  " . trim($line) . "\n";
        }
    } else {
        echo "No se encontraron logs recientes de parseExcelDate\n";
    }
} else {
    echo "Archivo de log no encontrado: {$logFile}\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";