<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\DeudaController;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use PhpOffice\PhpSpreadsheet\IOFactory;

echo "=== PRUEBA DE IMPORTACIÓN CON FECHAS PROBLEMÁTICAS ===\n\n";

// Limpiar datos de prueba anteriores
echo "Limpiando datos de prueba anteriores...\n";
Apartamento::where('numero', 'LIKE', '10%')->delete();
ReciboGastoComun::where('numero_recibo', 'LIKE', 'REC-PROB-%')->delete();
Pago::whereHas('apartamento', function($q) {
    $q->where('numero', 'LIKE', '10%');
})->delete();

// Cargar el archivo Excel
$filename = 'recibos_fechas_problematicas.xlsx';
if (!file_exists($filename)) {
    echo "❌ Error: No se encontró el archivo {$filename}\n";
    exit(1);
}

echo "Cargando archivo: {$filename}\n";
$spreadsheet = IOFactory::load($filename);

// Crear instancia del controlador
$controller = new DeudaController();

// Usar reflexión para acceder a métodos privados
$reflection = new ReflectionClass($controller);

$importApartamentosMethod = $reflection->getMethod('importApartamentosFromSheet');
$importApartamentosMethod->setAccessible(true);

$importRecibosMethod = $reflection->getMethod('importRecibosFromSheet');
$importRecibosMethod->setAccessible(true);

$importPagosMethod = $reflection->getMethod('importPagosFromSheet');
$importPagosMethod->setAccessible(true);

try {
    // Importar apartamentos
    echo "\nImportando apartamentos...\n";
    $apartamentosSheet = $spreadsheet->getSheetByName('Apartamentos');
    if ($apartamentosSheet) {
        $result = $importApartamentosMethod->invoke($controller, $apartamentosSheet);
        echo "Apartamentos importados: {$result['imported']}\n";
        if (!empty($result['errors'])) {
            echo "Errores en apartamentos:\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
    }

    // Importar recibos
    echo "\nImportando recibos...\n";
    $recibosSheet = $spreadsheet->getSheetByName('Recibos');
    if ($recibosSheet) {
        $result = $importRecibosMethod->invoke($controller, $recibosSheet);
        echo "Recibos importados: {$result['imported']}\n";
        if (!empty($result['errors'])) {
            echo "Errores en recibos:\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
    }

    // Importar pagos
    echo "\nImportando pagos...\n";
    $pagosSheet = $spreadsheet->getSheetByName('Pagos');
    if ($pagosSheet) {
        $result = $importPagosMethod->invoke($controller, $pagosSheet);
        echo "Pagos importados: {$result['imported']}\n";
        if (!empty($result['errors'])) {
            echo "Errores en pagos:\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
    }

} catch (Exception $e) {
    echo "❌ Error durante la importación: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

echo "\n=== VERIFICACIÓN POST-IMPORTACIÓN ===\n\n";

// Verificar apartamentos
$apartamentos = Apartamento::where('numero', 'LIKE', '10%')->get();
echo "Apartamentos importados: {$apartamentos->count()}\n";
foreach ($apartamentos as $apartamento) {
    echo "  - {$apartamento->numero}: {$apartamento->propietario}\n";
}

// Verificar recibos
$recibos = ReciboGastoComun::where('numero_recibo', 'LIKE', 'REC-PROB-%')->orderBy('numero_recibo')->get();
echo "\nRecibos importados: {$recibos->count()}\n";
foreach ($recibos as $recibo) {
    echo "  - {$recibo->numero_recibo}: Emisión {$recibo->fecha_emision->format('d/m/Y')}, Vencimiento {$recibo->fecha_vencimiento->format('d/m/Y')}\n";
}

// Verificar pagos
$pagos = Pago::whereHas('apartamento', function($q) {
    $q->where('numero', 'LIKE', '10%');
})->get();
echo "\nPagos importados: {$pagos->count()}\n";

echo "\n=== VERIFICACIÓN DE FECHAS ESPECÍFICAS ===\n\n";

// Verificar fechas problemáticas específicas
$fechasProblematicas = [
    '2024-02-29', // 29 de febrero bisiesto
    '2025-02-28', // 28 de febrero no bisiesto
    '2023-12-31', // Año 2023 (fuera de rango?)
    '2026-01-31'  // Año 2026 (fuera de rango?)
];

foreach ($fechasProblematicas as $fecha) {
    $recibosConFecha = ReciboGastoComun::where(function($query) use ($fecha) {
        $query->whereDate('fecha_emision', $fecha)
              ->orWhereDate('fecha_vencimiento', $fecha);
    })->get();
    
    echo "Recibos con fecha {$fecha}: {$recibosConFecha->count()}\n";
    foreach ($recibosConFecha as $recibo) {
        echo "  - {$recibo->numero_recibo}: Emisión {$recibo->fecha_emision->format('d/m/Y')}, Vencimiento {$recibo->fecha_vencimiento->format('d/m/Y')}\n";
    }
}

echo "\n=== VERIFICACIÓN DE LOGS RECIENTES ===\n\n";

// Buscar logs recientes de parseExcelDate
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -100); // Últimas 100 líneas
    
    $parseExcelDateLines = array_filter($recentLines, function($line) {
        return strpos($line, 'parseExcelDate') !== false;
    });
    
    if (!empty($parseExcelDateLines)) {
        echo "Logs recientes de parseExcelDate:\n";
        foreach (array_slice($parseExcelDateLines, -15) as $line) {
            echo "  " . trim($line) . "\n";
        }
    } else {
        echo "No se encontraron logs recientes de parseExcelDate\n";
    }
} else {
    echo "Archivo de log no encontrado: {$logFile}\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";