<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN ESTATUS ACTUAL ===\n\n";

// Contar por estatus
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$deudores = Apartamento::where('estatus_financiero', 'deudor')->count();
$morosos = Apartamento::where('estatus_financiero', 'moroso')->count();

echo "Distribución actual:\n";
echo "- Solventes: {$solventes}\n";
echo "- Deudores: {$deudores}\n";
echo "- Morosos: {$morosos}\n\n";

// Mostrar algunos ejemplos de cada tipo
echo "=== EJEMPLOS POR ESTATUS ===\n\n";

echo "SOLVENTES (primeros 5):\n";
$apartamentosSolventes = Apartamento::where('estatus_financiero', 'solvente')->limit(5)->get();
foreach ($apartamentosSolventes as $apt) {
    echo "- Apartamento {$apt->numero} (ID: {$apt->id})\n";
}

echo "\nDEUDORES (primeros 5):\n";
$apartamentosDeudores = Apartamento::where('estatus_financiero', 'deudor')->limit(5)->get();
foreach ($apartamentosDeudores as $apt) {
    echo "- Apartamento {$apt->numero} (ID: {$apt->id})\n";
}

echo "\nMOROSOS (primeros 5):\n";
$apartamentosMorosos = Apartamento::where('estatus_financiero', 'moroso')->limit(5)->get();
foreach ($apartamentosMorosos as $apt) {
    echo "- Apartamento {$apt->numero} (ID: {$apt->id})\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
?>