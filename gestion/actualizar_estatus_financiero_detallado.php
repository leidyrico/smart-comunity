<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

echo "=== ACTUALIZACIÓN ESTATUS FINANCIERO DETALLADO ===\n\n";

echo "Reglas de negocio:\n";
echo "- SOLVENTE: Sin recibos asignados (0 recibos)\n";
echo "- DEUDOR: 1-3 recibos vencidos asignados\n";
echo "- MOROSO: Más de 3 recibos vencidos asignados\n\n";

$apartamentos = Apartamento::orderBy('numero')->get();

echo "Total apartamentos a procesar: {$apartamentos->count()}\n\n";

$cambios = [];
$contadores = [
    'solvente' => 0,
    'deudor' => 0,
    'moroso' => 0,
    'actualizados' => 0
];

foreach ($apartamentos as $apartamento) {
    echo "Procesando apartamento {$apartamento->numero}...\n";
    
    // Obtener estatus actual
    $estatusAnterior = $apartamento->estatus_financiero;
    
    // Contar recibos asignados (excluyendo rechazados)
    $recibosAsignados = $apartamento->pagos()
        ->where('estado', '!=', 'rechazado')
        ->distinct('recibo_gasto_comun_id')
        ->count();
    
    // Contar recibos vencidos asignados
    $recibosVencidosAsignados = ReciboGastoComun::where('estado', 'vencido')
        ->whereHas('pagos', function($query) use ($apartamento) {
            $query->where('apartamento_id', $apartamento->id)
                  ->where('estado', '!=', 'rechazado');
        })->count();
    
    // Determinar nuevo estatus según las reglas
    if ($recibosAsignados == 0) {
        $nuevoEstatus = 'solvente';
    } else {
        if ($recibosVencidosAsignados > 3) {
            $nuevoEstatus = 'moroso';
        } else {
            $nuevoEstatus = 'deudor';
        }
    }
    
    echo "  - Recibos asignados: {$recibosAsignados}\n";
    echo "  - Recibos vencidos: {$recibosVencidosAsignados}\n";
    echo "  - Estatus anterior: {$estatusAnterior}\n";
    echo "  - Nuevo estatus: {$nuevoEstatus}\n";
    
    // Actualizar si es necesario
    if ($estatusAnterior !== $nuevoEstatus) {
        $apartamento->update([
            'estatus_financiero' => $nuevoEstatus,
            'fecha_cambio_estatus' => now()->toDateString()
        ]);
        
        $cambios[] = [
            'apartamento' => $apartamento->numero,
            'propietario' => $apartamento->propietario,
            'anterior' => $estatusAnterior,
            'nuevo' => $nuevoEstatus,
            'recibos_vencidos' => $recibosVencidosAsignados
        ];
        
        $contadores['actualizados']++;
        echo "  ✅ ACTUALIZADO\n";
    } else {
        echo "  ⚪ Sin cambios\n";
    }
    
    $contadores[$nuevoEstatus]++;
    echo "\n";
}

echo "=== RESUMEN DE ACTUALIZACIÓN ===\n";
echo "Total procesados: {$apartamentos->count()}\n";
echo "Actualizados: {$contadores['actualizados']}\n";
echo "Solventes: {$contadores['solvente']}\n";
echo "Deudores: {$contadores['deudor']}\n";
echo "Morosos: {$contadores['moroso']}\n\n";

if (!empty($cambios)) {
    echo "=== CAMBIOS REALIZADOS ===\n";
    foreach ($cambios as $cambio) {
        echo "Apartamento {$cambio['apartamento']} ({$cambio['propietario']}): ";
        echo "{$cambio['anterior']} → {$cambio['nuevo']} ";
        echo "({$cambio['recibos_vencidos']} recibos vencidos)\n";
    }
} else {
    echo "No se realizaron cambios.\n";
}

echo "\n✅ Proceso completado exitosamente.\n";