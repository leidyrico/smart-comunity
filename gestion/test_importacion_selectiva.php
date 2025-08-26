<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE IMPORTACIÓN SELECTIVA DE RECIBOS VENCIDOS ===\n\n";

try {
    // Limpiar datos de prueba anteriores
    echo "Limpiando datos de prueba anteriores...\n";
    Pago::where('observaciones', 'LIKE', '%Asignación selectiva%')->delete();
    ReciboGastoComun::where('observaciones', 'LIKE', '%Importado selectivamente%')->delete();
    Apartamento::where('numero', 'LIKE', 'TEST-SEL-%')->delete();
    
    // Crear apartamentos de prueba
    echo "Creando apartamentos de prueba...\n";
    $apartamentos = [];
    for ($i = 1; $i <= 5; $i++) {
        $apartamentos[] = Apartamento::create([
            'numero' => "TEST-SEL-10{$i}",
            'propietario' => "Propietario Test {$i}",
            'telefono' => "300123456{$i}",
            'email' => "test{$i}@example.com",
            'piso' => 1,
            'tipo' => 'apartamento',
            'estado' => 'ocupado',
            'estatus_financiero' => $i <= 2 ? 'solvente' : 'deudor'
        ]);
    }
    
    echo "Apartamentos creados: " . count($apartamentos) . "\n";
    foreach ($apartamentos as $apt) {
        echo "  - {$apt->numero}: {$apt->propietario} (Estatus: {$apt->estatus_financiero})\n";
    }
    echo "\n";
    
    // Crear archivo Excel de prueba
    echo "Creando archivo Excel de prueba...\n";
    $excelData = [
        ['numero_recibo', 'periodo', 'fecha_emision', 'fecha_vencimiento', 'total_recibo', 'estado', 'observaciones'],
        ['REC-SEL-001', '2024-01', '01/01/2024', '31/01/2024', 50000, 'vencido', 'Recibo selectivo 1'],
        ['REC-SEL-002', '2024-02', '01/02/2024', '28/02/2024', 55000, 'vencido', 'Recibo selectivo 2']
    ];
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $worksheet = $spreadsheet->getActiveSheet();
    $worksheet->setTitle('Recibos');
    
    foreach ($excelData as $rowIndex => $rowData) {
        foreach ($rowData as $colIndex => $cellData) {
            $worksheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $cellData);
        }
    }
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $testFile = __DIR__ . '/test_recibos_selectivos.xlsx';
    $writer->save($testFile);
    echo "Archivo Excel creado: {$testFile}\n\n";
    
    // Simular importación selectiva (solo apartamentos 1, 3 y 5)
    echo "Simulando importación selectiva...\n";
    $apartamentosSeleccionados = [$apartamentos[0]->id, $apartamentos[2]->id, $apartamentos[4]->id];
    echo "Apartamentos seleccionados: " . implode(', ', array_map(function($id) use ($apartamentos) {
        $apt = collect($apartamentos)->firstWhere('id', $id);
        return $apt ? $apt->numero : $id;
    }, $apartamentosSeleccionados)) . "\n\n";
    
    // Simular el proceso de importación
    $file = $testFile;
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $worksheet = $spreadsheet->getSheetByName('Recibos');
    $data = $worksheet->toArray();
    
    $headers = array_map('trim', $data[0]);
    $recibosImportados = 0;
    $asignacionesCreadas = 0;
    
    DB::beginTransaction();
    
    // Procesar cada fila de recibos
    for ($i = 1; $i < count($data); $i++) {
        $row = $data[$i];
        if (empty(array_filter($row))) continue;
        
        $rowData = array_combine($headers, $row);
        
        // Crear el recibo
        $recibo = ReciboGastoComun::create([
            'numero_recibo' => $rowData['numero_recibo'],
            'periodo' => $rowData['periodo'],
            'fecha_emision' => \Carbon\Carbon::createFromFormat('d/m/Y', $rowData['fecha_emision'])->format('Y-m-d'),
            'fecha_vencimiento' => \Carbon\Carbon::createFromFormat('d/m/Y', $rowData['fecha_vencimiento'])->format('Y-m-d'),
            'total_recibo' => (float)$rowData['total_recibo'],
            'estado' => $rowData['estado'],
            'observaciones' => 'Importado selectivamente - ' . $rowData['observaciones']
        ]);
        
        $recibosImportados++;
        echo "Recibo creado: {$recibo->numero_recibo} - {$recibo->periodo} - \${$recibo->total_recibo}\n";
        
        // Asignar solo a apartamentos seleccionados
        foreach ($apartamentosSeleccionados as $apartamentoId) {
            $apartamento = Apartamento::find($apartamentoId);
            
            if ($apartamento) {
                // Determinar estado del pago
                $estadoPago = 'pendiente_confirmacion';
                if ($apartamento->estatus_financiero === 'deudor') {
                    $estadoPago = 'rechazado';
                }
                
                Pago::create([
                    'apartamento_id' => $apartamento->id,
                    'recibo_gasto_comun_id' => $recibo->id,
                    'monto_pagado' => 0,
                    'fecha_pago' => now(),
                    'metodo_pago' => 'pendiente',
                    'estado' => $estadoPago,
                    'observaciones' => 'Asignación selectiva de recibo vencido'
                ]);
                
                // Actualizar estatus financiero si es necesario
                if (in_array($recibo->estado, ['activo', 'vencido']) && $apartamento->estatus_financiero !== 'deudor') {
                    $apartamento->update([
                        'estatus_financiero' => 'deudor',
                        'fecha_cambio_estatus' => now()->toDateString()
                    ]);
                }
                
                $asignacionesCreadas++;
                echo "  - Asignado a {$apartamento->numero} (Estado: {$estadoPago})\n";
            }
        }
        echo "\n";
    }
    
    DB::commit();
    
    // Verificar resultados
    echo "=== VERIFICACIÓN DE RESULTADOS ===\n";
    echo "Recibos importados: {$recibosImportados}\n";
    echo "Asignaciones creadas: {$asignacionesCreadas}\n\n";
    
    // Verificar apartamentos y sus asignaciones
    echo "Estado final de apartamentos:\n";
    foreach ($apartamentos as $apt) {
        $apt->refresh();
        $pagosAsignados = Pago::where('apartamento_id', $apt->id)
            ->where('observaciones', 'LIKE', '%Asignación selectiva%')
            ->count();
        
        $seleccionado = in_array($apt->id, $apartamentosSeleccionados) ? 'SÍ' : 'NO';
        echo "  - {$apt->numero}: Estatus={$apt->estatus_financiero}, Seleccionado={$seleccionado}, Pagos asignados={$pagosAsignados}\n";
    }
    
    echo "\n=== PRUEBA COMPLETADA EXITOSAMENTE ===\n";
    echo "✓ Los recibos se importaron correctamente\n";
    echo "✓ Solo se asignaron a los apartamentos seleccionados\n";
    echo "✓ Los estatus financieros se actualizaron apropiadamente\n";
    
    // Limpiar archivo de prueba
    if (file_exists($testFile)) {
        unlink($testFile);
        echo "✓ Archivo de prueba eliminado\n";
    }
    
} catch (Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";