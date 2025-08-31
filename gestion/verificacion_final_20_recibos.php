<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== VERIFICACIÓN FINAL: 20 RECIBOS PARA APARTAMENTO 13 ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "❌ Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// === SIMULACIÓN EXACTA DE LA VISTA DE DEUDAS ACTUALIZADA ===
echo "=== SIMULACIÓN VISTA DEUDAS (DeudaController::show) ===\n";

// Obtener solo los recibos que tienen pagos asociados a este apartamento
// Excluir pagos rechazados para no mostrar recibos desasignados
$recibosConPagos = $apartamento->pagos
    ->where('estado', '!=', 'rechazado')
    ->pluck('recibo_gasto_comun_id')
    ->unique();

echo "Recibos con pagos activos: {$recibosConPagos->count()}\n";

$recibos = ReciboGastoComun::whereIn('id', $recibosConPagos)
    ->whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_emision', 'desc')
    ->get();

echo "Recibos que aparecerán en la vista: {$recibos->count()}\n\n";

// Verificar que sean exactamente 20
if ($recibos->count() == 20) {
    echo "✅ PERFECTO: Se muestran exactamente 20 recibos\n\n";
} else {
    echo "❌ ERROR: Se muestran {$recibos->count()} recibos, esperados 20\n\n";
}

// Mostrar los recibos en orden
echo "=== LISTA DE RECIBOS (ORDEN DE APARICIÓN EN LA VISTA) ===\n";

$detalleRecibos = [];
$totalPendienteGeneral = 0;
$ultimoRecibo = null;

foreach ($recibos as $index => $recibo) {
    // Solo considerar pagos confirmados (excluir rechazados)
    $pagosTotales = $apartamento->pagos
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $pagosTotales;
    $totalPendienteGeneral += $saldoPendiente;
    
    echo ($index + 1) . ". {$recibo->numero_recibo}\n";
    echo "   Fecha emisión: {$recibo->fecha_emision}\n";
    echo "   Fecha vencimiento: {$recibo->fecha_vencimiento}\n";
    echo "   Total: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "   Pagado: $" . number_format($pagosTotales, 2) . "\n";
    echo "   Pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    echo "   Estado: " . ($saldoPendiente > 0 ? 'Pendiente' : 'Pagado') . "\n\n";
    
    // Guardar el último recibo (el de fecha más antigua)
    if ($index == $recibos->count() - 1) {
        $ultimoRecibo = $recibo;
    }
}

echo "=== VERIFICACIONES ESPECÍFICAS ===\n";

// Verificar que REC-0922 NO aparece
$tieneRec0922 = $recibos->where('numero_recibo', 'REC-0922')->count() > 0;
if ($tieneRec0922) {
    echo "❌ ERROR: REC-0922 aún aparece en la vista\n";
} else {
    echo "✅ REC-0922 NO aparece (correcto)\n";
}

// Verificar que REC-1022 NO aparece
$tieneRec1022 = $recibos->where('numero_recibo', 'REC-1022')->count() > 0;
if ($tieneRec1022) {
    echo "❌ ERROR: REC-1022 aún aparece en la vista\n";
} else {
    echo "✅ REC-1022 NO aparece (correcto)\n";
}

// Verificar que REC-0723 aparece
$tieneRec0723 = $recibos->where('numero_recibo', 'REC-0723')->count() > 0;
if ($tieneRec0723) {
    echo "✅ REC-0723 aparece en la vista (correcto)\n";
} else {
    echo "❌ ERROR: REC-0723 NO aparece en la vista\n";
}

// Verificar que REC-0723 es el último recibo (más antiguo)
if ($ultimoRecibo && $ultimoRecibo->numero_recibo == 'REC-0723') {
    echo "✅ REC-0723 es el último recibo en la lista (correcto)\n";
} else {
    echo "❌ ERROR: REC-0723 NO es el último recibo\n";
    if ($ultimoRecibo) {
        echo "   Último recibo actual: {$ultimoRecibo->numero_recibo}\n";
    }
}

echo "\n=== RESUMEN FINAL ===\n";
echo "Total de recibos mostrados: {$recibos->count()}\n";
echo "Esperado: 20 recibos\n";
echo "Último recibo: " . ($ultimoRecibo ? $ultimoRecibo->numero_recibo : 'N/A') . "\n";
echo "Esperado último: REC-0723\n";
echo "Total pendiente: $" . number_format($totalPendienteGeneral, 2) . "\n\n";

// Verificación de estado de pagos rechazados
echo "=== ESTADO DE PAGOS RECHAZADOS ===\n";

$pagosRechazados = Pago::where('apartamento_id', $apartamento->id)
    ->where('estado', 'rechazado')
    ->with('reciboGastoComun')
    ->get();

echo "Pagos rechazados para apartamento 13: {$pagosRechazados->count()}\n";

foreach ($pagosRechazados as $pago) {
    echo "- Pago ID: {$pago->id}\n";
    echo "  Recibo: {$pago->reciboGastoComun->numero_recibo}\n";
    echo "  Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "  Observaciones: {$pago->observaciones}\n\n";
}

if ($recibos->count() == 20 && 
    !$tieneRec0922 && 
    !$tieneRec1022 && 
    $tieneRec0723 && 
    $ultimoRecibo && $ultimoRecibo->numero_recibo == 'REC-0723') {
    
    echo "🎉 ¡ÉXITO TOTAL! 🎉\n";
    echo "✅ Exactamente 20 recibos\n";
    echo "✅ REC-0922 eliminado\n";
    echo "✅ REC-1022 eliminado\n";
    echo "✅ REC-0723 presente y es el último\n";
    echo "✅ Vista de deudas corregida completamente\n";
    
} else {
    echo "❌ Aún hay problemas que resolver:\n";
    if ($recibos->count() != 20) echo "   - Número de recibos incorrecto\n";
    if ($tieneRec0922) echo "   - REC-0922 aún aparece\n";
    if ($tieneRec1022) echo "   - REC-1022 aún aparece\n";
    if (!$tieneRec0723) echo "   - REC-0723 no aparece\n";
    if (!$ultimoRecibo || $ultimoRecibo->numero_recibo != 'REC-0723') echo "   - REC-0723 no es el último\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
?>