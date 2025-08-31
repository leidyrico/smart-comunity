<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== SIMULACIÓN DE PAGO GLOBAL CORREGIDO ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n";
echo "Simulando pago de $50.00\n\n";

// Simular exactamente la lógica del método storeGlobal corregido
$montoTotal = 50.00;
$montoRestante = $montoTotal;
$pagosSimulados = [];

// Obtener solo los recibos asignados al apartamento a través de la tabla pagos
$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id);
})->with(['pagos' => function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id)->where('estado', 'confirmado');
}])->orderBy('fecha_vencimiento', 'asc')->get();

echo "Recibos asignados encontrados: " . $recibosAsignados->count() . "\n\n";

// Filtrar solo los recibos con saldo pendiente para este apartamento
$recibosConSaldo = collect();

foreach ($recibosAsignados as $recibo) {
    $totalPagado = $recibo->pagos->sum('monto_pagado');
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    if ($saldoPendiente > 0) {
        $recibo->saldo_pendiente_calculado = $saldoPendiente;
        $recibosConSaldo->push($recibo);
    }
}

// Si no hay recibos activos asignados, buscar el recibo vencido más reciente asignado
if ($recibosConSaldo->where('estado', 'activo')->isEmpty()) {
    echo "⚠️ No hay recibos activos con saldo pendiente\n";
    echo "Aplicando nueva lógica: buscando recibo vencido más reciente...\n";
    
    $reciboVencidoReciente = $recibosAsignados->where('estado', 'vencido')->sortByDesc('fecha_vencimiento')->first();
        
    if ($reciboVencidoReciente) {
        $totalPagado = $reciboVencidoReciente->pagos->sum('monto_pagado');
        $saldoPendiente = $reciboVencidoReciente->total_recibo - $totalPagado;
        
        echo "✅ Recibo vencido más reciente encontrado: {$reciboVencidoReciente->numero_recibo}\n";
        echo "   Fecha: {$reciboVencidoReciente->fecha_vencimiento}\n";
        echo "   Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n\n";
        
        if ($saldoPendiente > 0) {
            $reciboVencidoReciente->saldo_pendiente_calculado = $saldoPendiente;
            // Agregar al inicio de la colección para que tenga prioridad
            $recibosConSaldo->prepend($reciboVencidoReciente);
        }
    }
} else {
    echo "✅ Hay recibos activos con saldo pendiente\n\n";
}

echo "=== PROCESAMIENTO DE PAGO ===\n";
echo "Monto a distribuir: $" . number_format($montoTotal, 2) . "\n\n";

// Procesar los recibos con saldo pendiente
foreach ($recibosConSaldo as $index => $recibo) {
    if ($montoRestante <= 0) break;
    
    // Usar el saldo pendiente ya calculado
    $saldoPendiente = $recibo->saldo_pendiente_calculado;
    
    // Determinar cuánto pagar de este recibo
    $montoPagar = min($montoRestante, $saldoPendiente);
    
    echo ($index + 1) . ". Recibo: {$recibo->numero_recibo}\n";
    echo "   Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    echo "   Monto a pagar: $" . number_format($montoPagar, 2) . "\n";
    echo "   Estado: {$recibo->estado}\n";
    echo "   Fecha: {$recibo->fecha_vencimiento}\n";
    
    // Simular la creación del pago
    $pagoSimulado = [
        'apartamento_id' => $apartamento->id,
        'recibo_gasto_comun_id' => $recibo->id,
        'numero_recibo' => $recibo->numero_recibo,
        'monto_pagado' => $montoPagar,
        'saldo_restante' => $saldoPendiente - $montoPagar
    ];
    
    $pagosSimulados[] = $pagoSimulado;
    $montoRestante -= $montoPagar;
    
    echo "   ✅ Pago simulado creado\n";
    echo "   Monto restante: $" . number_format($montoRestante, 2) . "\n\n";
    
    if ($montoRestante <= 0) {
        echo "💰 Monto completamente distribuido\n\n";
        break;
    }
}

echo "=== RESUMEN DE LA SIMULACIÓN ===\n";
echo "Total de pagos creados: " . count($pagosSimulados) . "\n";
echo "Monto total distribuido: $" . number_format($montoTotal - $montoRestante, 2) . "\n";
echo "Monto restante: $" . number_format($montoRestante, 2) . "\n\n";

if (count($pagosSimulados) > 0) {
    echo "🎯 PRIMER RECIBO QUE RECIBIÓ EL PAGO: " . $pagosSimulados[0]['numero_recibo'] . "\n";
    echo "   Monto asignado: $" . number_format($pagosSimulados[0]['monto_pagado'], 2) . "\n\n";
    
    echo "Detalle de todos los pagos simulados:\n";
    foreach ($pagosSimulados as $i => $pago) {
        echo ($i + 1) . ". {$pago['numero_recibo']}: $" . number_format($pago['monto_pagado'], 2) . "\n";
    }
}

echo "\n=== COMPARACIÓN CON PROBLEMA ORIGINAL ===\n";
echo "❌ ANTES: El pago se asignaba a REC-0922 (recibo muy antiguo no prioritario)\n";
if (count($pagosSimulados) > 0) {
    echo "✅ AHORA: El pago se asigna a " . $pagosSimulados[0]['numero_recibo'] . " (recibo correcto según nueva lógica)\n";
}

echo "\n=== SIMULACIÓN COMPLETADA ===\n";
?>