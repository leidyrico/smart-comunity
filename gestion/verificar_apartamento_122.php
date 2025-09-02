<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DEL APARTAMENTO 122 ===\n\n";

// Buscar el apartamento 122
$apartamento122 = Apartamento::where('numero', 122)->first();

if (!$apartamento122) {
    echo "❌ No se encontró el apartamento 122\n";
    exit;
}

echo "✅ Apartamento encontrado: {$apartamento122->numero} - {$apartamento122->propietario}\n";
echo "📊 Estatus financiero actual: {$apartamento122->estatus_financiero}\n";
echo "💰 Saldo pendiente actual: $" . number_format($apartamento122->saldo_pendiente, 2) . "\n\n";

// Obtener todos los pagos del apartamento 122
$pagos = Pago::where('apartamento_id', $apartamento122->id)
    ->with('reciboGastoComun')
    ->orderBy('created_at', 'desc')
    ->get();

echo "📋 Total de pagos registrados: {$pagos->count()}\n\n";

if ($pagos->count() > 0) {
    echo "=== DETALLE DE PAGOS ===\n";
    foreach ($pagos as $pago) {
        $recibo = $pago->reciboGastoComun;
        echo "💳 Pago ID: {$pago->id}\n";
        echo "   📋 Recibo: " . ($recibo ? $recibo->numero_recibo : 'N/A') . "\n";
        echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "   📅 Fecha: {$pago->fecha_pago}\n";
        echo "   🏷️  Estado: {$pago->estado}\n";
        echo "   📝 Observaciones: {$pago->observaciones}\n";
        echo "   🕒 Creado: {$pago->created_at}\n\n";
    }
}

// Obtener todos los recibos asignados al apartamento
$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento122) {
    $query->where('apartamento_id', $apartamento122->id)
          ->where('estado', '!=', 'rechazado');
})->with(['pagos' => function($query) use ($apartamento122) {
    $query->where('apartamento_id', $apartamento122->id);
}])->get();

echo "=== RECIBOS ASIGNADOS Y SALDOS ===\n";
echo "📋 Total de recibos asignados: {$recibosAsignados->count()}\n\n";

$saldoTotalCalculado = 0;

foreach ($recibosAsignados as $recibo) {
    $totalPagado = $recibo->pagos->where('estado', 'confirmado')->sum('monto_pagado');
    $saldoPendiente = max(0, $recibo->total_recibo - $totalPagado);
    $saldoTotalCalculado += $saldoPendiente;
    
    echo "📋 Recibo: {$recibo->numero_recibo}\n";
    echo "   💰 Total recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "   ✅ Total pagado: $" . number_format($totalPagado, 2) . "\n";
    echo "   ⚠️  Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    echo "   📅 Estado: {$recibo->estado}\n\n";
}

echo "=== RESUMEN ===\n";
echo "💰 Saldo total calculado manualmente: $" . number_format($saldoTotalCalculado, 2) . "\n";
echo "💰 Saldo pendiente del modelo: $" . number_format($apartamento122->saldo_pendiente, 2) . "\n";

if ($saldoTotalCalculado != $apartamento122->saldo_pendiente) {
    echo "⚠️  DISCREPANCIA DETECTADA - Actualizando estatus financiero...\n";
    
    // Forzar actualización del estatus financiero
    $apartamento122->actualizarEstatusFinanciero();
    $apartamento122->refresh();
    
    echo "✅ Estatus actualizado: {$apartamento122->estatus_financiero}\n";
    echo "💰 Nuevo saldo pendiente: $" . number_format($apartamento122->saldo_pendiente, 2) . "\n";
} else {
    echo "✅ Los saldos coinciden correctamente\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";

?>