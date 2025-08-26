<?php

require_once 'vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

echo "=== DEBUG DE LECTURA DE EXCEL ===\n\n";

// Función parseExcelDate del controlador
function parseExcelDate($dateValue)
{
    echo "    Procesando: " . var_export($dateValue, true) . " (" . gettype($dateValue) . ")\n";
    
    if (empty($dateValue)) {
        echo "    -> Vacío, usando now()\n";
        return now();
    }

    // Si es un número (fecha serial de Excel)
    if (is_numeric($dateValue)) {
        echo "    -> Es numérico, convirtiendo desde serial Excel\n";
        try {
            $result = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
            echo "    -> Resultado: " . $result->format('d-m-Y') . "\n";
            return $result;
        } catch (\Exception $e) {
            echo "    -> Error en serial Excel: " . $e->getMessage() . "\n";
            // Si falla, intentar como timestamp
            try {
                $result = Carbon::createFromTimestamp($dateValue);
                echo "    -> Resultado con timestamp: " . $result->format('d-m-Y') . "\n";
                return $result;
            } catch (\Exception $e2) {
                echo "    -> Error en timestamp: " . $e2->getMessage() . "\n";
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

    echo "    -> Es cadena, probando formatos...\n";
    foreach ($formats as $format) {
        try {
            $result = Carbon::createFromFormat($format, $dateValue);
            echo "    -> Éxito con '{$format}': " . $result->format('d-m-Y') . "\n";
            return $result;
        } catch (\Exception $e) {
            continue;
        }
    }

    // Si ningún formato funciona, intentar parse automático
    try {
        $result = Carbon::parse($dateValue);
        echo "    -> Éxito con parse automático: " . $result->format('d-m-Y') . "\n";
        return $result;
    } catch (\Exception $e) {
        echo "    -> Error en parse automático, usando now()\n";
        return now();
    }
}

// Leer el archivo Excel que acabamos de crear
$filename = 'test_fechas_correctas.xlsx';

if (!file_exists($filename)) {
    echo "❌ Error: No se encontró el archivo {$filename}\n";
    echo "Ejecuta primero: php crear_excel_fechas_correctas.php\n";
    exit(1);
}

echo "📖 Leyendo archivo: {$filename}\n\n";

try {
    $spreadsheet = IOFactory::load($filename);
    
    // Leer hoja de Recibos
    $recibosSheet = $spreadsheet->getSheetByName('Recibos');
    
    if (!$recibosSheet) {
        echo "❌ No se encontró la hoja 'Recibos'\n";
        exit(1);
    }
    
    echo "📋 Analizando hoja 'Recibos'...\n\n";
    
    // Obtener datos
    $data = $recibosSheet->toArray();
    $header = array_shift($data); // Remover header
    
    echo "Headers encontrados: " . implode(', ', $header) . "\n\n";
    
    // Encontrar índices de las columnas de fecha
    $fechaEmisionIndex = array_search('fecha_emision', $header);
    $fechaVencimientoIndex = array_search('fecha_vencimiento', $header);
    
    if ($fechaEmisionIndex === false) {
        echo "❌ No se encontró la columna 'fecha_emision'\n";
        exit(1);
    }
    
    echo "Índice fecha_emision: {$fechaEmisionIndex}\n";
    echo "Índice fecha_vencimiento: {$fechaVencimientoIndex}\n\n";
    
    echo "=== ANÁLISIS DE FECHAS POR FILA ===\n\n";
    
    foreach ($data as $rowIndex => $row) {
        if (empty($row) || count($row) < count($header)) {
            continue;
        }
        
        $rowData = array_combine($header, $row);
        
        echo "Fila " . ($rowIndex + 2) . " - Recibo: {$rowData['numero_recibo']}\n";
        echo "  Valor crudo fecha_emision: " . var_export($row[$fechaEmisionIndex], true) . "\n";
        
        $fechaParsed = parseExcelDate($row[$fechaEmisionIndex]);
        echo "  Fecha final: " . $fechaParsed->format('d-m-Y') . "\n";
        echo "\n";
        
        // Solo procesar las primeras 10 filas para no saturar
        if ($rowIndex >= 9) {
            echo "... (mostrando solo las primeras 10 filas)\n";
            break;
        }
    }
    
    echo "\n=== ANÁLISIS COMPLETADO ===\n";
    echo "\nEste debug muestra exactamente cómo se están leyendo las fechas del Excel\n";
    echo "y cómo las procesa el método parseExcelDate del controlador.\n";
    
} catch (\Exception $e) {
    echo "❌ Error al leer Excel: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}