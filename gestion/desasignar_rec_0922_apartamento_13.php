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

echo "=== DESASIGNACIÓN DE REC-0922 DEL APARTAMENTO 13 ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::find(91);
if (!$apartamento) {
    echo "❌ Apartamento 91 no encontrado\n";
    exit;
}

// Buscar recibo REC-0922
$recibo0922 = ReciboGastoComun::where('numero_recibo', 'REC-0922')->first();
if (!$recibo0922) {
    echo "❌ Recibo REC-0922 no encontrado\n";
    exit;
}

echo "Apartamento: {$apartamento->numero} (ID: {$apartamento->id})\n";
echo "Recibo: {$recibo0922->numero_recibo} (ID: {$recibo0922->id})\n\n";

// Verificar pagos existentes
$pagosExistentes = Pago::where('apartamento_id', $apartamento->id)
    ->where('recibo_gasto_comun_id', $recibo0922->id)
    ->get();

if ($pagosExistentes->count() == 0) {
    echo "✅ No hay pagos de REC-0922 asignados al apartamento 13\n";
    echo "No es necesario hacer cambios.\n";
    exit;
}

echo "=== PAGOS ENCONTRADOS ===\n";
foreach ($pagosExistentes as $pago) {
    echo "Pago ID: {$pago->id}\n";
    echo "Monto: $" . number_format($pago->monto_pagado, 2) . "\n";
    echo "Estado actual: {$pago->estado}\n";
    echo "Fecha pago: {$pago->fecha_pago}\n";
    echo "Observaciones: {$pago->observaciones}\n\n";
}

// Crear backup antes de hacer cambios
echo "=== CREANDO BACKUP ===\n";
$fechaBackup = date('Y-m-d_H-i-s');
$archivoBackup = "backup_rec_0922_apartamento_13_{$fechaBackup}.sql";

$contenidoBackup = "-- Backup de pagos REC-0922 apartamento 13 antes de desasignación\n";
$contenidoBackup .= "-- Fecha: {$fechaBackup}\n\n";

foreach ($pagosExistentes as $pago) {
    $contenidoBackup .= "INSERT INTO pagos (id, apartamento_id, recibo_gasto_comun_id, monto_pagado, fecha_pago, metodo_pago, numero_comprobante, observaciones, estado, created_at, updated_at) VALUES ";
    $contenidoBackup .= "({$pago->id}, {$pago->apartamento_id}, {$pago->recibo_gasto_comun_id}, {$pago->monto_pagado}, ";
    $contenidoBackup .= ($pago->fecha_pago ? "'{$pago->fecha_pago}'" : 'NULL') . ", ";
    $contenidoBackup .= ($pago->metodo_pago ? "'{$pago->metodo_pago}'" : 'NULL') . ", ";
    $contenidoBackup .= ($pago->numero_comprobante ? "'{$pago->numero_comprobante}'" : 'NULL') . ", ";
    $contenidoBackup .= "'" . addslashes($pago->observaciones) . "', ";
    $contenidoBackup .= "'{$pago->estado}', '{$pago->created_at}', '{$pago->updated_at}');\n";
}

file_put_contents($archivoBackup, $contenidoBackup);
echo "✅ Backup creado: {$archivoBackup}\n\n";

// Preguntar confirmación (simulada)
echo "=== CONFIRMACIÓN ===\n";
echo "¿Desea proceder con la desasignación de REC-0922 del apartamento 13?\n";
echo "Esto cambiará el estado de los pagos a 'rechazado' para mantener el historial.\n\n";

// Proceder con la desasignación
echo "=== PROCESANDO DESASIGNACIÓN ===\n";

try {
    DB::beginTransaction();
    
    $pagosActualizados = 0;
    
    foreach ($pagosExistentes as $pago) {
        $observacionesOriginales = $pago->observaciones;
        $nuevasObservaciones = "[DESASIGNADO] Recibo no corresponde al apartamento 13. Original: " . $observacionesOriginales;
        
        $pago->update([
            'estado' => 'rechazado',
            'observaciones' => $nuevasObservaciones
        ]);
        
        echo "✅ Pago ID {$pago->id} actualizado a estado 'rechazado'\n";
        $pagosActualizados++;
    }
    
    DB::commit();
    
    echo "\n=== DESASIGNACIÓN COMPLETADA ===\n";
    echo "Pagos actualizados: {$pagosActualizados}\n";
    echo "Estado cambiado a: rechazado\n";
    echo "Backup guardado en: {$archivoBackup}\n\n";
    
    echo "=== VERIFICACIÓN POST-DESASIGNACIÓN ===\n";
    
    // Verificar que los cambios se aplicaron
    $pagosVerificacion = Pago::where('apartamento_id', $apartamento->id)
        ->where('recibo_gasto_comun_id', $recibo0922->id)
        ->get();
    
    foreach ($pagosVerificacion as $pago) {
        echo "Pago ID {$pago->id}: Estado = {$pago->estado}\n";
    }
    
    // Verificar el impacto en el método createGlobal
    echo "\n=== IMPACTO EN VISTA GLOBAL ===\n";
    echo "Ahora REC-0922 NO debería aparecer en la vista de pagos globales del apartamento 13\n";
    echo "porque todos sus pagos están marcados como 'rechazado'.\n\n";
    
    echo "Para verificar, visite: http://localhost:8000/pagos/global/create/91\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la desasignación: " . $e->getMessage() . "\n";
    echo "Los cambios han sido revertidos.\n";
    echo "El backup se mantiene en: {$archivoBackup}\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
?>