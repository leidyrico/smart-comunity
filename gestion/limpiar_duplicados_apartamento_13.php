<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== LIMPIEZA DE DUPLICADOS APARTAMENTO 13 (ID 91) ===\n\n";

// Buscar apartamento 13
$apartamento = Apartamento::find(91);
if (!$apartamento) {
    echo "❌ Apartamento 91 no encontrado\n";
    exit;
}

echo "✅ Apartamento: {$apartamento->numero} - {$apartamento->propietario}\n\n";

// Encontrar duplicados
$duplicados = DB::select("
    SELECT recibo_gasto_comun_id, COUNT(*) as cantidad 
    FROM pagos 
    WHERE apartamento_id = 91 
    GROUP BY recibo_gasto_comun_id 
    HAVING COUNT(*) > 1
");

if (empty($duplicados)) {
    echo "✅ No se encontraron duplicados\n";
    exit;
}

echo "=== DUPLICADOS ENCONTRADOS ===\n";
foreach ($duplicados as $dup) {
    echo "Recibo ID {$dup->recibo_gasto_comun_id}: {$dup->cantidad} registros\n";
}

echo "\n¿Desea proceder con la limpieza de duplicados? (s/n): ";
$confirmacion = trim(fgets(STDIN));

if (strtolower($confirmacion) !== 's') {
    echo "Operación cancelada\n";
    exit;
}

echo "\n=== INICIANDO LIMPIEZA ===\n";

// Crear backup antes de limpiar
$backupFile = 'backup_duplicados_apartamento_13_' . date('Y-m-d_H-i-s') . '.sql';
echo "Creando backup en: {$backupFile}\n";

$pagosParaBackup = Pago::where('apartamento_id', 91)->get();
$backupContent = "-- Backup de pagos apartamento 13 antes de limpiar duplicados\n";
$backupContent .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($pagosParaBackup as $pago) {
    $backupContent .= "INSERT INTO pagos (id, apartamento_id, recibo_gasto_comun_id, monto_pagado, fecha_pago, metodo_pago, numero_comprobante, observaciones, estado, created_at, updated_at) VALUES ";
    $backupContent .= "({$pago->id}, {$pago->apartamento_id}, {$pago->recibo_gasto_comun_id}, {$pago->monto_pagado}, ";
    $backupContent .= $pago->fecha_pago ? "'{$pago->fecha_pago}'" : 'NULL';
    $backupContent .= ", " . ($pago->metodo_pago ? "'{$pago->metodo_pago}'" : 'NULL');
    $backupContent .= ", " . ($pago->numero_comprobante ? "'{$pago->numero_comprobante}'" : 'NULL');
    $backupContent .= ", " . ($pago->observaciones ? "'" . addslashes($pago->observaciones) . "'" : 'NULL');
    $backupContent .= ", '{$pago->estado}', '{$pago->created_at}', '{$pago->updated_at}');\n";
}

file_put_contents($backupFile, $backupContent);
echo "✅ Backup creado\n\n";

// Limpiar duplicados manteniendo el registro más relevante
DB::beginTransaction();

try {
    $registrosEliminados = 0;
    
    foreach ($duplicados as $dup) {
        $reciboId = $dup->recibo_gasto_comun_id;
        
        // Obtener todos los pagos para este recibo
        $pagosDelRecibo = Pago::where('apartamento_id', 91)
            ->where('recibo_gasto_comun_id', $reciboId)
            ->orderBy('estado', 'desc') // Priorizar confirmados
            ->orderBy('monto_pagado', 'desc') // Luego por monto
            ->orderBy('created_at', 'asc') // Finalmente por antigüedad
            ->get();
        
        echo "Procesando recibo ID {$reciboId}:\n";
        
        // Mantener el primer registro (más relevante) y eliminar el resto
        $pagoAMantener = $pagosDelRecibo->first();
        $pagosAEliminar = $pagosDelRecibo->slice(1);
        
        echo "  Manteniendo: Pago ID {$pagoAMantener->id} (Estado: {$pagoAMantener->estado}, Monto: $" . number_format($pagoAMantener->monto_pagado, 2) . ")\n";
        
        foreach ($pagosAEliminar as $pago) {
            echo "  Eliminando: Pago ID {$pago->id} (Estado: {$pago->estado}, Monto: $" . number_format($pago->monto_pagado, 2) . ")\n";
            $pago->delete();
            $registrosEliminados++;
        }
        
        echo "\n";
    }
    
    DB::commit();
    
    echo "=== LIMPIEZA COMPLETADA ===\n";
    echo "Registros eliminados: {$registrosEliminados}\n";
    
    // Verificar resultado
    $pagosRestantes = Pago::where('apartamento_id', 91)->count();
    $recibosUnicos = Pago::where('apartamento_id', 91)->distinct()->count('recibo_gasto_comun_id');
    
    echo "Pagos restantes: {$pagosRestantes}\n";
    echo "Recibos únicos: {$recibosUnicos}\n";
    
    if ($pagosRestantes == $recibosUnicos) {
        echo "✅ Limpieza exitosa: Ya no hay duplicados\n";
    } else {
        echo "⚠️ Aún pueden quedar duplicados\n";
    }
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error durante la limpieza: " . $e->getMessage() . "\n";
    echo "Transacción revertida\n";
}