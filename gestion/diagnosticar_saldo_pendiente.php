<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNÓSTICO DE SALDO PENDIENTE ===\n\n";

// 1. Verificar apartamentos con estatus deudor
echo "1. APARTAMENTOS CON ESTATUS DEUDOR:\n";
$apartamentosDeudores = Apartamento::where('estatus_financiero', 'deudor')->get();

foreach ($apartamentosDeudores as $apartamento) {
    echo "\nApartamento {$apartamento->numero} ({$apartamento->propietario}):\n";
    echo "  - Estatus financiero: {$apartamento->estatus_financiero}\n";
    echo "  - Fecha cambio estatus: {$apartamento->fecha_cambio_estatus}\n";
    echo "  - Saldo pendiente (método): {$apartamento->saldo_pendiente}\n";
    
    // Verificar si tiene pagos asociados
    $totalPagos = $apartamento->pagos()->count();
    echo "  - Total pagos asociados: {$totalPagos}\n";
    
    if ($totalPagos == 0) {
        echo "  ⚠️  PROBLEMA: No tiene pagos asociados\n";
        
        // Verificar recibos activos y vencidos que deberían estar incluidos
        $recibosActivos = ReciboGastoComun::where('estado', 'activo')->get();
        $recibosVencidos = ReciboGastoComun::where('estado', 'vencido')->get();
        
        echo "  - Recibos activos en sistema: {$recibosActivos->count()}\n";
        echo "  - Recibos vencidos en sistema: {$recibosVencidos->count()}\n";
        
        $totalDeudaEsperada = 0;
        foreach ($recibosActivos as $recibo) {
            $totalDeudaEsperada += $recibo->total_recibo;
        }
        foreach ($recibosVencidos as $recibo) {
            $totalDeudaEsperada += $recibo->total_recibo;
        }
        
        echo "  - Deuda esperada (sin pagos): $" . number_format($totalDeudaEsperada, 0, ',', '.') . "\n";
        
        if ($apartamento->saldo_pendiente != $totalDeudaEsperada) {
            echo "  ❌ ERROR: El saldo calculado no coincide con la deuda esperada\n";
        }
    } else {
        echo "  ✅ Tiene pagos asociados, analizando cálculo...\n";
        
        // Análisis detallado del cálculo actual
        $pagos = $apartamento->pagos()->with('reciboGastoComun')->get();
        $pagosPorRecibo = $pagos->groupBy('recibo_gasto_comun_id');
        
        $totalCalculadoManual = 0;
        
        foreach ($pagosPorRecibo as $reciboId => $pagosDelRecibo) {
            $recibo = $pagosDelRecibo->first()->reciboGastoComun;
            
            // Lógica del método getSaldoPendienteAttribute
            $incluirRecibo = false;
            
            if ($apartamento->estatus_financiero === 'solvente') {
                $incluirRecibo = ($recibo->estado === 'activo');
            } elseif ($apartamento->estatus_financiero === 'deudor') {
                $cambioHoy = $apartamento->fecha_cambio_estatus && 
                            $apartamento->fecha_cambio_estatus === now()->toDateString();
                
                if ($cambioHoy) {
                    $incluirRecibo = ($recibo->estado === 'activo');
                } else {
                    $incluirRecibo = in_array($recibo->estado, ['activo', 'vencido']);
                }
            } else {
                $incluirRecibo = in_array($recibo->estado, ['activo', 'vencido']);
            }
            
            if ($incluirRecibo) {
                $totalPagadoRecibo = $pagosDelRecibo
                    ->where('estado', '!=', 'rechazado')
                    ->where('estado', 'confirmado')
                    ->sum('monto_pagado');
                
                $saldo = $recibo->total_recibo - $totalPagadoRecibo;
                if ($saldo > 0) {
                    $totalCalculadoManual += $saldo;
                }
                
                echo "    - Recibo {$recibo->numero_recibo} ({$recibo->estado}): Total {$recibo->total_recibo}, Pagado {$totalPagadoRecibo}, Saldo {$saldo}\n";
            } else {
                echo "    - Recibo {$recibo->numero_recibo} ({$recibo->estado}): EXCLUIDO del cálculo\n";
            }
        }
        
        echo "  - Total calculado manual: $" . number_format($totalCalculadoManual, 0, ',', '.') . "\n";
        
        if ($apartamento->saldo_pendiente == $totalCalculadoManual) {
            echo "  ✅ El cálculo es correcto\n";
        } else {
            echo "  ❌ ERROR: Discrepancia en el cálculo\n";
        }
    }
}

// 2. Verificar recibos sin pagos asociados
echo "\n\n2. RECIBOS SIN PAGOS ASOCIADOS:\n";
$recibosSinPagos = ReciboGastoComun::whereDoesntHave('pagos')->get();

echo "Total recibos sin pagos: {$recibosSinPagos->count()}\n";

foreach ($recibosSinPagos as $recibo) {
    echo "  - {$recibo->numero_recibo} ({$recibo->estado}): $" . number_format($recibo->total_recibo, 0, ',', '.') . "\n";
}

// 3. Proponer solución
echo "\n\n3. ANÁLISIS DEL PROBLEMA:\n";
echo "El método getSaldoPendienteAttribute() tiene una limitación:\n";
echo "- Solo considera recibos que YA TIENEN pagos asociados\n";
echo "- NO considera recibos que no tienen ningún pago (deuda completa)\n";
echo "- Esto causa que apartamentos deudores muestren saldo 0 si no tienen pagos\n\n";

echo "SOLUCIÓN PROPUESTA:\n";
echo "Modificar el método para considerar TODOS los recibos activos/vencidos,\n";
echo "no solo los que tienen pagos asociados.\n\n";

echo "=== FIN DEL DIAGNÓSTICO ===\n";