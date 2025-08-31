<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== PRUEBA FINAL: VERIFICACIÓN SIN REC-0922 ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// === PRUEBA 1: MÉTODO createGlobal ACTUALIZADO ===
echo "=== PRUEBA 1: MÉTODO createGlobal ACTUALIZADO ===\n";

// Simular exactamente la lógica del método createGlobal actualizado
$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })
    ->whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

echo "Recibos asignados (excluyendo rechazados): " . $recibosAsignados->count() . "\n";

$recibos_pendientes = collect();
$total_pendiente = 0;

// Verificar si REC-0922 aparece
$rec0922Encontrado = false;

foreach ($recibosAsignados as $recibo) {
    if ($recibo->numero_recibo === 'REC-0922') {
        $rec0922Encontrado = true;
        echo "❌ ERROR: REC-0922 aún aparece en createGlobal\n";
    }
    
    // Calcular total pagado para este recibo y apartamento específico
    $totalPagado = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    // Solo incluir recibos con saldo pendiente > 0
    if ($saldoPendiente > 0) {
        $recibo->saldo_pendiente_apartamento = $saldoPendiente;
        $recibos_pendientes->push($recibo);
        $total_pendiente += $saldoPendiente;
    }
}

if (!$rec0922Encontrado) {
    echo "✅ REC-0922 NO aparece en createGlobal (correcto)\n";
}

echo "Recibos pendientes: " . $recibos_pendientes->count() . "\n";
echo "Total pendiente: $" . number_format($total_pendiente, 2) . "\n\n";

// === PRUEBA 2: MÉTODO storeGlobal ACTUALIZADO ===
echo "=== PRUEBA 2: MÉTODO storeGlobal ACTUALIZADO ===\n";

// Simular exactamente la lógica del método storeGlobal actualizado
$recibosAsignadosStore = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })
    ->with(['pagos' => function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', 'confirmado');
    }])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

echo "Recibos para storeGlobal (excluyendo rechazados): " . $recibosAsignadosStore->count() . "\n";

// Verificar si REC-0922 aparece en storeGlobal
$rec0922EnStore = false;
foreach ($recibosAsignadosStore as $recibo) {
    if ($recibo->numero_recibo === 'REC-0922') {
        $rec0922EnStore = true;
        echo "❌ ERROR: REC-0922 aún aparece en storeGlobal\n";
    }
}

if (!$rec0922EnStore) {
    echo "✅ REC-0922 NO aparece en storeGlobal (correcto)\n";
}

// Filtrar solo los recibos con saldo pendiente
$recibosConSaldo = collect();

foreach ($recibosAsignadosStore as $recibo) {
    $totalPagado = $recibo->pagos->sum('monto_pagado');
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    if ($saldoPendiente > 0) {
        $recibo->saldo_pendiente_calculado = $saldoPendiente;
        $recibosConSaldo->push($recibo);
    }
}

echo "Recibos con saldo pendiente para storeGlobal: " . $recibosConSaldo->count() . "\n\n";

// === PRUEBA 3: SIMULACIÓN DE PAGO ===
echo "=== PRUEBA 3: SIMULACIÓN DE PAGO DE $50.00 ===\n";

$montoTotal = 50.00;
$montoRestante = $montoTotal;
$pagosSimulados = [];

// Si no hay recibos activos asignados, buscar el recibo vencido más reciente asignado
if ($recibosConSaldo->where('estado', 'activo')->isEmpty()) {
    echo "⚠️ No hay recibos activos con saldo pendiente\n";
    echo "Aplicando lógica especial: buscando recibo vencido más reciente...\n";
    
    $reciboVencidoReciente = $recibosAsignadosStore->where('estado', 'vencido')
        ->sortByDesc('fecha_vencimiento')
        ->first();
        
    if ($reciboVencidoReciente) {
        $totalPagado = $reciboVencidoReciente->pagos->sum('monto_pagado');
        $saldoPendiente = $reciboVencidoReciente->total_recibo - $totalPagado;
        
        if ($saldoPendiente > 0) {
            $reciboVencidoReciente->saldo_pendiente_calculado = $saldoPendiente;
            $recibosConSaldo->prepend($reciboVencidoReciente);
            echo "✅ Recibo vencido más reciente agregado: {$reciboVencidoReciente->numero_recibo}\n";
        }
    }
} else {
    echo "✅ Hay recibos activos con saldo pendiente\n";
}

// Procesar los primeros 3 recibos para la simulación
echo "\nPrimeros 3 recibos que recibirían el pago:\n";
$contador = 0;
foreach ($recibosConSaldo as $recibo) {
    if ($contador >= 3 || $montoRestante <= 0) break;
    
    $saldoPendiente = $recibo->saldo_pendiente_calculado;
    $montoPagar = min($montoRestante, $saldoPendiente);
    
    echo ($contador + 1) . ". {$recibo->numero_recibo} - Pendiente: $" . number_format($saldoPendiente, 2) . " - Pagar: $" . number_format($montoPagar, 2) . "\n";
    
    $pagosSimulados[] = [
        'recibo' => $recibo->numero_recibo,
        'monto' => $montoPagar
    ];
    
    $montoRestante -= $montoPagar;
    $contador++;
}

echo "\nMonto restante después de simulación: $" . number_format($montoRestante, 2) . "\n\n";

// === RESUMEN FINAL ===
echo "=== RESUMEN FINAL ===\n";

if (!$rec0922Encontrado && !$rec0922EnStore) {
    echo "✅ ÉXITO TOTAL: REC-0922 ha sido completamente eliminado de las vistas\n";
    echo "✅ createGlobal: NO muestra REC-0922\n";
    echo "✅ storeGlobal: NO procesa REC-0922\n";
    echo "✅ El apartamento 13 ahora muestra solo recibos correctamente asignados\n\n";
    
    if (count($pagosSimulados) > 0) {
        echo "🎯 Primer recibo que recibiría un pago: {$pagosSimulados[0]['recibo']}\n";
        echo "💰 Monto que recibiría: $" . number_format($pagosSimulados[0]['monto'], 2) . "\n";
    }
    
} else {
    echo "❌ PROBLEMA: REC-0922 aún aparece en alguna vista\n";
    if ($rec0922Encontrado) echo "❌ Aparece en createGlobal\n";
    if ($rec0922EnStore) echo "❌ Aparece en storeGlobal\n";
}

echo "\n=== ESTADO DEL PAGO REC-0922 ===\n";
$recibo0922 = ReciboGastoComun::where('numero_recibo', 'REC-0922')->first();
if ($recibo0922) {
    $pagoRec0922 = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo0922->id)
        ->first();
    
    if ($pagoRec0922) {
        echo "Estado del pago: {$pagoRec0922->estado}\n";
        echo "Observaciones: {$pagoRec0922->observaciones}\n";
    }
}

echo "\n=== PRUEBA COMPLETADA ===\n";
?>