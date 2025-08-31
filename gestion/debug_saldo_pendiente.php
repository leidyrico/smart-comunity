<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== DEBUG SALDO PENDIENTE ===\n\n";

// Obtener apartamentos con recibos asignados
$apartamentos = Apartamento::whereHas('pagos')->take(3)->get();

foreach ($apartamentos as $apartamento) {
    echo "APARTAMENTO {$apartamento->numero}:\n";
    echo "Propietario: {$apartamento->propietario}\n";
    echo "Estatus financiero: {$apartamento->estatus_financiero}\n";
    
    // Obtener todos los recibos asignados
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    echo "Recibos asignados: {$recibosAsignados->count()}\n";
    
    $totalRecibos = 0;
    $totalPagosConfirmados = 0;
    $saldoCalculadoManual = 0;
    
    foreach ($recibosAsignados as $recibo) {
        $totalRecibos += $recibo->total_recibo;
        
        // Obtener pagos confirmados para este recibo específico
        $pagosConfirmados = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $totalPagosConfirmados += $pagosConfirmados;
        
        $saldoRecibo = max(0, $recibo->total_recibo - $pagosConfirmados);
        $saldoCalculadoManual += $saldoRecibo;
        
        echo "  - Recibo {$recibo->id}: Total $" . number_format($recibo->total_recibo, 2) . ", Pagado $" . number_format($pagosConfirmados, 2) . ", Saldo $" . number_format($saldoRecibo, 2) . "\n";
    }
    
    echo "\nRESUMEN:\n";
    echo "Total recibos: $" . number_format($totalRecibos, 2) . "\n";
    echo "Total pagos confirmados: $" . number_format($totalPagosConfirmados, 2) . "\n";
    echo "Saldo calculado manual: $" . number_format($saldoCalculadoManual, 2) . "\n";
    echo "Saldo método modelo: {$apartamento->saldo_pendiente}\n";
    
    if ($saldoCalculadoManual == $apartamento->saldo_pendiente) {
        echo "✅ CORRECTO: Los cálculos coinciden\n";
    } else {
        echo "❌ ERROR: Los cálculos NO coinciden\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "=== FIN DEBUG ===\n";