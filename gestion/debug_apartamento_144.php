<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== ANÁLISIS DETALLADO DEL APARTAMENTO 144 ===\n\n";

// Buscar el apartamento 144
$apartamento = Apartamento::where('numero', 144)->first();

if (!$apartamento) {
    echo "❌ No se encontró el apartamento 144\n";
    exit;
}

echo "📍 Apartamento encontrado: {$apartamento->numero}\n";
echo "👤 Propietario: {$apartamento->nombre_propietario}\n";
echo "💰 Estatus financiero actual: {$apartamento->estatus_financiero}\n\n";

// Obtener todos los recibos asociados
echo "=== RECIBOS ASOCIADOS ===\n";
$recibos = $apartamento->recibos()->get();
echo "📋 Total de recibos: " . $recibos->count() . "\n\n";

foreach ($recibos as $recibo) {
    echo "🧾 Recibo #{$recibo->numero_recibo}\n";
    echo "   📅 Fecha: {$recibo->fecha_emision}\n";
    echo "   💵 Monto total: $" . number_format($recibo->monto_total, 2) . "\n";
    
    // Calcular monto pagado para este recibo
    $montoPagado = $apartamento->pagos()
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->monto_total - $montoPagado;
    
    echo "   ✅ Monto pagado: $" . number_format($montoPagado, 2) . "\n";
    echo "   ⏳ Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    echo "   📊 Estado: " . ($saldoPendiente > 0 ? 'PENDIENTE' : 'PAGADO') . "\n\n";
}

// Obtener todos los pagos del apartamento
echo "=== PAGOS REALIZADOS ===\n";
$pagos = $apartamento->pagos()->where('estado', 'confirmado')->get();
echo "💳 Total de pagos confirmados: " . $pagos->count() . "\n\n";

foreach ($pagos as $pago) {
    echo "💰 Pago ID: {$pago->id}\n";
    echo "   📅 Fecha: {$pago->fecha_pago}\n";
    echo "   💵 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "   🧾 Recibo: #{$pago->reciboGastoComun->numero_recibo}\n";
    echo "   ✅ Estado: {$pago->estado}\n\n";
}

// Calcular manualmente el saldo pendiente total
echo "=== CÁLCULO MANUAL DEL SALDO PENDIENTE ===\n";
$totalRecibos = $recibos->sum('monto_total');
$totalPagado = $pagos->sum('monto_pagado');
$saldoPendienteTotal = $totalRecibos - $totalPagado;

echo "📊 Total de recibos: $" . number_format($totalRecibos, 2) . "\n";
echo "💰 Total pagado: $" . number_format($totalPagado, 2) . "\n";
echo "⏳ Saldo pendiente total: $" . number_format($saldoPendienteTotal, 2) . "\n\n";

// Contar recibos con saldo pendiente
$recibosPendientes = 0;
foreach ($recibos as $recibo) {
    $montoPagado = $apartamento->pagos()
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    if (($recibo->monto_total - $montoPagado) > 0) {
        $recibosPendientes++;
    }
}

echo "📋 Recibos con saldo pendiente: {$recibosPendientes}\n\n";

// Determinar el estatus correcto según la lógica
echo "=== DETERMINACIÓN DEL ESTATUS CORRECTO ===\n";

if ($saldoPendienteTotal <= 0) {
    $estatusEsperado = 'solvente';
    echo "✅ Estatus esperado: SOLVENTE (sin deudas)\n";
} elseif ($recibosPendientes == 1) {
    $estatusEsperado = 'deudor';
    echo "⚠️ Estatus esperado: DEUDOR (1 recibo pendiente)\n";
} else {
    $estatusEsperado = 'moroso';
    echo "❌ Estatus esperado: MOROSO (múltiples recibos pendientes)\n";
}

echo "\n=== COMPARACIÓN ===\n";
echo "🔍 Estatus actual en BD: {$apartamento->estatus_financiero}\n";
echo "🎯 Estatus esperado: {$estatusEsperado}\n";

if ($apartamento->estatus_financiero !== $estatusEsperado) {
    echo "❌ ¡INCONSISTENCIA DETECTADA!\n";
    echo "\n=== ACTUALIZANDO ESTATUS ===\n";
    
    // Llamar al método actualizarEstatusFinanciero
    $apartamento->actualizarEstatusFinanciero();
    $apartamento->refresh();
    
    echo "🔄 Estatus después de actualizar: {$apartamento->estatus_financiero}\n";
    
    if ($apartamento->estatus_financiero === $estatusEsperado) {
        echo "✅ ¡Estatus corregido exitosamente!\n";
    } else {
        echo "❌ El estatus sigue siendo incorrecto después de la actualización\n";
    }
} else {
    echo "✅ El estatus es correcto\n";
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";