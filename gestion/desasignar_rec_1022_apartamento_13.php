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

echo "=== DESASIGNAR REC-1022 DEL APARTAMENTO 13 ===\n\n";

// Obtener apartamento 13 (ID 91)
$apartamento = Apartamento::find(91);

if (!$apartamento) {
    echo "❌ Error: No se encontró el apartamento con ID 91\n";
    exit;
}

echo "Apartamento encontrado: {$apartamento->numero} (ID: {$apartamento->id})\n\n";

// Buscar REC-1022
$recibo1022 = ReciboGastoComun::where('numero_recibo', 'REC-1022')->first();

if (!$recibo1022) {
    echo "❌ Error: No se encontró el recibo REC-1022\n";
    exit;
}

echo "Recibo encontrado: {$recibo1022->numero_recibo} (ID: {$recibo1022->id})\n";
echo "Fecha vencimiento: {$recibo1022->fecha_vencimiento}\n";
echo "Total: $" . number_format($recibo1022->total_recibo, 2) . "\n\n";

// Buscar pagos asociados a REC-1022 para el apartamento 13
$pagosRec1022 = Pago::where('apartamento_id', $apartamento->id)
    ->where('recibo_gasto_comun_id', $recibo1022->id)
    ->get();

echo "Pagos encontrados para REC-1022: {$pagosRec1022->count()}\n\n";

if ($pagosRec1022->count() == 0) {
    echo "✅ No hay pagos asociados a REC-1022 para el apartamento 13\n";
    echo "El recibo ya está desasignado\n";
    exit;
}

// Mostrar detalles de los pagos antes de la modificación
echo "=== DETALLES DE PAGOS ANTES DE LA MODIFICACIÓN ===\n";
foreach ($pagosRec1022 as $pago) {
    echo "Pago ID: {$pago->id}\n";
    echo "Estado: {$pago->estado}\n";
    echo "Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "Fecha: {$pago->fecha_pago}\n";
    echo "Observaciones: {$pago->observaciones}\n\n";
}

// Crear backup de los pagos antes de modificar
$timestamp = date('Y-m-d_H-i-s');
$backupFile = "backup_pagos_rec_1022_apartamento_13_{$timestamp}.sql";

echo "Creando backup en: {$backupFile}\n";

$backupContent = "-- Backup de pagos REC-1022 apartamento 13 - {$timestamp}\n";
$backupContent .= "-- Ejecutar este script para restaurar los pagos si es necesario\n\n";

foreach ($pagosRec1022 as $pago) {
    $backupContent .= "UPDATE pagos SET estado = '{$pago->estado}', observaciones = '" . addslashes($pago->observaciones) . "' WHERE id = {$pago->id};\n";
}

file_put_contents($backupFile, $backupContent);
echo "✅ Backup creado exitosamente\n\n";

// Proceder con la desasignación
echo "=== INICIANDO DESASIGNACIÓN ===\n";

try {
    DB::beginTransaction();
    
    $pagosModificados = 0;
    
    foreach ($pagosRec1022 as $pago) {
        // Guardar observaciones originales
        $observacionesOriginales = $pago->observaciones;
        
        // Actualizar el estado a 'rechazado' y agregar observaciones
        $pago->estado = 'rechazado';
        $pago->observaciones = '[DESASIGNADO] Recibo no corresponde al apartamento 13. Original: ' . $observacionesOriginales;
        $pago->save();
        
        echo "✅ Pago ID {$pago->id} actualizado a estado 'rechazado'\n";
        $pagosModificados++;
    }
    
    DB::commit();
    
    echo "\n=== DESASIGNACIÓN COMPLETADA ===\n";
    echo "Pagos modificados: {$pagosModificados}\n";
    echo "Estado cambiado a: rechazado\n";
    echo "Backup guardado en: {$backupFile}\n\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error durante la desasignación: " . $e->getMessage() . "\n";
    exit;
}

// Verificar el resultado
echo "=== VERIFICACIÓN POST-DESASIGNACIÓN ===\n";

$pagosActualizados = Pago::where('apartamento_id', $apartamento->id)
    ->where('recibo_gasto_comun_id', $recibo1022->id)
    ->get();

foreach ($pagosActualizados as $pago) {
    echo "Pago ID: {$pago->id}\n";
    echo "Estado: {$pago->estado}\n";
    echo "Observaciones: {$pago->observaciones}\n\n";
}

// Verificar que REC-1022 ya no aparezca en la vista
echo "=== VERIFICACIÓN EN VISTA DE DEUDAS ===\n";

$recibosConPagos = $apartamento->pagos
    ->where('estado', '!=', 'rechazado')
    ->pluck('recibo_gasto_comun_id')
    ->unique();

echo "Recibos con pagos activos: {$recibosConPagos->count()}\n";

$tieneRec1022 = $recibosConPagos->contains($recibo1022->id);

if ($tieneRec1022) {
    echo "❌ ERROR: REC-1022 aún aparece en la vista\n";
} else {
    echo "✅ ÉXITO: REC-1022 ya no aparece en la vista\n";
}

echo "\n=== COMANDOS SQL PARA RESTAURAR (SI ES NECESARIO) ===\n";
echo "-- Para restaurar los pagos originales:\n";
foreach ($pagosRec1022 as $pago) {
    echo "UPDATE pagos SET estado = 'confirmado', observaciones = '" . addslashes($pago->observaciones) . "' WHERE id = {$pago->id};\n";
}

echo "\n-- Para eliminar completamente los registros (NO RECOMENDADO):\n";
foreach ($pagosRec1022 as $pago) {
    echo "DELETE FROM pagos WHERE id = {$pago->id};\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
echo "REC-1022 ha sido desasignado del apartamento 13\n";
echo "El apartamento 13 ahora debería mostrar solo 20 recibos\n";
?>