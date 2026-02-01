<?php
/**
 * Cargador de Variables de Entorno
 * Archivo: config/load_env.php
 */

// Función para cargar variables de entorno desde archivo .env
function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        throw new Exception("Archivo .env no encontrado en: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

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
        }
    }
}

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