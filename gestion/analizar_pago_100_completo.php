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

echo "=== ANÁLISIS COMPLETO DEL PAGO DE $100 ===\n\n";

// Buscar TODOS los pagos con el comprobante 672832207870
echo "=== TODOS LOS PAGOS CON COMPROBANTE 672832207870 ===\n";
$todosPagosComprobante = Pago::where('numero_comprobante', '672832207870')
    ->orderBy('created_at', 'asc')
    ->get();

if ($todosPagosComprobante->isEmpty()) {
    echo "❌ No se encontraron pagos con este comprobante\n";
    exit;
}

echo "✅ Total de pagos encontrados: " . $todosPagosComprobante->count() . "\n";
$montoTotal = $todosPagosComprobante->sum('monto_pagado');
echo "💰 Monto total: $" . number_format($montoTotal, 2) . "\n\n";

foreach ($todosPagosComprobante as $index => $pago) {
    echo "📋 PAGO #" . ($index + 1) . " - ID: {$pago->id}\n";
    echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "   📅 Fecha: {$pago->fecha_pago}\n";
    echo "   📅 Creado: {$pago->created_at}\n";
    echo "   🔄 Estado: {$pago->estado}\n";
    echo "   📄 Comprobante: {$pago->numero_comprobante}\n";
    echo "   📝 Método: {$pago->metodo_pago}\n";
    echo "   📝 Observaciones: {$pago->observaciones}\n";
    
    // Información del apartamento
    $apartamento = Apartamento::find($pago->apartamento_id);
    if ($apartamento) {
        echo "   🏠 Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n";
    }
    
    // Información del recibo
    if ($pago->recibo_gasto_comun_id) {
        $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
        if ($recibo) {
            echo "   📋 Recibo: {$recibo->numero_recibo} ($" . number_format($recibo->monto, 2) . ")\n";
        }
    } else {
        echo "   📋 PAGO GLOBAL (sin recibo específico)\n";
    }
    
    echo "   " . str_repeat("=", 70) . "\n";
}

// Analizar el estado del pago
echo "\n=== ANÁLISIS DEL ESTADO DEL PAGO ===\n";
$pagosConfirmados = $todosPagosComprobante->filter(function($pago) { return $pago->estado === 'confirmado'; });
$pagosRechazados = $todosPagosComprobante->filter(function($pago) { return $pago->estado === 'rechazado'; });
$pagosPendientes = $todosPagosComprobante->filter(function($pago) { return $pago->estado === 'pendiente_confirmacion'; });

echo "✅ Pagos confirmados: " . $pagosConfirmados->count() . " (Total: $" . number_format($pagosConfirmados->sum('monto_pagado'), 2) . ")\n";
echo "❌ Pagos rechazados: " . $pagosRechazados->count() . " (Total: $" . number_format($pagosRechazados->sum('monto_pagado'), 2) . ")\n";
echo "⏳ Pagos pendientes: " . $pagosPendientes->count() . " (Total: $" . number_format($pagosPendientes->sum('monto_pagado'), 2) . ")\n";

if ($pagosConfirmados->count() > 0) {
    echo "\n🎯 PAGOS ACTIVOS ENCONTRADOS:\n";
    foreach ($pagosConfirmados as $pago) {
        $apartamento = Apartamento::find($pago->apartamento_id);
        echo "   📋 Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " - Apartamento {$apartamento->numero} ({$apartamento->propietario})\n";
        
        if ($pago->recibo_gasto_comun_id) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            if ($recibo) {
                echo "      📋 Aplicado a: {$recibo->numero_recibo}\n";
            }
        }
    }
}

if ($pagosRechazados->count() > 0) {
    echo "\n❌ PAGOS DESASIGNADOS/RECHAZADOS:\n";
    foreach ($pagosRechazados as $pago) {
        $apartamento = Apartamento::find($pago->apartamento_id);
        echo "   📋 Pago ID {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " - Apartamento {$apartamento->numero} ({$apartamento->propietario})\n";
        echo "      ❌ Motivo: {$pago->observaciones}\n";
    }
}

// Buscar si hay pagos con el mismo comprobante en otros apartamentos
echo "\n=== DISTRIBUCIÓN POR APARTAMENTOS ===\n";
$apartamentosAfectados = $todosPagosComprobante->groupBy('apartamento_id');

foreach ($apartamentosAfectados as $apartamentoId => $pagos) {
    $apartamento = Apartamento::find($apartamentoId);
    $totalApartamento = $pagos->sum('monto_pagado');
    $pagosActivos = $pagos->filter(function($pago) { return $pago->estado === 'confirmado'; })->count();
    $pagosRechazados = $pagos->filter(function($pago) { return $pago->estado === 'rechazado'; })->count();
    
    echo "🏠 Apartamento {$apartamento->numero} ({$apartamento->propietario}):\n";
    echo "   💰 Total pagado: $" . number_format($totalApartamento, 2) . "\n";
    echo "   ✅ Pagos activos: {$pagosActivos}\n";
    echo "   ❌ Pagos rechazados: {$pagosRechazados}\n";
    echo "   📊 Total pagos: " . $pagos->count() . "\n";
    echo "   " . str_repeat("-", 50) . "\n";
}

echo "\n=== RESUMEN FINAL ===\n";
echo "💰 Monto total del pago original: $" . number_format($montoTotal, 2) . "\n";
echo "📊 Total de transacciones: " . $todosPagosComprobante->count() . "\n";
echo "✅ Pagos activos: " . $pagosConfirmados->count() . " ($" . number_format($pagosConfirmados->sum('monto_pagado'), 2) . ")\n";
echo "❌ Pagos rechazados: " . $pagosRechazados->count() . " ($" . number_format($pagosRechazados->sum('monto_pagado'), 2) . ")\n";

if ($pagosConfirmados->sum('monto_pagado') > 0) {
    echo "\n🎯 ESTADO: El pago de $100 está PARCIALMENTE ACTIVO\n";
    echo "💡 Hay $" . number_format($pagosConfirmados->sum('monto_pagado'), 2) . " aplicados correctamente\n";
} else {
    echo "\n⚠️  ESTADO: El pago de $100 está COMPLETAMENTE DESASIGNADO\n";
    echo "💡 Todos los pagos con este comprobante fueron rechazados\n";
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";