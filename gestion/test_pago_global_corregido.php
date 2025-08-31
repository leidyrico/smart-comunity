<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== PRUEBA DEL MÉTODO storeGlobal CORREGIDO ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// Simular la lógica del método storeGlobal corregido
echo "=== LÓGICA CORREGIDA DEL storeGlobal ===\n";

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

echo "Recibos con saldo pendiente (antes de lógica especial): " . $recibosConSaldo->count() . "\n";

// Si no hay recibos activos asignados, buscar el recibo vencido más reciente asignado
if ($recibosConSaldo->where('estado', 'activo')->isEmpty()) {
    echo "\n⚠️ No hay recibos activos con saldo pendiente\n";
    echo "Buscando recibo vencido más reciente asignado...\n";
    
    $reciboVencidoReciente = $recibosAsignados->where('estado', 'vencido')->sortByDesc('fecha_vencimiento')->first();
        
    if ($reciboVencidoReciente) {
        $totalPagado = $reciboVencidoReciente->pagos->sum('monto_pagado');
        $saldoPendiente = $reciboVencidoReciente->total_recibo - $totalPagado;
        
        echo "Recibo vencido más reciente: {$reciboVencidoReciente->numero_recibo} (Fecha: {$reciboVencidoReciente->fecha_vencimiento})\n";
        echo "Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
        
        if ($saldoPendiente > 0) {
            $reciboVencidoReciente->saldo_pendiente_calculado = $saldoPendiente;
            // Agregar al inicio de la colección para que tenga prioridad
            $recibosConSaldo->prepend($reciboVencidoReciente);
            echo "✅ Recibo vencido agregado con prioridad\n";
        } else {
            echo "❌ El recibo vencido más reciente no tiene saldo pendiente\n";
        }
    } else {
        echo "❌ No se encontró ningún recibo vencido asignado\n";
    }
} else {
    echo "✅ Hay recibos activos con saldo pendiente\n";
}

echo "\nRecibos con saldo pendiente (después de lógica especial): " . $recibosConSaldo->count() . "\n\n";

// Mostrar el orden de los recibos que se procesarían
echo "=== ORDEN DE PROCESAMIENTO DE RECIBOS ===\n";
foreach ($recibosConSaldo->take(5) as $index => $recibo) {
    echo ($index + 1) . ". {$recibo->numero_recibo} - Fecha: {$recibo->fecha_vencimiento} - Estado: {$recibo->estado} - Pendiente: $" . number_format($recibo->saldo_pendiente_calculado, 2) . "\n";
}

if ($recibosConSaldo->count() > 5) {
    echo "... y " . ($recibosConSaldo->count() - 5) . " recibos más\n";
}

// Verificar cuál sería el primer recibo asignado
if ($recibosConSaldo->count() > 0) {
    $primerRecibo = $recibosConSaldo->first();
    echo "\n🎯 PRIMER RECIBO QUE RECIBIRÍA EL PAGO: {$primerRecibo->numero_recibo}\n";
    echo "   Fecha vencimiento: {$primerRecibo->fecha_vencimiento}\n";
    echo "   Estado: {$primerRecibo->estado}\n";
    echo "   Saldo pendiente: $" . number_format($primerRecibo->saldo_pendiente_calculado, 2) . "\n";
} else {
    echo "\n❌ No hay recibos disponibles para recibir pagos\n";
}

// Buscar específicamente REC-0124 y REC-0922
echo "\n=== VERIFICACIÓN DE RECIBOS ESPECÍFICOS ===\n";

$rec0124 = ReciboGastoComun::where('numero_recibo', 'REC-0124')->first();
if ($rec0124) {
    $tieneAsignacion = Pago::where('apartamento_id', $apartamento->id)->where('recibo_gasto_comun_id', $rec0124->id)->exists();
    echo "REC-0124: " . ($tieneAsignacion ? '✅ ASIGNADO' : '❌ NO ASIGNADO') . " al apartamento 13\n";
    echo "   Fecha: {$rec0124->fecha_vencimiento}, Estado: {$rec0124->estado}\n";
} else {
    echo "REC-0124: ❌ NO ENCONTRADO\n";
}

$rec0922 = ReciboGastoComun::where('numero_recibo', 'REC-0922')->first();
if ($rec0922) {
    $tieneAsignacion = Pago::where('apartamento_id', $apartamento->id)->where('recibo_gasto_comun_id', $rec0922->id)->exists();
    echo "REC-0922: " . ($tieneAsignacion ? '✅ ASIGNADO' : '❌ NO ASIGNADO') . " al apartamento 13\n";
    echo "   Fecha: {$rec0922->fecha_vencimiento}, Estado: {$rec0922->estado}\n";
} else {
    echo "REC-0922: ❌ NO ENCONTRADO\n";
}

echo "\n=== PRUEBA COMPLETADA ===\n";
?>