<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReciboGastoComun;
use Carbon\Carbon;

echo "=== CORRECCIÓN ESTADO DE RECIBOS ===\n\n";

$fechaActual = Carbon::now();
echo "Fecha actual: {$fechaActual->format('Y-m-d H:i:s')}\n\n";

// Obtener todos los recibos
$recibos = ReciboGastoComun::all();

echo "Total recibos en el sistema: {$recibos->count()}\n\n";

$contadores = [
    'vigentes' => 0,
    'vencidos' => 0,
    'corregidos_a_vigente' => 0,
    'corregidos_a_vencido' => 0,
    'sin_cambios' => 0
];

$cambios = [];

echo "Procesando recibos...\n\n";

foreach ($recibos as $recibo) {
    $fechaVencimiento = Carbon::parse($recibo->fecha_vencimiento);
    $estadoAnterior = $recibo->estado;
    
    // Determinar el estado correcto basado en la fecha
    if ($fechaVencimiento->isFuture()) {
        $estadoCorreto = 'vigente';
    } else {
        $estadoCorreto = 'vencido';
    }
    
    echo "Recibo ID {$recibo->id}: Vence {$fechaVencimiento->format('Y-m-d')} ";
    echo "(Estado actual: {$estadoAnterior}, Correcto: {$estadoCorreto})";
    
    // Actualizar si es necesario
    if ($estadoAnterior !== $estadoCorreto) {
        $recibo->update(['estado' => $estadoCorreto]);
        
        $cambios[] = [
            'id' => $recibo->id,
            'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
            'anterior' => $estadoAnterior,
            'nuevo' => $estadoCorreto
        ];
        
        if ($estadoCorreto === 'vigente') {
            $contadores['corregidos_a_vigente']++;
        } else {
            $contadores['corregidos_a_vencido']++;
        }
        
        echo " → CORREGIDO";
    } else {
        $contadores['sin_cambios']++;
        echo " → Sin cambios";
    }
    
    // Actualizar contadores finales
    if ($estadoCorreto === 'vigente') {
        $contadores['vigentes']++;
    } else {
        $contadores['vencidos']++;
    }
    
    echo "\n";
}

echo "\n=== RESUMEN DE CORRECCIÓN ===\n";
echo "Total recibos procesados: {$recibos->count()}\n";
echo "Recibos vigentes: {$contadores['vigentes']}\n";
echo "Recibos vencidos: {$contadores['vencidos']}\n";
echo "Corregidos a vigente: {$contadores['corregidos_a_vigente']}\n";
echo "Corregidos a vencido: {$contadores['corregidos_a_vencido']}\n";
echo "Sin cambios: {$contadores['sin_cambios']}\n";

if (!empty($cambios)) {
    echo "\n=== CAMBIOS REALIZADOS ===\n";
    foreach ($cambios as $cambio) {
        echo "Recibo {$cambio['id']} (vence {$cambio['fecha_vencimiento']}): ";
        echo "{$cambio['anterior']} → {$cambio['nuevo']}\n";
    }
} else {
    echo "\nNo se realizaron cambios.\n";
}

echo "\n✅ Corrección de estados completada.\n";
echo "\n🔄 Ahora se debe recalcular el estatus financiero de los apartamentos.\n";