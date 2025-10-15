<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'admin@sc.com';
$new = 'admin123';

$user = User::where('email', $email)->first();
if (!$user) {
    echo "Usuario no encontrado: $email\n";
    exit(1);
}
$user->password = Hash::make($new);
$user->save();
echo "✓ Password actualizado para $email -> $new\n";