<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrapping Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Apartamento;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== CREACIÓN DE USUARIOS PROPIETARIOS POR APARTAMENTO ===\n\n";

$apartamentos = Apartamento::orderBy('numero')->get();
if ($apartamentos->isEmpty()) {
    echo "No hay apartamentos en la base de datos.\n";
    exit(0);
}

$creados = 0;
$actualizados = 0;

foreach ($apartamentos as $ap) {
    $nombre = $ap->propietario ? ("Propietario: " . $ap->propietario) : ("Propietario " . $ap->numero);

    // Si el apartamento tiene correo configurado y no está usado por otro usuario propietario del mismo apartamento, usarlo
    $emailPreferido = null;
    if (!empty($ap->email)) {
        $emailPreferido = trim($ap->email);
    }

    // Fallback: correo basado en número de apartamento
    $numeroSan = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', (string)$ap->numero));
    $emailFallback = "propietario-{$numeroSan}@gestionactas.com";

    // Buscar si ya existe un usuario propietario para este apartamento
    $existing = User::where('apartamento_id', $ap->id)
        ->where('role', User::ROLE_USUARIO_PROPIETARIO)
        ->first();

    if ($existing) {
        // Mantener email actual si ya está configurado, si no, asignar preferido o fallback
        $newEmail = $existing->email ?: ($emailPreferido ?: $emailFallback);
        $existing->name = $nombre;
        $existing->email = $newEmail;
        $existing->status = true;
        $existing->save();
        echo sprintf("✓ Actualizado propietario para Apto %s (User ID %d, email %s)\n", $ap->numero, $existing->id, $existing->email);
        $actualizados++;
        continue;
    }

    // Determinar email a usar evitando conflicto por email único
    $emailToUse = $emailPreferido ?: $emailFallback;
    $emailConflict = User::where('email', $emailToUse)->exists();
    if ($emailConflict) {
        // Ajustar email para evitar duplicados
        $emailToUse = "propietario-{$numeroSan}-" . uniqid() . "@gestionactas.com";
    }

    $user = new User([
        'name' => $nombre,
        'email' => $emailToUse,
        'password' => Hash::make('propietario123'),
        'role' => User::ROLE_USUARIO_PROPIETARIO,
        'status' => true,
        'apartamento_id' => $ap->id,
    ]);
    $user->save();

    echo sprintf("+ Creado propietario para Apto %s (User ID %d, email %s)\n", $ap->numero, $user->id, $user->email);
    $creados++;
}

echo "\n=== RESUMEN ===\n";
echo "Usuarios creados: {$creados}\n";
echo "Usuarios actualizados: {$actualizados}\n";
echo "Contraseña inicial: propietario123 (puede ser cambiada luego)\n";
echo "Proceso completado.\n";