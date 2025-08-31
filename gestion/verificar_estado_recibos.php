<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReciboGastoComun;
use App\Models\Apartamento;

echo "=== VERIFICACIÓN ESTADO DE RECIBOS ===\n\n";

// Contar recibos por estado
$estadosRecibos = ReciboGastoComun::selectRaw('estado, COUNT(*) as total')
    ->groupBy('estado')
    ->get();

echo "Estados de recibos en el sistema:\n";
foreach ($estadosRecibos as $estado) {
    echo "- {$estado->estado}: {$estado->total} recibos\n";
}

echo "\n=== ANÁLISIS DETALLADO ===\n";

// Verificar fechas de vencimiento
$recibosPorFecha = ReciboGastoComun::selectRaw('DATE(fecha_vencimiento) as fecha, COUNT(*) as total, estado')
    ->groupBy('fecha_vencimiento', 'estado')
    ->orderBy('fecha_vencimiento')
    ->get();

echo "\nRecibos por fecha de vencimiento:\n";
foreach ($recibosPorFecha as $recibo) {
    echo "- {$recibo->fecha} ({$recibo->estado}): {$recibo->total} recibos\n";
}

// Verificar si hay recibos que deberían estar vigentes
$fechaActual = now();
echo "\nFecha actual: {$fechaActual->format('Y-m-d')}\n";

$recibosVigentes = ReciboGastoComun::where('fecha_vencimiento', '>', $fechaActual)
    ->where('estado', '!=', 'vencido')
    ->count();

$recibosQueDeberianEstarVigentes = ReciboGastoComun::where('fecha_vencimiento', '>', $fechaActual)
    ->where('estado', 'vencido')
    ->count();

echo "Recibos vigentes (no vencidos): {$recibosVigentes}\n";
echo "Recibos marcados como vencidos pero con fecha futura: {$recibosQueDeberianEstarVigentes}\n";

// Mostrar algunos ejemplos de recibos problemáticos
if ($recibosQueDeberianEstarVigentes > 0) {
    echo "\n=== RECIBOS PROBLEMÁTICOS (marcados vencidos pero con fecha futura) ===\n";
    $ejemplos = ReciboGastoComun::where('fecha_vencimiento', '>', $fechaActual)
        ->where('estado', 'vencido')
        ->limit(10)
        ->get();
    
    foreach ($ejemplos as $recibo) {
        echo "ID: {$recibo->id}, Fecha vencimiento: {$recibo->fecha_vencimiento}, Estado: {$recibo->estado}\n";
    }
}

// Verificar apartamentos con recibos vencidos
echo "\n=== APARTAMENTOS CON RECIBOS VENCIDOS ===\n";

$apartamentosConRecibosVencidos = Apartamento::whereHas('pagos', function($query) {
    $query->whereHas('reciboGastoComun', function($subQuery) {
        $subQuery->where('estado', 'vencido');
    })->where('estado', '!=', 'rechazado');
})->with(['pagos' => function($query) {
    $query->whereHas('reciboGastoComun', function($subQuery) {
        $subQuery->where('estado', 'vencido');
    })->where('estado', '!=', 'rechazado');
}])->get();

echo "Total apartamentos con recibos vencidos: {$apartamentosConRecibosVencidos->count()}\n\n";

foreach ($apartamentosConRecibosVencidos->take(10) as $apartamento) {
    $recibosVencidos = $apartamento->pagos->count();
    echo "Apartamento {$apartamento->numero}: {$recibosVencidos} recibos vencidos (Estatus: {$apartamento->estatus_financiero})\n";
}

echo "\n✅ Verificación completada.\n";