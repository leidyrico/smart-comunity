<?php

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "<h2>Simulación del proceso de importación Excel</h2>";

// Crear un archivo Excel de prueba similar al que subiría el usuario
echo "<h3>1. Creando archivo Excel de prueba...</h3>";
try {
    $spreadsheet = new Spreadsheet();
    
    // Hoja de Apartamentos
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Apartamentos');
    $sheet->setCellValue('A1', 'numero');
    $sheet->setCellValue('B1', 'propietario');
    $sheet->setCellValue('C1', 'telefono');
    $sheet->setCellValue('D1', 'email');
    $sheet->setCellValue('A2', '101');
    $sheet->setCellValue('B2', 'Juan Pérez');
    $sheet->setCellValue('C2', '123456789');
    $sheet->setCellValue('D2', 'juan@email.com');
    
    $writer = new Xlsx($spreadsheet);
    $testFile = '../storage/app/test_import.xlsx';
    
    // Asegurar que el directorio existe
    $dir = dirname($testFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $writer->save($testFile);
    
    echo "<p>✓ Archivo Excel creado: " . $testFile . "</p>";
    echo "<p>✓ Tamaño: " . filesize($testFile) . " bytes</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error al crear archivo Excel: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "</p>";
    exit;
}

// Simular el proceso exacto del controlador
echo "<h3>2. Simulando proceso del controlador...</h3>";
try {
    echo "<p>Verificando archivo...</p>";
    
    if (!file_exists($testFile)) {
        throw new Exception("El archivo no existe: " . $testFile);
    }
    
    if (!is_readable($testFile)) {
        throw new Exception("El archivo no es legible: " . $testFile);
    }
    
    echo "<p>✓ Archivo existe y es legible</p>";
    
    // Verificar ZipArchive antes de cargar
    if (!class_exists('ZipArchive')) {
        throw new Exception("La clase ZipArchive no está disponible. Verifique que la extensión zip esté habilitada.");
    }
    
    echo "<p>✓ Clase ZipArchive disponible</p>";
    
    // Intentar crear una instancia de ZipArchive
    $zip = new ZipArchive();
    echo "<p>✓ Instancia de ZipArchive creada</p>";
    
    // Cargar el archivo Excel
    echo "<p>Cargando archivo Excel...</p>";
    $spreadsheet = IOFactory::load($testFile);
    echo "<p>✓ Archivo Excel cargado exitosamente</p>";
    
    // Obtener hojas
    $sheetNames = $spreadsheet->getSheetNames();
    echo "<p>✓ Hojas encontradas: " . implode(', ', $sheetNames) . "</p>";
    
    // Leer datos de la hoja de apartamentos
    if (in_array('Apartamentos', $sheetNames)) {
        $worksheet = $spreadsheet->getSheetByName('Apartamentos');
        $data = $worksheet->toArray();
        
        echo "<p>✓ Datos de apartamentos leídos:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach ($data as $index => $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($cell ?? '') . "</td>";
            }
            echo "</tr>";
            if ($index >= 5) { // Limitar a 5 filas para no sobrecargar
                echo "<tr><td colspan='4'>... (más filas)</td></tr>";
                break;
            }
        }
        echo "</table>";
    }
    
    echo "<p style='color:green'><strong>✓ ÉXITO: El proceso de importación funcionó correctamente</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'><strong>✗ ERROR: " . $e->getMessage() . "</strong></p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
    echo "<p>Trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} finally {
    // Limpiar archivo de prueba
    if (isset($testFile) && file_exists($testFile)) {
        unlink($testFile);
        echo "<p>✓ Archivo de prueba eliminado</p>";
    }
}

echo "<h3>3. Información del entorno</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Extensión ZIP: " . (extension_loaded('zip') ? 'Habilitada' : 'Deshabilitada') . "</p>";
echo "<p>Clase ZipArchive: " . (class_exists('ZipArchive') ? 'Disponible' : 'No disponible') . "</p>";
echo "<p>PhpSpreadsheet: " . (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet') ? 'Disponible' : 'No disponible') . "</p>";

?>