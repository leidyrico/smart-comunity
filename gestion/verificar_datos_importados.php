<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== VERIFICACIÓN DE DATOS IMPORTADOS ===\n\n";

echo "Apartamentos totales: " . Apartamento::count() . "\n";
echo "Recibos totales: " . ReciboGastoComun::count() . "\n";
echo "Pagos totales: " . Pago::count() . "\n\n";

echo "=== ÚLTIMOS 10 RECIBOS IMPORTADOS ===\n";
$recibos = ReciboGastoComun::latest()->take(10)->get();
foreach($recibos as $r) {
    echo "- Recibo {$r->numero_recibo}: ";
    echo "Emisión {$r->fecha_emision->format('d-m-Y')}, ";
    echo "Vencimiento {$r->fecha_vencimiento->format('d-m-Y')}";
    echo " (Apartamento: " . ($r->apartamento ? $r->apartamento->numero : 'Sin apartamento') . ")\n";
}

echo "\n=== RECIBOS CON FECHAS ESPECÍFICAS ===\n";

// Buscar recibos con fechas problemáticas
$fechasProblematicas = [
    '2024-02-29', // 29 de febrero 2024 (año bisiesto)
    '2025-02-28', // 28 de febrero 2025
    '2025-03-01'  // 1 de marzo 2025 (posible conversión de 29/02/2025)
];

foreach ($fechasProblematicas as $fecha) {
    $recibosEmision = ReciboGastoComun::whereDate('fecha_emision', $fecha)->get();
    $recibosVencimiento = ReciboGastoComun::whereDate('fecha_vencimiento', $fecha)->get();
    
    echo "Fecha {$fecha}:\n";
    echo "  - Recibos con fecha emisión: {$recibosEmision->count()}\n";
    echo "  - Recibos con fecha vencimiento: {$recibosVencimiento->count()}\n";
    
    foreach ($recibosEmision as $r) {
        echo "    * Emisión - Recibo {$r->numero_recibo}: {$r->fecha_emision->format('d-m-Y')}\n";
    }
    foreach ($recibosVencimiento as $r) {
        echo "    * Vencimiento - Recibo {$r->numero_recibo}: {$r->fecha_vencimiento->format('d-m-Y')}\n";
    }
    echo "\n";
}

echo "=== APARTAMENTOS IMPORTADOS ===\n";
$apartamentos = Apartamento::latest()->take(5)->get();
foreach($apartamentos as $apt) {
    echo "- Apartamento {$apt->numero}: {$apt->propietario}\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";