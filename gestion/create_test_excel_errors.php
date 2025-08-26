<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Crear nuevo spreadsheet
$spreadsheet = new Spreadsheet();

// === HOJA 1: APARTAMENTOS (con errores) ===
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Apartamentos');

// Headers
$headers1 = ['numero', 'piso', 'torre', 'propietario', 'telefono', 'email', 'area_m2', 'tipo', 'estado', 'estatus_financiero', 'observaciones'];
$sheet1->fromArray($headers1, null, 'A1');

// Datos con errores
$apartamentos = [
    // Apartamento válido
    ['801', '8', 'A', 'Pedro Martínez', '3001234567', 'pedro@email.com', '80', 'apartamento', 'ocupado', 'Solvente', ''],
    // Apartamento con número duplicado (error)
    ['801', '8', 'B', 'Ana García', '3007654321', 'ana@email.com', '85', 'apartamento', 'ocupado', 'Deudor', 'Duplicado'],
    // Apartamento sin propietario (error)
    ['802', '8', 'A', '', '3009876543', 'vacio@email.com', '75', 'apartamento', 'ocupado', 'Solvente', 'Sin propietario'],
    // Apartamento con email inválido (error)
    ['803', '8', 'A', 'Carlos López', '3005432109', 'email-invalido', '90', 'apartamento', 'ocupado', 'Deudor', 'Email malo'],
    // Apartamento con tipo inválido (error)
    ['804', '8', 'A', 'María Rodríguez', '3002468135', 'maria@email.com', '70', 'tipo_invalido', 'ocupado', 'Solvente', 'Tipo malo']
];

$sheet1->fromArray($apartamentos, null, 'A2');

// === HOJA 2: RECIBOS (con errores) ===
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Recibos');

$headers2 = ['numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'valor_administracion', 'valor_aseo', 'valor_vigilancia', 'valor_mantenimiento', 'otros_conceptos', 'estado', 'observaciones'];
$sheet2->fromArray($headers2, null, 'A1');

$recibos = [
    // Recibo válido
    ['REC-801-2024-01', '2024-01', '01-01-2024', '15-01-2024', '180000', '15000', '20000', '10000', '5000', 'activo', ''],
    // Recibo con número duplicado (error)
    ['REC-801-2024-01', '2024-01', '01-01-2024', '15-01-2024', '180000', '15000', '20000', '10000', '5000', 'activo', 'Duplicado'],
    // Recibo con fecha inválida (error)
    ['REC-802-2024-01', '2024-01', 'fecha-mala', '15-01-2024', '185000', '15000', '20000', '10000', '5000', 'activo', 'Fecha mala'],
    // Recibo con valores negativos (error)
    ['REC-803-2024-01', '2024-01', '01-01-2024', '15-01-2024', '-100000', '15000', '20000', '10000', '5000', 'activo', 'Valor negativo'],
    // Recibo con estado inválido (error)
    ['REC-804-2024-01', '2024-01', '01-01-2024', '15-01-2024', '190000', '15000', '20000', '10000', '5000', 'estado_malo', 'Estado inválido']
];

$sheet2->fromArray($recibos, null, 'A2');

// === HOJA 3: PAGOS (con errores) ===
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Pagos');

$headers3 = ['apartamento_numero', 'recibo_numero', 'fecha_pago', 'monto_pagado', 'metodo_pago', 'numero_comprobante', 'estado', 'observaciones'];
$sheet3->fromArray($headers3, null, 'A1');

$pagos = [
    // Pago válido
    ['801', 'REC-801-2024-01', '10-01-2024', '230000', 'transferencia', 'TRF-001', 'confirmado', ''],
    // Pago con apartamento inexistente (error)
    ['999', 'REC-801-2024-01', '10-01-2024', '100000', 'efectivo', 'EFE-001', 'confirmado', 'Apartamento no existe'],
    // Pago con recibo inexistente (error)
    ['801', 'REC-INEXISTENTE', '10-01-2024', '150000', 'cheque', 'CHE-001', 'confirmado', 'Recibo no existe'],
    // Pago con monto negativo (error)
    ['801', 'REC-801-2024-01', '10-01-2024', '-50000', 'efectivo', 'EFE-002', 'confirmado', 'Monto negativo'],
    // Pago con fecha inválida (error)
    ['801', 'REC-801-2024-01', 'fecha-invalida', '75000', 'transferencia', 'TRF-002', 'confirmado', 'Fecha mala']
];

$sheet3->fromArray($pagos, null, 'A2');

// Aplicar estilos a los headers
foreach ([$sheet1, $sheet2, $sheet3] as $sheet) {
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);
    
    // Auto-ajustar columnas
    foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// Guardar archivo
$writer = new Xlsx($spreadsheet);
$filename = 'test_import_errors.xlsx';
$writer->save($filename);

echo "Archivo Excel con errores creado: {$filename}\n";
echo "Hojas creadas:\n";
echo "- Apartamentos: 5 registros (4 con errores)\n";
echo "- Recibos: 5 registros (4 con errores)\n";
echo "- Pagos: 5 registros (4 con errores)\n";
echo "\nErrores incluidos:\n";
echo "- Números duplicados\n";
echo "- Campos obligatorios vacíos\n";
echo "- Formatos de fecha inválidos\n";
echo "- Valores negativos\n";
echo "- Estados/tipos inválidos\n";
echo "- Referencias a registros inexistentes\n";
?>