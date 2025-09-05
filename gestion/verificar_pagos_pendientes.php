<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== VERIFICACIÓN DE PAGOS PENDIENTES ===\n\n";

// Buscar pagos pendientes de confirmación
$pagosPendientes = Pago::where('estado', 'pendiente_confirmacion')
    ->with(['apartamento', 'reciboGastoComun'])
    ->get();

echo "Total de pagos pendientes de confirmación: {$pagosPendientes->count()}\n\n";

if ($pagosPendientes->count() > 0) {
    echo "=== DETALLES DE PAGOS PENDIENTES ===\n";
    foreach ($pagosPendientes as $pago) {
        echo "- Pago ID: {$pago->id}\n";
        echo "  Apartamento: {$pago->apartamento->numero} ({$pago->apartamento->propietario})\n";
        echo "  Recibo ID: {$pago->recibo_gasto_comun_id}\n";
        echo "  Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "  Fecha: {$pago->fecha_pago}\n";
        echo "  Método: {$pago->metodo_pago}\n";
        echo "  Comprobante: {$pago->numero_comprobante}\n";
        echo "\n";
    }
}

// Verificar apartamentos que podrían cambiar de estatus si se confirman los pagos
echo "=== ANÁLISIS DE IMPACTO EN ESTATUS FINANCIERO ===\n";

$apartamentosAfectados = [];

foreach ($pagosPendientes as $pago) {
    $apartamento = $pago->apartamento;
    
    if (!isset($apartamentosAfectados[$apartamento->id])) {
        $apartamentosAfectados[$apartamento->id] = [
            'apartamento' => $apartamento,
            'estatus_actual' => $apartamento->estatus_financiero,
            'saldo_actual' => $apartamento->saldo_pendiente,
            'pagos_pendientes' => [],
            'total_pagos_pendientes' => 0
        ];
    }
    
    $apartamentosAfectados[$apartamento->id]['pagos_pendientes'][] = $pago;
    $apartamentosAfectados[$apartamento->id]['total_pagos_pendientes'] += $pago->monto_pagado;
}

foreach ($apartamentosAfectados as $data) {
    $apartamento = $data['apartamento'];
    $totalPagosPendientes = $data['total_pagos_pendientes'];
    
    echo "\nApartamento {$apartamento->numero}:\n";
    echo "  Estatus actual: {$data['estatus_actual']}\n";
    echo "  Saldo actual: $" . number_format($data['saldo_actual'], 2) . "\n";
    echo "  Total pagos pendientes: $" . number_format($totalPagosPendientes, 2) . "\n";
    
    // Simular qué pasaría si se confirman los pagos
    $saldoSimulado = max(0, $data['saldo_actual'] - $totalPagosPendientes);
    
    // Contar recibos activos/vencidos que tendrían saldo después de confirmar pagos
    $recibosConSaldo = 0;
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })
    ->whereIn('estado', ['activo', 'vencido'])
    ->get();
    
    foreach ($recibosAsignados as $recibo) {
        $totalPagadoConfirmado = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
            
        $totalPagadoPendiente = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'pendiente_confirmacion')
            ->sum('monto_pagado');
            
        $saldoReciboSimulado = max(0, $recibo->total_recibo - $totalPagadoConfirmado - $totalPagadoPendiente);
        
        if ($saldoReciboSimulado > 0) {
            $recibosConSaldo++;
        }
    }
    
    // Determinar estatus simulado
    $estatusSimulado = 'solvente';
    if ($saldoSimulado > 0) {
        if ($recibosConSaldo > 3) {
            $estatusSimulado = 'moroso';
        } else {
            $estatusSimulado = 'deudor';
        }
    }
    
    echo "  Saldo después de confirmar: $" . number_format($saldoSimulado, 2) . "\n";
    echo "  Recibos con saldo restantes: {$recibosConSaldo}\n";
    echo "  Estatus después de confirmar: {$estatusSimulado}\n";
    
    if ($data['estatus_actual'] !== $estatusSimulado) {
        echo "  🔄 CAMBIARÍA DE {$data['estatus_actual']} A {$estatusSimulado}\n";
    } else {
        echo "  ✅ Mantendría el estatus {$data['estatus_actual']}\n";
    }
}

echo "\n=== RECOMENDACIONES ===\n";
if ($pagosPendientes->count() > 0) {
    echo "1. Hay {$pagosPendientes->count()} pagos pendientes de confirmación\n";
    echo "2. Confirmar estos pagos podría cambiar el estatus financiero de algunos apartamentos\n";
    echo "3. Revisar el proceso de creación de pagos para asegurar que se marquen como 'confirmado'\n";
    echo "4. Implementar un proceso para confirmar pagos pendientes masivamente\n";
} else {
    echo "✅ No hay pagos pendientes de confirmación\n";
    echo "El problema podría estar en otro lugar del flujo\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";

?>