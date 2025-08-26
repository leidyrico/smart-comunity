<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

echo "Creando archivo Excel con fechas problemáticas...\n";

$spreadsheet = new Spreadsheet();

// Crear hoja de Apartamentos
$apartamentosSheet = $spreadsheet->getActiveSheet();
$apartamentosSheet->setTitle('Apartamentos');

$apartamentosHeaders = ['numero', 'propietario', 'telefono', 'email', 'area_m2', 'tipo', 'estado', 'observaciones'];
$apartamentosSheet->fromArray($apartamentosHeaders, null, 'A1');

$apartamentosData = [
    ['101', 'Juan Pérez', '555-0101', 'juan@email.com', 85, 'apartamento', 'ocupado', ''],
    ['102', 'María García', '555-0102', 'maria@email.com', 90, 'apartamento', 'ocupado', ''],
    ['103', 'Carlos López', '555-0103', 'carlos@email.com', 75, 'apartamento', 'ocupado', '']
];
$apartamentosSheet->fromArray($apartamentosData, null, 'A2');

// Crear hoja de Recibos con fechas problemáticas
$recibosSheet = $spreadsheet->createSheet();
$recibosSheet->setTitle('Recibos');

$recibosHeaders = ['apartamento_numero', 'numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'valor_administracion', 'valor_aseo', 'valor_vigilancia', 'valor_mantenimiento', 'otros_conceptos', 'estado'];
$recibosSheet->fromArray($recibosHeaders, null, 'A1');

// Fechas problemáticas específicas
$fechasProblematicas = [
    // Fechas que pueden causar problemas
    ['101', 'REC-PROB-001', '2024-02', '29/02/2024', '31/03/2024'], // 29 de febrero en año bisiesto
    ['102', 'REC-PROB-002', '2025-02', '28/02/2025', '31/03/2025'], // 28 de febrero en año no bisiesto
    ['103', 'REC-PROB-003', '2024-01', '31/01/2024', '29/02/2024'], // Vencimiento 29 de febrero
    ['101', 'REC-PROB-004', '2025-01', '31/01/2025', '28/02/2025'], // Vencimiento 28 de febrero
    ['102', 'REC-PROB-005', '2024-04', '30/04/2024', '31/05/2024'], // 30 de abril
    ['103', 'REC-PROB-006', '2024-06', '30/06/2024', '31/07/2024'], // 30 de junio
    ['101', 'REC-PROB-007', '2024-09', '30/09/2024', '31/10/2024'], // 30 de septiembre
    ['102', 'REC-PROB-008', '2024-11', '30/11/2024', '31/12/2024'], // 30 de noviembre
    // Fechas con años diferentes para probar rangos
    ['103', 'REC-PROB-009', '2023-12', '31/12/2023', '31/01/2024'], // Año 2023
    ['101', 'REC-PROB-010', '2026-01', '31/01/2026', '28/02/2026'], // Año 2026
];

echo "Creando recibos con fechas problemáticas...\n";

$row = 2;
foreach ($fechasProblematicas as $data) {
    $apartamento = $data[0];
    $numeroRecibo = $data[1];
    $periodo = $data[2];
    $fechaEmision = $data[3];
    $fechaVencimiento = $data[4];
    
    echo "  - {$numeroRecibo}: {$fechaEmision} -> {$fechaVencimiento}\n";
    
    $recibosSheet->setCellValue("A{$row}", $apartamento);
    $recibosSheet->setCellValue("B{$row}", $numeroRecibo);
    $recibosSheet->setCellValue("C{$row}", $periodo);
    $recibosSheet->setCellValue("D{$row}", $fechaEmision);
    $recibosSheet->setCellValue("E{$row}", $fechaVencimiento);
    $recibosSheet->setCellValue("F{$row}", 25000);
    $recibosSheet->setCellValue("G{$row}", 5000);
    $recibosSheet->setCellValue("H{$row}", 8000);
    $recibosSheet->setCellValue("I{$row}", 3000);
    $recibosSheet->setCellValue("J{$row}", 0);
    $recibosSheet->setCellValue("K{$row}", 'activo');
    
    $row++;
}

// Crear hoja de Pagos (vacía)
$pagosSheet = $spreadsheet->createSheet();
$pagosSheet->setTitle('Pagos');

$pagosHeaders = ['apartamento_numero', 'recibo_numero', 'fecha_pago', 'monto_pagado', 'metodo_pago', 'numero_comprobante', 'estado', 'observaciones'];
$pagosSheet->fromArray($pagosHeaders, null, 'A1');

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$filename = 'recibos_fechas_problematicas.xlsx';
$writer->save($filename);

echo "\n✅ Archivo Excel creado: {$filename}\n";
echo "\nHojas creadas:\n";
echo "- Apartamentos: 3 registros\n";
echo "- Recibos: " . count($fechasProblematicas) . " registros con fechas problemáticas\n";
echo "- Pagos: 0 registros (solo headers)\n";

echo "\n=== FECHAS PROBLEMÁTICAS INCLUIDAS ===\n";
foreach ($fechasProblematicas as $i => $data) {
    echo ($i + 1) . ". {$data[1]}: {$data[3]} -> {$data[4]}\n";
}

echo "\n=== ARCHIVO LISTO PARA IMPORTAR ===\n";
echo "Usa este archivo para probar la importación y verificar problemas de fechas.\n";