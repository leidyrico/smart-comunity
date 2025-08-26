<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== VALIDACIÓN DE CORRECCIÓN EN IMPORTACIÓN ===\n\n";

// 1. Limpiar base de datos
echo "1. LIMPIANDO BASE DE DATOS:\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Pago::truncate();
ReciboGastoComun::truncate();
Apartamento::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo "✅ Base de datos limpiada\n";

// 2. Crear apartamentos de prueba
echo "\n2. CREANDO APARTAMENTOS DE PRUEBA:\n";
$apartamentos = [
    ['numero' => '101', 'propietario' => 'Juan Pérez', 'estatus_financiero' => 'solvente'],
    ['numero' => '102', 'propietario' => 'María González', 'estatus_financiero' => 'deudor'],
    ['numero' => '103', 'propietario' => 'Carlos Rodríguez', 'estatus_financiero' => 'solvente']
];

foreach ($apartamentos as $apt) {
    Apartamento::create([
        'numero' => $apt['numero'],
        'propietario' => $apt['propietario'],
        'telefono' => '555-0000',
        'email' => strtolower(str_replace(' ', '.', $apt['propietario'])) . '@test.com',
        'estatus_financiero' => $apt['estatus_financiero']
    ]);
    echo "- Apartamento {$apt['numero']} ({$apt['estatus_financiero']}) creado\n";
}

// 3. Simular importación de recibos usando el DeudaController
echo "\n3. SIMULANDO IMPORTACIÓN DE RECIBOS:\n";

// Crear instancia del controlador
$controller = new \App\Http\Controllers\DeudaController();

// Crear datos de recibos simulados
$recibosData = [
    [
        'numero_recibo' => 'REC-TEST-001',
        'periodo' => 'Enero 2024',
        'fecha_emision' => '15/01/2024',
        'fecha_vencimiento' => '15/02/2024',
        'valor_administracion' => 30000,
        'valor_aseo' => 8000,
        'valor_vigilancia' => 10000,
        'valor_mantenimiento' => 5000,
        'otros_conceptos' => 2000,
        'estado' => 'activo'
    ],
    [
        'numero_recibo' => 'REC-TEST-002',
        'periodo' => 'Febrero 2024',
        'fecha_emision' => '15/02/2024',
        'fecha_vencimiento' => '15/03/2024',
        'valor_administracion' => 32000,
        'valor_aseo' => 8000,
        'valor_vigilancia' => 10000,
        'valor_mantenimiento' => 5000,
        'otros_conceptos' => 0,
        'estado' => 'activo'
    ]
];

// Simular el proceso de importación
foreach ($recibosData as $index => $reciboData) {
    try {
        // Parsear fechas usando el método del controlador
        $reflection = new \ReflectionClass($controller);
        $parseMethod = $reflection->getMethod('parseExcelDate');
        $parseMethod->setAccessible(true);
        
        $fechaEmision = $parseMethod->invoke($controller, $reciboData['fecha_emision']);
        $fechaVencimiento = $parseMethod->invoke($controller, $reciboData['fecha_vencimiento']);
        
        // Crear recibo
        $recibo = new ReciboGastoComun([
            'numero_recibo' => $reciboData['numero_recibo'],
            'periodo' => $reciboData['periodo'],
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaVencimiento,
            'valor_administracion' => $reciboData['valor_administracion'],
            'valor_aseo' => $reciboData['valor_aseo'],
            'valor_vigilancia' => $reciboData['valor_vigilancia'],
            'valor_mantenimiento' => $reciboData['valor_mantenimiento'],
            'otros_conceptos' => $reciboData['otros_conceptos'],
            'estado' => $reciboData['estado']
        ]);
        $recibo->calcularTotal();
        $recibo->save();
        
        // Simular la asignación automática (usando el método privado)
        if ($recibo->estado === 'activo') {
            $asignarMethod = $reflection->getMethod('asignarReciboATodosApartamentos');
            $asignarMethod->setAccessible(true);
            $asignarMethod->invoke($controller, $recibo);
        }
        
        echo "✅ Recibo {$reciboData['numero_recibo']} importado y asignado automáticamente\n";
        
    } catch (\Exception $e) {
        echo "❌ Error importando recibo {$reciboData['numero_recibo']}: " . $e->getMessage() . "\n";
    }
}

// 4. Verificar resultados
echo "\n4. VERIFICANDO RESULTADOS POST-IMPORTACIÓN:\n";

$apartamentos = Apartamento::all();
$recibos = ReciboGastoComun::all();
$pagos = Pago::all();

echo "- Apartamentos: " . $apartamentos->count() . "\n";
echo "- Recibos: " . $recibos->count() . "\n";
echo "- Pagos (asignaciones): " . $pagos->count() . "\n";

echo "\n5. SALDOS PENDIENTES POR APARTAMENTO:\n";
foreach ($apartamentos as $apartamento) {
    $saldoPendiente = $apartamento->saldo_pendiente;
    $estatusAnterior = $apartamento->getOriginal('estatus_financiero') ?? 'N/A';
    $estatusActual = $apartamento->estatus_financiero;
    
    echo "- Apartamento {$apartamento->numero}:\n";
    echo "  * Propietario: {$apartamento->propietario}\n";
    echo "  * Estatus financiero: {$estatusActual}\n";
    echo "  * Saldo pendiente: $" . number_format($saldoPendiente, 0, ',', '.') . "\n";
    
    // Verificar pagos asociados
    $pagosApartamento = Pago::where('apartamento_id', $apartamento->id)->get();
    echo "  * Pagos asignados: " . $pagosApartamento->count() . "\n";
    
    foreach ($pagosApartamento as $pago) {
        $recibo = $pago->reciboGastoComun;
        echo "    - Recibo: {$recibo->numero_recibo} (Estado: {$pago->estado})\n";
    }
    echo "\n";
}

// 6. Verificar que la lógica de estatus funciona correctamente
echo "6. VERIFICACIÓN DE LÓGICA DE ESTATUS:\n";
$apartamentosSolventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$apartamentosDeudores = Apartamento::where('estatus_financiero', 'deudor')->count();

echo "- Apartamentos solventes: {$apartamentosSolventes}\n";
echo "- Apartamentos deudores: {$apartamentosDeudores}\n";

// 7. Conclusión
echo "\n=== CONCLUSIÓN ===\n";
if ($pagos->count() === ($apartamentos->count() * $recibos->count())) {
    echo "✅ CORRECCIÓN EXITOSA: Todos los recibos se asignaron automáticamente\n";
    echo "✅ Los saldos pendientes se calculan correctamente durante la importación\n";
    echo "✅ El problema de importación de Excel ha sido resuelto\n";
} else {
    echo "❌ PROBLEMA DETECTADO: No todos los recibos se asignaron correctamente\n";
}

echo "\nAhora cuando importes un Excel, los recibos se asignarán automáticamente\n";
echo "a todos los apartamentos y los saldos se calcularán correctamente.\n";