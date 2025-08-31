<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== TODOS LOS RECIBOS APARTAMENTO 13 (ID 91) ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::find(91);
if (!$apartamento) {
    echo "❌ Apartamento 91 no encontrado\n";
    exit;
}

echo "✅ Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n\n";

// Obtener TODOS los recibos asignados al apartamento a través de la tabla pagos
$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) {
        $query->where('apartamento_id', 91);
    })
    ->whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

echo "=== RECIBOS ASIGNADOS AL APARTAMENTO ===\n";
echo "Total de recibos asignados: " . $recibosAsignados->count() . "\n\n";

foreach ($recibosAsignados as $recibo) {
    echo "📋 Recibo {$recibo->numero_recibo} (ID: {$recibo->id})\n";
    echo "   Estado: {$recibo->estado}\n";
    echo "   Total: $" . number_format($recibo->total, 2) . "\n";
    echo "   Fecha vencimiento: {$recibo->fecha_vencimiento}\n";
    
    // Verificar si tiene pagos
    $pagos = Pago::where('recibo_gasto_comun_id', $recibo->id)->get();
    
    if ($pagos->count() > 0) {
        echo "   Pagos registrados: {$pagos->count()}\n";
        $totalPagado = $pagos->sum('monto_pagado');
        echo "   Total pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "   Saldo pendiente: $" . number_format($recibo->total - $totalPagado, 2) . "\n";
        
        foreach ($pagos as $pago) {
            echo "     - Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " ({$pago->estado})\n";
        }
    } else {
        echo "   ❌ SIN PAGOS REGISTRADOS\n";
        echo "   Saldo pendiente: $" . number_format($recibo->total, 2) . "\n";
    }
    
    echo "\n";
}

// Resumen
echo "=== RESUMEN ===\n";
echo "Total recibos asignados: " . $recibosAsignados->count() . "\n";

$recibosConPagos = 0;
$recibosSinPagos = 0;
$totalPendiente = 0;

foreach ($recibosAsignados as $recibo) {
    $pagos = Pago::where('recibo_gasto_comun_id', $recibo->id)->get();
    $totalPagado = $pagos->sum('monto_pagado');
    $saldoPendiente = $recibo->total - $totalPagado;
    
    if ($pagos->count() > 0) {
        $recibosConPagos++;
    } else {
        $recibosSinPagos++;
    }
    
    if ($saldoPendiente > 0) {
        $totalPendiente += $saldoPendiente;
    }
}

echo "Recibos con pagos registrados: {$recibosConPagos}\n";
echo "Recibos sin pagos registrados: {$recibosSinPagos}\n";
echo "Total pendiente: $" . number_format($totalPendiente, 2) . "\n";

if ($recibosAsignados->count() == 20) {
    echo "✅ El apartamento tiene exactamente 20 recibos como esperado\n";
} else {
    echo "⚠️ El apartamento tiene " . $recibosAsignados->count() . " recibos, no 20 como esperado\n";
}