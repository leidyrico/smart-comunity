<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== VERIFICACIÓN VISTA APARTAMENTOS ===\n\n";

// Simular exactamente lo que hace el controlador de apartamentos
$apartamentos = Apartamento::orderBy('numero')->get();

echo "Total apartamentos: {$apartamentos->count()}\n\n";

foreach ($apartamentos->take(10) as $apartamento) {
    echo "APARTAMENTO {$apartamento->numero}:\n";
    echo "Propietario: {$apartamento->propietario}\n";
    echo "Estatus: {$apartamento->estatus_financiero}\n";
    
    // Verificar si tiene recibos asignados
    $tieneRecibos = $apartamento->pagos()->count() > 0;
    echo "Tiene recibos asignados: " . ($tieneRecibos ? 'SÍ' : 'NO') . "\n";
    
    if ($tieneRecibos) {
        // Cálculo manual del saldo pendiente
        $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
            $query->where('apartamento_id', $apartamento->id)
                  ->where('estado', '!=', 'rechazado');
        })->get();
        
        $totalRecibos = $recibosAsignados->sum('total_recibo');
        
        $totalPagosConfirmados = 0;
        foreach ($recibosAsignados as $recibo) {
            $pagosConfirmados = $apartamento->pagos()
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            $totalPagosConfirmados += $pagosConfirmados;
        }
        
        $saldoCalculadoManual = $totalRecibos - $totalPagosConfirmados;
        
        echo "Total recibos: $" . number_format($totalRecibos, 2) . "\n";
        echo "Total pagos confirmados: $" . number_format($totalPagosConfirmados, 2) . "\n";
        echo "Saldo calculado manual: $" . number_format($saldoCalculadoManual, 2) . "\n";
    }
    
    // Lo que muestra el método del modelo
    $saldoModelo = $apartamento->saldo_pendiente;
    echo "Saldo método modelo: $" . number_format($saldoModelo, 2) . "\n";
    
    // Lo que se mostraría en la vista
    $saldoVista = number_format($apartamento->saldo_pendiente, 2);
    echo "Saldo en vista: $" . $saldoVista . "\n";
    
    if ($tieneRecibos) {
        if (abs($saldoCalculadoManual - $saldoModelo) < 0.01) {
            echo "✅ CORRECTO: Cálculo coincide\n";
        } else {
            echo "❌ ERROR: Cálculo NO coincide\n";
        }
    } else {
        if ($saldoModelo == 0) {
            echo "✅ CORRECTO: Sin recibos, saldo = 0\n";
        } else {
            echo "❌ ERROR: Sin recibos pero saldo != 0\n";
        }
    }
    
    echo "\n" . str_repeat('-', 40) . "\n\n";
}

echo "=== FIN VERIFICACIÓN ===\n";