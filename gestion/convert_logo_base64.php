<?php

// Script para convertir logo.png a base64
$logoPath = __DIR__ . '/public/logo.png';

if (file_exists($logoPath)) {
    $imageData = file_get_contents($logoPath);
    $base64 = base64_encode($imageData);
    $mimeType = 'image/png';
    $base64Image = 'data:' . $mimeType . ';base64,' . $base64;
    
    echo "Logo convertido a base64:\n";
    echo "Tamaño del archivo: " . filesize($logoPath) . " bytes\n";
    echo "Longitud base64: " . strlen($base64) . " caracteres\n\n";
    echo "Base64 completo:\n";
    echo $base64Image;
    
    // Guardar en archivo para referencia
    file_put_contents(__DIR__ . '/logo_base64.txt', $base64Image);
    echo "\n\nGuardado en logo_base64.txt";
} else {
    echo "Error: No se encontró el archivo logo.png en: " . $logoPath;
}