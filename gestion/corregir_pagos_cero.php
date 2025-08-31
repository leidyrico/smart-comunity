<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pago;
use App\Models\Apartamento;

echo "=== CORRECCIÓN DE PAGOS CON MONTO $0.00 ===\n\n";

// Buscar pagos con monto_pagado = 0 o NULL
$pagosProblematicos = Pago::where(function($query) {
    $query->where('monto_pagado', 0)
          ->orWhereNull('monto_pagado');
})->with(['apartamento', 'reciboGastoComun'])->get();

echo "Pagos encontrados con monto $0.00 o NULL: {$pagosProblematicos->count()}\n\n";

if ($pagosProblematicos->count() > 0) {
    echo "DETALLES DE PAGOS PROBLEMÁTICOS:\n";
    echo str_repeat('-', 80) . "\n";
    
    foreach ($pagosProblematicos as $pago) {
        echo "ID: {$pago->id}\n";
        echo "Apartamento: {$pago->apartamento->numero} - {$pago->apartamento->propietario}\n";
        echo "Recibo: " . ($pago->reciboGastoComun->numero_recibo ? $pago->reciboGastoComun->numero_recibo : 'N/A') . "\n";
        echo "Monto: $" . number_format($pago->monto_pagado ? $pago->monto_pagado : 0, 2) . "\n";
        echo "Estado: {$pago->estado}\n";
        echo "Fecha: " . ($pago->fecha_pago ? $pago->fecha_pago : 'N/A') . "\n";
        echo "Observaciones: {$pago->observaciones}\n";
        echo "Creado: {$pago->created_at}\n";
        echo str_repeat('-', 80) . "\n";
    }
    
    // Filtrar específicamente apartamento 13 (ID 91)
    $pagosApto13 = $pagosProblematicos->where('apartamento_id', 91);
    
    echo "\n=== PAGOS PROBLEMÁTICOS DEL APARTAMENTO 13 ===\n";
    echo "Cantidad: {$pagosApto13->count()}\n\n";
    
    if ($pagosApto13->count() > 0) {
        foreach ($pagosApto13 as $pago) {
            echo "Pago ID {$pago->id}: Monto $" . number_format($pago->monto_pagado ? $pago->monto_pagado : 0, 2) . " - {$pago->observaciones}\n";
        }
        
        echo "\n¿OPCIONES DE CORRECCIÓN?\n";
        echo "1. Eliminar pagos con monto $0.00 (recomendado si son errores)\n";
        echo "2. Investigar si deberían tener un monto específico\n";
        echo "3. Marcar como 'rechazado' en lugar de eliminar\n\n";
        
        // Analizar el patrón de estos pagos
        echo "ANÁLISIS DE PATRONES:\n";
        $observacionesComunes = $pagosApto13->groupBy('observaciones');
        foreach ($observacionesComunes as $obs => $pagos) {
            echo "- '{$obs}': {$pagos->count()} pagos\n";
        }
        
        $estadosComunes = $pagosApto13->groupBy('estado');
        foreach ($estadosComunes as $estado => $pagos) {
            echo "- Estado '{$estado}': {$pagos->count()} pagos\n";
        }
    }
    
    echo "\n=== RECOMENDACIÓN ===\n";
    echo "Los pagos con monto $0.00 parecen ser errores de importación o asignación.\n";
    echo "Se recomienda eliminarlos para limpiar la base de datos.\n";
    echo "\nPara eliminar los pagos problemáticos del apartamento 13, ejecutar:\n";
    echo "DELETE FROM pagos WHERE apartamento_id = 91 AND (monto_pagado = 0 OR monto_pagado IS NULL);\n";
    
} else {
    echo "✅ No se encontraron pagos con monto $0.00\n";
}

echo "\n=== FIN DEL ANÁLISIS ===\n";
?>