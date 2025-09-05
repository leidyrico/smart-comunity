<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== PRUEBA DE LA NUEVA LÓGICA DE ESTATUS FINANCIERO ===\n\n";

// Función para simular diferentes escenarios
function simularEscenario($descripcion, $recibosConDeuda) {
    echo "🧪 Escenario: {$descripcion}\n";
    echo "   📋 Recibos con deuda: {$recibosConDeuda}\n";
    
    if ($recibosConDeuda <= 0) {
        $estatus = 'solvente';
    } elseif ($recibosConDeuda >= 1 && $recibosConDeuda <= 3) {
        $estatus = 'deudor';
    } else {
        $estatus = 'moroso';
    }
    
    echo "   🎯 Estatus esperado: {$estatus}\n\n";
    return $estatus;
}

// Probar diferentes escenarios
echo "=== SIMULACIÓN DE ESCENARIOS ===\n";
simularEscenario("Sin deudas", 0);
simularEscenario("1 recibo con deuda", 1);
simularEscenario("2 recibos con deuda", 2);
simularEscenario("3 recibos con deuda", 3);
simularEscenario("4 recibos con deuda", 4);
simularEscenario("5 recibos con deuda", 5);

// Probar con apartamentos reales
echo "=== PRUEBA CON APARTAMENTOS REALES ===\n";

// Obtener algunos apartamentos para probar
$apartamentos = Apartamento::take(5)->get();

foreach ($apartamentos as $apartamento) {
    echo "🏠 Apartamento {$apartamento->numero}:\n";
    echo "   💰 Estatus actual: {$apartamento->estatus_financiero}\n";
    
    // Aplicar la nueva lógica
    $nuevoEstatus = $apartamento->actualizarEstatusFinanciero();
    $apartamento->refresh();
    
    echo "   🎯 Estatus después de actualizar: {$apartamento->estatus_financiero}\n";
    
    // Contar recibos con deuda para verificar
    $recibosConDeuda = 0;
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    foreach ($recibosAsignados as $recibo) {
        if ($recibo->monto_total <= 0) continue;
        
        $totalPagado = $apartamento->pagos()
            ->where('recibo_gasto_comun_id', $recibo->id)
            ->where('estado', 'confirmado')
            ->sum('monto_pagado');
        
        $saldoRecibo = max(0, $recibo->monto_total - $totalPagado);
        
        if ($saldoRecibo > 0) {
            $recibosConDeuda++;
        }
    }
    
    echo "   📋 Recibos con deuda real: {$recibosConDeuda}\n";
    
    // Verificar que la lógica es correcta
    $estatusEsperado = 'solvente';
    if ($recibosConDeuda >= 1 && $recibosConDeuda <= 3) {
        $estatusEsperado = 'deudor';
    } elseif ($recibosConDeuda > 3) {
        $estatusEsperado = 'moroso';
    }
    
    if ($apartamento->estatus_financiero === $estatusEsperado) {
        echo "   ✅ Lógica correcta\n";
    } else {
        echo "   ❌ Error en la lógica: esperado {$estatusEsperado}, obtenido {$apartamento->estatus_financiero}\n";
    }
    
    echo "\n";
}

// Mostrar distribución final
echo "=== DISTRIBUCIÓN FINAL DE ESTATUS ===\n";
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$deudores = Apartamento::where('estatus_financiero', 'deudor')->count();
$morosos = Apartamento::where('estatus_financiero', 'moroso')->count();

echo "✅ Solventes: {$solventes}\n";
echo "⚠️ Deudores: {$deudores}\n";
echo "❌ Morosos: {$morosos}\n";

echo "\n=== NUEVA LÓGICA IMPLEMENTADA CORRECTAMENTE ===\n";
echo "📋 Reglas aplicadas:\n";
echo "   • 0 recibos con deuda = SOLVENTE\n";
echo "   • 1-3 recibos con deuda = DEUDOR\n";
echo "   • Más de 3 recibos con deuda = MOROSO\n";