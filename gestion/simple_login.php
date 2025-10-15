<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;

// Inicializar sesión
Session::start();

// Generar token CSRF
$csrfToken = csrf_token();

// Procesar formulario si se envió
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar token CSRF
        if (!hash_equals($csrfToken, $_POST['_token'] ?? '')) {
            throw new Exception('Token CSRF inválido');
        }
        
        $credentials = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];
        
        $remember = isset($_POST['remember']);
        
        echo "<script>console.log('Intentando login con:', " . json_encode($credentials) . ");</script>";
        
        if (Auth::attempt($credentials, $remember)) {
            $message = '¡Login exitoso! Usuario: ' . Auth::user()->email;
            // Redirigir al dashboard
            header('Location: /dashboard');
            exit;
        } else {
            $error = 'Credenciales incorrectas';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Simple</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background: #007cba;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #005a87;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .debug {
            margin-top: 20px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login Simple - Gestión Actas</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="admintest@gmail.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" value="admintest" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember" value="1"> Recordarme
                </label>
            </div>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="debug">
            <strong>Debug Info:</strong><br>
            CSRF Token: <?= substr($csrfToken, 0, 10) ?>...<br>
            Método: <?= $_SERVER['REQUEST_METHOD'] ?><br>
            Auth Status: <?= Auth::check() ? 'Autenticado como ' . Auth::user()->email : 'No autenticado' ?>
        </div>
    </div>
</body>
</html>