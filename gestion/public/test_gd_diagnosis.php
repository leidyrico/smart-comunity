<?php
// Diagnóstico completo de la extensión GD
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnóstico de Extensión GD</h1>";

// 1. Verificar si GD está cargada
echo "<h2>1. Estado de la extensión GD:</h2>";
if (extension_loaded('gd')) {
    echo "<p style='color: green;'>✓ Extensión GD está CARGADA</p>";
    
    // Información de GD
    $gd_info = gd_info();
    echo "<h3>Información de GD:</h3>";
    echo "<ul>";
    foreach ($gd_info as $key => $value) {
        echo "<li><strong>$key:</strong> " . (is_bool($value) ? ($value ? 'Sí' : 'No') : $value) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Extensión GD NO está cargada</p>";
}

// 2. Verificar funciones específicas
echo "<h2>2. Funciones GD disponibles:</h2>";
$functions = ['imagecreatefromstring', 'imagepng', 'imagejpeg', 'imagegif', 'base64_decode'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<p style='color: green;'>✓ $func está disponible</p>";
    } else {
        echo "<p style='color: red;'>✗ $func NO está disponible</p>";
    }
}

// 3. Probar procesamiento de imagen base64
echo "<h2>3. Prueba de procesamiento de imagen base64:</h2>";

try {
    // Leer el archivo base64 del logo
    $logoBase64File = __DIR__ . '/../logo_base64.txt';
    
    if (file_exists($logoBase64File)) {
        $logoBase64 = file_get_contents($logoBase64File);
        echo "<p style='color: green;'>✓ Archivo logo_base64.txt encontrado</p>";
        
        // Extraer solo la parte base64 (sin el prefijo data:image/png;base64,)
        if (strpos($logoBase64, 'data:image/png;base64,') === 0) {
            $base64Data = substr($logoBase64, strlen('data:image/png;base64,'));
            echo "<p style='color: green;'>✓ Prefijo base64 removido correctamente</p>";
            
            // Decodificar base64
            $imageData = base64_decode($base64Data);
            if ($imageData !== false) {
                echo "<p style='color: green;'>✓ Decodificación base64 exitosa</p>";
                echo "<p>Tamaño de datos decodificados: " . strlen($imageData) . " bytes</p>";
                
                // Crear imagen desde string
                if (extension_loaded('gd')) {
                    $image = imagecreatefromstring($imageData);
                    if ($image !== false) {
                        echo "<p style='color: green;'>✓ Imagen creada exitosamente desde string</p>";
                        echo "<p>Dimensiones: " . imagesx($image) . "x" . imagesy($image) . " píxeles</p>";
                        
                        // Mostrar la imagen
                        echo "<h3>Imagen procesada:</h3>";
                        echo "<img src='$logoBase64' alt='Logo procesado' style='max-width: 200px; border: 1px solid #ccc;'>";
                        
                        imagedestroy($image);
                    } else {
                        echo "<p style='color: red;'>✗ Error al crear imagen desde string</p>";
                    }
                } else {
                    echo "<p style='color: red;'>✗ GD no está disponible para crear imagen</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Error en decodificación base64</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Formato de base64 incorrecto</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Archivo logo_base64.txt no encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error durante la prueba: " . $e->getMessage() . "</p>";
}

// 4. Información del servidor
echo "<h2>4. Información del servidor:</h2>";
echo "<p><strong>Versión PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>SAPI:</strong> " . php_sapi_name() . "</p>";
echo "<p><strong>Archivo php.ini:</strong> " . php_ini_loaded_file() . "</p>";

// 5. Extensiones cargadas
echo "<h2>5. Todas las extensiones cargadas:</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<p>" . implode(', ', $extensions) . "</p>";

echo "<hr>";
echo "<p><em>Diagnóstico completado: " . date('Y-m-d H:i:s') . "</em></p>";
?>