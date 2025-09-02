<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ESTADOS DE RECIBOS ===\n";

$estados = \App\Models\ReciboGastoComun::selectRaw('estado, count(*) as total')
    ->groupBy('estado')
    ->get();

foreach ($estados as $estado) {
    echo "Estado: {$estado->estado} - Total: {$estado->total}\n";
}

echo "\n=== TOTAL DE RECIBOS ===\n";
echo "Total: " . \App\Models\ReciboGastoComun::count() . "\n";

echo "\n=== RECIBOS CON PAGOS ===\n";
$recibosConPagos = \App\Models\ReciboGastoComun::whereHas('pagos')->count();
echo "Recibos con pagos: {$recibosConPagos}\n";

echo "\n=== PRIMEROS 5 RECIBOS ===\n";
$primeros = \App\Models\ReciboGastoComun::take(5)->get(['id', 'numero_recibo', 'estado', 'periodo']);
foreach ($primeros as $recibo) {
    echo "ID: {$recibo->id}, Número: {$recibo->numero_recibo}, Estado: {$recibo->estado}, Período: {$recibo->periodo}\n";
}