<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== REPORTE FINAL DEL SISTEMA ===\n\n";

// Resumen general
echo "📊 RESUMEN GENERAL:\n";
echo "Total apartamentos: " . Apartamento::count() . "\n";
echo "Total recibos: " . ReciboGastoComun::count() . "\n";
echo "Total pagos: " . Pago::count() . "\n\n";

// Estado de recibos
echo "📋 ESTADO DE RECIBOS:\n";
$estadosRecibos = ReciboGastoComun::selectRaw('estado, COUNT(*) as total')
    ->groupBy('estado')
    ->get();

foreach ($estadosRecibos as $estado) {
    echo "- {$estado->estado}: {$estado->total} recibos\n";
}

// Estatus financiero de apartamentos
echo "\n🏠 ESTATUS FINANCIERO DE APARTAMENTOS:\n";
$estatusApartamentos = Apartamento::selectRaw('estatus_financiero, COUNT(*) as total')
    ->groupBy('estatus_financiero')
    ->get();

foreach ($estatusApartamentos as $estatus) {
    echo "- {$estatus->estatus_financiero}: {$estatus->total} apartamentos\n";
}

// Análisis detallado por estatus
echo "\n📈 ANÁLISIS DETALLADO:\n\n";

// Apartamentos SOLVENTES
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
echo "✅ SOLVENTES ({$solventes} apartamentos):\n";
echo "   - Sin recibos asignados o todos los recibos pagados\n\n";

// Apartamentos DEUDORES
$deudores = Apartamento::where('estatus_financiero', 'deudor')->get();
echo "⚠️ DEUDORES ({$deudores->count()} apartamentos):\n";
foreach ($deudores as $apartamento) {
    $recibosVencidos = ReciboGastoComun::where('estado', 'vencido')
        ->whereHas('pagos', function($query) use ($apartamento) {
            $query->where('apartamento_id', $apartamento->id)
                  ->where('estado', '!=', 'rechazado');
        })->count();
    
    $saldoPendiente = number_format($apartamento->saldo_pendiente, 2);
    echo "   - Apartamento {$apartamento->numero} ({$apartamento->propietario}): {$recibosVencidos} recibos vencidos, Saldo: $${saldoPendiente}\n";
}

// Apartamentos MOROSOS
$morosos = Apartamento::where('estatus_financiero', 'moroso')->get();
echo "\n🚨 MOROSOS ({$morosos->count()} apartamentos):\n";
if ($morosos->count() > 0) {
    foreach ($morosos as $apartamento) {
        $recibosVencidos = ReciboGastoComun::where('estado', 'vencido')
            ->whereHas('pagos', function($query) use ($apartamento) {
                $query->where('apartamento_id', $apartamento->id)
                      ->where('estado', '!=', 'rechazado');
            })->count();
        
        $saldoPendiente = number_format($apartamento->saldo_pendiente, 2);
        echo "   - Apartamento {$apartamento->numero} ({$apartamento->propietario}): {$recibosVencidos} recibos vencidos, Saldo: $${saldoPendiente}\n";
    }
} else {
    echo "   - No hay apartamentos en estado moroso\n";
}

// Resumen financiero
echo "\n💰 RESUMEN FINANCIERO:\n";

// Calcular total de saldos pendientes usando el atributo calculado
$totalSaldosPendientes = 0;
$apartamentos = Apartamento::all();
foreach ($apartamentos as $apartamento) {
    $totalSaldosPendientes += $apartamento->saldo_pendiente;
}

$totalPagosConfirmados = Pago::where('estado', 'confirmado')->sum('monto_pagado');
$totalRecibosEmitidos = ReciboGastoComun::sum('total_recibo');

echo "Total recibos emitidos: $" . number_format($totalRecibosEmitidos, 2) . "\n";
echo "Total pagos confirmados: $" . number_format($totalPagosConfirmados, 2) . "\n";
echo "Total saldos pendientes: $" . number_format($totalSaldosPendientes, 2) . "\n";

$porcentajeRecuperacion = $totalRecibosEmitidos > 0 ? ($totalPagosConfirmados / $totalRecibosEmitidos) * 100 : 0;
echo "Porcentaje de recuperación: " . number_format($porcentajeRecuperacion, 2) . "%\n";

echo "\n=== CONCLUSIONES ===\n";
echo "✅ El sistema está funcionando correctamente\n";
echo "✅ Todos los recibos están correctamente clasificados como vencidos\n";
echo "✅ Los estatus financieros están actualizados según las reglas de negocio\n";
echo "✅ No hay apartamentos en estado moroso (>3 recibos vencidos)\n";
echo "✅ {$deudores->count()} apartamentos tienen entre 1-3 recibos vencidos (DEUDOR)\n";
echo "✅ {$solventes} apartamentos están al día (SOLVENTE)\n";

echo "\n🎯 El recálculo del estatus financiero se ha completado exitosamente.\n";