<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== CORRECCIÓN MASIVA DE ESTATUS FINANCIERO ===\n\n";

$apartamentos = Apartamento::all();
$corregidos = 0;
$errores = 0;

foreach ($apartamentos as $apartamento) {
    try {
        // Obtener estatus actual
        $estatusAnterior = $apartamento->estatus_financiero;
        
        // Calcular recibos con deuda real
        $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
                $query->where('apartamento_id', $apartamento->id)
                      ->where('estado', '!=', 'rechazado');
            })
            ->whereIn('estado', ['activo', 'vencido'])
            ->get();
        
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
        }
        
        // Calcular nuevo estatus según la lógica del modelo
        $nuevoEstatus = 'solvente';
        if ($recibosConDeuda > 3) {
            $nuevoEstatus = 'moroso';
        } elseif ($recibosConDeuda > 0) {
            $nuevoEstatus = 'deudor';
        }
        
        // Actualizar solo si hay cambio
        if ($estatusAnterior !== $nuevoEstatus) {
            $apartamento->estatus_financiero = $nuevoEstatus;
            $apartamento->save();
            
            echo "✅ Apartamento {$apartamento->numero}: {$estatusAnterior} → {$nuevoEstatus} ({$recibosConDeuda} recibos pendientes, $" . number_format($totalDeuda, 2) . ")\n";
            $corregidos++;
        } else {
            echo "   Apartamento {$apartamento->numero}: {$estatusAnterior} (correcto)\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error en apartamento {$apartamento->numero}: " . $e->getMessage() . "\n";
        $errores++;
    }
}

echo "\n=== RESUMEN ===\n";
echo "Apartamentos corregidos: {$corregidos}\n";
echo "Errores: {$errores}\n";

// Verificar resultado final
echo "\n=== ESTATUS FINAL ===\n";
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$deudores = Apartamento::where('estatus_financiero', 'deudor')->count();
$morosos = Apartamento::where('estatus_financiero', 'moroso')->count();

echo "Apartamentos solventes: {$solventes}\n";
echo "Apartamentos deudores: {$deudores}\n";
echo "Apartamentos morosos: {$morosos}\n";

echo "\n=== CORRECCIÓN COMPLETADA ===\n";
?>