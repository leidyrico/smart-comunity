<?php
echo "<h2>Prueba de procesamiento de imágenes base64 con GD</h2>";

// Verificar si GD está habilitada
if (!extension_loaded('gd')) {
    die("<p style='color: red;'>ERROR: Extensión GD no está habilitada</p>");
}

echo "<p style='color: green;'>✓ Extensión GD está habilitada</p>";

// Leer el archivo base64 del logo
$base64File = 'logo_base64.txt';
if (!file_exists($base64File)) {
    die("<p style='color: red;'>ERROR: Archivo logo_base64.txt no encontrado</p>");
}

$base64Data = file_get_contents($base64File);
echo "<p style='color: green;'>✓ Archivo base64 leído correctamente (" . strlen($base64Data) . " caracteres)</p>";

// Extraer solo la parte base64 (sin el prefijo data:image/png;base64,)
if (strpos($base64Data, 'data:image/png;base64,') === 0) {
    $base64Only = substr($base64Data, strlen('data:image/png;base64,'));
    echo "<p style='color: green;'>✓ Prefijo data: removido correctamente</p>";
} else {
    $base64Only = $base64Data;
    echo "<p style='color: orange;'>⚠ No se encontró prefijo data:, usando datos tal como están</p>";
}

// Intentar decodificar la imagen
$imageData = base64_decode($base64Only);
if ($imageData === false) {
    die("<p style='color: red;'>ERROR: No se pudo decodificar la imagen base64</p>");
}

echo "<p style='color: green;'>✓ Imagen decodificada correctamente (" . strlen($imageData) . " bytes)</p>";

// Intentar crear una imagen desde los datos
$image = imagecreatefromstring($imageData);
if ($image === false) {
    die("<p style='color: red;'>ERROR: No se pudo crear imagen desde los datos decodificados</p>");
}

echo "<p style='color: green;'>✓ Imagen creada exitosamente con GD</p>";

// Obtener información de la imagen
$width = imagesx($image);
$height = imagesy($image);
echo "<p style='color: green;'>✓ Dimensiones de la imagen: {$width}x{$height} píxeles</p>";

// Mostrar la imagen
echo "<h3>Imagen procesada:</h3>";
echo "<img src='data:image/png;base64," . base64_encode($imageData) . "' alt='Logo procesado' style='max-width: 200px; border: 1px solid #ccc;'>";

// Limpiar memoria
imagedestroy($image);

echo "<p style='color: green; font-weight: bold;'>✓ TODAS LAS PRUEBAS PASARON - La extensión GD está funcionando correctamente</p>";
?>