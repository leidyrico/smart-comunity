<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartamento;

echo "=== BÚSQUEDA DE APARTAMENTOS ESPECÍFICOS ===\n\n";

$apartamentos = ['PB-1', '11', '13'];

foreach($apartamentos as $numero) {
    $apartamento = Apartamento::where('numero', $numero)->first();
    
    if($apartamento) {
        echo "Apartamento {$numero} - ID: {$apartamento->id}\n";
    } else {
        echo "Apartamento {$numero} - NO ENCONTRADO\n";
    }
}

echo "\n=== FIN DE BÚSQUEDA ===\n";
?>