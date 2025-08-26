<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

echo "=== ANÁLISIS DE ARCHIVOS EXCEL EXISTENTES ===\n";
echo "Fecha y hora: " . date('Y-m-d H:i:s') . "\n\n";

// Lista de archivos Excel a analizar
$archivosExcel = [
    'test_import_completo.xlsx',
    'test_fechas_correctas.xlsx',
    'test_import.xlsx',
    'test_import_errors.xlsx',
    'test_estatus_financiero.xlsx'
];

foreach ($archivosExcel as $archivo) {
    if (!file_exists($archivo)) {
        echo "⚠️  Archivo no encontrado: {$archivo}\n";
        continue;
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "📁 ANALIZANDO: {$archivo}\n";
    echo str_repeat('=', 60) . "\n";
    
    try {
        $spreadsheet = IOFactory::load($archivo);
        
        // Listar todas las hojas
        echo "Hojas encontradas: ";
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            echo $sheet->getTitle() . " ";
        }
        echo "\n\n";
        
        // Buscar hoja de recibos
        $recibosSheet = null;
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (stripos($sheet->getTitle(), 'recibo') !== false) {
                $recibosSheet = $sheet;
                break;
            }
        }
        
        if (!$recibosSheet && $spreadsheet->getSheetCount() > 1) {
            $recibosSheet = $spreadsheet->getSheet(1); // Segunda hoja
        }
        
        if (!$recibosSheet) {
            $recibosSheet = $spreadsheet->getActiveSheet(); // Primera hoja
        }
        
        echo "📋 Analizando hoja: " . $recibosSheet->getTitle() . "\n";
        
        $highestRow = $recibosSheet->getHighestRow();
        $highestColumn = $recibosSheet->getHighestColumn();
        
        echo "Dimensiones: {$highestRow} filas x {$highestColumn} columnas\n\n";
        
        // Leer encabezados
        $headers = [];
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellValue = $recibosSheet->getCell([$col, 1])->getValue();
            $headers[$col] = $cellValue;
        }
        
        echo "Encabezados:\n";
        foreach ($headers as $col => $header) {
            echo "  [{$col}] {$header}\n";
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
        
        if ($fechaEmisionCol || $fechaVencimientoCol) {
            echo "🗓️  ANÁLISIS DE FECHAS:\n";
            echo "Columna fecha_emision: " . ($fechaEmisionCol ? "[{$fechaEmisionCol}] " . $headers[$fechaEmisionCol] : 'NO ENCONTRADA') . "\n";
            echo "Columna fecha_vencimiento: " . ($fechaVencimientoCol ? "[{$fechaVencimientoCol}] " . $headers[$fechaVencimientoCol] : 'NO ENCONTRADA') . "\n\n";
            
            // Analizar las primeras 5 filas de datos
            for ($row = 2; $row <= min($highestRow, 6); $row++) {
                echo "--- FILA {$row} ---\n";
                
                if ($fechaEmisionCol) {
                    $cell = $recibosSheet->getCell([$fechaEmisionCol, $row]);
                    $rawValue = $cell->getValue();
                    $formattedValue = $cell->getFormattedValue();
                    $calculatedValue = $cell->getCalculatedValue();
                    
                    echo "Fecha emisión:\n";
                    echo "  Raw: " . var_export($rawValue, true) . " (" . gettype($rawValue) . ")\n";
                    echo "  Formatted: " . var_export($formattedValue, true) . "\n";
                    echo "  Calculated: " . var_export($calculatedValue, true) . "\n";
                    
                    // Si es numérico, convertir desde serial Excel
                    if (is_numeric($rawValue) && $rawValue > 1) {
                        try {
                            $dateFromSerial = Date::excelToDateTimeObject($rawValue);
                            echo "  Convertido desde serial: " . $dateFromSerial->format('d-m-Y') . "\n";
                        } catch (Exception $e) {
                            echo "  Error al convertir serial: " . $e->getMessage() . "\n";
                        }
                    }
                    
                    // Verificar si la celda tiene formato de fecha
                    $style = $cell->getStyle();
                    $numberFormat = $style->getNumberFormat()->getFormatCode();
                    echo "  Formato de celda: {$numberFormat}\n";
                    
                    echo "\n";
                }
                
                if ($fechaVencimientoCol) {
                    $cell = $recibosSheet->getCell([$fechaVencimientoCol, $row]);
                    $rawValue = $cell->getValue();
                    $formattedValue = $cell->getFormattedValue();
                    
                    echo "Fecha vencimiento:\n";
                    echo "  Raw: " . var_export($rawValue, true) . " (" . gettype($rawValue) . ")\n";
                    echo "  Formatted: " . var_export($formattedValue, true) . "\n";
                    
                    if (is_numeric($rawValue) && $rawValue > 1) {
                        try {
                            $dateFromSerial = Date::excelToDateTimeObject($rawValue);
                            echo "  Convertido desde serial: " . $dateFromSerial->format('d-m-Y') . "\n";
                        } catch (Exception $e) {
                            echo "  Error al convertir serial: " . $e->getMessage() . "\n";
                        }
                    }
                    
                    $style = $cell->getStyle();
                    $numberFormat = $style->getNumberFormat()->getFormatCode();
                    echo "  Formato de celda: {$numberFormat}\n";
                }
                
                echo "\n";
            }
        } else {
            echo "⚠️  No se encontraron columnas de fechas en esta hoja\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error al procesar {$archivo}: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "=== ANÁLISIS COMPLETADO ===\n";
echo "\nEste análisis muestra cómo están almacenadas las fechas en los archivos Excel\n";
echo "y puede ayudar a identificar si vienen como números seriales o como texto.\n";