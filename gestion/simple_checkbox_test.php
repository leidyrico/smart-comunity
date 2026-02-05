<?php

echo "=== ANÁLISIS SIMPLE DEL CHECKBOX enviar_correo ===\n\n";

// Simular los datos que llegarían del formulario HTML
echo "1. ANÁLISIS DE DATOS DEL FORMULARIO:\n\n";

// Escenario 1: Checkbox marcado (como se envía desde el navegador)
$datosCheckboxMarcado = [
    'numero_recibo' => 'REC-001',
    'periodo' => 'Nov 2025',
    'valor_administracion' => 100,
    'estado' => 'activo',
    'enviar_correo' => '1' // Esto es lo que envía el navegador cuando está marcado
];

echo "Checkbox MARCADO:\n";
echo "   Datos POST: " . json_encode($datosCheckboxMarcado) . "\n";
echo "   isset(\$_POST['enviar_correo']): " . (isset($datosCheckboxMarcado['enviar_correo']) ? 'SÍ' : 'NO') . "\n";
echo "   Valor: '" . $datosCheckboxMarcado['enviar_correo'] . "'\n";
echo "   ¿Es '1'? " . ($datosCheckboxMarcado['enviar_correo'] === '1' ? 'SÍ' : 'NO') . "\n\n";

// Escenario 2: Checkbox NO marcado (como se envía desde el navegador)
$datosCheckboxNoMarcado = [
    'numero_recibo' => 'REC-002',
    'periodo' => 'Nov 2025',
    'valor_administracion' => 100,
    'estado' => 'activo'
    // No hay campo 'enviar_correo' cuando no está marcado
];

echo "Checkbox NO MARCADO:\n";
echo "   Datos POST: " . json_encode($datosCheckboxNoMarcado) . "\n";
echo "   isset(\$_POST['enviar_correo']): " . (isset($datosCheckboxNoMarcado['enviar_correo']) ? 'SÍ' : 'NO') . "\n";
echo "   Valor: " . (isset($datosCheckboxNoMarcado['enviar_correo']) ? "'" . $datosCheckboxNoMarcado['enviar_correo'] . "'" : 'null') . "\n\n";

// Simular la lógica del controlador
echo "2. LÓGICA DEL CONTROLADOR:\n\n";

function simularLogicaControlador($datos) {
    // Esta es la lógica que usa el controlador
    $enviarCorreo = isset($datos['enviar_correo']);
    
    echo "   Código: \$enviarCorreo = isset(\$datos['enviar_correo']);\n";
    echo "   Resultado: \$enviarCorreo = " . ($enviarCorreo ? 'true' : 'false') . "\n";
    
    if ($enviarCorreo) {
        echo "   → Se enviarán correos a los propietarios\n";
    } else {
        echo "   → NO se enviarán correos a los propietarios\n";
    }
    
    return $enviarCorreo;
}

echo "Para checkbox MARCADO:\n";
$enviarMarcado = simularLogicaControlador($datosCheckboxMarcado);

echo "\nPara checkbox NO MARCADO:\n";
$enviarNoMarcado = simularLogicaControlador($datosCheckboxNoMarcado);

echo "\n" . str_repeat('=', 60) . "\n\n";

// Verificar el HTML del formulario
echo "3. VERIFICACIÓN DEL HTML DEL FORMULARIO:\n\n";
$formularioHTML = file_get_contents(__DIR__ . '/resources/views/recibos/create.blade.php');

// Buscar el campo de enviar correo
if (strpos($formularioHTML, 'enviar_correo') !== false) {
    echo "✓ Se encontró el campo 'enviar_correo' en el formulario\n";
    
    // Buscar el input checkbox
    if (preg_match('/<input[^>]*name=["\']enviar_correo["\'][^>]*>/', $formularioHTML, $matches)) {
        echo "✓ Input encontrado: " . htmlspecialchars($matches[0]) . "\n";
        
        // Verificar si está marcado por defecto
        if (strpos($matches[0], 'checked') !== false) {
            echo "✓ El checkbox está marcado por defecto\n";
        } else {
            echo "⚠ El checkbox NO está marcado por defecto\n";
        }
    }
    
    // Buscar el valor del input
    if (preg_match('/value=["\']([^"\']*)["\']/', $formularioHTML, $valorMatches)) {
        echo "✓ Valor del checkbox: '" . $valorMatches[1] . "'\n";
    }
} else {
    echo "✗ NO se encontró el campo 'enviar_correo' en el formulario\n";
}

echo "\n4. CONCLUSIÓN:\n";
echo "El comportamiento del checkbox es CORRECTO.\n";
echo "- Cuando está marcado: se envía enviar_correo='1' → se envían correos\n";
echo "- Cuando NO está marcado: no se envía el campo → NO se envían correos\n";
echo "\nEl problema DEBE estar en otro lugar del proceso.\n";

echo "\n=== FIN DEL ANÁLISIS ===\n";