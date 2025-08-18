<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== DEBUG LOGIN DETAILED ===\n\n";

// 1. Verificar configuración de autenticación
echo "1. Configuración de Auth:\n";
echo "Driver: " . config('auth.guards.web.driver') . "\n";
echo "Provider: " . config('auth.guards.web.provider') . "\n";
echo "Provider Driver: " . config('auth.providers.users.driver') . "\n";
echo "Provider Model: " . config('auth.providers.users.model') . "\n\n";

// 2. Verificar usuario en base de datos
echo "2. Usuario en base de datos:\n";
$user = User::where('email', 'admintest@gmail.com')->first();
if ($user) {
    echo "Usuario encontrado:\n";
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Password (primeros 20 chars): " . substr($user->password, 0, 20) . "...\n";
    echo "Remember Token: " . ($user->remember_token ?? 'NULL') . "\n";
    echo "Created At: {$user->created_at}\n";
    echo "Updated At: {$user->updated_at}\n\n";
    
    // 3. Verificar password
    echo "3. Verificación de password:\n";
    $plainPassword = 'admintest';
    echo "Password plano a verificar: '{$plainPassword}'\n";
    echo "Hash en BD: {$user->password}\n";
    echo "Hash::check result: " . (Hash::check($plainPassword, $user->password) ? 'TRUE' : 'FALSE') . "\n";
    echo "Comparación directa: " . ($plainPassword === $user->password ? 'TRUE' : 'FALSE') . "\n\n";
    
    // 4. Verificar estructura de tabla users
    echo "4. Estructura de tabla users:\n";
    $columns = DB::select("DESCRIBE users");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) - {$column->Null} - {$column->Key} - {$column->Default}\n";
    }
    echo "\n";
    
    // 5. Verificar tabla sessions
    echo "5. Verificación de tabla sessions:\n";
    try {
        $sessionsCount = DB::table('sessions')->count();
        echo "Tabla sessions existe. Registros: {$sessionsCount}\n";
    } catch (Exception $e) {
        echo "Error con tabla sessions: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // 6. Simular autenticación manual
    echo "6. Simulación de autenticación:\n";
    try {
        $credentials = ['email' => 'admintest@gmail.com', 'password' => 'admintest'];
        $authResult = Auth::attempt($credentials);
        echo "Auth::attempt result: " . ($authResult ? 'SUCCESS' : 'FAILED') . "\n";
        
        if ($authResult) {
            echo "Usuario autenticado: " . Auth::user()->email . "\n";
            Auth::logout();
        }
    } catch (Exception $e) {
        echo "Error en Auth::attempt: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "Usuario NO encontrado\n";
    echo "Usuarios disponibles:\n";
    $users = User::all();
    foreach ($users as $u) {
        echo "- ID: {$u->id}, Email: {$u->email}\n";
    }
}

echo "\n=== FIN DEBUG ===\n";
?>