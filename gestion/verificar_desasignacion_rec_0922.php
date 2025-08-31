<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== VERIFICACIÓN POST-DESASIGNACIÓN REC-0922 ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// Simular exactamente la lógica del método createGlobal corregido
echo "=== SIMULACIÓN DEL MÉTODO createGlobal DESPUÉS DE DESASIGNACIÓN ===\n";

// Obtener solo los recibos asignados al apartamento a través de la tabla pagos
// EXCLUYENDO los pagos rechazados
$recibosAsignados = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id)
          ->where('estado', '!=', 'rechazado'); // Excluir pagos rechazados
})->with(['pagos' => function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id)
          ->where('estado', '!=', 'rechazado'); // Excluir pagos rechazados
}])->orderBy('fecha_vencimiento', 'asc')->get();

echo "Recibos asignados encontrados (excluyendo rechazados): " . $recibosAsignados->count() . "\n\n";

// Verificar específicamente si REC-0922 aparece
$rec0922Encontrado = false;
$recibosConSaldo = collect();

foreach ($recibosAsignados as $recibo) {
    if ($recibo->numero_recibo === 'REC-0922') {
        $rec0922Encontrado = true;
        echo "⚠️ REC-0922 AÚN APARECE en los recibos asignados\n";
    }
    
    $totalPagado = $recibo->pagos->where('estado', 'confirmado')->sum('monto_pagado');
    $saldoPendiente = $recibo->total_recibo - $totalPagado;
    
    if ($saldoPendiente > 0) {
        $recibo->saldo_pendiente_calculado = $saldoPendiente;
        $recibosConSaldo->push($recibo);
    }
}

if (!$rec0922Encontrado) {
    echo "✅ REC-0922 NO aparece en los recibos asignados (correcto)\n";
}

echo "\nRecibos con saldo pendiente: " . $recibosConSaldo->count() . "\n\n";

// Mostrar los primeros 10 recibos para verificación
echo "=== PRIMEROS 10 RECIBOS CON SALDO PENDIENTE ===\n";
$contador = 0;
foreach ($recibosConSaldo as $recibo) {
    if ($contador >= 10) break;
    
    echo ($contador + 1) . ". {$recibo->numero_recibo} - Pendiente: $" . number_format($recibo->saldo_pendiente_calculado, 2) . "\n";
    $contador++;
}

if ($recibosConSaldo->count() > 10) {
    echo "... y " . ($recibosConSaldo->count() - 10) . " recibos más\n";
}

// Verificar el estado actual del pago de REC-0922
echo "\n=== ESTADO ACTUAL DEL PAGO REC-0922 ===\n";
$recibo0922 = ReciboGastoComun::where('numero_recibo', 'REC-0922')->first();

if ($recibo0922) {
    $pagosRec0922 = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo0922->id)
        ->get();
    
    if ($pagosRec0922->count() > 0) {
        foreach ($pagosRec0922 as $pago) {
            echo "Pago ID {$pago->id}: Estado = {$pago->estado}\n";
            echo "Observaciones: {$pago->observaciones}\n";
        }
    } else {
        echo "No hay pagos de REC-0922 para el apartamento 13\n";
    }
} else {
    echo "REC-0922 no encontrado en la base de datos\n";
}

// Verificar también con la lógica original (sin filtro de rechazados)
echo "\n=== COMPARACIÓN: LÓGICA SIN FILTRO DE RECHAZADOS ===\n";
$recibosAsignadosSinFiltro = ReciboGastoComun::whereHas('pagos', function($query) use ($apartamento) {
    $query->where('apartamento_id', $apartamento->id);
})->count();

echo "Recibos asignados (incluyendo rechazados): {$recibosAsignadosSinFiltro}\n";
echo "Recibos asignados (excluyendo rechazados): " . $recibosAsignados->count() . "\n";
echo "Diferencia: " . ($recibosAsignadosSinFiltro - $recibosAsignados->count()) . " recibo(s) rechazado(s)\n\n";

echo "=== RESUMEN ===\n";
if (!$rec0922Encontrado) {
    echo "✅ ÉXITO: REC-0922 ha sido correctamente desasignado del apartamento 13\n";
    echo "✅ Ya no aparece en la vista de pagos globales\n";
    echo "✅ El pago se mantiene en la base de datos con estado 'rechazado' para historial\n";
} else {
    echo "❌ PROBLEMA: REC-0922 aún aparece en los recibos asignados\n";
    echo "❌ Puede ser necesario revisar la lógica de filtrado\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
?>