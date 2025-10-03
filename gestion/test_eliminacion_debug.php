<?php
require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\ReciboGastoComun;
use App\Models\Apartamento;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA SIMPLE DE ELIMINACIÓN ===\n\n";

try {
    \DB::beginTransaction();
    
    // 1. Crear recibo de prueba con todos los campos requeridos
    echo "1. Creando recibo de prueba...\n";
    $reciboTest = ReciboGastoComun::create([
        'numero_recibo' => 'TEST-ELIM-' . time(),
        'periodo' => '2025-10',
        'fecha_emision' => now(),
        'fecha_vencimiento' => now()->addDays(30),
        'valor_administracion' => 100.00,
        'valor_aseo' => 0.00,
        'valor_vigilancia' => 0.00,
        'valor_mantenimiento' => 0.00,
        'otros_conceptos' => 0.00,
        'total_recibo' => 100.00,
        'estado' => 'activo'
    ]);
    
    echo "   ✅ Recibo creado: {$reciboTest->numero_recibo} (ID: {$reciboTest->id})\n";
    
    // 2. Crear algunos pagos asociados
    echo "\n2. Creando pagos asociados...\n";
    $apartamentos = Apartamento::take(2)->get();
    $pagosCreados = 0;
    
    foreach ($apartamentos as $apartamento) {
        Pago::create([
            'apartamento_id' => $apartamento->id,
            'recibo_gasto_comun_id' => $reciboTest->id,
            'monto' => 100.00,
            'fecha_pago' => now(),
            'confirmado' => false
        ]);
        $pagosCreados++;
    }
    
    echo "   ✅ {$pagosCreados} pagos asociados creados\n";
    
    // 3. Verificar que existe antes de eliminar
    echo "\n3. Verificando existencia antes de eliminar...\n";
    $reciboAntes = ReciboGastoComun::find($reciboTest->id);
    $pagosAntes = Pago::where('recibo_gasto_comun_id', $reciboTest->id)->count();
    
    echo "   📊 Recibo existe: " . ($reciboAntes ? "SÍ" : "NO") . "\n";
    echo "   📊 Pagos asociados: {$pagosAntes}\n";
    
    // 4. Simular eliminación (como lo hace el controlador)
    echo "\n4. Simulando eliminación...\n";
    
    // Validar clave de administrador
    $adminPassword = 'admin123';
    $configuredPassword = config('app.admin_password', 'admin123');
    
    if ($adminPassword === $configuredPassword) {
        echo "   ✅ Validación de clave: EXITOSA\n";
        
        // Obtener pagos asociados antes de eliminar
        $pagosAsociados = Pago::where('recibo_gasto_comun_id', $reciboTest->id)->get();
        echo "   📋 Pagos encontrados para eliminar: " . $pagosAsociados->count() . "\n";
        
        // Eliminar el recibo
        $numeroRecibo = $reciboTest->numero_recibo;
        $reciboId = $reciboTest->id;
        
        echo "   🗑️ Ejecutando delete()...\n";
        $reciboTest->delete();
        echo "   ✅ Método delete() ejecutado\n";
        
        // 5. Verificar eliminación
        echo "\n5. Verificando eliminación...\n";
        $reciboDespues = ReciboGastoComun::find($reciboId);
        $pagosDespues = Pago::where('recibo_gasto_comun_id', $reciboId)->count();
        
        echo "   📊 Recibo después de delete(): " . ($reciboDespues ? "EXISTE (ERROR)" : "NO EXISTE (CORRECTO)") . "\n";
        echo "   📊 Pagos después de delete(): {$pagosDespues}\n";
        
        if ($reciboDespues === null) {
            echo "   ✅ ELIMINACIÓN EXITOSA\n";
        } else {
            echo "   ❌ ERROR: EL RECIBO NO FUE ELIMINADO\n";
            echo "   📋 Datos del recibo que debería estar eliminado:\n";
            echo "       - ID: {$reciboDespues->id}\n";
            echo "       - Número: {$reciboDespues->numero_recibo}\n";
            echo "       - Estado: {$reciboDespues->estado}\n";
        }
        
        if ($pagosDespues === 0) {
            echo "   ✅ PAGOS ELIMINADOS CORRECTAMENTE (CASCADA)\n";
        } else {
            echo "   ❌ ERROR: LOS PAGOS NO FUERON ELIMINADOS\n";
        }
        
    } else {
        echo "   ❌ Validación de clave: FALLIDA\n";
    }
    
    \DB::rollback(); // Rollback para no afectar la base de datos
    echo "\n🔄 Transacción revertida (rollback)\n";
    
} catch (\Exception $e) {
    \DB::rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";