<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();

// Crear hoja de Apartamentos
$apartamentosSheet = $spreadsheet->getActiveSheet();
$apartamentosSheet->setTitle('Apartamentos');

// Headers para Apartamentos
$apartamentosHeaders = ['numero', 'piso', 'torre', 'propietario', 'telefono', 'email', 'area_m2', 'tipo', 'estado', 'estatus_financiero', 'observaciones'];
$apartamentosSheet->fromArray($apartamentosHeaders, null, 'A1');

// Datos de ejemplo para apartamentos
$apartamentosData = [
    ['701', '7', 'A', 'Carlos Rodriguez', '3001111111', 'carlos@email.com', '70.5', 'apartamento', 'ocupado', 'solvente', ''],
    ['702', '7', 'A', 'Ana Martinez', '3002222222', 'ana@email.com', '65.0', 'apartamento', 'ocupado', 'solvente', ''],
    ['703', '7', 'A', 'Luis Gomez', '3003333333', 'luis@email.com', '80.0', 'apartamento', 'ocupado', 'solvente', '']
];
$apartamentosSheet->fromArray($apartamentosData, null, 'A2');

// Crear hoja de Recibos
$recibosSheet = $spreadsheet->createSheet();
$recibosSheet->setTitle('Recibos');

// Headers para Recibos
$recibosHeaders = ['numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'valor_administracion', 'valor_aseo', 'valor_vigilancia', 'valor_mantenimiento', 'otros_conceptos', 'estado'];
$recibosSheet->fromArray($recibosHeaders, null, 'A1');

// Datos de ejemplo para recibos
$recibosData = [
    ['REC-701-2024-01', '2024-01', '01-01-2024', '31-01-2024', '150000', '20000', '30000', '15000', '0', 'activo'],
    ['REC-701-2024-02', '2024-02', '01-02-2024', '29-02-2024', '150000', '20000', '30000', '15000', '5000', 'activo'],
    ['REC-702-2024-01', '2024-01', '01-01-2024', '31-01-2024', '140000', '18000', '28000', '12000', '0', 'activo'],
    ['REC-702-2024-02', '2024-02', '01-02-2024', '29-02-2024', '140000', '18000', '28000', '12000', '0', 'activo'],
    ['REC-703-2024-01', '2024-01', '01-01-2024', '31-01-2024', '160000', '22000', '32000', '18000', '0', 'activo'],
    ['REC-703-2024-02', '2024-02', '01-02-2024', '29-02-2024', '160000', '22000', '32000', '18000', '8000', 'activo']
];
$recibosSheet->fromArray($recibosData, null, 'A2');

// Crear hoja de Pagos
$pagosSheet = $spreadsheet->createSheet();
$pagosSheet->setTitle('Pagos');

// Headers para Pagos
$pagosHeaders = ['apartamento_numero', 'recibo_numero', 'fecha_pago', 'monto_pagado', 'metodo_pago', 'numero_comprobante', 'estado', 'observaciones'];
$pagosSheet->fromArray($pagosHeaders, null, 'A1');

// Datos de ejemplo para pagos
$pagosData = [
    ['701', 'REC-701-2024-01', '15-01-2024', '215000', 'transferencia', 'TRF-001', 'confirmado', 'Pago completo'],
    ['702', 'REC-702-2024-01', '20-01-2024', '100000', 'efectivo', 'EFE-001', 'confirmado', 'Pago parcial'],
    ['703', 'REC-703-2024-01', '25-01-2024', '232000', 'cheque', 'CHE-001', 'confirmado', 'Pago completo']
];
$pagosSheet->fromArray($pagosData, null, 'A2');

// Guardar el archivo
$writer = new Xlsx($spreadsheet);
$writer->save('test_import_completo.xlsx');

echo "Archivo Excel creado exitosamente: test_import_completo.xlsx\n";
echo "Hojas creadas: Apartamentos, Recibos, Pagos\n";
echo "Apartamentos: 3 registros\n";
echo "Recibos: 6 registros\n";
echo "Pagos: 3 registros\n";
?>