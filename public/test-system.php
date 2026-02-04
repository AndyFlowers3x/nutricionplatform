<?php
/**
 * Script de verificación del sistema
 * Usa esto para verificar que todo está configurado correctamente
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test del Sistema</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{color:green;}.error{color:red;}.warning{color:orange;}";
echo "h2{border-bottom:2px solid #10B981;padding-bottom:10px;}</style></head><body>";

echo "<h1>🔍 Verificación del Sistema Weightloss Professional Nutrition</h1>";

// 1. Verificar PHP
echo "<h2>1. PHP</h2>";
echo "<p class='success'>✅ PHP Version: " . phpversion() . "</p>";

// 2. Verificar extensiones
echo "<h2>2. Extensiones PHP</h2>";
$extensions = ['pdo', 'pdo_mysql', 'curl', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ $ext: Instalado</p>";
    } else {
        echo "<p class='error'>❌ $ext: NO instalado</p>";
    }
}

// 3. Verificar archivos críticos
echo "<h2>3. Archivos del Sistema</h2>";
$files = [
    '../.env' => 'Configuración',
    '../config/database.php' => 'Base de datos',
    '../config/load_env.php' => 'Variables de entorno',
    '../config/google.php' => 'Google OAuth',
    '../config/jwt.php' => 'JWT',
    '../api/auth/google.php' => 'Auth Google',
    '../api/health/save-profile.php' => 'Guardar perfil',
    'login.php' => 'Login',
    'dashboard.php' => 'Dashboard',
    'questionnaire.php' => 'Cuestionario',
    '../assets/css/main.css' => 'CSS Principal',
    '../assets/css/dashboard.css' => 'CSS Dashboard',
    '../assets/css/questionnaire.css' => 'CSS Cuestionario',
    '../assets/js/auth.js' => 'JS Auth',
    '../assets/js/dashboard.js' => 'JS Dashboard',
    '../assets/js/questionnaire.js' => 'JS Cuestionario'
];

foreach ($files as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='success'>✅ $desc: Existe</p>";
    } else {
        echo "<p class='error'>❌ $desc: NO encontrado ($file)</p>";
    }
}

// 4. Verificar base de datos
echo "<h2>4. Conexión a Base de Datos</h2>";
try {
    require_once __DIR__ . '/../config/load_env.php';
    require_once __DIR__ . '/../config/database.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p class='success'>✅ Conexión exitosa</p>";
    
    // Verificar tablas
    $tables = ['users', 'health_profiles', 'user_settings', 'user_streaks', 'meals', 'meal_plans', 'calories_log'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ Tabla '$table': Existe</p>";
        } else {
            echo "<p class='error'>❌ Tabla '$table': NO existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// 5. Verificar Google OAuth
echo "<h2>5. Configuración Google OAuth</h2>";
if (isset($_ENV['GOOGLE_CLIENT_ID']) && !empty($_ENV['GOOGLE_CLIENT_ID'])) {
    echo "<p class='success'>✅ Client ID: Configurado</p>";
} else {
    echo "<p class='error'>❌ Client ID: NO configurado</p>";
}

if (isset($_ENV['GOOGLE_CLIENT_SECRET']) && !empty($_ENV['GOOGLE_CLIENT_SECRET'])) {
    echo "<p class='success'>✅ Client Secret: Configurado</p>";
} else {
    echo "<p class='error'>❌ Client Secret: NO configurado</p>";
}

echo "<h2>✅ Verificación Completada</h2>";
echo "<p><a href='login.php' style='display:inline-block;padding:10px 20px;background:#10B981;color:white;text-decoration:none;border-radius:5px;'>Ir al Login</a></p>";

echo "</body></html>";
?>