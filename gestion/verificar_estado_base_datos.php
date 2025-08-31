<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN ESTADO BASE DE DATOS ===\n\n";

try {
    // Verificar conexión
    $dbName = DB::connection()->getDatabaseName();
    echo "✅ Conectado a base de datos: {$dbName}\n\n";
    
    // Contar registros en tablas principales
    $apartamentos = Apartamento::count();
    $recibos = ReciboGastoComun::count();
    $pagos = Pago::count();
    
    echo "=== CONTEO DE REGISTROS ===\n";
    echo "Apartamentos: {$apartamentos}\n";
    echo "Recibos: {$recibos}\n";
    echo "Pagos: {$pagos}\n\n";
    
    if ($apartamentos == 0 || $recibos == 0) {
        echo "❌ PROBLEMA: Las tablas están vacías o casi vacías\n";
        echo "Esto explica por qué no se muestran los recibos correctamente\n\n";
        
        // Verificar si hay backups disponibles
        echo "=== ARCHIVOS DE BACKUP DISPONIBLES ===\n";
        $backupFiles = glob('backup_*.sql');
        
        if (!empty($backupFiles)) {
            foreach ($backupFiles as $file) {
                $size = filesize($file);
                $date = date('Y-m-d H:i:s', filemtime($file));
                echo "📁 {$file} - Tamaño: " . number_format($size) . " bytes - Fecha: {$date}\n";
            }
            
            echo "\n💡 RECOMENDACIÓN: Restaurar desde el backup más reciente\n";
            echo "Ejecutar: C:\\xampp\\mysql\\bin\\mysql.exe -u root smart_comunity < backup_pagos_recibos_2025-08-29_22-33-43.sql\n";
        } else {
            echo "❌ No se encontraron archivos de backup\n";
        }
    } else {
        echo "✅ Las tablas contienen datos\n";
        
        // Si hay datos, verificar apartamento 91 específicamente
        $apartamento91 = Apartamento::find(91);
        if ($apartamento91) {
            echo "\n✅ Apartamento 91 encontrado: {$apartamento91->numero}\n";
            
            $pagosApt91 = Pago::where('apartamento_id', 91)->count();
            echo "Pagos asignados al apartamento 91: {$pagosApt91}\n";
        } else {
            echo "\n❌ Apartamento 91 no encontrado\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}