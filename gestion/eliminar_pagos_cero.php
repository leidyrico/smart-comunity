<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pago;
use App\Models\Apartamento;
use Illuminate\Support\Facades\DB;

echo "=== ELIMINACIÓN DE PAGOS CON MONTO $0.00 ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', '13')->first();
if (!$apartamento) {
    echo "❌ No se encontró el apartamento 13\n";
    exit(1);
}

echo "Apartamento encontrado: {$apartamento->numero} - {$apartamento->propietario} (ID: {$apartamento->id})\n\n";

// Buscar pagos problemáticos del apartamento 13
$pagosProblematicos = Pago::where('apartamento_id', $apartamento->id)
    ->where(function($query) {
        $query->where('monto_pagado', 0)
              ->orWhereNull('monto_pagado');
    })
    ->get();

echo "Pagos con monto $0.00 encontrados: {$pagosProblematicos->count()}\n\n";

if ($pagosProblematicos->count() > 0) {
    echo "DETALLES DE LOS PAGOS A ELIMINAR:\n";
    echo str_repeat('-', 60) . "\n";
    
    foreach ($pagosProblematicos as $pago) {
        echo "ID: {$pago->id} | Recibo: {$pago->recibo_gasto_comun_id} | Estado: {$pago->estado} | Obs: {$pago->observaciones}\n";
    }
    
    echo str_repeat('-', 60) . "\n";
    echo "\n¿Proceder con la eliminación? (y/N): ";
    
    // Para automatizar, vamos a proceder directamente
    echo "y\n";
    
    try {
        DB::beginTransaction();
        
        // Eliminar los pagos problemáticos
        $eliminados = Pago::where('apartamento_id', $apartamento->id)
            ->where(function($query) {
                $query->where('monto_pagado', 0)
                      ->orWhereNull('monto_pagado');
            })
            ->delete();
        
        DB::commit();
        
        echo "\n✅ ÉXITO: Se eliminaron {$eliminados} pagos con monto $0.00\n";
        
        // Verificar el estado después de la eliminación
        $pagosRestantes = Pago::where('apartamento_id', $apartamento->id)->count();
        echo "Pagos restantes para el apartamento 13: {$pagosRestantes}\n";
        
        // Verificar si quedan pagos problemáticos
        $pagosProblematicosRestantes = Pago::where('apartamento_id', $apartamento->id)
            ->where(function($query) {
                $query->where('monto_pagado', 0)
                      ->orWhereNull('monto_pagado');
            })
            ->count();
        
        if ($pagosProblematicosRestantes == 0) {
            echo "✅ No quedan pagos con monto $0.00 para el apartamento 13\n";
        } else {
            echo "⚠️  Aún quedan {$pagosProblematicosRestantes} pagos problemáticos\n";
        }
        
    } catch (Exception $e) {
        DB::rollback();
        echo "\n❌ ERROR: No se pudieron eliminar los pagos\n";
        echo "Mensaje: {$e->getMessage()}\n";
    }
    
} else {
    echo "✅ No se encontraron pagos con monto $0.00 para eliminar\n";
}

echo "\n=== FIN DE LA OPERACIÓN ===\n";
?>