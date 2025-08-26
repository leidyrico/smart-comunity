<?php

require_once 'vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

echo "=== PRUEBA DE PARSEADO DE FECHAS EXCEL ===\n\n";

// Función parseExcelDate copiada del controlador
function parseExcelDate($dateValue)
{
    echo "Procesando valor: " . var_export($dateValue, true) . " (tipo: " . gettype($dateValue) . ")\n";
    
    if (empty($dateValue)) {
        echo "  -> Valor vacío, usando fecha actual\n";
        return now();
    }

    // Si es un número (fecha serial de Excel)
    if (is_numeric($dateValue)) {
        echo "  -> Es numérico, intentando conversión Excel serial\n";
        try {
            $result = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
            echo "  -> Éxito con Excel serial: " . $result->format('d-m-Y') . "\n";
            return $result;
        } catch (\Exception $e) {
            echo "  -> Falló Excel serial, intentando timestamp: " . $e->getMessage() . "\n";
            // Si falla, intentar como timestamp
            try {
                $result = Carbon::createFromTimestamp($dateValue);
                echo "  -> Éxito con timestamp: " . $result->format('d-m-Y') . "\n";
                return $result;
            } catch (\Exception $e2) {
                echo "  -> Falló timestamp: " . $e2->getMessage() . "\n";
            }
        }
    }

    // Si es una cadena, intentar diferentes formatos
    $formats = [
        'd/m/Y',
        'm/d/Y', 
        'Y-m-d',
        'd-m-Y',
        'm-d-Y',
        'd/m/y',
        'm/d/y',
        'Y/m/d'
    ];

    echo "  -> Es cadena, probando formatos...\n";
    foreach ($formats as $format) {
        try {
            $result = Carbon::createFromFormat($format, $dateValue);
            echo "  -> Éxito con formato '{$format}': " . $result->format('d-m-Y') . "\n";
            return $result;
        } catch (\Exception $e) {
            echo "  -> Falló formato '{$format}': " . $e->getMessage() . "\n";
            continue;
        }
    }

    // Si ningún formato funciona, intentar parse automático
    echo "  -> Intentando parse automático...\n";
    try {
        $result = Carbon::parse($dateValue);
        echo "  -> Éxito con parse automático: " . $result->format('d-m-Y') . "\n";
        return $result;
    } catch (\Exception $e) {
        echo "  -> Falló parse automático: " . $e->getMessage() . "\n";
        // Como último recurso, usar fecha actual
        echo "  -> Usando fecha actual como último recurso\n";
        return now();
    }
}

// Casos de prueba con diferentes tipos de fechas
$testCases = [
    // Fechas como cadenas en diferentes formatos
    '31/07/2023',
    '31-07-2023', 
    '2023-07-31',
    '07/31/2023',
    '31/7/23',
    
    // Fechas seriales de Excel (números)
    44773, // 31/07/2023 en formato serial Excel
    44927, // 31/01/2024 en formato serial Excel
    44958, // 29/02/2024 (año bisiesto)
    
    // Casos problemáticos
    '29/02/2025', // Fecha inválida (2025 no es bisiesto)
    '',           // Cadena vacía
    null,         // Valor nulo
    0,            // Cero
    
    // Fechas que podrían venir mal del Excel
    '1900-01-01', // Fecha base de Excel
    '2025-03-01', // Fecha futura que podría ser incorrecta
];

echo "=== CASOS DE PRUEBA ===\n\n";

foreach ($testCases as $i => $testCase) {
    echo "Caso " . ($i + 1) . ": ";
    $result = parseExcelDate($testCase);
    echo "Resultado final: " . $result->format('d-m-Y H:i:s') . "\n";
    echo "---\n";
}

echo "\n=== ANÁLISIS DE FECHAS SERIALES EXCEL ===\n\n";

// Convertir las fechas correctas a seriales de Excel para ver qué números deberían ser
$fechasCorrectas = [
    '31-07-2023',
    '31-01-2024',
    '29-02-2024',
    '31-03-2024',
    '30-04-2024',
    '31-05-2024',
    '30-06-2024'
];

foreach ($fechasCorrectas as $fecha) {
    $carbon = Carbon::createFromFormat('d-m-Y', $fecha);
    $serial = \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($carbon);
    echo "Fecha: {$fecha} -> Serial Excel: {$serial}\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";