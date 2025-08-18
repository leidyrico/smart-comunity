<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

echo "=== DEBUG ENVÍO DE FORMULARIO ===\n\n";

// 1. Verificar que la ruta de login existe
echo "1. Verificando ruta de login:\n";
try {
    $loginRoute = Route::getRoutes()->getByName('login');
    if ($loginRoute) {
        echo "✓ Ruta 'login' encontrada: " . $loginRoute->uri() . "\n";
        echo "  Métodos: " . implode(', ', $loginRoute->methods()) . "\n";
        echo "  Acción: " . $loginRoute->getActionName() . "\n";
    } else {
        echo "✗ Ruta 'login' NO encontrada\n";
    }
} catch (Exception $e) {
    echo "✗ Error verificando ruta: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Simular datos del formulario
echo "2. Simulando envío de formulario:\n";
$formData = [
    'user' => 'admintest@gmail.com',
    'password' => 'admintest',
    'remember' => false
];

echo "Datos del formulario:\n";
foreach ($formData as $key => $value) {
    echo "  {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
}

echo "\n";

// 3. Validar datos usando las reglas del LoginRequest
echo "3. Validando datos del formulario:\n";
try {
    $loginRequest = new LoginRequest();
    $rules = $loginRequest->rules();
    
    echo "Reglas de validación:\n";
    foreach ($rules as $field => $rule) {
        echo "  {$field}: " . (is_array($rule) ? implode('|', $rule) : $rule) . "\n";
    }
    
    $validator = Validator::make($formData, $rules);
    
    if ($validator->passes()) {
        echo "✓ Validación exitosa\n";
    } else {
        echo "✗ Errores de validación:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "  - {$error}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error en validación: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Probar autenticación directa
echo "4. Probando autenticación directa:\n";
try {
    $credentials = [
        'user' => $formData['user'],
        'password' => $formData['password']
    ];
    
    if (Auth::attempt($credentials, $formData['remember'])) {
        echo "✓ Autenticación exitosa\n";
        echo "  Usuario autenticado: " . Auth::user()->user . "\n";
        Auth::logout();
    } else {
        echo "✗ Autenticación fallida\n";
    }
} catch (Exception $e) {
    echo "✗ Error en autenticación: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEBUG ===\n";

?>