<?php
/**
 * Cargador de variables de entorno
 * Lee el archivo .env y carga las variables
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Archivo .env no encontrado en: " . $path);
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Verificar que la línea contenga un '='
        if (strpos($line, '=') === false) {
            continue; // Saltar líneas sin '='
        }

        // Separar clave y valor
        $parts = explode('=', $line, 2);
        
        if (count($parts) !== 2) {
            continue; // Saltar si no hay exactamente 2 partes
        }
        
        $name = trim($parts[0]);
        $value = trim($parts[1]);

        // Asignar a $_ENV
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

// Cargar .env desde la raíz
$envPath = __DIR__ . '/../.env';
loadEnv($envPath);
?>
```

