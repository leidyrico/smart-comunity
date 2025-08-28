<?php
require_once 'vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pago;
use App\Models\Apartamento;
use App\Models\ReciboGastoComun;

echo "=== DEBUG: Verificando pagos en la base de datos ===\n\n";

// Verificar si hay pagos
$totalPagos = Pago::count();
echo "Total de pagos en la base de datos: {$totalPagos}\n\n";

if ($totalPagos > 0) {
    // Mostrar algunos pagos de ejemplo
    $pagos = Pago::with(['apartamento', 'reciboGastoComun'])
                 ->where('estado', 'confirmado')
                 ->limit(5)
                 ->get();
    
    echo "Primeros 5 pagos confirmados:\n";
    foreach ($pagos as $pago) {
        echo "- ID: {$pago->id}, Apartamento: {$pago->apartamento->numero}, Recibo: {$pago->reciboGastoComun->numero_recibo}, Monto: {$pago->monto_pagado}\n";
    }
    
    echo "\n=== Verificando apartamentos con pagos ===\n";
    
    // Verificar apartamentos que tienen pagos
    $apartamentosConPagos = Apartamento::whereHas('pagos', function($query) {
        $query->where('estado', 'confirmado');
    })->with(['pagos' => function($query) {
        $query->where('estado', 'confirmado');
    }])->limit(3)->get();
    
    foreach ($apartamentosConPagos as $apartamento) {
        echo "\nApartamento {$apartamento->numero}:\n";
        foreach ($apartamento->pagos as $pago) {
            echo "  - Pago ID: {$pago->id}, Monto: {$pago->monto_pagado}, Fecha: {$pago->fecha_pago}\n";
        }
    }
} else {
    echo "No hay pagos en la base de datos.\n";
    echo "Creando un pago de prueba...\n\n";
    
    // Buscar un apartamento y un recibo para crear un pago de prueba
    $apartamento = Apartamento::first();
    $recibo = ReciboGastoComun::first();
    
    if ($apartamento && $recibo) {
        $pago = new Pago();
        $pago->apartamento_id = $apartamento->id;
        $pago->recibo_gasto_comun_id = $recibo->id;
        $pago->monto_pagado = 50.00;
        $pago->fecha_pago = now();
        $pago->metodo_pago = 'transferencia';
        $pago->estado = 'confirmado';
        $pago->observaciones = 'Pago de prueba para testing';
        $pago->save();
        
        echo "Pago de prueba creado:\n";
        echo "- ID: {$pago->id}\n";
        echo "- Apartamento: {$apartamento->numero}\n";
        echo "- Recibo: {$recibo->numero_recibo}\n";
        echo "- Monto: {$pago->monto_pagado}\n";
    } else {
        echo "No se encontraron apartamentos o recibos para crear el pago de prueba.\n";
    }
}

echo "\n=== Fin del debug ===\n";
?>