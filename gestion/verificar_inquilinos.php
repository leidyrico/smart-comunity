<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inquilino;
use Illuminate\Support\Facades\DB;

echo "=== LISTADO CORRECTO DE INQUILINOS ===\n\n";

// Consulta con los campos correctos
$inquilinos = Inquilino::all();

echo "Total de inquilinos: " . $inquilinos->count() . "\n\n";

if ($inquilinos->count() > 0) {
    foreach ($inquilinos as $inquilino) {
        echo "==========================================\n";
        echo "ID: " . $inquilino->id . "\n";
        echo "Nombre: " . ($inquilino->nombre_inquilino ?? 'No especificado') . "\n";
        echo "Apartamento: " . ($inquilino->nro_apartamento ?? 'No especificado') . "\n";
        echo "Monto Deuda: $" . number_format($inquilino->monto_deuda ?? 0, 2) . "\n";
        echo "Fecha Deuda: " . ($inquilino->fecha_deuda ?? 'No especificada') . "\n";
        echo "Monto Último Pago: $" . number_format($inquilino->monto_ultimo_pago ?? 0, 2) . "\n";
        echo "Fecha Último Pago: " . ($inquilino->fecha_ultimo_pago ?? 'No especificada') . "\n";
        echo "Fecha de creación: " . $inquilino->created_at . "\n";
        echo "Última actualización: " . $inquilino->updated_at . "\n";
        echo "==========================================\n\n";
    }
    
    // Estadísticas
    echo "\n=== ESTADÍSTICAS ===\n";
    $totalDeuda = $inquilinos->sum('monto_deuda');
    $inquilinosConDeuda = $inquilinos->where('monto_deuda', '>', 0)->count();
    
    echo "Total deuda acumulada: $" . number_format($totalDeuda, 2) . "\n";
    echo "Inquilinos con deuda: " . $inquilinosConDeuda . "\n";
    echo "Inquilinos sin deuda: " . ($inquilinos->count() - $inquilinosConDeuda) . "\n";
    
} else {
    echo "No se encontraron inquilinos.\n";
}

// Verificar si hay más registros con consulta SQL directa
echo "\n=== VERIFICACIÓN CON SQL DIRECTO ===\n";
$sqlInquilinos = DB::table('inquilinos')->get();
echo "Registros encontrados con SQL directo: " . $sqlInquilinos->count() . "\n";

if ($sqlInquilinos->count() > $inquilinos->count()) {
    echo "¡ATENCIÓN! Hay más registros en la base de datos que los que muestra Eloquent.\n";
    echo "Esto podría indicar un problema con el modelo o soft deletes.\n";
}

echo "\n=== FIN DEL LISTADO ===\n";