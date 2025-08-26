<?php

// Simular una petición AJAX para probar la funcionalidad
$apartamento_id = 10; // ID del apartamento de prueba

// Hacer una petición HTTP a la API
$url = "http://localhost/smart-comunity/gestion/public/api/recibos-por-apartamento?apartamento_id=" . $apartamento_id;

echo "=== PRUEBA DE API AJAX ===\n";
echo "URL: $url\n\n";

// Usar cURL para hacer la petición
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Código HTTP: $httpCode\n";

if ($error) {
    echo "Error cURL: $error\n";
} else {
    echo "Respuesta:\n";
    echo $response . "\n\n";
    
    // Intentar decodificar JSON
    $data = json_decode($response, true);
    if ($data !== null) {
        echo "Datos decodificados:\n";
        echo "Total recibos encontrados: " . count($data) . "\n";
        
        foreach ($data as $recibo) {
            echo "- ID: {$recibo['id']}, Número: {$recibo['numero_recibo']}, Período: {$recibo['periodo']}, Total: {$recibo['total_recibo']}\n";
        }
    } else {
        echo "Error al decodificar JSON\n";
    }
}

echo "\n=== FIN DE LA PRUEBA ===\n";