<?php
/**
 * Script de Diagnóstico Mejorado - Sistema de Calorías
 * Ejecutar en: /nutricion-platform/api/test-connection-v2.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Diagnóstico Completo del Sistema</h1>";
echo "<hr>";

// 1. Verificar archivos
echo "<h2>1️⃣ Verificación de Archivos</h2>";

$files_to_check = [
    __DIR__ . '/foods/search.php' => 'Búsqueda de alimentos',
    __DIR__ . '/calories/log-meal.php' => 'Registro de comidas',
    __DIR__ . '/../config/database.php' => 'Configuración de BD',
    __DIR__ . '/../middleware/auth.php' => 'Middleware de autenticación'
];

foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        echo "✅ <strong>$description:</strong> Existe<br>";
    } else {
        echo "❌ <strong>$description:</strong> NO EXISTE - $file<br>";
    }
}

echo "<hr>";

// 2. Verificar configuración de base de datos
echo "<h2>2️⃣ Verificación de Configuración de BD</h2>";

$config_file = __DIR__ . '/../config/database.php';

if (file_exists($config_file)) {
    echo "✅ <strong>Archivo database.php:</strong> Existe<br><br>";
    
    // Mostrar contenido (primeras líneas)
    echo "<details>";
    echo "<summary>📄 Ver contenido de database.php (click para expandir)</summary>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    $content = file_get_contents($config_file);
    // Ocultar contraseñas
    $content = preg_replace('/(password|passwd|pwd)[\s]*=[\s]*[\'"]([^\'"]*)[\'"]/', '$1 = \'****\'', $content);
    echo htmlspecialchars($content);
    echo "</pre>";
    echo "</details><br>";
} else {
    echo "❌ <strong>Archivo database.php:</strong> NO EXISTE<br>";
}

echo "<hr>";

// 3. Intentar conexión directa
echo "<h2>3️⃣ Prueba de Conexión Directa a MySQL</h2>";

// Intentar con credenciales por defecto de XAMPP
$default_configs = [
    [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db' => 'nutricion_platform'
    ],
    [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'db' => 'nutricion_platform'
    ]
];

$connected = false;
$working_config = null;

foreach ($default_configs as $config) {
    try {
        echo "🔄 Intentando: {$config['user']}@{$config['host']}/{$config['db']}<br>";
        
        $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4";
        $conn = new PDO($dsn, $config['user'], $config['pass']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ <strong>CONEXIÓN EXITOSA!</strong><br>";
        echo "Host: {$config['host']}<br>";
        echo "Usuario: {$config['user']}<br>";
        echo "Base de datos: {$config['db']}<br><br>";
        
        $connected = true;
        $working_config = $config;
        break;
        
    } catch (PDOException $e) {
        echo "❌ Falló: " . $e->getMessage() . "<br><br>";
    }
}

if (!$connected) {
    echo "<div style='background: #fee; padding: 15px; border-left: 4px solid #f00; margin: 10px 0;'>";
    echo "<strong>⚠️ NO SE PUDO CONECTAR A LA BASE DE DATOS</strong><br><br>";
    echo "<strong>Posibles causas:</strong><br>";
    echo "1. XAMPP MySQL no está corriendo<br>";
    echo "2. El nombre de la base de datos es incorrecto<br>";
    echo "3. Las credenciales son incorrectas<br><br>";
    echo "<strong>Soluciones:</strong><br>";
    echo "• Abre el Panel de Control de XAMPP<br>";
    echo "• Asegúrate que 'MySQL' esté en verde (Start)<br>";
    echo "• Verifica en phpMyAdmin que exista la BD 'nutricion_platform'<br>";
    echo "</div>";
}

echo "<hr>";

// 4. Si hay conexión, verificar tablas
if ($connected && $conn) {
    echo "<h2>4️⃣ Verificación de Tablas</h2>";
    
    $tables = ['foods', 'calories_log', 'users', 'health_profiles', 'user_settings'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                // Contar registros
                $count_stmt = $conn->query("SELECT COUNT(*) as total FROM $table");
                $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                echo "✅ <strong>Tabla '$table':</strong> Existe con $count registros<br>";
            } else {
                echo "❌ <strong>Tabla '$table':</strong> NO EXISTE<br>";
            }
        } catch (Exception $e) {
            echo "❌ <strong>Tabla '$table':</strong> Error - " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<hr>";
    
    // 5. Mostrar algunos alimentos
    echo "<h2>5️⃣ Alimentos Disponibles (primeros 10)</h2>";
    
    try {
        $stmt = $conn->query("SELECT id, name, category, calories FROM foods LIMIT 10");
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Calorías</th></tr>";
        while ($food = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$food['id']}</td>";
            echo "<td>{$food['name']}</td>";
            echo "<td>{$food['category']}</td>";
            echo "<td>{$food['calories']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
    
    echo "<hr>";
    
    // 6. Configuración sugerida para database.php
    echo "<h2>6️⃣ Configuración Recomendada</h2>";
    
    if ($working_config) {
        echo "<div style='background: #efe; padding: 15px; border-left: 4px solid #0a0; margin: 10px 0;'>";
        echo "<strong>✅ Usa esta configuración en tu archivo database.php:</strong><br><br>";
        echo "<pre style='background: white; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars("<?php
class Database {
    private \$host = '{$working_config['host']}';
    private \$db_name = '{$working_config['db']}';
    private \$username = '{$working_config['user']}';
    private \$password = '{$working_config['pass']}';
    private \$conn;
    
    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8mb4\",
                \$this->username,
                \$this->password
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException \$e) {
            echo \"Error de conexión: \" . \$e->getMessage();
        }
        return \$this->conn;
    }
}
?>");
        echo "</pre>";
        echo "</div>";
    }
}

echo "<hr>";

// 7. Sesión
echo "<h2>7️⃣ Verificación de Sesión</h2>";

session_start();
if (isset($_SESSION['user_id'])) {
    echo "✅ <strong>Usuario autenticado:</strong> ID " . $_SESSION['user_id'] . "<br>";
} else {
    echo "⚠️ <strong>No hay sesión activa</strong><br>";
    echo "Debes iniciar sesión en: <a href='../pages/login.php'>Login</a><br>";
}

echo "<hr>";

// 8. URLs de prueba
echo "<h2>8️⃣ URLs de Prueba</h2>";

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
            "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

echo "<p><strong>Una vez que inicies sesión, prueba estos endpoints:</strong></p>";
echo "<ul>";
echo "<li><a href='{$base_url}/foods/search.php?q=pollo' target='_blank'>Buscar 'pollo'</a></li>";
echo "<li><a href='{$base_url}/foods/search.php?category=fruits' target='_blank'>Ver frutas</a></li>";
echo "<li><a href='{$base_url}/foods/search.php' target='_blank'>Todos los alimentos</a></li>";
echo "</ul>";

echo "<hr>";

// 9. Información del sistema
echo "<h2>9️⃣ Información del Sistema</h2>";
echo "📁 <strong>Directorio actual:</strong> " . __DIR__ . "<br>";
echo "🌐 <strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "🐘 <strong>PHP:</strong> " . PHP_VERSION . "<br>";
echo "💾 <strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? '✅ Instalado' : '❌ No instalado') . "<br>";

echo "<hr>";

if ($connected) {
    echo "<div style='background: #efe; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #0a0; margin-top: 0;'>✅ ¡Sistema Listo!</h3>";
    echo "<p>La base de datos está funcionando correctamente.</p>";
    echo "<p><strong>Siguiente paso:</strong></p>";
    echo "<ol>";
    echo "<li>Actualiza tu archivo <code>config/database.php</code> con la configuración mostrada arriba</li>";
    echo "<li>Inicia sesión en: <a href='../pages/login.php'>../pages/login.php</a></li>";
    echo "<li>Ve a: <a href='../pages/calories.php'>../pages/calories.php</a></li>";
    echo "<li>Prueba agregar una comida</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #fee; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #f00; margin-top: 0;'>❌ Problema de Conexión</h3>";
    echo "<p>No se pudo conectar a la base de datos.</p>";
    echo "<p><strong>Pasos a seguir:</strong></p>";
    echo "<ol>";
    echo "<li>Abre el Panel de Control de XAMPP</li>";
    echo "<li>Asegúrate que MySQL esté corriendo (botón verde)</li>";
    echo "<li>Abre phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Verifica que exista la base de datos 'nutricion_platform'</li>";
    echo "<li>Recarga esta página</li>";
    echo "</ol>";
    echo "</div>";
}
?>