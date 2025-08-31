<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== ANÁLISIS DETALLADO APARTAMENTO 13 (ID 91) ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::find(91);
if (!$apartamento) {
    echo "❌ Apartamento 91 no encontrado\n";
    exit;
}

echo "✅ Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n\n";

// Obtener todos los pagos del apartamento 91
$pagos = Pago::where('apartamento_id', 91)
    ->with('reciboGastoComun')
    ->orderBy('recibo_gasto_comun_id')
    ->get();

echo "Total de registros en tabla 'pagos': {$pagos->count()}\n\n";

// Agrupar por recibo para ver duplicados
$recibosPorId = [];
foreach ($pagos as $pago) {
    $reciboId = $pago->recibo_gasto_comun_id;
    if (!isset($recibosPorId[$reciboId])) {
        $recibosPorId[$reciboId] = [];
    }
    $recibosPorId[$reciboId][] = $pago;
}

echo "=== RECIBOS ASIGNADOS (AGRUPADOS) ===\n";
$recibosUnicos = 0;
$duplicados = 0;

foreach ($recibosPorId as $reciboId => $pagosDelRecibo) {
    $recibosUnicos++;
    $recibo = $pagosDelRecibo[0]->reciboGastoComun;
    
    if ($recibo) {
        echo "📋 Recibo {$recibo->numero_recibo} (ID: {$reciboId})\n";
        echo "   Estado: {$recibo->estado}\n";
        echo "   Total: $" . number_format($recibo->total_recibo, 2) . "\n";
        echo "   Registros en pagos: " . count($pagosDelRecibo) . "\n";
        
        if (count($pagosDelRecibo) > 1) {
            $duplicados++;
            echo "   ⚠️ DUPLICADO - Detalles:\n";
            foreach ($pagosDelRecibo as $index => $pago) {
                echo "     Pago " . ($index + 1) . ": ID {$pago->id}, Monto: $" . number_format($pago->monto_pagado, 2) . ", Estado: {$pago->estado}\n";
            }
        } else {
            $pago = $pagosDelRecibo[0];
            echo "   Pago: ID {$pago->id}, Monto: $" . number_format($pago->monto_pagado, 2) . ", Estado: {$pago->estado}\n";
        }
        echo "\n";
    } else {
        echo "❌ Recibo ID {$reciboId} no encontrado en base de datos\n\n";
    }
}

echo "=== RESUMEN ===\n";
echo "Total de recibos únicos asignados: {$recibosUnicos}\n";
echo "Total de registros en tabla pagos: {$pagos->count()}\n";
echo "Recibos con registros duplicados: {$duplicados}\n\n";

if ($recibosUnicos != 20) {
    echo "⚠️ DISCREPANCIA: Se esperaban 20 recibos pero se encontraron {$recibosUnicos}\n";
    
    if ($recibosUnicos > 20) {
        echo "💡 Hay " . ($recibosUnicos - 20) . " recibos adicionales asignados\n";
    } else {
        echo "💡 Faltan " . (20 - $recibosUnicos) . " recibos por asignar\n";
    }
}

if ($duplicados > 0) {
    echo "\n⚠️ Se encontraron {$duplicados} recibos con registros duplicados en la tabla pagos\n";
    echo "Esto puede causar cálculos incorrectos en el saldo pendiente\n";
}