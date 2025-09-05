<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simular una request HTTP
$request = Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $request);

use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

echo "=== INVESTIGACIÓN DE PAGOS CON MONTO $0.00 ===\n\n";

// Buscar todos los pagos con monto 0
$pagosCero = Pago::where('monto_pagado', 0)
    ->with(['apartamento', 'reciboGastoComun'])
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total de pagos con monto $0.00: " . $pagosCero->count() . "\n\n";

if ($pagosCero->count() > 0) {
    echo "=== ANÁLISIS DE PAGOS CON MONTO $0.00 ===\n";
    
    // Agrupar por observaciones
    $porObservaciones = $pagosCero->groupBy('observaciones');
    
    foreach ($porObservaciones as $observacion => $pagos) {
        echo "\n📝 Observación: {$observacion}\n";
        echo "   Cantidad: " . $pagos->count() . " pagos\n";
        
        // Mostrar algunos ejemplos
        $ejemplos = $pagos->take(3);
        foreach ($ejemplos as $pago) {
            echo "   - Apartamento: {$pago->apartamento->numero}";
            echo " | Recibo: " . ($pago->reciboGastoComun ? $pago->reciboGastoComun->numero_recibo : 'N/A');
            echo " | Estado: {$pago->estado}";
            echo " | Fecha creación: {$pago->created_at}";
            echo " | Fecha pago: " . ($pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'N/A') . "\n";
        }
    }
    
    // Buscar pagos con fecha específica 04/09/2025
    echo "\n=== PAGOS CON FECHA 04/09/2025 ===\n";
    $pagosFecha = $pagosCero->filter(function($pago) {
        return $pago->fecha_pago && $pago->fecha_pago->format('d/m/Y') === '04/09/2025';
    });
    
    if ($pagosFecha->count() > 0) {
        echo "Encontrados " . $pagosFecha->count() . " pagos con fecha 04/09/2025:\n";
        
        foreach ($pagosFecha->take(10) as $pago) {
            echo "- ID: {$pago->id}";
            echo " | Apartamento: {$pago->apartamento->numero}";
            echo " | Recibo: " . ($pago->reciboGastoComun ? $pago->reciboGastoComun->numero_recibo : 'N/A');
            echo " | Observaciones: {$pago->observaciones}";
            echo " | Estado: {$pago->estado}\n";
        }
    } else {
        echo "No se encontraron pagos con fecha exacta 04/09/2025\n";
    }
    
    // Verificar fechas de pago más comunes
    echo "\n=== FECHAS DE PAGO MÁS COMUNES EN PAGOS $0.00 ===\n";
    $fechasPago = $pagosCero->filter(function($pago) {
        return $pago->fecha_pago !== null;
    })->groupBy(function($pago) {
        return $pago->fecha_pago->format('d/m/Y');
    });
    
    foreach ($fechasPago->sortByDesc(function($pagos) {
        return $pagos->count();
    })->take(10) as $fecha => $pagos) {
        echo "📅 {$fecha}: " . $pagos->count() . " pagos\n";
    }
    
    // Verificar estados
    echo "\n=== ESTADOS DE PAGOS $0.00 ===\n";
    $porEstado = $pagosCero->groupBy('estado');
    
    foreach ($porEstado as $estado => $pagos) {
        echo "🔄 {$estado}: " . $pagos->count() . " pagos\n";
    }
    
    // Verificar apartamentos más afectados
    echo "\n=== APARTAMENTOS CON MÁS PAGOS $0.00 ===\n";
    $porApartamento = $pagosCero->groupBy('apartamento_id');
    
    foreach ($porApartamento->sortByDesc(function($pagos) {
        return $pagos->count();
    })->take(10) as $apartamentoId => $pagos) {
        $apartamento = $pagos->first()->apartamento;
        echo "🏠 Apartamento {$apartamento->numero}: " . $pagos->count() . " pagos $0.00\n";
    }
    
} else {
    echo "No se encontraron pagos con monto $0.00\n";
}

echo "\n=== INVESTIGACIÓN COMPLETADA ===\n";