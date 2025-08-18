<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "=== PRUEBA FINAL DE LOGIN ===\n\n";

// 1. Verificar usuario en base de datos
echo "1. Verificando usuario en base de datos:\n";
$user = User::where('email', 'admintest@gmail.com')->first();
if ($user) {
    echo "✓ Usuario encontrado:\n";
    echo "  ID: {$user->id}\n";
    echo "  Email: {$user->email}\n";
    echo "  Password hash: " . substr($user->password, 0, 30) . "...\n";
    echo "  Role: {$user->role}\n";
    echo "  Status: {$user->status}\n\n";
} else {
    echo "✗ Usuario NO encontrado\n\n";
    exit(1);
}

// 2. Probar autenticación manual
echo "2. Probando autenticación manual:\n";
$credentials = [
    'email' => 'admintest@gmail.com',
    'password' => 'admintest'
];

echo "Credenciales a probar:\n";
echo "  User: {$credentials['user']}\n";
echo "  Password: {$credentials['password']}\n\n";

// Intentar autenticación
if (Auth::attempt($credentials)) {
    echo "✓ AUTENTICACIÓN EXITOSA!\n";
    echo "Usuario autenticado: " . Auth::user()->user . "\n";
    Auth::logout(); // Limpiar sesión
} else {
    echo "✗ AUTENTICACIÓN FALLIDA\n";
    
    // Verificar password manualmente
    echo "\nVerificación manual de password:\n";
    $plainPassword = 'admintest';
    $hashedPassword = $user->password;
    
    echo "Password plano: '{$plainPassword}'\n";
    echo "Password hash: '{$hashedPassword}'\n";
    
    // Probar comparación directa
    if ($plainPassword === $hashedPassword) {
        echo "✓ Coincidencia directa (texto plano)\n";
    } else {
        echo "✗ No hay coincidencia directa\n";
    }
    
    // Probar verificación de hash
    if (Hash::check($plainPassword, $hashedPassword)) {
        echo "✓ Verificación de hash exitosa\n";
    } else {
        echo "✗ Verificación de hash fallida\n";
    }
}

echo "\n=== FIN DE PRUEBA ===\n";

?>