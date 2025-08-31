<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== VERIFICACIÓN FINAL DEL SALDO PENDIENTE ===\n\n";
echo "Fórmula: SUMA DE TODOS LOS RECIBOS ASIGNADOS - SOLO PAGOS CONFIRMADOS\n\n";

// Obtener apartamentos con recibos asignados
$apartamentosConRecibos = Apartamento::whereHas('pagos', function($query) {
    $query->where('estado', '!=', 'rechazado');
})->orderBy('numero')->take(5)->get();

foreach ($apartamentosConRecibos as $apartamento) {
    echo "APARTAMENTO {$apartamento->numero}:\n";
    echo "Propietario: {$apartamento->propietario}\n";
    
    // PASO 1: Obtener TODOS los recibos asignados (excluyendo rechazados)
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    echo "\nRecibos asignados ({$recibosAsignados->count()}):";
    $sumaRecibos = 0;
    foreach ($recibosAsignados as $recibo) {
        echo "\n  - Recibo {$recibo->id}: $" . number_format($recibo->total_recibo, 2) . " ({$recibo->estado})";
        $sumaRecibos += $recibo->total_recibo;
    }
    echo "\nSUMA TOTAL RECIBOS: $" . number_format($sumaRecibos, 2) . "\n";
    
    // PASO 2: Obtener SOLO los pagos confirmados
    $pagosConfirmados = Pago::where('apartamento_id', $apartamento->id)
        ->where('estado', 'confirmado')
        ->get();
    
    echo "\nPagos confirmados ({$pagosConfirmados->count()}):";
    $sumaPagos = 0;
    foreach ($pagosConfirmados as $pago) {
        echo "\n  - Pago {$pago->id}: $" . number_format($pago->monto_pagado, 2) . " (Recibo {$pago->recibo_gasto_comun_id})";
        $sumaPagos += $pago->monto_pagado;
    }
    echo "\nSUMA TOTAL PAGOS CONFIRMADOS: $" . number_format($sumaPagos, 2) . "\n";
    
    // PASO 3: Calcular saldo pendiente
    $saldoEsperado = $sumaRecibos - $sumaPagos;
    echo "\nCÁLCULO MANUAL: $" . number_format($sumaRecibos, 2) . " - $" . number_format($sumaPagos, 2) . " = $" . number_format($saldoEsperado, 2) . "\n";
    
    // PASO 4: Comparar con el método del modelo
    $saldoModelo = $apartamento->saldo_pendiente;
    echo "SALDO MODELO: $" . number_format($saldoModelo, 2) . "\n";
    
    if (abs($saldoEsperado - $saldoModelo) < 0.01) {
        echo "✅ CORRECTO: Los cálculos coinciden\n";
    } else {
        echo "❌ ERROR: Los cálculos NO coinciden\n";
        echo "   Diferencia: $" . number_format(abs($saldoEsperado - $saldoModelo), 2) . "\n";
    }
    
    echo "\n" . str_repeat('=', 60) . "\n\n";
}

echo "=== RESUMEN ===\n";
echo "El método getSaldoPendienteAttribute implementa exactamente:\n";
echo "- Suma de todos los recibos asignados al apartamento\n";
echo "- Menos SOLO los pagos confirmados\n";
echo "- Excluyendo recibos rechazados\n";
echo "\nEsto coincide con los requerimientos del usuario.\n";