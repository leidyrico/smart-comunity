<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA DE LECTURA DIRECTA DE EXCEL ===\n";
echo "Fecha y hora: " . date('Y-m-d H:i:s') . "\n\n";

// Función parseExcelDate copiada del controlador con logging mejorado
function parseExcelDate($dateValue) {
    // Log del valor original para debugging
    echo "parseExcelDate - Valor original: " . var_export($dateValue, true) . " (tipo: " . gettype($dateValue) . ")\n";
    
    if (empty($dateValue)) {
        echo "parseExcelDate - Valor vacío, usando fecha actual\n";
        return now();
    }

    // Si es un número (fecha serial de Excel)
    if (is_numeric($dateValue)) {
        try {
            // Verificar que sea un número válido de fecha Excel (entre 1 y 2958465)
            if ($dateValue < 1 || $dateValue > 2958465) {
                throw new \Exception("Número fuera del rango válido de fechas Excel: {$dateValue}");
            }
            
            $result = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
            echo "parseExcelDate - Éxito con serial Excel: {$dateValue} -> " . $result->format('d-m-Y') . "\n";
            return $result;
        } catch (\Exception $e) {
            echo "parseExcelDate - Error con serial Excel: {$dateValue} - " . $e->getMessage() . "\n";
            
            // Si falla, intentar como timestamp solo si es un número razonable
            if ($dateValue > 946684800 && $dateValue < 4102444800) { // Entre 2000 y 2100
                try {
                    $result = Carbon::createFromTimestamp($dateValue);
                    echo "parseExcelDate - Éxito con timestamp: {$dateValue} -> " . $result->format('d-m-Y') . "\n";
                    return $result;
                } catch (\Exception $e2) {
                    echo "parseExcelDate - Error con timestamp: {$dateValue} - " . $e2->getMessage() . "\n";
                }
            }
        }
    }

    // Si es una cadena, intentar diferentes formatos
    $formats = [
        'd/m/Y',   // 31/07/2023
        'd-m-Y',   // 31-07-2023
        'Y-m-d',   // 2023-07-31
        'm/d/Y',   // 07/31/2023 (formato US)
        'm-d-Y',   // 07-31-2023
        'd/m/y',   // 31/07/23
        'd-m-y',   // 31-07-23
        'm/d/y',   // 07/31/23
        'm-d-y',   // 07-31-23
        'Y/m/d'    // 2023/07/31
    ];

    foreach ($formats as $format) {
        try {
            $result = Carbon::createFromFormat($format, $dateValue);
            
            // Validar que la fecha sea razonable (entre 2020 y 2030)
            if ($result->year < 2020 || $result->year > 2030) {
                echo "parseExcelDate - Fecha fuera de rango razonable: {$dateValue} con formato {$format} -> año {$result->year}\n";
                continue;
            }
            
            echo "parseExcelDate - Éxito con formato {$format}: {$dateValue} -> " . $result->format('d-m-Y') . "\n";
            return $result;
        } catch (\Exception $e) {
            continue;
        }
    }

    // Si ningún formato funciona, intentar parse automático
    try {
        $result = Carbon::parse($dateValue);
        
        // Validar que la fecha sea razonable
        if ($result->year < 2020 || $result->year > 2030) {
            throw new \Exception("Fecha fuera de rango razonable: {$result->year}");
        }
        
        echo "parseExcelDate - Éxito con parse automático: {$dateValue} -> " . $result->format('d-m-Y') . "\n";
        return $result;
    } catch (\Exception $e) {
        echo "parseExcelDate - Todos los métodos fallaron: {$dateValue} - " . $e->getMessage() . "\n";
        
        // Como último recurso, usar fecha actual
        return now();
    }
}

try {
    // Cargar el archivo Excel
    $excelFile = 'test_fechas_seriales.xlsx';
    if (!file_exists($excelFile)) {
        echo "ERROR: No se encontró el archivo {$excelFile}\n";
        exit(1);
    }
    
    echo "Cargando archivo Excel: {$excelFile}\n\n";
    
    $spreadsheet = IOFactory::load($excelFile);
    
    // Procesar hoja de recibos (asumiendo que es la segunda hoja)
    $recibosSheet = $spreadsheet->getSheet(1);
    echo "=== PROCESANDO HOJA DE RECIBOS ===\n";
    echo "Nombre de la hoja: " . $recibosSheet->getTitle() . "\n";
    
    $highestRow = $recibosSheet->getHighestRow();
    echo "Número de filas: {$highestRow}\n\n";
    
    // Leer encabezados (fila 1)
    $headers = [];
    $highestColumn = $recibosSheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
    
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $headers[$col] = $recibosSheet->getCell([$col, 1])->getValue();
    }
    
    echo "Encabezados encontrados:\n";
    foreach ($headers as $col => $header) {
        echo "  Columna {$col}: {$header}\n";
    }
    echo "\n";
    
    // Buscar columnas de fechas
    $fechaEmisionCol = null;
    $fechaVencimientoCol = null;
    
    foreach ($headers as $col => $header) {
        if (stripos($header, 'fecha_emision') !== false || stripos($header, 'emision') !== false) {
            $fechaEmisionCol = $col;
        }
        if (stripos($header, 'fecha_vencimiento') !== false || stripos($header, 'vencimiento') !== false) {
            $fechaVencimientoCol = $col;
        }
    }
    
    echo "Columna fecha_emision: " . ($fechaEmisionCol ? $fechaEmisionCol : 'NO ENCONTRADA') . "\n";
    echo "Columna fecha_vencimiento: " . ($fechaVencimientoCol ? $fechaVencimientoCol : 'NO ENCONTRADA') . "\n\n";
    
    if (!$fechaEmisionCol || !$fechaVencimientoCol) {
        echo "ERROR: No se encontraron las columnas de fechas necesarias\n";
        exit(1);
    }
    
    // Procesar algunas filas de datos
    echo "=== PROCESANDO FECHAS DE RECIBOS ===\n";
    
    for ($row = 2; $row <= min($highestRow, 6); $row++) { // Solo las primeras 5 filas de datos
        echo "\n--- FILA {$row} ---\n";
        
        // Obtener valores raw de las celdas
        $fechaEmisionRaw = $recibosSheet->getCell([$fechaEmisionCol, $row])->getValue();
        $fechaVencimientoRaw = $recibosSheet->getCell([$fechaVencimientoCol, $row])->getValue();
        
        echo "Fecha emisión RAW: " . var_export($fechaEmisionRaw, true) . "\n";
        echo "Fecha vencimiento RAW: " . var_export($fechaVencimientoRaw, true) . "\n";
        
        // Procesar con parseExcelDate
        echo "\nProcesando fecha_emision:\n";
        $fechaEmisionParsed = parseExcelDate($fechaEmisionRaw);
        
        echo "\nProcesando fecha_vencimiento:\n";
        $fechaVencimientoParsed = parseExcelDate($fechaVencimientoRaw);
        
        echo "\nResultados finales:\n";
        echo "  Fecha emisión: " . $fechaEmisionParsed->format('d-m-Y') . "\n";
        echo "  Fecha vencimiento: " . $fechaVencimientoParsed->format('d-m-Y') . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";