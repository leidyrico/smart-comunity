<?php

// Simular una petición POST para probar la importación
$url = 'http://localhost:8000/deudas/import/completo';
$filePath = __DIR__ . '/test_import_completo.xlsx';

if (!file_exists($filePath)) {
    die("Error: El archivo Excel no existe: $filePath\n");
}

echo "=== INICIANDO PRUEBA DE IMPORTACIÓN EXCEL ===\n";
echo "Archivo: $filePath\n";
echo "Tamaño: " . filesize($filePath) . " bytes\n";
echo "URL: $url\n\n";

// Crear el archivo CURLFile
$cfile = new CURLFile($filePath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'test_import_completo.xlsx');

// Datos del formulario
$postData = [
    'excel_file' => $cfile,
    'borrar_datos' => '1',
    'validar_relaciones' => '1',
    'continuar_errores' => '1',
    '_token' => 'test' // Necesitaremos obtener el token CSRF real
];

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_HEADER, true);

// Ejecutar la petición
echo "Enviando petición...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\n=== RESULTADO ===\n";
echo "Código HTTP: $httpCode\n";
if ($error) {
    echo "Error cURL: $error\n";
}

echo "\n=== RESPUESTA ===\n";
echo $response;

echo "\n\n=== VERIFICANDO LOGS ===\n";
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    if (!empty($logs)) {
        echo "Logs encontrados:\n";
        echo $logs;
    } else {
        echo "No hay logs nuevos.\n";
    }
} else {
    echo "Archivo de log no encontrado.\n";
}

?>