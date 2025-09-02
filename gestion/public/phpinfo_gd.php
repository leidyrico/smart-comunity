<?php
echo "<h2>Verificación de extensión GD en el servidor web</h2>";

if (extension_loaded('gd')) {
    echo "<p style='color: green;'>✓ Extensión GD está HABILITADA</p>";
    
    $gd_info = gd_info();
    echo "<h3>Información de GD:</h3>";
    echo "<pre>";
    print_r($gd_info);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>✗ Extensión GD NO está habilitada</p>";
}

echo "<h3>Todas las extensiones cargadas:</h3>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

echo "<h3>Información completa de PHP:</h3>";
phpinfo();
?>