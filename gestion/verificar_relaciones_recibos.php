<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\{Apartamento, Pago, ReciboGastoComun};

echo "=== VERIFICACIÓN DE RELACIONES APARTAMENTOS-RECIBOS ===\n\n";

// Contar registros en cada tabla
echo "CONTEO DE REGISTROS:\n";
echo "- Total apartamentos: " . Apartamento::count() . "\n";
echo "- Total recibos: " . ReciboGastoComun::count() . "\n";
echo "- Total pagos (asignaciones): " . Pago::count() . "\n\n";

// Verificar relaciones
echo "RELACIONES:\n";
echo "- Apartamentos con pagos asignados: " . Apartamento::whereHas('pagos')->count() . "\n";
echo "- Recibos con pagos asignados: " . ReciboGastoComun::whereHas('pagos')->count() . "\n\n";

// Mostrar algunos ejemplos de pagos
echo "EJEMPLOS DE PAGOS (primeros 10):\n";
$pagos = Pago::with(['apartamento', 'reciboGastoComun'])->limit(10)->get();

if ($pagos->count() > 0) {
    foreach ($pagos as $pago) {
        $apartamento = $pago->apartamento ? $pago->apartamento->numero : 'N/A';
        $recibo = $pago->reciboGastoComun ? $pago->reciboGastoComun->numero_recibo : 'N/A';
        echo "- Apartamento {$apartamento} -> Recibo {$recibo} (Estado: {$pago->estado})\n";
    }
} else {
    echo "No hay pagos registrados en la base de datos.\n";
}

echo "\n";

// Verificar estatus financieros actuales
echo "ESTATUS FINANCIEROS ACTUALES:\n";
$solventes = Apartamento::where('estatus_financiero', 'solvente')->count();
$deudores = Apartamento::where('estatus_financiero', 'deudor')->count();
$morosos = Apartamento::where('estatus_financiero', 'moroso')->count();

echo "- Solventes: {$solventes}\n";
echo "- Deudores: {$deudores}\n";
echo "- Morosos: {$morosos}\n\n";

// Verificar algunos apartamentos específicos
echo "VERIFICACIÓN DE APARTAMENTOS ESPECÍFICOS:\n";
$apartamentos = Apartamento::limit(5)->get();

foreach ($apartamentos as $apt) {
    $recibosAsignados = $apt->pagos()->count();
    $recibosVencidos = $apt->pagos()
        ->whereHas('reciboGastoComun', function($query) {
            $query->where('estado', 'vencido');
        })->count();
    
    echo "- Apartamento {$apt->numero}: {$recibosAsignados} recibos asignados, {$recibosVencidos} vencidos (Estatus: {$apt->estatus_financiero})\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";