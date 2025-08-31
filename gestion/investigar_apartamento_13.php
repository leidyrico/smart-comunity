<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== INVESTIGACIÓN DETALLADA APARTAMENTO 13 ===\n\n";

$apartamento = Apartamento::where('numero', '13')->first();

if (!$apartamento) {
    echo "❌ No se encontró el apartamento 13\n";
    exit;
}

echo "APARTAMENTO {$apartamento->numero}:\n";
echo "Propietario: {$apartamento->propietario}\n";
echo "ID: {$apartamento->id}\n\n";

// Método 1: Obtener recibos a través de la relación pagos
echo "=== MÉTODO 1: A través de relación pagos ===\n";
$recibosViaPagos = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id)
          ->where('estado', '!=', 'rechazado');
})->get();

echo "Recibos encontrados vía pagos: {$recibosViaPagos->count()}\n";
$totalViaPagos = 0;
foreach ($recibosViaPagos as $recibo) {
    echo "  - Recibo {$recibo->id}: $" . number_format($recibo->total_recibo, 2) . " ({$recibo->estado})\n";
    $totalViaPagos += $recibo->total_recibo;
}
echo "TOTAL VÍA PAGOS: $" . number_format($totalViaPagos, 2) . "\n\n";

// Método 2: Obtener TODOS los pagos del apartamento (incluyendo rechazados)
echo "=== MÉTODO 2: Todos los pagos del apartamento ===\n";
$todosPagos = Pago::where('apartamento_id', $apartamento->id)->get();
echo "Total pagos registrados: {$todosPagos->count()}\n";

$recibosUnicos = [];
foreach ($todosPagos as $pago) {
    if (!isset($recibosUnicos[$pago->recibo_gasto_comun_id])) {
        $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
        if ($recibo) {
            $recibosUnicos[$pago->recibo_gasto_comun_id] = $recibo;
        }
    }
}

echo "Recibos únicos encontrados: " . count($recibosUnicos) . "\n";
$totalTodosRecibos = 0;
foreach ($recibosUnicos as $recibo) {
    echo "  - Recibo {$recibo->id}: $" . number_format($recibo->total_recibo, 2) . " ({$recibo->estado})\n";
    $totalTodosRecibos += $recibo->total_recibo;
}
echo "TOTAL TODOS LOS RECIBOS: $" . number_format($totalTodosRecibos, 2) . "\n\n";

// Método 3: Verificar pagos por estado
echo "=== MÉTODO 3: Análisis por estado de pagos ===\n";
$pagosPorEstado = Pago::where('apartamento_id', $apartamento->id)
    ->selectRaw('estado, COUNT(*) as cantidad, SUM(monto_pagado) as total')
    ->groupBy('estado')
    ->get();

foreach ($pagosPorEstado as $grupo) {
    echo "Estado '{$grupo->estado}': {$grupo->cantidad} pagos, Total: $" . number_format($grupo->total, 2) . "\n";
}

// Método 4: Recibos excluyendo solo rechazados
echo "\n=== MÉTODO 4: Recibos excluyendo solo rechazados ===\n";
$recibosNoRechazados = [];
foreach ($todosPagos as $pago) {
    if ($pago->estado !== 'rechazado') {
        if (!isset($recibosNoRechazados[$pago->recibo_gasto_comun_id])) {
            $recibo = ReciboGastoComun::find($pago->recibo_gasto_comun_id);
            if ($recibo) {
                $recibosNoRechazados[$pago->recibo_gasto_comun_id] = $recibo;
            }
        }
    }
}

echo "Recibos no rechazados: " . count($recibosNoRechazados) . "\n";
$totalNoRechazados = 0;
foreach ($recibosNoRechazados as $recibo) {
    echo "  - Recibo {$recibo->id}: $" . number_format($recibo->total_recibo, 2) . " ({$recibo->estado})\n";
    $totalNoRechazados += $recibo->total_recibo;
}
echo "TOTAL NO RECHAZADOS: $" . number_format($totalNoRechazados, 2) . "\n\n";

// Comparar con el método del modelo
echo "=== COMPARACIÓN CON MÉTODO DEL MODELO ===\n";
$saldoModelo = $apartamento->saldo_pendiente;
echo "Saldo según modelo: $" . number_format($saldoModelo, 2) . "\n";

echo "\n=== RESUMEN ===\n";
echo "Método 1 (vía pagos): $" . number_format($totalViaPagos, 2) . "\n";
echo "Método 2 (todos): $" . number_format($totalTodosRecibos, 2) . "\n";
echo "Método 4 (no rechazados): $" . number_format($totalNoRechazados, 2) . "\n";
echo "Modelo actual: $" . number_format($saldoModelo, 2) . "\n";