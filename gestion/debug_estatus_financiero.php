<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== DEBUG ESTATUS FINANCIERO ===\n\n";

// Buscar un apartamento que esté marcado como moroso pero que tenga pagos
$apartamento = Apartamento::where('estatus_financiero', 'moroso')
    ->whereHas('pagos', function($query) {
        $query->where('estado', 'confirmado');
    })
    ->first();

if (!$apartamento) {
    echo "No se encontró un apartamento moroso con pagos confirmados.\n";
    echo "Buscando cualquier apartamento con pagos...\n";
    
    $apartamento = Apartamento::whereHas('pagos', function($query) {
        $query->where('estado', 'confirmado');
    })->first();
}

if (!$apartamento) {
    echo "No se encontró ningún apartamento con pagos.\n";
    exit;
}

echo "Apartamento seleccionado: {$apartamento->numero}\n";
echo "Propietario: {$apartamento->propietario}\n";
echo "Estatus financiero actual: {$apartamento->estatus_financiero}\n";
echo "Fecha cambio estatus: {$apartamento->fecha_cambio_estatus}\n\n";

// Verificar saldo pendiente ANTES de refrescar
echo "=== ANTES DE REFRESCAR ===\n";
echo "Saldo pendiente (sin refrescar): $" . number_format($apartamento->saldo_pendiente, 2) . "\n";

// Refrescar el modelo desde la base de datos
$apartamento->refresh();

echo "\n=== DESPUÉS DE REFRESCAR ===\n";
echo "Saldo pendiente (después de refrescar): $" . number_format($apartamento->saldo_pendiente, 2) . "\n";

// Contar recibos activos/vencidos asignados
$recibosActivosVencidos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id);
    })->count();

echo "Recibos activos/vencidos asignados: {$recibosActivosVencidos}\n";

// Mostrar detalles de pagos
echo "\n=== DETALLES DE PAGOS ===\n";
$pagos = $apartamento->pagos()->with('reciboGastoComun')->get();
foreach ($pagos as $pago) {
    echo "- Recibo ID: {$pago->recibo_gasto_comun_id}, Monto: $" . number_format($pago->monto_pagado, 2) . ", Estado: {$pago->estado}\n";
    if ($pago->reciboGastoComun) {
        echo "  Total recibo: $" . number_format($pago->reciboGastoComun->total_recibo, 2) . ", Estado recibo: {$pago->reciboGastoComun->estado}\n";
    }
}

// Ejecutar actualización de estatus financiero
echo "\n=== EJECUTANDO ACTUALIZACIÓN DE ESTATUS ===\n";
$estatusAnterior = $apartamento->estatus_financiero;
$nuevoEstatus = $apartamento->actualizarEstatusFinanciero();

echo "Estatus anterior: {$estatusAnterior}\n";
echo "Nuevo estatus: {$nuevoEstatus}\n";

// Refrescar y verificar
$apartamento->refresh();
echo "Estatus en BD después de actualizar: {$apartamento->estatus_financiero}\n";
echo "Saldo pendiente final: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";

echo "\n=== ANÁLISIS ===\n";
if ($apartamento->saldo_pendiente == 0) {
    echo "✅ El apartamento debería ser SOLVENTE (saldo = 0)\n";
} elseif ($recibosActivosVencidos > 3) {
    echo "⚠️  El apartamento debería ser MOROSO (>3 recibos activos/vencidos)\n";
} else {
    echo "⚠️  El apartamento debería ser DEUDOR (1-3 recibos activos/vencidos)\n";
}

if ($apartamento->estatus_financiero === 'solvente' && $apartamento->saldo_pendiente == 0) {
    echo "✅ CORRECTO: Apartamento solvente con saldo 0\n";
} elseif ($apartamento->estatus_financiero === 'deudor' && $apartamento->saldo_pendiente > 0 && $recibosActivosVencidos <= 3) {
    echo "✅ CORRECTO: Apartamento deudor con saldo > 0 y <= 3 recibos\n";
} elseif ($apartamento->estatus_financiero === 'moroso' && $apartamento->saldo_pendiente > 0 && $recibosActivosVencidos > 3) {
    echo "✅ CORRECTO: Apartamento moroso con saldo > 0 y > 3 recibos\n";
} else {
    echo "❌ PROBLEMA: El estatus no coincide con las reglas de negocio\n";
}

echo "\n=== FIN DEL DEBUG ===\n";

?>