<?php

/**
 * Configuración para aumentar el tiempo de ejecución máximo
 * Soluciona el error: Maximum execution time of 60 seconds exceeded
 * en AbstractStream.php línea 82 durante envío de correos SMTP
 */

// Aumentar tiempo de ejecución a 1800 segundos (30 minutos)
ini_set('max_execution_time', 1800);

// También aumentar el tiempo límite de memoria si es necesario
ini_set('memory_limit', '256M');

// Configurar timeouts específicos para operaciones de red
ini_set('default_socket_timeout', 120);

// Configuración aplicada silenciosamente