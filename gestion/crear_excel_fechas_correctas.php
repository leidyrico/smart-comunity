<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

echo "=== CREANDO EXCEL CON FECHAS CORRECTAS ===\n\n";

// Crear nuevo spreadsheet
$spreadsheet = new Spreadsheet();

// Eliminar hoja por defecto
$spreadsheet->removeSheetByIndex(0);

// Crear hoja de Apartamentos
$apartamentosSheet = $spreadsheet->createSheet();
$apartamentosSheet->setTitle('Apartamentos');

// Headers para apartamentos
$apartamentosHeaders = ['numero', 'piso', 'torre', 'propietario', 'telefono', 'email', 'area_m2', 'tipo', 'estado', 'estatus_financiero', 'observaciones'];
$apartamentosSheet->fromArray($apartamentosHeaders, null, 'A1');

// Datos de apartamentos
$apartamentosData = [
    ['PB-1', 'PB', 'A', 'Juan Pérez', '3001234567', 'juan@email.com', '75', 'apartamento', 'ocupado', 'deudor', 'Apartamento con deudas'],
    ['PB-2', 'PB', 'A', 'María García', '3007654321', 'maria@email.com', '80', 'apartamento', 'ocupado', 'deudor', 'Apartamento con deudas'],
    ['PB-3', 'PB', 'A', 'Carlos López', '3009876543', 'carlos@email.com', '70', 'apartamento', 'ocupado', 'deudor', 'Apartamento con deudas']
];

$apartamentosSheet->fromArray($apartamentosData, null, 'A2');

// Crear hoja de Recibos
$recibosSheet = $spreadsheet->createSheet();
$recibosSheet->setTitle('Recibos');

// Headers para recibos
$recibosHeaders = ['apartamento_numero', 'numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'valor_administracion', 'valor_aseo', 'valor_vigilancia', 'valor_mantenimiento', 'otros_conceptos', 'estado'];
$recibosSheet->fromArray($recibosHeaders, null, 'A1');

// Fechas correctas que proporcionaste
$fechasCorrectas = [
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
    '28-02-2025', // Corregido: 2025 no es bisiesto
    '31-03-2025',
    '30-04-2025',
    '31-05-2025',
    '30-06-2025'
];

// Crear recibos con fechas correctas
$recibosData = [];
$apartamentos = ['PB-1', 'PB-2', 'PB-3'];
$reciboCounter = 1;

foreach ($fechasCorrectas as $i => $fechaStr) {
    // Alternar entre apartamentos
    $apartamento = $apartamentos[$i % 3];
    
    // Crear fecha Carbon
    $fechaEmision = Carbon::createFromFormat('d-m-Y', $fechaStr);
    $fechaVencimiento = $fechaEmision->copy()->addDays(30);
    
    // Generar número de recibo
    $numeroRecibo = 'REC-' . str_pad($reciboCounter, 4, '0', STR_PAD_LEFT);
    
    // Período basado en la fecha
    $periodo = $fechaEmision->format('Y-m');
    
    $recibosData[] = [
        $apartamento,
        $numeroRecibo,
        $periodo,
        $fechaStr, // Usar formato string d-m-Y
        $fechaVencimiento->format('d-m-Y'),
        25000,
        5000,
        8000,
        3000,
        0,
        'vencido'
    ];
    
    $reciboCounter++;
}

// Agregar un recibo activo para cada apartamento
foreach ($apartamentos as $apartamento) {
    $fechaEmision = Carbon::now()->startOfMonth();
    $fechaVencimiento = $fechaEmision->copy()->addDays(30);
    $numeroRecibo = 'REC-' . str_pad($reciboCounter, 4, '0', STR_PAD_LEFT);
    $periodo = $fechaEmision->format('Y-m');
    
    $recibosData[] = [
        $apartamento,
        $numeroRecibo,
        $periodo,
        $fechaEmision->format('d-m-Y'),
        $fechaVencimiento->format('d-m-Y'),
        25000,
        5000,
        8000,
        3000,
        0,
        'activo'
    ];
    
    $reciboCounter++;
}

$recibosSheet->fromArray($recibosData, null, 'A2');

// Crear hoja de Pagos (vacía para esta prueba)
$pagosSheet = $spreadsheet->createSheet();
$pagosSheet->setTitle('Pagos');

$pagosHeaders = ['apartamento_numero', 'recibo_numero', 'fecha_pago', 'monto_pagado', 'metodo_pago', 'numero_comprobante', 'estado', 'observaciones'];
$pagosSheet->fromArray($pagosHeaders, null, 'A1');

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$filename = 'test_fechas_correctas.xlsx';
$writer->save($filename);

echo "✅ Archivo Excel creado: {$filename}\n";
echo "\nHojas creadas:\n";
echo "- Apartamentos: 3 registros\n";
echo "- Recibos: " . count($recibosData) . " registros\n";
echo "- Pagos: 0 registros (solo headers)\n";

echo "\nFechas incluidas en los recibos:\n";
foreach ($fechasCorrectas as $i => $fecha) {
    echo ($i + 1) . ". {$fecha}\n";
}

echo "\n=== ARCHIVO LISTO PARA IMPORTAR ===\n";
echo "Puedes usar este archivo para probar la importación y verificar si las fechas se procesan correctamente.\n";