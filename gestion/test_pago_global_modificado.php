<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== PRUEBA DEL MÉTODO createGlobal MODIFICADO ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// Simular la lógica del método createGlobal modificado
echo "=== LÓGICA MODIFICADA ===\n";

// Obtener solo los recibos asignados al apartamento a través de la tabla pagos
$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id);
    })
    ->whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

echo "Recibos asignados encontrados: " . $recibosAsignados->count() . "\n\n";

$recibos_pendientes = collect();
$total_pendiente = 0;

foreach ($recibosAsignados as $recibo) {
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
        
        echo "Recibo ID: {$recibo->id} - Fecha: {$recibo->fecha_vencimiento} - Total: $" . number_format($recibo->total_recibo, 2) . " - Pagado: $" . number_format($totalPagado, 2) . " - Pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Total de recibos asignados: " . $recibosAsignados->count() . "\n";
echo "Recibos con saldo pendiente: " . $recibos_pendientes->count() . "\n";
echo "Total pendiente: $" . number_format($total_pendiente, 2) . "\n";

// Comparar con la lógica anterior
echo "\n=== COMPARACIÓN CON LÓGICA ANTERIOR ===\n";

$todosLosRecibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

$recibos_pendientes_anterior = collect();
$total_pendiente_anterior = 0;

foreach ($todosLosRecibos as $recibo) {
    $totalPagado = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    if ($saldoPendiente > 0) {
        $recibos_pendientes_anterior->push($recibo);
        $total_pendiente_anterior += $saldoPendiente;
    }
}

echo "Lógica anterior - Recibos pendientes: " . $recibos_pendientes_anterior->count() . "\n";
echo "Lógica anterior - Total pendiente: $" . number_format($total_pendiente_anterior, 2) . "\n";
echo "Lógica modificada - Recibos pendientes: " . $recibos_pendientes->count() . "\n";
echo "Lógica modificada - Total pendiente: $" . number_format($total_pendiente, 2) . "\n";

echo "\n=== PRUEBA COMPLETADA ===\n";
?>