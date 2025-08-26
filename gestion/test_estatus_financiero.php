<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "=== PRUEBA DE ESTATUS FINANCIERO ===\n\n";

// 1. Crear archivo Excel de prueba con diferentes estatus financieros
echo "1. Creando archivo Excel de prueba...\n";

$spreadsheet = new Spreadsheet();

// Hoja de Apartamentos
$apartamentosSheet = $spreadsheet->getActiveSheet();
$apartamentosSheet->setTitle('Apartamentos');

// Encabezados
$apartamentosSheet->setCellValue('A1', 'numero');
$apartamentosSheet->setCellValue('B1', 'piso');
$apartamentosSheet->setCellValue('C1', 'torre');
$apartamentosSheet->setCellValue('D1', 'propietario');
$apartamentosSheet->setCellValue('E1', 'telefono');
$apartamentosSheet->setCellValue('F1', 'email');
$apartamentosSheet->setCellValue('G1', 'area_m2');
$apartamentosSheet->setCellValue('H1', 'tipo');
$apartamentosSheet->setCellValue('I1', 'estado');
$apartamentosSheet->setCellValue('J1', 'estatus_financiero');
$apartamentosSheet->setCellValue('K1', 'observaciones');

// Datos de prueba - Apartamento SOLVENTE
$apartamentosSheet->setCellValue('A2', '201');
$apartamentosSheet->setCellValue('B2', '2');
$apartamentosSheet->setCellValue('C2', 'A');
$apartamentosSheet->setCellValue('D2', 'Carlos Solvente');
$apartamentosSheet->setCellValue('E2', '3001111111');
$apartamentosSheet->setCellValue('F2', 'carlos@email.com');
$apartamentosSheet->setCellValue('G2', '70');
$apartamentosSheet->setCellValue('H2', 'apartamento');
$apartamentosSheet->setCellValue('I2', 'ocupado');
$apartamentosSheet->setCellValue('J2', 'solvente');
$apartamentosSheet->setCellValue('K2', 'Apartamento al día');

// Datos de prueba - Apartamento DEUDOR
$apartamentosSheet->setCellValue('A3', '202');
$apartamentosSheet->setCellValue('B3', '2');
$apartamentosSheet->setCellValue('C3', 'A');
$apartamentosSheet->setCellValue('D3', 'María Deudora');
$apartamentosSheet->setCellValue('E3', '3002222222');
$apartamentosSheet->setCellValue('F3', 'maria@email.com');
$apartamentosSheet->setCellValue('G3', '65');
$apartamentosSheet->setCellValue('H3', 'apartamento');
$apartamentosSheet->setCellValue('I3', 'ocupado');
$apartamentosSheet->setCellValue('J3', 'deudor');
$apartamentosSheet->setCellValue('K3', 'Apartamento con deuda');

// Hoja de Recibos
$recibosSheet = $spreadsheet->createSheet();
$recibosSheet->setTitle('Recibos');

// Encabezados
$recibosSheet->setCellValue('A1', 'numero_recibo');
$recibosSheet->setCellValue('B1', 'periodo');
$recibosSheet->setCellValue('C1', 'fecha_emision');
$recibosSheet->setCellValue('D1', 'fecha_vencimiento');
$recibosSheet->setCellValue('E1', 'valor_administracion');
$recibosSheet->setCellValue('F1', 'valor_aseo');
$recibosSheet->setCellValue('G1', 'valor_vigilancia');
$recibosSheet->setCellValue('H1', 'valor_mantenimiento');
$recibosSheet->setCellValue('I1', 'otros_conceptos');
$recibosSheet->setCellValue('J1', 'estado');

// Recibo vencido
$recibosSheet->setCellValue('A2', 'REC-202501');
$recibosSheet->setCellValue('B2', '2025-01');
$recibosSheet->setCellValue('C2', '01-01-2025');
$recibosSheet->setCellValue('D2', '15-01-2025');
$recibosSheet->setCellValue('E2', '150000');
$recibosSheet->setCellValue('F2', '25000');
$recibosSheet->setCellValue('G2', '30000');
$recibosSheet->setCellValue('H2', '20000');
$recibosSheet->setCellValue('I2', '5000');
$recibosSheet->setCellValue('J2', 'vencido');

