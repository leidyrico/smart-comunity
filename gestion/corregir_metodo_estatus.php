<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== CORRECCIÓN DEL MÉTODO ACTUALIZAR ESTATUS FINANCIERO ===\n\n";

// Función para calcular el estatus correcto
function calcularEstatusCorrect($apartamento) {
    $recibosConDeuda = 0;
    $saldoPendienteTotal = 0;
    
    // Obtener todos los recibos asignados (excluyendo rechazados)
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    echo "🔍 Analizando recibos del apartamento {$apartamento->numero}:\n";
    
    foreach ($recibosAsignados as $recibo) {
        // Solo considerar recibos con monto > 0
        if ($recibo->monto_total <= 0) {
            echo "   ⚠️ Recibo #{$recibo->numero_recibo} - Monto $0.00 (ignorado)\n";
            continue;
        }
        
        $totalPagado = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoRecibo = max(0, $recibo->monto_total - $totalPagado);
        
        if ($saldoRecibo > 0) {
            $recibosConDeuda++;
            $saldoPendienteTotal += $saldoRecibo;
            echo "   🧾 Recibo #{$recibo->numero_recibo} - Saldo: $" . number_format($saldoRecibo, 2) . "\n";
        } else {
            echo "   ✅ Recibo #{$recibo->numero_recibo} - Pagado completamente\n";
        }
    }
    
    echo "\n📊 Resumen:\n";
    echo "   💰 Saldo pendiente total: $" . number_format($saldoPendienteTotal, 2) . "\n";
    echo "   📋 Recibos con deuda: {$recibosConDeuda}\n";
    
    // Determinar estatus
    if ($saldoPendienteTotal <= 0) {
        return 'solvente';
    } elseif ($recibosConDeuda == 1) {
        return 'deudor';
    } else {
        return 'moroso';
    }
}

// Probar con el apartamento 144
$apartamento = Apartamento::where('numero', 144)->first();

if (!$apartamento) {
    echo "❌ No se encontró el apartamento 144\n";
    exit;
}

echo "📍 Apartamento: {$apartamento->numero}\n";
echo "💰 Estatus actual: {$apartamento->estatus_financiero}\n";
echo "⏳ Saldo pendiente (método actual): $" . number_format($apartamento->saldo_pendiente, 2) . "\n\n";

$estatusCorrect = calcularEstatusCorrect($apartamento);

echo "\n🎯 Estatus correcto calculado: {$estatusCorrect}\n";

if ($apartamento->estatus_financiero !== $estatusCorrect) {
    echo "\n🔧 APLICANDO CORRECCIÓN...\n";
    
    // Actualizar directamente en la base de datos
    $apartamento->update([
        'estatus_financiero' => $estatusCorrect,
        'fecha_cambio_estatus' => now()->toDateString()
    ]);
    
    echo "✅ Estatus actualizado de '{$apartamento->estatus_financiero}' a '{$estatusCorrect}'\n";
} else {
    echo "✅ El estatus ya es correcto\n";
}

// Verificar el resultado
$apartamento->refresh();
echo "\n📊 Estado final:\n";
echo "   🏠 Apartamento: {$apartamento->numero}\n";
echo "   💰 Estatus: {$apartamento->estatus_financiero}\n";
echo "   📅 Fecha cambio: {$apartamento->fecha_cambio_estatus}\n";

echo "\n=== CORRECCIÓN COMPLETADA ===\n";