<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DE TOTALES DE RECIBOS ===\n\n";

// Obtener algunos recibos para verificar sus totales
$recibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

echo "Últimos 10 recibos en la base de datos:\n\n";

foreach ($recibos as $recibo) {
    echo "📋 Recibo {$recibo->numero_recibo} (ID: {$recibo->id})\n";
    echo "   Estado: {$recibo->estado}\n";
    echo "   Valor administración: $" . number_format($recibo->valor_administracion ?? 0, 2) . "\n";
    echo "   Valor aseo: $" . number_format($recibo->valor_aseo ?? 0, 2) . "\n";
    echo "   Valor vigilancia: $" . number_format($recibo->valor_vigilancia ?? 0, 2) . "\n";
    echo "   Valor mantenimiento: $" . number_format($recibo->valor_mantenimiento ?? 0, 2) . "\n";
    echo "   Otros conceptos: $" . number_format($recibo->otros_conceptos ?? 0, 2) . "\n";
    echo "   Total recibo: $" . number_format($recibo->total_recibo ?? 0, 2) . "\n";
    echo "   Fecha vencimiento: {$recibo->fecha_vencimiento}\n\n";
}

// Verificar si hay recibos con totales > 0
$recibosConTotal = ReciboGastoComun::where('total_recibo', '>', 0)
    ->whereIn('estado', ['activo', 'vencido'])
    ->count();

echo "=== ESTADÍSTICAS ===\n";
echo "Recibos con total > 0: {$recibosConTotal}\n";

$totalRecibos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->count();
echo "Total recibos activos/vencidos: {$totalRecibos}\n";

// Verificar estructura de la tabla
echo "\n=== ESTRUCTURA DE LA TABLA ===\n";
$columns = DB::select("DESCRIBE recibo_gasto_comuns");
foreach ($columns as $column) {
    echo "- {$column->Field}: {$column->Type}\n";
}

// Verificar si hay algún problema con el campo total_recibo
echo "\n=== VERIFICACIÓN CAMPO TOTAL_RECIBO ===\n";
$recibosNulos = ReciboGastoComun::whereNull('total_recibo')->count();
echo "Recibos con total_recibo NULL: {$recibosNulos}\n";

$recibosCero = ReciboGastoComun::where('total_recibo', 0)->count();
echo "Recibos con total_recibo = 0: {$recibosCero}\n";

$recibosPositivos = ReciboGastoComun::where('total_recibo', '>', 0)->count();
echo "Recibos con total_recibo > 0: {$recibosPositivos}\n";