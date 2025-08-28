<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

echo "=== VERIFICACIÓN DEL SALDO PENDIENTE (SUMA TOTAL DE RECIBOS) ===\n\n";

// Obtener algunos apartamentos con recibos asignados
$apartamentos = Apartamento::whereHas('pagos', function($query) {
    $query->where('estado', '!=', 'rechazado');
})->take(5)->get();

foreach ($apartamentos as $apartamento) {
    echo "Apartamento: {$apartamento->numero}\n";
    
    // Obtener recibos asignados
    $recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
        $query->where('apartamento_id', $apartamento->id)
              ->where('estado', '!=', 'rechazado');
    })->get();
    
    echo "Recibos asignados: {$recibosAsignados->count()}\n";
    
    $totalRecibos = 0;
    foreach ($recibosAsignados as $recibo) {
        echo "  - Recibo {$recibo->id}: $" . number_format($recibo->total_recibo, 2) . "\n";
        $totalRecibos += $recibo->total_recibo;
    }
    
    echo "Total recibos manual: $" . number_format($totalRecibos, 2) . "\n";
    echo "Saldo pendiente (método): $" . number_format($apartamento->saldo_pendiente, 2) . "\n";
    echo "¿Coinciden? " . ($totalRecibos == $apartamento->saldo_pendiente ? 'SÍ' : 'NO') . "\n";
    echo "---\n";
}

echo "\n=== RESUMEN GENERAL ===\n";
$totalApartamentos = Apartamento::count();
$apartamentosConRecibos = Apartamento::whereHas('pagos', function($query) {
    $query->where('estado', '!=', 'rechazado');
})->count();

echo "Total apartamentos: {$totalApartamentos}\n";
echo "Apartamentos con recibos asignados: {$apartamentosConRecibos}\n";

// Calcular saldo pendiente total del sistema
$saldoTotalSistema = Apartamento::all()->sum('saldo_pendiente');
echo "Saldo pendiente total del sistema: $" . number_format($saldoTotalSistema, 2) . "\n";

echo "\nVerificación completada.\n";