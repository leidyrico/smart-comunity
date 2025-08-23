<?php
header('Content-Type: text/plain');

echo "=== Intentando cargar extensión ZIP dinámicamente ===\n";

// Verificar estado inicial
echo "Estado inicial:\n";
echo "- Extensión zip cargada: " . (extension_loaded('zip') ? 'SÍ' : 'NO') . "\n";
echo "- Clase ZipArchive existe: " . (class_exists('ZipArchive') ? 'SÍ' : 'NO') . "\n";

// Intentar cargar la extensión dinámicamente
if (!extension_loaded('zip')) {
    echo "\nIntentando cargar extensión zip dinámicamente...\n";
    
    // Posibles nombres de la extensión
    $possible_extensions = [
        'zip.dll',
        'php_zip.dll',
        'zip.so',
        'php_zip.so'
    ];
    
    foreach ($possible_extensions as $ext) {
        echo "Intentando cargar: $ext\n";
        if (@dl($ext)) {
            echo "✓ Extensión $ext cargada exitosamente\n";
            break;
        } else {
            echo "✗ No se pudo cargar $ext\n";
        }
    }
    
    // Verificar nuevamente
    echo "\nEstado después del intento de carga:\n";
    echo "- Extensión zip cargada: " . (extension_loaded('zip') ? 'SÍ' : 'NO') . "\n";
    echo "- Clase ZipArchive existe: " . (class_exists('ZipArchive') ? 'SÍ' : 'NO') . "\n";
}

// Información sobre dl()
echo "\n=== Información sobre carga dinámica ===\n";
echo "Función dl() habilitada: " . (function_exists('dl') ? 'SÍ' : 'NO') . "\n";
echo "enable_dl: " . (ini_get('enable_dl') ? 'SÍ' : 'NO') . "\n";

// Información sobre extensiones disponibles
echo "\n=== Directorio de extensiones ===\n";
echo "extension_dir: " . ini_get('extension_dir') . "\n";

$ext_dir = ini_get('extension_dir');
if (is_dir($ext_dir)) {
    echo "\nArchivos en el directorio de extensiones:\n";
    $files = scandir($ext_dir);
    foreach ($files as $file) {
        if (strpos($file, 'zip') !== false) {
            echo "- $file\n";
        }
    }
} else {
    echo "El directorio de extensiones no existe o no es accesible\n";
}

// Información del php.ini
echo "\n=== Información del php.ini ===\n";
echo "php.ini cargado: " . php_ini_loaded_file() . "\n";
echo "Archivos .ini adicionales: " . php_ini_scanned_files() . "\n";

// Verificar configuración específica de zip
echo "\n=== Configuración de ZIP en php.ini ===\n";
$ini_content = file_get_contents(php_ini_loaded_file());
if (preg_match('/^\s*;?\s*extension\s*=\s*zip/m', $ini_content, $matches)) {
    echo "Línea encontrada: " . trim($matches[0]) . "\n";
    if (strpos($matches[0], ';') === false || strpos($matches[0], ';') > strpos($matches[0], 'extension')) {
        echo "Estado: HABILITADA\n";
    } else {
        echo "Estado: COMENTADA (deshabilitada)\n";
    }
} else {
    echo "No se encontró configuración de extensión zip en php.ini\n";
}

?>