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

echo "=== ESTADO DEL PAGO DE $100 - COMPROBANTE 672832207870 ===\n\n";

// Buscar todos los pagos con el comprobante
$pagos = Pago::where('numero_comprobante', '672832207870')
    ->orderBy('created_at', 'asc')
    ->get();

if ($pagos->isEmpty()) {
    echo "❌ No se encontraron pagos con este comprobante\n";
    exit;
}

echo "📊 Total de pagos encontrados: " . $pagos->count() . "\n";
echo "💰 Monto total: $" . number_format($pagos->sum('monto_pagado'), 2) . "\n\n";

$confirmados = 0;
$rechazados = 0;
$pendientes = 0;
$montoConfirmado = 0;
$montoRechazado = 0;
$montoPendiente = 0;

echo "=== DETALLE DE CADA PAGO ===\n";
foreach ($pagos as $index => $pago) {
    echo "📋 PAGO #" . ($index + 1) . " - ID: {$pago->id}\n";
    echo "   💰 Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "   📅 Fecha: {$pago->fecha_pago}\n";
    echo "   🔄 Estado: {$pago->estado}\n";
    
    // Contar por estado
    if ($pago->estado === 'confirmado') {
        $confirmados++;
        $montoConfirmado += $pago->monto_pagado;
        echo "   ✅ ACTIVO\n";
    } elseif ($pago->estado === 'rechazado') {
        $rechazados++;
        $montoRechazado += $pago->monto_pagado;
        echo "   ❌ RECHAZADO\n";
    } else {
        $pendientes++;
        $montoPendiente += $pago->monto_pagado;
        echo "   ⏳ PENDIENTE\n";
    }
    
    // Información del apartamento
    $apartamento = Apartamento::find($pago->apartamento_id);
    if ($apartamento) {
        echo "   🏠 Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n";
    }
    
    // Información del recibo
    if ($pago->recibo_gasto_comun_id) {
        $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
        if ($recibo) {
            echo "   📋 Recibo: {$recibo->numero_recibo}\n";
        }
    } else {
        echo "   📋 PAGO GLOBAL\n";
    }
    
    if (!empty($pago->observaciones)) {
        echo "   📝 Observaciones: {$pago->observaciones}\n";
    }
    
    echo "   " . str_repeat("-", 60) . "\n";
}

echo "\n=== RESUMEN POR ESTADO ===\n";
echo "✅ Pagos confirmados: {$confirmados} (Total: $" . number_format($montoConfirmado, 2) . ")\n";
echo "❌ Pagos rechazados: {$rechazados} (Total: $" . number_format($montoRechazado, 2) . ")\n";
echo "⏳ Pagos pendientes: {$pendientes} (Total: $" . number_format($montoPendiente, 2) . ")\n";

echo "\n=== ESTADO ACTUAL ===\n";
if ($confirmados > 0) {
    echo "🎯 El pago de $100 está PARCIALMENTE ACTIVO\n";
    echo "💡 Hay $" . number_format($montoConfirmado, 2) . " aplicados correctamente\n";
    echo "💡 Hay $" . number_format($montoRechazado, 2) . " desasignados/rechazados\n";
} else {
    echo "⚠️  El pago de $100 está COMPLETAMENTE DESASIGNADO\n";
    echo "💡 Todos los pagos con este comprobante fueron rechazados\n";
    echo "💡 Total rechazado: $" . number_format($montoRechazado, 2) . "\n";
}

if ($rechazados > 0) {
    echo "\n❓ POSIBLE SOLUCIÓN:\n";
    echo "💡 Los pagos fueron desasignados porque se aplicaron a recibos incorrectos\n";
    echo "💡 Para reactivar el pago, necesitas reasignarlo a los recibos correctos\n";
    echo "💡 O crear un nuevo pago global si era un abono general\n";
}

echo "\n=== ANÁLISIS COMPLETADO ===\n";