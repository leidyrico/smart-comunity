<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\DeudaController;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== PRUEBA DE IMPORTACIÓN CON ERRORES ===\n";

// Verificar que el archivo existe
$filePath = __DIR__ . '/test_import_errors.xlsx';
if (!file_exists($filePath)) {
    echo "Error: No se encontró el archivo {$filePath}\n";
    exit(1);
}

echo "Archivo encontrado: {$filePath}\n";
echo "Tamaño: " . filesize($filePath) . " bytes\n\n";

// Limpiar log antes de la prueba
file_put_contents('storage/logs/laravel.log', '');

// Contar datos antes de la importación
echo "=== DATOS ANTES DE LA IMPORTACIÓN ===\n";
echo "Apartamentos: " . Apartamento::count() . "\n";
echo "Recibos: " . ReciboGastoComun::count() . "\n";
echo "Pagos: " . Pago::count() . "\n\n";

echo "Iniciando importación con errores...\n\n";

try {
    // Crear un UploadedFile simulado
    $uploadedFile = new UploadedFile(
        $filePath,
        'test_import_errors.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true // test mode
    );

    // Crear request simulado
    $request = new Request();
    $request->files->set('excel_file', $uploadedFile);
    $request->merge([
        'borrar_datos' => true,
        'validar_relaciones' => true,
        'continuar_errores' => true, // Continuar a pesar de errores
        '_token' => 'test_token'
    ]);

    // Ejecutar importación
    $controller = new DeudaController();
    $response = $controller->importCompleto($request);
    
    echo "Importación completada\n\n";
    
} catch (\Exception $e) {
    echo "Error durante la importación: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
}

// Contar datos después de la importación
echo "=== DATOS DESPUÉS DE LA IMPORTACIÓN ===\n";
echo "Apartamentos: " . Apartamento::count() . "\n";
echo "Recibos: " . ReciboGastoComun::count() . "\n";
echo "Pagos: " . Pago::count() . "\n\n";

// Mostrar detalles de los datos importados
echo "=== APARTAMENTOS IMPORTADOS ===\n";
$apartamentos = Apartamento::all();
foreach ($apartamentos as $apt) {
    echo "- {$apt->numero}: {$apt->propietario} ({$apt->estatus_financiero})\n";
}

echo "\n=== RECIBOS IMPORTADOS ===\n";
$recibos = ReciboGastoComun::all();
foreach ($recibos as $recibo) {
    echo "- {$recibo->numero_recibo}: {$recibo->periodo} - Total: {$recibo->total_recibo} ({$recibo->estado})\n";
}

echo "\n=== PAGOS IMPORTADOS ===\n";
$pagos = Pago::with(['apartamento', 'reciboGastoComun'])->get();
foreach ($pagos as $pago) {
    $aptNum = $pago->apartamento ? $pago->apartamento->numero : 'N/A';
    $reciboNum = $pago->reciboGastoComun ? $pago->reciboGastoComun->numero_recibo : 'N/A';
    echo "- Apt {$aptNum}, Recibo {$reciboNum}: {$pago->monto_pagado} ({$pago->estado})\n";
}

// Mostrar errores del log
echo "\n=== ERRORES EN EL LOG ===\n";
$logContent = file_get_contents('storage/logs/laravel.log');
if (empty($logContent)) {
    echo "No hay errores en el log.\n";
} else {
    $lines = explode("\n", $logContent);
    $errorLines = array_filter($lines, function($line) {
        return strpos($line, 'ERROR') !== false || strpos($line, 'error') !== false;
    });
    
    if (empty($errorLines)) {
        echo "No se encontraron líneas de error específicas.\n";
    } else {
        foreach (array_slice($errorLines, -10) as $line) { // Últimas 10 líneas de error
            echo $line . "\n";
        }
    }
}

echo "\n=== FIN DE LA PRUEBA DE ERRORES ===\n";
?>