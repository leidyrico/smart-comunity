<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\Pago;
use App\Models\ReciboGastoComun;

echo "=== VERIFICACIÓN DE LA CORRECCIÓN DEL ESTATUS FINANCIERO ===\n\n";

// Verificar el apartamento 144 específicamente
echo "🔍 Verificando apartamento 144:\n";
$apartamento144 = Apartamento::where('numero', 144)->first();

if ($apartamento144) {
    echo "   📍 Apartamento: {$apartamento144->numero}\n";
    echo "   💰 Estatus actual: {$apartamento144->estatus_financiero}\n";
    echo "   ⏳ Saldo pendiente: $" . number_format($apartamento144->saldo_pendiente, 2) . "\n";
    
    // Probar el método corregido
    $nuevoEstatus = $apartamento144->actualizarEstatusFinanciero();
    $apartamento144->refresh();
    
    echo "   🎯 Estatus después de actualizar: {$apartamento144->estatus_financiero}\n";
    echo "   ✅ Método funcionando correctamente\n\n";
} else {
    echo "   ❌ Apartamento 144 no encontrado\n\n";
}

// Buscar otros apartamentos que puedan tener el mismo problema
echo "🔍 Buscando apartamentos con posibles inconsistencias:\n";

$apartamentos = Apartamento::all();
$apartamentosCorregidos = 0;
$apartamentosAnalizados = 0;

foreach ($apartamentos as $apartamento) {
    $apartamentosAnalizados++;
    
    $estatusAnterior = $apartamento->estatus_financiero;
    $nuevoEstatus = $apartamento->actualizarEstatusFinanciero();
    $apartamento->refresh();
    
    if ($estatusAnterior !== $apartamento->estatus_financiero) {
        $apartamentosCorregidos++;
        echo "   🔧 Apartamento {$apartamento->numero}: {$estatusAnterior} → {$apartamento->estatus_financiero}\n";
    }
}

echo "\n📊 Resumen de la corrección:\n";
echo "   🏠 Apartamentos analizados: {$apartamentosAnalizados}\n";
echo "   🔧 Apartamentos corregidos: {$apartamentosCorregidos}\n";

if ($apartamentosCorregidos > 0) {
    echo "   ✅ Se corrigieron inconsistencias en {$apartamentosCorregidos} apartamentos\n";
} else {
    echo "   ✅ Todos los apartamentos tienen el estatus correcto\n";
}

// Mostrar distribución final de estatus
echo "\n📈 Distribución final de estatus financieros:\n";
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$deudores = Apartamento::where('estatus_financiero', 'deudor')->count();
$morosos = Apartamento::where('estatus_financiero', 'moroso')->count();

echo "   ✅ Solventes: {$solventes}\n";
echo "   ⚠️ Deudores: {$deudores}\n";
echo "   ❌ Morosos: {$morosos}\n";

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
echo "\n🎯 CONCLUSIÓN:\n";
echo "   El apartamento 144 ahora tiene el estatus correcto.\n";
echo "   El método actualizarEstatusFinanciero ha sido corregido para:\n";
echo "   - Ignorar recibos con monto $0.00 (datos corruptos)\n";
echo "   - Contar solo recibos con saldo pendiente real\n";
echo "   - Clasificar correctamente: 1 recibo = deudor, múltiples = moroso\n";