<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== VERIFICACIÓN RECIBO 264 ===\n\n";

// Buscar apartamento 72 (ID 59)
$apartamento = Apartamento::find(59);
echo "✅ Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n";
echo "   Estatus: {$apartamento->estatus_financiero}\n\n";

// Buscar recibo 264
$recibo = ReciboGastoComun::find(264);

if ($recibo) {
    echo "✅ Recibo 264 encontrado:\n";
    echo "   Número: {$recibo->numero_recibo}\n";
    echo "   Período: {$recibo->periodo}\n";
    echo "   Total: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "   Estado: {$recibo->estado}\n";
    echo "   Fecha vencimiento: {$recibo->fecha_vencimiento}\n\n";
    
    // Verificar pagos para este recibo y apartamento
    $pagos = Pago::where('apartamento_id', 59)
                 ->where('recibo_gasto_comun_id', 264)
                 ->where('estado', 'confirmado')
                 ->get();
    
    echo "=== PAGOS PARA ESTE RECIBO ===\n";
    $totalPagado = 0;
    
    if ($pagos->count() > 0) {
        foreach ($pagos as $pago) {
            echo "💳 Pago ID: {$pago->id}\n";
            echo "   Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
            echo "   Fecha: {$pago->fecha_pago}\n";
            echo "   Estado: {$pago->estado}\n\n";
            $totalPagado += $pago->monto_pagado;
        }
    } else {
        echo "❌ No hay pagos confirmados para este recibo\n\n";
    }
    
    echo "=== CÁLCULO DE SALDO ===\n";
    echo "Total recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "Total pagado: $" . number_format($totalPagado, 2) . "\n";
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    echo "Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n\n";
    
    if ($saldoPendiente > 0) {
        echo "✅ El recibo tiene saldo pendiente, debería aparecer en el formulario\n";
    } else {
        echo "❌ El recibo NO tiene saldo pendiente, no debería aparecer\n";
    }
    
} else {
    echo "❌ Recibo 264 NO encontrado\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";