<?php
// Script para habilitar la extensión GD en XAMPP

$phpIniPath = 'C:\\xampp\\php\\php.ini';

echo "Habilitando extensión GD en PHP...\n";

// Leer el contenido del archivo php.ini
$content = file_get_contents($phpIniPath);

if ($content === false) {
    die("Error: No se pudo leer el archivo php.ini en: $phpIniPath\n");
}

// Buscar y descomentar la línea de GD
$originalContent = $content;
$content = str_replace(';extension=gd', 'extension=gd', $content);

if ($content === $originalContent) {
    echo "La extensión GD ya está habilitada o no se encontró la línea.\n";
} else {
    // Escribir el contenido modificado de vuelta al archivo
    $result = file_put_contents($phpIniPath, $content);
    
    if ($result === false) {
        die("Error: No se pudo escribir en el archivo php.ini\n");
    }
    
    echo "✓ Extensión GD habilitada exitosamente.\n";
    echo "IMPORTANTE: Debes reiniciar el servidor web (Apache) para que los cambios tomen efecto.\n";
    echo "\nPuedes reiniciar Apache desde el Panel de Control de XAMPP.\n";
}

echo "\nVerificando extensiones PHP actuales...\n";
exec('C:\\xampp\\php\\php.exe -m | findstr -i gd', $output, $returnCode);

if ($returnCode === 0 && !empty($output)) {
    echo "✓ GD está disponible: " . implode(', ', $output) . "\n";
} else {
    echo "⚠ GD aún no está disponible. Reinicia Apache y vuelve a verificar.\n";
}
?>