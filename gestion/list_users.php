<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Lista de usuarios ===\n";
$users = User::select('id','name','email','role','password')->limit(10)->get();
if ($users->isEmpty()) {
    echo "No hay usuarios en la base de datos.\n";
    exit(0);
}
foreach ($users as $u) {
    $pass = $u->password ? substr($u->password, 0, 12) . (strlen($u->password) > 12 ? '...' : '') : '(null)';
    echo sprintf("- [%d] %s <%s> rol=%s pass=%s\n", $u->id, $u->name, $u->email, $u->role ?: 'N/A', $pass);
}