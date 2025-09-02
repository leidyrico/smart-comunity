<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== ANÁLISIS DE OBSERVACIONES EN PAGOS ===\n\n";

// Verificar todas las observaciones únicas en pagos
$observacionesUnicas = Pago::select('observaciones')
    ->distinct()
    ->whereNotNull('observaciones')
    ->where('observaciones', '!=', '')
    ->pluck('observaciones');

echo "Observaciones únicas encontradas en pagos:\n";
foreach ($observacionesUnicas as $obs) {
    $count = Pago::where('observaciones', $obs)->count();
    echo "- '$obs' (" . $count . " pagos)\n";
}

echo "\n=== RECIBOS Y SUS PAGOS ===\n\n";

// Verificar recibos vencidos y sus pagos
$recibosVencidos = ReciboGastoComun::where('estado', 'vencido')
    ->with('pagos')
    ->take(5)
    ->get();

echo "Primeros 5 recibos vencidos:\n";
foreach ($recibosVencidos as $recibo) {
    echo "Recibo ID: {$recibo->id}, Total: {$recibo->total_recibo}, Pagos: {$recibo->pagos->count()}\n";
    
    foreach ($recibo->pagos as $pago) {
        echo "  - Pago ID: {$pago->id}, Monto: {$pago->monto_pagado}, Obs: '{$pago->observaciones}'\n";
    }
    echo "\n";
}

echo "\n=== RECIBOS CON FILTRO ACTUAL ===\n\n";

// Verificar recibos con el filtro actual
$recibosConFiltro = ReciboGastoComun::where('estado', 'vencido')
    ->whereHas('pagos', function($query) {
        $query->where(function($subQuery) {
            $subQuery->where('observaciones', 'like', '%Asignación manual%')
                     ->orWhere('observaciones', 'like', '%Pago global distribuido automáticamente%');
        });
    })
    ->count();

echo "Recibos vencidos con filtro de observaciones: $recibosConFiltro\n";

// Verificar recibos sin filtro
$recibosSinFiltro = ReciboGastoComun::where('estado', 'vencido')->count();
echo "Recibos vencidos sin filtro: $recibosSinFiltro\n";

echo "\n=== APARTAMENTO 122 ESPECÍFICO ===\n\n";

// Verificar apartamento 122 específicamente
use App\Models\Apartamento;

$apartamento122 = Apartamento::where('numero', 122)->with('pagos.reciboGastoComun')->first();
if ($apartamento122) {
    echo "Apartamento 122 encontrado, pagos: {$apartamento122->pagos->count()}\n";
    
    foreach ($apartamento122->pagos->take(5) as $pago) {
        $recibo = $pago->reciboGastoComun;
        echo "  - Pago: {$pago->monto_pagado}, Recibo ID: {$pago->recibo_gasto_comun_id}, Estado recibo: {$recibo->estado}, Obs: '{$pago->observaciones}'\n";
    }
} else {
    echo "Apartamento 122 no encontrado\n";
}

?>