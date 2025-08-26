<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

echo "=== CREANDO EXCEL CON FECHAS COMO NÚMEROS SERIALES ===\n";
echo "Fecha y hora: " . date('Y-m-d H:i:s') . "\n\n";

// Crear nuevo libro de Excel
$spreadsheet = new Spreadsheet();

// Crear hoja de Apartamentos
$apartamentosSheet = $spreadsheet->getActiveSheet();
$apartamentosSheet->setTitle('Apartamentos');

$apartamentosHeaders = ['numero', 'piso', 'torre', 'propietario', 'telefono', 'email', 'area_m2', 'tipo', 'estado', 'estatus_financiero', 'observaciones'];
$apartamentosSheet->fromArray($apartamentosHeaders, null, 'A1');

$apartamentosData = [
    ['101', '1', 'A', 'Juan Pérez', '3001234567', 'juan@email.com', '70.5', 'apartamento', 'ocupado', 'solvente', ''],
    ['102', '1', 'A', 'María García', '3007654321', 'maria@email.com', '65.0', 'apartamento', 'ocupado', 'deudor', ''],
    ['103', '1', 'A', 'Carlos López', '3009876543', 'carlos@email.com', '80.0', 'apartamento', 'ocupado', 'solvente', '']
];

$apartamentosSheet->fromArray($apartamentosData, null, 'A2');

// Crear hoja de Recibos con fechas como números seriales
$recibosSheet = $spreadsheet->createSheet();
$recibosSheet->setTitle('Recibos');

$recibosHeaders = ['apartamento_numero', 'numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'valor_administracion', 'valor_aseo', 'valor_vigilancia', 'valor_mantenimiento', 'otros_conceptos', 'estado'];
$recibosSheet->fromArray($recibosHeaders, null, 'A1');

// Fechas que queremos usar (las mismas que causaron problemas)
$fechasEmision = [
    '31-07-2023',
    '31-01-2024', 
    '29-02-2024',
    '31-03-2024',
    '30-04-2024',
    '31-05-2024',
    '30-06-2024',
    '31-07-2024',
    '31-08-2024',
    '30-09-2024',
    '31-10-2024',
    '30-11-2024',
    '31-12-2024',
    '31-01-2025',
    '28-02-2025', // Corregida para 2025 (no bisiesto)
    '31-03-2025',
    '30-04-2025',
    '31-05-2025',
    '30-06-2025',
    '31-07-2025',
    '31-08-2025',
    '30-09-2025'
];

echo "Creando recibos con fechas como números seriales de Excel...\n";

$reciboCounter = 1;
foreach ($fechasEmision as $fechaStr) {
    $apartamento = 101 + (($reciboCounter - 1) % 3); // Rotar entre apartamentos 101, 102, 103
    $numeroRecibo = 'REC-' . str_pad($reciboCounter, 4, '0', STR_PAD_LEFT);
    
    // Convertir fecha string a Carbon
    $fechaEmision = Carbon::createFromFormat('d-m-Y', $fechaStr);
    $fechaVencimiento = $fechaEmision->copy()->addDays(30);
    
    // Convertir a números seriales de Excel
    $fechaEmisionSerial = Date::dateTimeToExcel($fechaEmision);
    $fechaVencimientoSerial = Date::dateTimeToExcel($fechaVencimiento);
    
    echo "Recibo {$numeroRecibo}: {$fechaStr} -> Serial: {$fechaEmisionSerial}\n";
    
    $periodo = $fechaEmision->format('Y-m');
    
    // Insertar datos en la fila
    $row = $reciboCounter + 1;
    $recibosSheet->setCellValue("A{$row}", $apartamento);
    $recibosSheet->setCellValue("B{$row}", $numeroRecibo);
    $recibosSheet->setCellValue("C{$row}", $periodo);
    
    // Insertar fechas como números seriales
    $recibosSheet->setCellValue("D{$row}", $fechaEmisionSerial);
    $recibosSheet->setCellValue("E{$row}", $fechaVencimientoSerial);
    
    // Aplicar formato de fecha a las celdas (esto es importante)
    $recibosSheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
    $recibosSheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
    
    // Resto de datos del recibo
    $recibosSheet->setCellValue("F{$row}", 25000);
    $recibosSheet->setCellValue("G{$row}", 5000);
    $recibosSheet->setCellValue("H{$row}", 8000);
    $recibosSheet->setCellValue("I{$row}", 3000);
    $recibosSheet->setCellValue("J{$row}", 0);
    $recibosSheet->setCellValue("K{$row}", 'activo');
    
    $reciboCounter++;
}

// Crear hoja de Pagos (vacía)
$pagosSheet = $spreadsheet->createSheet();
$pagosSheet->setTitle('Pagos');

$pagosHeaders = ['apartamento_numero', 'recibo_numero', 'fecha_pago', 'monto_pagado', 'metodo_pago', 'numero_comprobante', 'estado', 'observaciones'];
$pagosSheet->fromArray($pagosHeaders, null, 'A1');

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$filename = 'test_fechas_seriales.xlsx';
$writer->save($filename);

echo "\n✅ Archivo Excel creado: {$filename}\n";
echo "\nCaracterísticas del archivo:\n";
echo "- Apartamentos: 3 registros\n";
echo "- Recibos: " . count($fechasEmision) . " registros\n";
echo "- Fechas almacenadas como números seriales de Excel\n";
echo "- Formato de celda aplicado para mostrar fechas correctamente\n";
echo "\n=== ARCHIVO LISTO PARA PROBAR IMPORTACIÓN ===\n";
echo "Este archivo contiene fechas como números seriales de Excel,\n";
echo "que es probablemente lo que está causando el problema en la importación.\n";