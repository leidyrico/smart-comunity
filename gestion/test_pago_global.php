<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== TEST PAGO GLOBAL APARTAMENTO 13 ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', 13)->first();

if (!$apartamento) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento encontrado: {$apartamento->numero} - {$apartamento->propietario}\n\n";

// Simular exactamente la lógica del método createGlobal
echo "=== SIMULANDO MÉTODO createGlobal ===\n";

// Obtener TODOS los recibos activos y vencidos del sistema
$todosLosRecibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

echo "Total de recibos activos/vencidos en el sistema: {$todosLosRecibos->count()}\n\n";

$recibos_pendientes = collect();
$total_pendiente = 0;

foreach ($todosLosRecibos as $recibo) {
    // Calcular total pagado para este recibo y apartamento específico
    $totalPagado = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    // Solo incluir recibos con saldo pendiente > 0
    if ($saldoPendiente > 0) {
        // Agregar el saldo pendiente calculado al recibo
        $recibo->saldo_pendiente_apartamento = $saldoPendiente;
        $recibos_pendientes->push($recibo);
        $total_pendiente += $saldoPendiente;
    }
}

echo "Recibos pendientes encontrados para apartamento 13: {$recibos_pendientes->count()}\n";
echo "Total pendiente: $" . number_format($total_pendiente, 2) . "\n\n";

if ($recibos_pendientes->count() > 0) {
    echo "PRIMEROS 10 RECIBOS PENDIENTES:\n";
    foreach ($recibos_pendientes->take(10) as $recibo) {
        echo "- Recibo {$recibo->numero_recibo}: $" . number_format($recibo->saldo_pendiente_apartamento, 2) . " pendiente\n";
    }
    echo "\n";
}

// Simular un pago global de $100
echo "=== SIMULANDO PAGO GLOBAL DE $100 ===\n";

$montoTotal = 100.00;
$montoRestante = $montoTotal;
$pagosSimulados = [];

// Obtener TODOS los recibos activos y vencidos del sistema (como en storeGlobal)
$todosLosRecibosStore = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->with(['pagos' => function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', 'confirmado');
    }])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();
    
// Filtrar solo los recibos con saldo pendiente para este apartamento
$recibosConSaldo = collect();

foreach ($todosLosRecibosStore as $recibo) {
    $totalPagado = $recibo->pagos->sum('monto_pagado');
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    if ($saldoPendiente > 0) {
        $recibo->saldo_pendiente_calculado = $saldoPendiente;
        $recibosConSaldo->push($recibo);
    }
}

echo "Recibos con saldo para procesar: {$recibosConSaldo->count()}\n\n";

// Procesar los recibos con saldo pendiente
foreach ($recibosConSaldo as $recibo) {
    if ($montoRestante <= 0) break;
    
    // Usar el saldo pendiente ya calculado
    $saldoPendiente = $recibo->saldo_pendiente_calculado;
    
    // Determinar cuánto pagar de este recibo
    $montoPagar = min($montoRestante, $saldoPendiente);
    
    $pagosSimulados[] = [
        'recibo' => $recibo->numero_recibo,
        'saldo_pendiente' => $saldoPendiente,
        'monto_a_pagar' => $montoPagar
    ];
    
    $montoRestante -= $montoPagar;
    
    echo "Pago simulado: Recibo {$recibo->numero_recibo} - Pagar $" . number_format($montoPagar, 2) . " de $" . number_format($saldoPendiente, 2) . " pendiente\n";
}

echo "\nMonto restante después de distribución: $" . number_format($montoRestante, 2) . "\n";
echo "Total de pagos que se crearían: " . count($pagosSimulados) . "\n\n";

echo "=== FIN TEST ===\n";