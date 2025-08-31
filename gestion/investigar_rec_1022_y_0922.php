<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== INVESTIGACIÓN REC-1022 Y REC-0922 PARA APARTAMENTO 13 ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// Investigar REC-0922
echo "=== INVESTIGACIÓN REC-0922 ===\n";
$recibo0922 = ReciboGastoComun::where('numero_recibo', 'REC-0922')->first();

if ($recibo0922) {
    echo "Recibo encontrado: {$recibo0922->numero_recibo} (ID: {$recibo0922->id})\n";
    echo "Estado: {$recibo0922->estado}\n";
    echo "Fecha vencimiento: {$recibo0922->fecha_vencimiento}\n";
    echo "Total: $" . number_format($recibo0922->total_recibo, 2) . "\n\n";
    
    // Buscar todos los pagos asociados a este recibo para el apartamento 13
    $pagos0922 = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo0922->id)
        ->get();
    
    echo "Pagos encontrados para REC-0922: {$pagos0922->count()}\n";
    
    foreach ($pagos0922 as $pago) {
        echo "  - Pago ID: {$pago->id}\n";
        echo "    Estado: {$pago->estado}\n";
        echo "    Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "    Fecha: {$pago->fecha_pago}\n";
        echo "    Observaciones: {$pago->observaciones}\n\n";
    }
} else {
    echo "❌ REC-0922 no encontrado\n\n";
}

// Investigar REC-1022
echo "=== INVESTIGACIÓN REC-1022 ===\n";
$recibo1022 = ReciboGastoComun::where('numero_recibo', 'REC-1022')->first();

if ($recibo1022) {
    echo "Recibo encontrado: {$recibo1022->numero_recibo} (ID: {$recibo1022->id})\n";
    echo "Estado: {$recibo1022->estado}\n";
    echo "Fecha vencimiento: {$recibo1022->fecha_vencimiento}\n";
    echo "Total: $" . number_format($recibo1022->total_recibo, 2) . "\n\n";
    
    // Buscar todos los pagos asociados a este recibo para el apartamento 13
    $pagos1022 = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo1022->id)
        ->get();
    
    echo "Pagos encontrados para REC-1022: {$pagos1022->count()}\n";
    
    foreach ($pagos1022 as $pago) {
        echo "  - Pago ID: {$pago->id}\n";
        echo "    Estado: {$pago->estado}\n";
        echo "    Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "    Fecha: {$pago->fecha_pago}\n";
        echo "    Observaciones: {$pago->observaciones}\n\n";
    }
} else {
    echo "❌ REC-1022 no encontrado\n\n";
}

// Verificar la vista de deudas actualizada
echo "=== SIMULACIÓN VISTA DEUDAS ACTUALIZADA ===\n";

// Simular el método show() actualizado del DeudaController
$recibosConPagos = $apartamento->pagos
    ->where('estado', '!=', 'rechazado')
    ->pluck('recibo_gasto_comun_id')
    ->unique();

echo "Recibos con pagos (excluyendo rechazados): {$recibosConPagos->count()}\n";

$recibos = ReciboGastoComun::whereIn('id', $recibosConPagos)
    ->whereIn('estado', ['activo', 'vencido'])
    ->orderBy('fecha_emision', 'desc')
    ->get();

echo "Recibos que aparecerán en la vista: {$recibos->count()}\n\n";

$detalleRecibos = [];
$contador = 0;

foreach ($recibos as $recibo) {
    $contador++;
    
    // Solo considerar pagos confirmados (excluir rechazados)
    $pagosTotales = $apartamento->pagos
        ->where('recibo_gasto_comun_id', $recibo->id)
        ->where('estado', 'confirmado')
        ->sum('monto_pagado');
    
    $saldoPendiente = $recibo->total_recibo - $pagosTotales;
    
    echo "{$contador}. {$recibo->numero_recibo}\n";
    echo "   Fecha vencimiento: {$recibo->fecha_vencimiento}\n";
    echo "   Total: $" . number_format($recibo->total_recibo, 2) . "\n";
    echo "   Pagado: $" . number_format($pagosTotales, 2) . "\n";
    echo "   Pendiente: $" . number_format($saldoPendiente, 2) . "\n";
    echo "   Estado: " . ($saldoPendiente > 0 ? 'Pendiente' : 'Pagado') . "\n\n";
    
    // Verificar si es REC-0922 o REC-1022
    if ($recibo->numero_recibo === 'REC-0922') {
        echo "   ❌ ERROR: REC-0922 aún aparece en la vista\n\n";
    }
    
    if ($recibo->numero_recibo === 'REC-1022') {
        echo "   ⚠️ ATENCIÓN: REC-1022 aparece en la vista\n";
        echo "   Verificar si debe estar asignado al apartamento 13\n\n";
    }
}

echo "=== VERIFICACIÓN FINAL ===\n";

// Contar recibos que deberían aparecer según el usuario (solo 20, último REC-0723)
$reciboUltimo = ReciboGastoComun::where('numero_recibo', 'REC-0723')->first();

if ($reciboUltimo) {
    echo "✅ REC-0723 encontrado (ID: {$reciboUltimo->id})\n";
    echo "Fecha vencimiento: {$reciboUltimo->fecha_vencimiento}\n";
    
    // Verificar si REC-0723 está asignado al apartamento 13
    $pagoRec0723 = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $reciboUltimo->id)
        ->where('estado', '!=', 'rechazado')
        ->first();
    
    if ($pagoRec0723) {
        echo "✅ REC-0723 está correctamente asignado al apartamento 13\n";
    } else {
        echo "❌ REC-0723 NO está asignado al apartamento 13\n";
    }
} else {
    echo "❌ REC-0723 no encontrado\n";
}

echo "\n=== RESUMEN ===\n";
echo "Total de recibos que aparecen en la vista: {$recibos->count()}\n";
echo "Esperado por el usuario: 20 recibos (último REC-0723)\n";

if ($recibos->count() == 20) {
    echo "✅ El número de recibos coincide con lo esperado\n";
} else {
    echo "❌ El número de recibos NO coincide con lo esperado\n";
    echo "Diferencia: " . ($recibos->count() - 20) . " recibos\n";
}

echo "\n=== INVESTIGACIÓN COMPLETADA ===\n";
?>