// Hoja de Pagos (vacía para esta prueba)
$pagosSheet = $spreadsheet->createSheet();
$pagosSheet->setTitle('Pagos');

// Encabezados
$pagosSheet->setCellValue('A1', 'apartamento_numero');
$pagosSheet->setCellValue('B1', 'recibo_numero');
$pagosSheet->setCellValue('C1', 'fecha_pago');
$pagosSheet->setCellValue('D1', 'monto_pagado');
$pagosSheet->setCellValue('E1', 'metodo_pago');
$pagosSheet->setCellValue('F1', 'numero_comprobante');
$pagosSheet->setCellValue('G1', 'estado');
$pagosSheet->setCellValue('H1', 'observaciones');

// Guardar archivo
$writer = new Xlsx($spreadsheet);
$filename = 'test_estatus_financiero.xlsx';
$writer->save($filename);

echo "Archivo creado: $filename\n\n";

// 2. Limpiar datos existentes
echo "2. Limpiando datos existentes...\n";
// Usar delete en lugar de truncate para evitar problemas con claves foráneas
Pago::query()->delete();
ReciboGastoComun::query()->delete();
Apartamento::query()->delete();
echo "Datos limpiados.\n\n";

// 3. Simular importación (sin usar el controlador para evitar problemas)
echo "3. Importando datos manualmente...\n";

// Importar apartamentos
$apartamentos = [
    ['numero' => '201', 'propietario' => 'Carlos Solvente', 'estatus_financiero' => 'solvente'],
    ['numero' => '202', 'propietario' => 'María Deudora', 'estatus_financiero' => 'deudor']
];

foreach ($apartamentos as $data) {
    Apartamento::create([
        'numero' => $data['numero'],
        'propietario' => $data['propietario'],
        'telefono' => '3001234567',
        'email' => 'test@email.com',
        'piso' => 2,
        'torre' => 'A',
        'area_m2' => 70,
        'tipo' => 'apartamento',
        'estado' => 'ocupado',
        'estatus_financiero' => $data['estatus_financiero'],
        'observaciones' => 'Prueba estatus financiero'
    ]);
    echo "Apartamento {$data['numero']} creado con estatus: {$data['estatus_financiero']}\n";
}

// Importar recibo vencido
$totalRecibo = 150000 + 25000 + 30000 + 20000 + 5000; // 230000
$recibo = ReciboGastoComun::create([
    'numero_recibo' => 'REC-202501',
    'periodo' => '2025-01',
    'fecha_emision' => '2025-01-01',
    'fecha_vencimiento' => '2025-01-15',
    'valor_administracion' => 150000,
    'valor_aseo' => 25000,
    'valor_vigilancia' => 30000,
    'valor_mantenimiento' => 20000,
    'otros_conceptos' => 5000,
    'total_recibo' => $totalRecibo,
    'estado' => 'vencido'
]);
echo "Recibo vencido creado: {$recibo->numero_recibo} por {$recibo->total_recibo}\n\n";

// 4. Verificar saldos pendientes
echo "4. Verificando saldos pendientes...\n";

$apartamentos = Apartamento::all();
foreach ($apartamentos as $apartamento) {
    echo "Apartamento {$apartamento->numero} ({$apartamento->propietario}):\n";
    echo "  - Estatus financiero: {$apartamento->estatus_financiero}\n";
    echo "  - Saldo pendiente: {$apartamento->saldo_pendiente}\n";
    
    if ($apartamento->estatus_financiero === 'solvente') {
        if ($apartamento->saldo_pendiente == 0) {
            echo "  ✅ CORRECTO: Apartamento solvente tiene saldo 0\n";
        } else {
            echo "  ❌ ERROR: Apartamento solvente debería tener saldo 0\n";
        }
    } else {
        if ($apartamento->saldo_pendiente > 0) {
            echo "  ✅ CORRECTO: Apartamento deudor tiene saldo > 0\n";
        } else {
            echo "  ❌ ERROR: Apartamento deudor debería tener saldo > 0\n";
        }
    }
    echo "\n";
}

echo "=== FIN DE PRUEBA ===\n";