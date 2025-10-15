<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Inicializar el kernel de consola
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReciboGastoComun;
use App\Models\Pago;

$numero = $argv[1] ?? null;
if (!$numero) {
    fwrite(STDERR, "Uso: php count_pagados_recibo.php REC-XXXX\n");
    exit(1);
}

$recibo = ReciboGastoComun::where('numero_recibo', $numero)->first();
if (!$recibo) {
    fwrite(STDERR, "No se encontró el recibo {$numero}\n");
    exit(2);
}

// Contar apartamentos distintos que han realizado pagos confirmados reales para este recibo
$apartamentosPagados = Pago::confirmadosReales()
    ->where('recibo_gasto_comun_id', $recibo->id)
    ->distinct('apartamento_id')
    ->count('apartamento_id');

echo $apartamentosPagados . "\n";