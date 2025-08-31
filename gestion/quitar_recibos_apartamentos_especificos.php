<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\{Apartamento, Pago, ReciboGastoComun};
use Illuminate\Support\Facades\DB;

echo "=== SCRIPT PARA QUITAR RECIBOS DE APARTAMENTOS ESPECÍFICOS ===\n\n";

// IDs de los apartamentos específicos
$apartamentosIds = [
    32, // PB-1
    34, // 11
    91  // 13
];

// Verificar que los apartamentos existen
echo "VERIFICANDO APARTAMENTOS:\n";
foreach($apartamentosIds as $id) {
    $apartamento = Apartamento::find($id);
    if($apartamento) {
        echo "✓ Apartamento {$apartamento->numero} (ID: {$id}) encontrado\n";
    } else {
        echo "❌ Apartamento con ID {$id} NO encontrado\n";
        exit(1);
    }
}

echo "\n";

// Mostrar estadísticas antes de la eliminación
echo "ESTADÍSTICAS ANTES DE LA ELIMINACIÓN:\n";
foreach($apartamentosIds as $id) {
    $apartamento = Apartamento::find($id);
    $totalPagos = Pago::where('apartamento_id', $id)->count();
    $pagosConfirmados = Pago::where('apartamento_id', $id)->where('estado', 'confirmado')->count();
    $pagosPendientes = Pago::where('apartamento_id', $id)->where('estado', 'pendiente_confirmacion')->count();
    
    echo "- Apartamento {$apartamento->numero}: {$totalPagos} recibos asignados ({$pagosConfirmados} confirmados, {$pagosPendientes} pendientes)\n";
}

echo "\n";

// Confirmar antes de proceder
echo "¿Desea continuar con la eliminación de TODOS los recibos asignados a estos apartamentos? (y/n): ";
$confirmacion = trim(fgets(STDIN));

if(strtolower($confirmacion) !== 'y' && strtolower($confirmacion) !== 'yes') {
    echo "Operación cancelada por el usuario.\n";
    exit(0);
}

echo "\n=== INICIANDO ELIMINACIÓN ===\n\n";

// Crear backup antes de eliminar
echo "Creando backup de los pagos que se van a eliminar...\n";
$timestamp = date('Y-m-d_H-i-s');
$backupFile = "backup_pagos_eliminados_{$timestamp}.sql";

$pagosAEliminar = Pago::whereIn('apartamento_id', $apartamentosIds)->get();

if($pagosAEliminar->count() > 0) {
    $backupContent = "-- Backup de pagos eliminados - {$timestamp}\n\n";
    
    foreach($pagosAEliminar as $pago) {
        $backupContent .= "INSERT INTO pagos (id, apartamento_id, recibo_gasto_comun_id, monto_pagado, fecha_pago, metodo_pago, numero_comprobante, observaciones, estado, created_at, updated_at) VALUES ";
        $backupContent .= "({$pago->id}, {$pago->apartamento_id}, {$pago->recibo_gasto_comun_id}, {$pago->monto_pagado}, ";
        $backupContent .= $pago->fecha_pago ? "'{$pago->fecha_pago}'" : 'NULL';
        $backupContent .= ", " . ($pago->metodo_pago ? "'{$pago->metodo_pago}'" : 'NULL');
        $backupContent .= ", " . ($pago->numero_comprobante ? "'{$pago->numero_comprobante}'" : 'NULL');
        $backupContent .= ", " . ($pago->observaciones ? "'{$pago->observaciones}'" : 'NULL');
        $backupContent .= ", '{$pago->estado}', '{$pago->created_at}', '{$pago->updated_at}');\n";
    }
    
    file_put_contents($backupFile, $backupContent);
    echo "✓ Backup creado: {$backupFile}\n\n";
}

// Eliminar los pagos (asignaciones de recibos)
DB::beginTransaction();

try {
    $totalEliminados = 0;
    
    foreach($apartamentosIds as $id) {
        $apartamento = Apartamento::find($id);
        $pagosEliminados = Pago::where('apartamento_id', $id)->delete();
        
        echo "✓ Apartamento {$apartamento->numero}: {$pagosEliminados} recibos eliminados\n";
        $totalEliminados += $pagosEliminados;
        
        // Actualizar el estatus financiero del apartamento
        $apartamento->actualizarEstatusFinanciero();
        echo "  - Estatus financiero actualizado: {$apartamento->estatus_financiero}\n";
    }
    
    DB::commit();
    
    echo "\n=== ELIMINACIÓN COMPLETADA ===\n";
    echo "Total de recibos eliminados: {$totalEliminados}\n";
    echo "Backup guardado en: {$backupFile}\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error durante la eliminación: " . $e->getMessage() . "\n";
    echo "Se ha revertido la transacción.\n";
    exit(1);
}

// Mostrar estadísticas después de la eliminación
echo "\nESTADÍSTICAS DESPUÉS DE LA ELIMINACIÓN:\n";
foreach($apartamentosIds as $id) {
    $apartamento = Apartamento::find($id);
    $totalPagos = Pago::where('apartamento_id', $id)->count();
    
    echo "- Apartamento {$apartamento->numero}: {$totalPagos} recibos asignados (Estatus: {$apartamento->estatus_financiero})\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
echo "Los apartamentos PB-1, 11 y 13 ya no tienen recibos asignados.\n";
echo "Sus estatus financieros han sido actualizados automáticamente.\n";

?>