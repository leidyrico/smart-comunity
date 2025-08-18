<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== MONITOR DE LOGIN ===\n";
echo "Presiona Ctrl+C para salir\n\n";

// Configurar logging personalizado
Log::info('=== INICIO DE MONITOREO DE LOGIN ===');

// Verificar estado inicial
echo "Estado inicial:\n";
echo "- Servidor: " . (file_exists('storage/framework/sessions') ? 'OK' : 'ERROR') . "\n";
echo "- Base de datos: ";
try {
    DB::connection()->getPdo();
    echo "OK\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "- Usuario de prueba: ";
try {
    $user = DB::table('users')->where('email', 'admintest@gmail.com')->first();
    echo $user ? "OK (ID: {$user->id})" : "NO ENCONTRADO";
    echo "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nMonitoreando intentos de login...\n";
echo "Intenta hacer login ahora en el navegador\n\n";

// Monitorear logs en tiempo real
$lastLogSize = 0;
if (file_exists('storage/logs/laravel.log')) {
    $lastLogSize = filesize('storage/logs/laravel.log');
}

while (true) {
    sleep(1);
    
    if (file_exists('storage/logs/laravel.log')) {
        $currentSize = filesize('storage/logs/laravel.log');
        if ($currentSize > $lastLogSize) {
            $newContent = file_get_contents('storage/logs/laravel.log', false, null, $lastLogSize);
            if (trim($newContent)) {
                echo "[" . date('H:i:s') . "] NUEVO LOG:\n";
                echo $newContent . "\n";
                echo str_repeat('-', 50) . "\n";
            }
            $lastLogSize = $currentSize;
        }
    }
}

?>