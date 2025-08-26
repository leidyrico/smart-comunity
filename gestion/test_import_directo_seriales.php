<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\DeudaController;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA DIRECTA DE IMPORTACIÓN CON FECHAS SERIALES ===\n\n";

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

try {
    // Cargar el archivo Excel
    $spreadsheet = IOFactory::load($filePath);
    
    // Crear instancia del controlador
    $controller = new DeudaController();
    
    // Usar reflexión para acceder a métodos privados
    $reflection = new ReflectionClass($controller);
    
    // Importar apartamentos
    echo "=== IMPORTANDO APARTAMENTOS ===\n";
    $apartamentosSheet = $spreadsheet->getSheetByName('Apartamentos');
    if ($apartamentosSheet) {
        $importApartamentosMethod = $reflection->getMethod('importApartamentosFromSheet');
        $importApartamentosMethod->setAccessible(true);
        
        $result = $importApartamentosMethod->invoke($controller, $apartamentosSheet, false, false, true);
        echo "Apartamentos importados: {$result['imported']}\n";
        if (!empty($result['errors'])) {
            echo "Errores: " . implode(', ', $result['errors']) . "\n";
        }
    } else {
        echo "Hoja 'Apartamentos' no encontrada\n";
    }
    echo "\n";
    
    // Importar recibos
    echo "=== IMPORTANDO RECIBOS ===\n";
    $recibosSheet = $spreadsheet->getSheetByName('Recibos');
    if ($recibosSheet) {
        $importRecibosMethod = $reflection->getMethod('importRecibosFromSheet');
        $importRecibosMethod->setAccessible(true);
        
        $result = $importRecibosMethod->invoke($controller, $recibosSheet, false, false, true);
        echo "Recibos importados: {$result['imported']}\n";
        if (!empty($result['errors'])) {
            echo "Errores: " . implode(', ', $result['errors']) . "\n";
        }
    } else {
        echo "Hoja 'Recibos' no encontrada\n";
    }
    echo "\n";
    
    // Importar pagos
    echo "=== IMPORTANDO PAGOS ===\n";
    $pagosSheet = $spreadsheet->getSheetByName('Pagos');
    if ($pagosSheet) {
        $importPagosMethod = $reflection->getMethod('importPagosFromSheet');
        $importPagosMethod->setAccessible(true);
        
        $result = $importPagosMethod->invoke($controller, $pagosSheet, false, false, true);
        echo "Pagos importados: {$result['imported']}\n";
        if (!empty($result['errors'])) {
            echo "Errores: " . implode(', ', $result['errors']) . "\n";
        }
    } else {
        echo "Hoja 'Pagos' no encontrada\n";
    }
    echo "\n";
    
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

// Verificar recibos con fechas específicas
$recibos = ReciboGastoComun::where('observaciones', 'LIKE', '%Prueba serial%')->get();
echo "Recibos importados: " . $recibos->count() . "\n";
foreach ($recibos->take(10) as $recibo) {
    echo "  - Recibo {$recibo->numero_recibo}: ";
    echo "Emisión {$recibo->fecha_emision->format('d-m-Y')}, ";
    echo "Vencimiento {$recibo->fecha_vencimiento->format('d-m-Y')}\n";
}
if ($recibos->count() > 10) {
    echo "  ... y " . ($recibos->count() - 10) . " más\n";
}
echo "\n";

// Verificar pagos con fechas específicas
$pagos = Pago::whereHas('apartamento', function($q) {
    $q->where('numero', 'LIKE', 'TEST-%');
})->get();
echo "Pagos importados: " . $pagos->count() . "\n";
foreach ($pagos->take(10) as $pago) {
    echo "  - Pago {$pago->numero_comprobante}: ";
    echo "Fecha {$pago->fecha_pago->format('d-m-Y')}, ";
    echo "Monto {$pago->monto_pagado}\n";
}
if ($pagos->count() > 10) {
    echo "  ... y " . ($pagos->count() - 10) . " más\n";
}

echo "\n=== ANÁLISIS DE FECHAS ESPECÍFICAS ===\n";

// Buscar fechas problemáticas específicas
$fechasProblematicas = [
    '29-02-2024', // Año bisiesto válido
    '28-02-2025', // Febrero no bisiesto
    '29-02-2025'  // Fecha inválida que debería convertirse
];

foreach ($fechasProblematicas as $fecha) {
    $recibosConFecha = ReciboGastoComun::where(function($q) use ($fecha) {
        $q->whereDate('fecha_emision', \Carbon\Carbon::createFromFormat('d-m-Y', $fecha))
          ->orWhereDate('fecha_vencimiento', \Carbon\Carbon::createFromFormat('d-m-Y', $fecha));
    })->get();
    
    $pagosConFecha = Pago::whereDate('fecha_pago', \Carbon\Carbon::createFromFormat('d-m-Y', $fecha))->get();
    
    echo "Fecha {$fecha}: {$recibosConFecha->count()} recibos, {$pagosConFecha->count()} pagos\n";
}

echo "\n=== VERIFICACIÓN DE LOGS RECIENTES ===\n";

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
        echo "Logs recientes de parseExcelDate (últimos 15):\n";
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