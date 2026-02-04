<?php
/**
<<<<<<< HEAD
 * Cargador de variables de entorno
 * Lee el archivo .env y carga las variables
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Archivo .env no encontrado en: " . $path);
=======
 * Cargador de Variables de Entorno
 * Archivo: config/load_env.php
 */

// Función para cargar variables de entorno desde archivo .env
function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        throw new Exception("Archivo .env no encontrado en: $path");
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

<<<<<<< HEAD
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
=======
        // Separar clave=valor
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            
            $name = trim($name);
            $value = trim($value);
            
            // Remover comillas si existen
            $value = trim($value, '"\'');
            
            // Establecer en $_ENV y putenv
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
        }
    }
}

<<<<<<< HEAD
// Cargar .env desde la raíz
$envPath = __DIR__ . '/../.env';
loadEnv($envPath);
?>
```

=======
// Función helper para obtener variables de entorno
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        $value = $_ENV[$key] ?? $default;
    }
    
    // Convertir valores booleanos
    if (is_string($value)) {
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        if ($lower === 'null') return null;
    }
    
    return $value;
}

// Cargar automáticamente al incluir este archivo
try {
    loadEnv();
} catch (Exception $e) {
    error_log("Error cargando .env: " . $e->getMessage());
    if (ini_get('display_errors')) {
        die("Error: " . $e->getMessage());
    }
}
?>
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
