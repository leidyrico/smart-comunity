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

echo "=== VERIFICACIÓN DE REC-0922 Y APARTAMENTO 13 ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::find(91);
if (!$apartamento) {
    echo "❌ Apartamento 91 no encontrado\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// Buscar recibo REC-0922
$recibo0922 = ReciboGastoComun::where('numero_recibo', 'REC-0922')->first();

if (!$recibo0922) {
    echo "❌ Recibo REC-0922 no encontrado en la base de datos\n";
    exit;
}

echo "=== INFORMACIÓN DEL RECIBO REC-0922 ===\n";
echo "ID: {$recibo0922->id}\n";
echo "Número: {$recibo0922->numero_recibo}\n";
echo "Total: $" . number_format($recibo0922->total_recibo, 2) . "\n";
echo "Estado: {$recibo0922->estado}\n";
echo "Fecha emisión: {$recibo0922->fecha_emision}\n";
echo "Fecha vencimiento: {$recibo0922->fecha_vencimiento}\n\n";

// Verificar si hay pagos de apartamento 13 para REC-0922
echo "=== PAGOS DEL APARTAMENTO 13 PARA REC-0922 ===\n";
$pagosRec0922 = Pago::where('apartamento_id', $apartamento->id)
    ->where('recibo_gasto_comun_id', $recibo0922->id)
    ->get();

if ($pagosRec0922->count() > 0) {
    echo "✅ Se encontraron {$pagosRec0922->count()} registro(s) de pago:\n\n";
    
    foreach ($pagosRec0922 as $pago) {
        echo "Pago ID: {$pago->id}\n";
        echo "Monto pagado: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "Estado: {$pago->estado}\n";
        echo "Fecha pago: {$pago->fecha_pago}\n";
        echo "Método: {$pago->metodo_pago}\n";
        echo "Observaciones: {$pago->observaciones}\n";
        echo "Creado: {$pago->created_at}\n";
        echo "Actualizado: {$pago->updated_at}\n\n";
    }
    
    // Calcular saldo pendiente
    $totalPagado = $pagosRec0922->where('estado', 'confirmado')->sum('monto_pagado');
    $saldoPendiente = $recibo0922->total_recibo - $totalPagado;
    
    echo "Total pagado (confirmado): $" . number_format($totalPagado, 2) . "\n";
    echo "Saldo pendiente: $" . number_format($saldoPendiente, 2) . "\n\n";
    
} else {
    echo "❌ NO se encontraron pagos del apartamento 13 para REC-0922\n\n";
}

// Verificar todos los apartamentos que tienen pagos para REC-0922
echo "=== TODOS LOS APARTAMENTOS CON PAGOS PARA REC-0922 ===\n";
$todosLosPagosRec0922 = DB::table('pagos')
    ->join('apartamentos', 'pagos.apartamento_id', '=', 'apartamentos.id')
    ->where('pagos.recibo_gasto_comun_id', $recibo0922->id)
    ->select('apartamentos.numero', 'apartamentos.id as apt_id', 'pagos.*')
    ->get();

if ($todosLosPagosRec0922->count() > 0) {
    echo "Apartamentos con pagos para REC-0922: {$todosLosPagosRec0922->count()}\n\n";
    
    foreach ($todosLosPagosRec0922 as $pago) {
        echo "Apartamento {$pago->numero} (ID: {$pago->apt_id}):\n";
        echo "  Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
        echo "  Estado: {$pago->estado}\n";
        echo "  Creado: {$pago->created_at}\n\n";
    }
} else {
    echo "❌ NO hay pagos para REC-0922 en ningún apartamento\n\n";
}

// Verificar si REC-0922 debería estar asignado al apartamento 13
echo "=== ANÁLISIS DE ASIGNACIÓN ===\n";

// Verificar la fecha del recibo vs el apartamento
echo "Fecha del recibo REC-0922: {$recibo0922->fecha_vencimiento}\n";
echo "Estado del recibo: {$recibo0922->estado}\n";
echo "Estatus financiero del apartamento 13: {$apartamento->estatus_financiero}\n\n";

// Verificar si hay una lógica específica de asignación
echo "=== RECOMENDACIÓN ===\n";
if ($pagosRec0922->count() > 0) {
    echo "⚠️ REC-0922 ESTÁ asignado al apartamento 13\n";
    echo "\nPosibles razones:\n";
    echo "1. Asignación automática durante importación masiva\n";
    echo "2. Asignación manual incorrecta\n";
    echo "3. Lógica de sistema que asigna todos los recibos a todos los apartamentos\n\n";
    
    echo "Para DESASIGNAR REC-0922 del apartamento 13:\n";
    echo "1. Eliminar registros de la tabla 'pagos' donde apartamento_id=91 y recibo_gasto_comun_id={$recibo0922->id}\n";
    echo "2. O cambiar el estado a 'rechazado' si se quiere mantener el historial\n\n";
    
    echo "SQL para eliminar:\n";
    echo "DELETE FROM pagos WHERE apartamento_id = 91 AND recibo_gasto_comun_id = {$recibo0922->id};\n\n";
    
    echo "SQL para rechazar:\n";
    echo "UPDATE pagos SET estado = 'rechazado', observaciones = 'Recibo no corresponde al apartamento' WHERE apartamento_id = 91 AND recibo_gasto_comun_id = {$recibo0922->id};\n";
    
} else {
    echo "✅ REC-0922 NO está asignado al apartamento 13\n";
    echo "El problema puede estar en otro lugar del código.\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
?>