<?php

echo "=== Información de PHP para el servidor web ===".PHP_EOL;
echo "<br>";

echo "Archivo de configuración cargado: " . php_ini_loaded_file() . PHP_EOL;
echo "<br>";

echo "Extensión zip cargada: " . (extension_loaded('zip') ? 'SÍ' : 'NO') . PHP_EOL;
echo "<br>";

echo "Clase ZipArchive existe: " . (class_exists('ZipArchive') ? 'SÍ' : 'NO') . PHP_EOL;
echo "<br>";

if (class_exists('ZipArchive')) {
    try {
        $zip = new ZipArchive();
        echo "Instancia de ZipArchive creada: SÍ" . PHP_EOL;
        echo "<br>";
    } catch (Exception $e) {
        echo "Error al crear ZipArchive: " . $e->getMessage() . PHP_EOL;
        echo "<br>";
    }
}

echo "<br><br>";
echo "=== Extensiones cargadas ===".PHP_EOL;
echo "<br>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    if (strpos(strtolower($ext), 'zip') !== false || strpos(strtolower($ext), 'zlib') !== false) {
        echo "- " . $ext . PHP_EOL;
        echo "<br>";
    }
}

echo "<br><br>";
echo "=== Información completa de ZIP ===".PHP_EOL;
echo "<br>";
if (extension_loaded('zip')) {
    echo "<pre>";
    ob_start();
    phpinfo(INFO_MODULES);
    $info = ob_get_clean();
    
    // Extraer solo la sección de ZIP
    if (preg_match('/<h2>zip<\/h2>.*?(?=<h2>|$)/s', $info, $matches)) {
        echo $matches[0];
    } else {
        echo "No se encontró información específica de ZIP";
    }
    echo "</pre>";
}

?>