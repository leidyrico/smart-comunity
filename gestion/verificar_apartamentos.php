<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\ReciboGastoComun;
use App\Models\Pago;

echo "=== VERIFICACIÓN DE APARTAMENTOS ===\n\n";

$apartamentos = Apartamento::all();
echo "Total apartamentos: {$apartamentos->count()}\n\n";

foreach($apartamentos as $apt) {
    echo "Apartamento {$apt->numero}:\n";
    echo "  - Propietario: {$apt->propietario}\n";
    echo "  - Estatus financiero: {$apt->estatus_financiero}\n";
    echo "  - Saldo pendiente: {$apt->saldo_pendiente}\n";
    echo "  - Fecha cambio estatus: {$apt->fecha_cambio_estatus}\n";
    
    $totalPagos = $apt->pagos()->count();
    echo "  - Total pagos asociados: {$totalPagos}\n";
    
    if ($apt->estatus_financiero === 'deudor' && $apt->saldo_pendiente == 0) {
        echo "  ⚠️  PROBLEMA: Apartamento deudor con saldo 0\n";
    }
    
    echo "\n";
}

$recibos = ReciboGastoComun::all();
echo "Total recibos: {$recibos->count()}\n";

$pagos = Pago::all();
echo "Total pagos: {$pagos->count()}\n";

echo "\n=== FIN DE VERIFICACIÓN ===\n";