<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== CONSULTA RECIBOS ASIGNADOS APARTAMENTO 141 ===\n\n";

// Buscar apartamento 141
$apartamento = Apartamento::where('numero', '141')->first();
if (!$apartamento) {
    echo "❌ Apartamento 141 no encontrado\n";
    exit;
}

echo "✅ Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n";
echo "ID: {$apartamento->id}\n\n";

// Método 1: Contar todos los pagos (registros en tabla pagos)
$totalPagos = Pago::where('apartamento_id', $apartamento->id)->count();
echo "Total de registros en tabla 'pagos': {$totalPagos}\n";

// Método 2: Contar recibos únicos asignados
$recibosUnicos = Pago::where('apartamento_id', $apartamento->id)
    ->distinct()
    ->count('recibo_gasto_comun_id');
echo "Total de recibos únicos asignados: {$recibosUnicos}\n";

// Método 3: Contar recibos asignados con asignaciones históricas (según lógica del DeudaController)
$recibosConAsignacion = Pago::where('apartamento_id', $apartamento->id)
    ->where(function($query) {
        $query->where('observaciones', 'like', '%Asignación manual%')
              ->orWhere('observaciones', 'like', '%Pago global distribuido automáticamente%')
              ->orWhere('observaciones', 'like', '%Recibo asignado automáticamente%');
    })
    ->distinct()
    ->count('recibo_gasto_comun_id');
echo "Recibos con asignaciones históricas detectables: {$recibosConAsignacion}\n\n";

// Método 4: Listar todos los recibos asignados con detalles
echo "=== DETALLE DE RECIBOS ASIGNADOS ===\n";

$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id);
    })
    ->with(['pagos' => function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id);
    }])
    ->orderBy('fecha_emision', 'desc')
    ->get();

echo "Recibos encontrados: {$recibosAsignados->count()}\n\n";

$contador = 1;
foreach ($recibosAsignados as $recibo) {
    echo "{$contador}. Recibo {$recibo->numero_recibo} (ID: {$recibo->id})\n";
    echo "   Estado: {$recibo->estado}\n";
    echo "   Total: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "   Fecha emisión: {$recibo->fecha_emision}\n";
    echo "   Fecha vencimiento: {$recibo->fecha_vencimiento}\n";
    
    // Verificar pagos para este recibo
    $pagosRecibo = $recibo->pagos;
    $totalPagado = $pagosRecibo->where('estado', 'confirmado')->sum('monto_pagado');
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    echo "   Pagos confirmados: $" . number_format($totalPagado, 2) . "\n";
    echo "   Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    
    // Mostrar tipo de asignación
    $tipoAsignacion = 'Desconocido';
    foreach ($pagosRecibo as $pago) {
        if (strpos($pago->observaciones, 'Asignación manual') !== false) {
            $tipoAsignacion = 'Manual';
            break;
        } elseif (strpos($pago->observaciones, 'Pago global distribuido automáticamente') !== false) {
            $tipoAsignacion = 'Pago Global';
            break;
        } elseif (strpos($pago->observaciones, 'Recibo asignado automáticamente') !== false) {
            $tipoAsignacion = 'Automático';
            break;
        }
    }
    echo "   Tipo de asignación: {$tipoAsignacion}\n\n";
    
    $contador++;
}

echo "=== RESUMEN FINAL ===\n";
echo "📋 Total de recibos asignados al apartamento 141: {$recibosAsignados->count()}\n";
echo "💰 Estatus financiero actual: {$apartamento->estatus_financiero}\n";
echo "💵 Saldo pendiente: $" . number_format($apartamento->saldo_pendiente, 2) . "\n";

echo "\n=== FIN DE LA CONSULTA ===\n";
?>