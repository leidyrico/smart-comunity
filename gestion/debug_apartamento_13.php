<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG APARTAMENTO 13 ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', 13)->first();

if (!$apartamento) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "✅ Apartamento encontrado:\n";
echo "   ID: {$apartamento->id}\n";
echo "   Número: {$apartamento->numero}\n";
echo "   Propietario: {$apartamento->propietario}\n";
echo "   Estatus financiero: {$apartamento->estatus_financiero}\n";
echo "   Saldo pendiente: $" . number_format($apartamento->saldo_pendiente, 2) . "\n\n";

// Verificar todos los recibos del sistema
echo "=== TODOS LOS RECIBOS DEL SISTEMA ===\n";
$todosRecibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

echo "Total de recibos activos/vencidos: {$todosRecibos->count()}\n\n";

if ($todosRecibos->count() > 0) {
    foreach ($todosRecibos as $recibo) {
        echo "Recibo ID: {$recibo->id} | Número: {$recibo->numero_recibo} | Estado: {$recibo->estado} | Total: $" . number_format($recibo->total_recibo, 2) . "\n";
    }
    echo "\n";
}

// Verificar recibos relacionados con el apartamento 13 a través de pagos
echo "=== RECIBOS RELACIONADOS CON APARTAMENTO 13 (VÍA PAGOS) ===\n";
$recibosRelacionados = DB::table('recibo_gasto_comuns')
    ->join('pagos', 'recibo_gasto_comuns.id', '=', 'pagos.recibo_gasto_comun_id')
    ->where('pagos.apartamento_id', $apartamento->id)
    ->whereIn('recibo_gasto_comuns.estado', ['activo', 'vencido'])
    ->select('recibo_gasto_comuns.*')
    ->distinct()
    ->orderBy('recibo_gasto_comuns.fecha_vencimiento', 'asc')
    ->get();

echo "Recibos relacionados con apartamento 13: {$recibosRelacionados->count()}\n\n";

if ($recibosRelacionados->count() > 0) {
    foreach ($recibosRelacionados as $recibo) {
        // Calcular pagos confirmados para este recibo y apartamento
        $totalPagado = Pago::where('apartamento_id', $apartamento->id)
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoPendiente = $recibo->total_recibo - $totalPagado;
        
        echo "Recibo ID: {$recibo->id} | Número: {$recibo->numero_recibo}\n";
        echo "   Estado: {$recibo->estado}\n";
        echo "   Total: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   Pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "   Pendiente: $" . number_format($saldoPendiente, 2) . "\n\n";
    }
} else {
    echo "❌ No hay recibos relacionados con el apartamento 13\n\n";
}

// Verificar pagos del apartamento 13
echo "=== PAGOS DEL APARTAMENTO 13 ===\n";
$pagos = Pago::where('apartamento_id', $apartamento->id)
    ->with('reciboGastoComun')
    ->orderBy('fecha_pago', 'desc')
    ->get();

echo "Total de pagos: {$pagos->count()}\n\n";

if ($pagos->count() > 0) {
    foreach ($pagos as $pago) {
        echo "Pago ID: {$pago->id}\n";
        $numeroRecibo = $pago->reciboGastoComun ? $pago->reciboGastoComun->numero_recibo : 'N/A';
        echo "   Recibo: {$numeroRecibo}\n";
        echo "   Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   Fecha: {$pago->fecha_pago}\n";
        echo "   Estado: {$pago->estado}\n";
        echo "   Método: {$pago->metodo_pago}\n";
        echo "   Comprobante: {$pago->numero_comprobante}\n\n";
    }
} else {
    echo "❌ No hay pagos registrados para el apartamento 13\n\n";
}

// Simular la lógica del createGlobal
echo "=== SIMULACIÓN LÓGICA createGlobal ===\n";
$recibos_pendientes = collect();
$total_pendiente = 0;

foreach ($todosRecibos as $recibo) {
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
        
        echo "✅ Recibo pendiente encontrado:\n";
        echo "   ID: {$recibo->id} | Número: {$recibo->numero_recibo}\n";
        echo "   Total recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   Pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "   Pendiente: $" . number_format($saldoPendiente, 2) . "\n\n";
    }
}

echo "Total de recibos pendientes para apartamento 13: {$recibos_pendientes->count()}\n";
echo "Total pendiente: $" . number_format($total_pendiente, 2) . "\n\n";

echo "=== FIN DEBUG ===\n";