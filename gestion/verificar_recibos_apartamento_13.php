<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DE RECIBOS ASIGNADOS AL APARTAMENTO 13 ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::where('numero', '13')->first();
if (!$apartamento) {
    echo "❌ No se encontró el apartamento 13\n";
    exit(1);
}

echo "Apartamento encontrado: {$apartamento->numero} - {$apartamento->propietario} (ID: {$apartamento->id})\n\n";

// Método 1: Contar todos los recibos en el sistema
$totalRecibos = ReciboGastoComun::count();
echo "Total de recibos en el sistema: {$totalRecibos}\n";

// Método 2: Contar recibos activos/vencidos
$recibosActivos = ReciboGastoComun::whereIn('estado', ['activo', 'vencido'])->count();
echo "Recibos activos/vencidos: {$recibosActivos}\n";

// Método 3: Verificar qué recibos tienen pagos del apartamento 13
$recibosConPagos = DB::table('pagos')
    ->where('apartamento_id', $apartamento->id)
    ->distinct()
    ->count('recibo_gasto_comun_id');
echo "Recibos con pagos del apartamento 13: {$recibosConPagos}\n\n";

// Método 4: Listar todos los recibos con sus estados
echo "=== LISTADO DE TODOS LOS RECIBOS ===\n";
$recibos = ReciboGastoComun::orderBy('fecha_vencimiento', 'asc')->get();

echo "Total de recibos encontrados: {$recibos->count()}\n\n";

foreach ($recibos as $index => $recibo) {
    // Verificar si tiene pagos del apartamento 13
    $tienePagos = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->exists();
    
    $totalPagado = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    $marcador = $tienePagos ? '✓' : ' ';
    $estadoPago = $saldoPendiente > 0 ? 'PENDIENTE' : ($totalPagado > 0 ? 'PAGADO' : 'SIN PAGOS');
    
    echo sprintf(
        "%2d. [%s] %s | $%s | %s | %s | %s\n",
        $index + 1,
        $marcador,
        $recibo->numero_recibo,
        number_format($recibo->total_recibo, 2),
        $recibo->estado,
        $recibo->fecha_vencimiento,
        $estadoPago
    );
}

// Método 5: Análisis específico de asignación
echo "\n=== ANÁLISIS DE ASIGNACIÓN ===\n";

// Verificar si todos los recibos están "asignados" a todos los apartamentos
$apartamentos = Apartamento::all();
echo "Total de apartamentos: {$apartamentos->count()}\n";

// Verificar la lógica de asignación
echo "\n¿Cómo se determina si un recibo está 'asignado' a un apartamento?\n";
echo "- Opción A: Todos los recibos están asignados a todos los apartamentos (sistema global)\n";
echo "- Opción B: Solo recibos con pagos están asignados\n";
echo "- Opción C: Hay una tabla de asignación específica\n\n";

// Verificar si existe una tabla de asignación
$tablas = DB::select("SHOW TABLES LIKE '%asignacion%'");
if (count($tablas) > 0) {
    echo "Tablas de asignación encontradas:\n";
    foreach ($tablas as $tabla) {
        echo "- " . array_values((array)$tabla)[0] . "\n";
    }
} else {
    echo "No se encontraron tablas de asignación específicas.\n";
}

// Verificar la lógica del controlador de deudas
echo "\n=== RECOMENDACIÓN ===\n";
echo "Según la URL proporcionada (/deudas), el apartamento 13 debería tener 20 recibos.\n";
echo "Actualmente el sistema muestra {$recibos->count()} recibos en total.\n";
echo "\nPosibles causas de la discrepancia:\n";
echo "1. Algunos recibos fueron creados después de la fecha esperada\n";
echo "2. Hay recibos duplicados o erróneos\n";
echo "3. La lógica de filtrado en /deudas es diferente\n";
echo "4. Algunos recibos deberían estar marcados como 'rechazado'\n";

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
?>