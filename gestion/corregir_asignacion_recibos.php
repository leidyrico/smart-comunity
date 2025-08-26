<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Models\Pago;

echo "=== CORRECCIÓN DE ASIGNACIÓN DE RECIBOS ===\n\n";

// 1. Verificar estado actual
echo "1. ESTADO ACTUAL:\n";
$recibos = ReciboGastoComun::all();
$apartamentos = Apartamento::all();
$pagos = Pago::all();

echo "- Recibos totales: " . $recibos->count() . "\n";
echo "- Apartamentos totales: " . $apartamentos->count() . "\n";
echo "- Pagos totales: " . $pagos->count() . "\n";

// 2. Identificar recibos sin asignar
echo "\n2. IDENTIFICANDO RECIBOS SIN ASIGNAR:\n";
$recibosSinAsignar = [];

foreach ($recibos as $recibo) {
    $pagosDelRecibo = Pago::where('recibo_gasto_comun_id', $recibo->id)->count();
    $apartamentosTotales = $apartamentos->count();
    
    if ($pagosDelRecibo < $apartamentosTotales) {
        $recibosSinAsignar[] = $recibo;
        echo "- Recibo {$recibo->numero_recibo} ({$recibo->estado}): {$pagosDelRecibo}/{$apartamentosTotales} apartamentos asignados\n";
    }
}

if (empty($recibosSinAsignar)) {
    echo "✅ Todos los recibos están correctamente asignados.\n";
    exit;
}

echo "\n⚠️  Encontrados " . count($recibosSinAsignar) . " recibos con asignaciones incompletas.\n";

// 3. Función para asignar recibo a todos los apartamentos
function asignarReciboATodosApartamentos($recibo) {
    $apartamentos = Apartamento::all();
    $asignacionesCreadas = 0;
    
    foreach ($apartamentos as $apartamento) {
        // Verificar si ya existe un pago para este apartamento y recibo
        $pagoExistente = Pago::where('recibo_gasto_comun_id', $recibo->id)
                            ->where('apartamento_id', $apartamento->id)
                            ->first();
        
        if ($pagoExistente) {
            continue; // Ya existe, saltar
        }
        
        // Determinar el estado del pago según el estatus financiero del apartamento y estado del recibo
        $estadoPago = 'pendiente_confirmacion';
        $observaciones = 'Recibo asignado automáticamente - Corrección';
        
        // Si el apartamento es solvente y el recibo está vencido, crear el pago como rechazado
        if ($apartamento->estatus_financiero === 'solvente' && $recibo->estado === 'vencido') {
            $estadoPago = 'rechazado';
            $observaciones = 'Recibo asignado automáticamente - Apartamento solvente con recibo vencido - Corrección';
        } elseif ($apartamento->estatus_financiero === 'solvente') {
            $observaciones = 'Recibo asignado automáticamente - Apartamento solvente - Corrección';
        }
        
        // Crear registro de pago
        Pago::create([
            'recibo_gasto_comun_id' => $recibo->id,
            'apartamento_id' => $apartamento->id,
            'monto_pagado' => 0,
            'fecha_pago' => null,
            'metodo_pago' => null,
            'numero_comprobante' => null,
            'observaciones' => $observaciones,
            'estado' => $estadoPago
        ]);
        
        $asignacionesCreadas++;
    }
    
    return $asignacionesCreadas;
}

// 4. Corregir asignaciones
echo "\n3. CORRIGIENDO ASIGNACIONES:\n";
$totalAsignacionesCreadas = 0;

foreach ($recibosSinAsignar as $recibo) {
    echo "\nProcesando recibo {$recibo->numero_recibo} ({$recibo->estado})...\n";
    $asignacionesCreadas = asignarReciboATodosApartamentos($recibo);
    $totalAsignacionesCreadas += $asignacionesCreadas;
    echo "  ✅ Creadas {$asignacionesCreadas} asignaciones\n";
}

echo "\n4. RESULTADO FINAL:\n";
echo "- Total de asignaciones creadas: {$totalAsignacionesCreadas}\n";

// 5. Verificar estado después de la corrección
echo "\n5. VERIFICACIÓN POST-CORRECCIÓN:\n";
foreach ($apartamentos as $apartamento) {
    $saldoPendiente = $apartamento->saldo_pendiente;
    echo "- Apartamento {$apartamento->numero} ({$apartamento->estatus_financiero}): Saldo pendiente $" . number_format($saldoPendiente, 0, ',', '.') . "\n";
}

echo "\n✅ CORRECCIÓN COMPLETADA\n";
echo "\nAhora todos los recibos deberían estar correctamente asignados a todos los apartamentos.\n";
echo "Los apartamentos 'deudor' deberían mostrar el saldo pendiente correcto.\n";