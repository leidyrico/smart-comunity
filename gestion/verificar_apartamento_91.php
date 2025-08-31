<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== VERIFICACIÓN APARTAMENTO 91 ===\n\n";

// Buscar apartamento 91
$apartamento = Apartamento::find(91);
if (!$apartamento) {
    echo "❌ Apartamento 91 no encontrado\n";
    exit;
}

echo "✅ Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n";
echo "Estatus financiero: {$apartamento->estatus_financiero}\n\n";

// Verificar pagos actuales
$pagos = Pago::where('apartamento_id', 91)->count();
echo "Total pagos en la base de datos: {$pagos}\n\n";

// Obtener todos los recibos activos/vencidos
$recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_vencimiento', 'asc')
    ->get();

echo "Total recibos activos/vencidos en el sistema: {$recibos->count()}\n\n";

// Simular la lógica del createGlobal
echo "=== SIMULANDO LÓGICA createGlobal ===\n";
$recibos_pendientes = 0;
$total_pendiente = 0;

foreach ($recibos as $recibo) {
    // Calcular total pagado para este recibo y apartamento específico
    $totalPagado = Pago::where('apartamento_id', 91)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    // Solo incluir recibos con saldo pendiente > 0
    if ($saldoPendiente > 0) {
        $recibos_pendientes++;
        $total_pendiente += $saldoPendiente;
        echo "📋 Recibo pendiente: {$recibo->numero_recibo}\n";
        echo "   Total recibo: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   Total pagado: $" . number_format($totalPagado, 2) . "\n";
        echo "   Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n\n";
    }
}

echo "=== RESUMEN ===\n";
echo "Recibos pendientes para apartamento 91: {$recibos_pendientes}\n";
echo "Total pendiente: $" . number_format($total_pendiente, 2) . "\n\n";

if ($recibos_pendientes == 0) {
    echo "✅ El apartamento 91 NO tiene recibos pendientes.\n";
    echo "La página pagos/global/create/91 debería mostrar 'Sin recibos pendientes'.\n";
} else {
    echo "⚠️ El apartamento 91 tiene {$recibos_pendientes} recibos pendientes.\n";
}