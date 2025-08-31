<?php

echo "=== CORRECCIÓN DE ARCHIVO DE BACKUP ===\n\n";

// Buscar el archivo de backup más reciente
$backupFiles = glob('backup_*.sql');
if (empty($backupFiles)) {
    echo "No se encontraron archivos de backup.\n";
    exit;
}

// Ordenar por fecha de modificación (más reciente primero)
usort($backupFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$originalFile = $backupFiles[0];
$correctedFile = str_replace('.sql', '_corregido.sql', $originalFile);

echo "Archivo original: {$originalFile}\n";
echo "Archivo corregido: {$correctedFile}\n\n";

// Leer el contenido del backup
$content = file_get_contents($originalFile);
if ($content === false) {
    echo "Error: No se pudo leer el archivo de backup.\n";
    exit;
}

echo "Contenido original leído: " . number_format(strlen($content)) . " caracteres\n";

// Corregir los problemas de sintaxis SQL
$corrections = 0;

// 1. Corregir campos de monto vacíos en pagos (monto, -> monto_pagado)
$pattern1 = '/INSERT INTO pagos \([^)]+\) VALUES \([^,]+, [^,]+, [^,]+, , /i';
$replacement1 = function($matches) {
    return str_replace(', , ', ', 0, ', $matches[0]);
};
$content = preg_replace_callback($pattern1, $replacement1, $content, -1, $count1);
$corrections += $count1;
echo "Corregidos {$count1} campos de monto vacíos en pagos\n";

// 2. Corregir campos de monto vacíos en recibos
$pattern2 = '/INSERT INTO recibo_gasto_comuns \([^)]+\) VALUES \([^,]+, [^,]+, , /i';
$replacement2 = function($matches) {
    return str_replace(', , ', ', 0, ', $matches[0]);
};
$content = preg_replace_callback($pattern2, $replacement2, $content, -1, $count2);
$corrections += $count2;
echo "Corregidos {$count2} campos de monto vacíos en recibos\n";

// 3. Corregir cualquier otro campo vacío que cause problemas
// Buscar patrones como ", , " y reemplazar con ", 0, " o ", NULL, " según el contexto
$pattern3 = '/, , /';
$content = preg_replace($pattern3, ', 0, ', $content, -1, $count3);
$corrections += $count3;
echo "Corregidos {$count3} campos adicionales vacíos\n";

// 4. Asegurar que no hay líneas que terminen con comas sueltas
$lines = explode("\n", $content);
$correctedLines = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '--') === 0) {
        $correctedLines[] = $line;
        continue;
    }
    
    // Si es una línea INSERT, verificar que esté bien formada
    if (strpos($line, 'INSERT INTO') === 0) {
        // Asegurar que termine con punto y coma
        if (!empty($line) && substr($line, -1) !== ';') {
            $line .= ';';
        }
    }
    
    $correctedLines[] = $line;
}

$content = implode("\n", $correctedLines);

echo "\nTotal de correcciones aplicadas: {$corrections}\n";
echo "Contenido corregido: " . number_format(strlen($content)) . " caracteres\n\n";

// Guardar el archivo corregido
if (file_put_contents($correctedFile, $content) === false) {
    echo "Error: No se pudo guardar el archivo corregido.\n";
    exit;
}

echo "✓ Archivo corregido guardado exitosamente: {$correctedFile}\n";
echo "Tamaño del archivo: " . number_format(filesize($correctedFile)) . " bytes\n\n";

// Mostrar una muestra del contenido corregido
echo "=== MUESTRA DEL CONTENIDO CORREGIDO ===\n";
$lines = explode("\n", $content);
$sampleLines = array_slice($lines, 0, 10);
foreach ($sampleLines as $i => $line) {
    echo ($i + 1) . ": {$line}\n";
}

echo "\n✓ Corrección completada. Ahora puedes usar el archivo corregido para la restauración.\n";

?>