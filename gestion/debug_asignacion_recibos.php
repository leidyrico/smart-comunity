<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG ASIGNACIÓN DE RECIBOS ===\n\n";

// Obtener algunos apartamentos para verificar
$apartamentos = Apartamento::limit(5)->get();

foreach ($apartamentos as $apartamento) {
    echo "🏠 Apartamento {$apartamento->numero} (ID: {$apartamento->id})\n";
    echo "   Estatus actual: {$apartamento->estatus_financiero}\n";
    
    // Verificar recibos asignados a través de pagos
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
            $query->where('apartamento_id', $apartamento->id)
                  ->where('estado', '!=', 'rechazado');
        })
        ->whereIn('estado', ['activo', 'vencido'])
        ->get();
    
    echo "   Recibos asignados: {$recibosAsignados->count()}\n";
    
    if ($recibosAsignados->count() > 0) {
        $recibosConDeuda = 0;
        $totalDeuda = 0;
        
        foreach ($recibosAsignados as $recibo) {
            // Calcular total pagado para este recibo y apartamento
            $totalPagado = Pago::where('apartamento_id', $apartamento->id)
                ->where('recibo_gasto_comun_id', $recibo->id)
                ->where('estado', 'confirmado')
                ->sum('monto_pagado');
            
            $saldoPendiente = $recibo->total_recibo - $totalPagado;
            
            if ($saldoPendiente > 0) {
                $recibosConDeuda++;
                $totalDeuda += $saldoPendiente;
            }
            
            echo "     - {$recibo->numero_recibo}: Total $" . number_format($recibo->total_recibo, 2) . 
                 ", Pagado $" . number_format($totalPagado, 2) . 
                 ", Pendiente $" . number_format($saldoPendiente, 2) . "\n";
        }
        
        echo "   📊 Resumen:\n";
        echo "     - Recibos con deuda: {$recibosConDeuda}\n";
        echo "     - Total deuda: $" . number_format($totalDeuda, 2) . "\n";
        
        // Calcular estatus según la lógica del modelo
        $estatusCalculado = 'solvente';
        if ($recibosConDeuda > 3) {
            $estatusCalculado = 'moroso';
        } elseif ($recibosConDeuda > 0) {
            $estatusCalculado = 'deudor';
        }
        
        echo "     - Estatus calculado: {$estatusCalculado}\n";
        
        if ($apartamento->estatus_financiero !== $estatusCalculado) {
            echo "     ⚠️ INCONSISTENCIA: Estatus actual ({$apartamento->estatus_financiero}) != Calculado ({$estatusCalculado})\n";
        } else {
            echo "     ✅ Estatus correcto\n";
        }
    } else {
        echo "   ❌ No tiene recibos asignados\n";
    }
    
    echo "\n";
}

echo "=== VERIFICACIÓN GLOBAL ===\n";

// Contar apartamentos por estatus
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$deudores = Apartamento::where('estatus_financiero', 'deudor')->count();
$morosos = Apartamento::where('estatus_financiero', 'moroso')->count();

echo "Apartamentos solventes: {$solventes}\n";
echo "Apartamentos deudores: {$deudores}\n";
echo "Apartamentos morosos: {$morosos}\n";

// Verificar si hay recibos sin asignar
echo "\n=== RECIBOS SIN ASIGNAR ===\n";
$recibosSinAsignar = ReciboGastoComun::whereDoesntHave('pagos')
    ->whereIn('estado', ['activo', 'vencido'])
    ->get();

echo "Recibos sin asignar a ningún apartamento: {$recibosSinAsignar->count()}\n";

if ($recibosSinAsignar->count() > 0) {
    foreach ($recibosSinAsignar as $recibo) {
        echo "  - {$recibo->numero_recibo} (Total: $" . number_format($recibo->total_recibo, 2) . ", Estado: {$recibo->estado})\n";
    }
}

echo "\n=== FIN DEBUG ===\n";
?>