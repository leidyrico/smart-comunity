<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;

echo "=== VERIFICACIÓN APARTAMENTO 72 ===\n\n";

// Buscar apartamento 72
$apt72 = Apartamento::where('numero', '72')->first();

if ($apt72) {
    echo "✅ Apartamento 72 encontrado:\n";
    echo "   ID: {$apt72->id}\n";
    echo "   Propietario: {$apt72->propietario}\n";
    echo "   Estatus: {$apt72->estatus_financiero}\n\n";
} else {
    echo "❌ Apartamento 72 NO encontrado\n\n";
}

// Verificar apartamento ID 59
$apt59 = Apartamento::find(59);

if ($apt59) {
    echo "✅ Apartamento ID 59 encontrado:\n";
    echo "   Número: {$apt59->numero}\n";
    echo "   Propietario: {$apt59->propietario}\n";
    echo "   Estatus: {$apt59->estatus_financiero}\n\n";
} else {
    echo "❌ Apartamento ID 59 NO encontrado\n\n";
}

// Listar algunos apartamentos para entender la numeración
echo "=== ALGUNOS APARTAMENTOS EN EL SISTEMA ===\n";
$apartamentos = Apartamento::orderBy('numero')->take(10)->get();

foreach ($apartamentos as $apt) {
    echo "ID: {$apt->id} | Número: {$apt->numero} | Propietario: {$apt->propietario}\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";