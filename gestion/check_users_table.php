<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== VERIFICANDO USUARIOS EXISTENTES ===\n\n";

// Mostrar usuarios existentes
$users = User::all(['id', 'name', 'email', 'role']);
echo "Usuarios actuales en la base de datos:\n";
foreach ($users as $user) {
    echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
}

echo "\n=== CREANDO USUARIOS SOLICITADOS ===\n\n";

// Crear admin@sc.com si no existe
$adminSc = User::where('email', 'admin@sc.com')->first();
if (!$adminSc) {
    User::create([
        'name' => 'Admin SC',
        'email' => 'admin@sc.com',
        'password' => Hash::make('admin123'),
        'role' => User::ROLE_ADMIN,
        'status' => true,
    ]);
    echo "✓ Usuario admin@sc.com creado exitosamente\n";
} else {
    echo "✓ Usuario admin@sc.com ya existe\n";
}

// Crear edrey@sc.com si no existe
$edreySc = User::where('email', 'edrey@sc.com')->first();
if (!$edreySc) {
    User::create([
        'name' => 'Edrey SC',
        'email' => 'edrey@sc.com',
        'password' => Hash::make('edrey123'),
        'role' => User::ROLE_ADMIN,
        'status' => true,
    ]);
    echo "✓ Usuario edrey@sc.com creado exitosamente\n";
} else {
    echo "✓ Usuario edrey@sc.com ya existe\n";
}

echo "\n=== USUARIOS FINALES ===\n\n";

// Mostrar usuarios finales
$finalUsers = User::all(['id', 'name', 'email', 'role']);
foreach ($finalUsers as $user) {
    echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
}

echo "\n=== CREDENCIALES PARA LOGIN ===\n\n";
echo "admin@sc.com / admin123\n";
echo "edrey@sc.com / edrey123\n";
echo "admin@gestionactas.com / admin123\n";

echo "\n=== PROCESO COMPLETADO ===\n";

?>