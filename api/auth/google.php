<?php
/**
 * Google OAuth Callback
 * Maneja autenticación con Google
 */

// Habilitar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Cargar variables de entorno
require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/jwt.php';

// Configuración de Google
$google_config = require __DIR__ . '/../../config/google.php';

// Si no hay código, redirigir a Google
if (!isset($_GET['code'])) {
    $auth_url = $google_config['auth_url'] . '?' . http_build_query([
        'client_id' => $google_config['client_id'],
        'redirect_uri' => $google_config['redirect_uri'],
        'response_type' => 'code',
        'scope' => implode(' ', $google_config['scopes']),
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);

    header('Location: ' . $auth_url);
    exit;
}

try {
    // Intercambiar código por token
    $ch = curl_init($google_config['token_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code' => $_GET['code'],
        'client_id' => $google_config['client_id'],
        'client_secret' => $google_config['client_secret'],
        'redirect_uri' => $google_config['redirect_uri'],
        'grant_type' => 'authorization_code'
    ]));

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        throw new Exception('Error en cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (!isset($token_data['access_token'])) {
        throw new Exception('Error al obtener token de Google: ' . json_encode($token_data));
    }

    // Obtener información del usuario
    $ch = curl_init($google_config['userinfo_url'] . '?access_token=' . $token_data['access_token']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userinfo_response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        throw new Exception('Error al obtener userinfo: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    $userinfo = json_decode($userinfo_response, true);

    if (!isset($userinfo['id'])) {
        throw new Exception('Error al decodificar userinfo: ' . json_encode($userinfo));
    }

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->getConnection();

    // Guardar o actualizar usuario
// Guardar o actualizar usuario
$stmt = $conn->prepare("
    INSERT INTO users (google_id, email, name, picture, last_login) 
    VALUES (:google_id, :email, :name, :picture, NOW())
    ON DUPLICATE KEY UPDATE 
        name = VALUES(name), 
        picture = VALUES(picture), 
        last_login = NOW()
");

$result = $stmt->execute([
    'google_id' => $userinfo['id'],
    'email' => $userinfo['email'],
    'name' => $userinfo['name'],
    'picture' => $userinfo['picture'] ?? null
]);
    if (!$result) {
        throw new Exception('Error al guardar usuario en base de datos');
    }

    // Obtener ID del usuario
    $stmt = $conn->prepare("SELECT id FROM users WHERE google_id = :google_id");
    $stmt->execute(['google_id' => $userinfo['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Error al recuperar usuario de la base de datos');
    }

    // Crear JWT
    $jwt = JWT::create($user['id'], $userinfo['email'], $userinfo['name']);

    // Guardar en cookie
    setcookie('auth_token', $jwt, [
        'expires' => time() + 86400,
        'path' => '/',
        'domain' => '',
        'secure' => false, // false para localhost
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Redirigir al dashboard
    header('Location: /nutricion-platform/public/dashboard.php');
    exit;

} catch (Exception $e) {
    // Mostrar error detallado para debug
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error de Autenticación</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .error-box {
                background: white;
                padding: 30px;
                border-radius: 10px;
                border-left: 5px solid #ef4444;
            }
            h1 { color: #ef4444; }
            code {
                background: #f3f4f6;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 14px;
            }
            .back-btn {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>❌ Error al procesar autenticación</h1>
            <p><strong>Mensaje de error:</strong></p>
            <code>" . htmlspecialchars($e->getMessage()) . "</code>
            
            <h3>Información de debug:</h3>
            <ul>
                <li><strong>Archivo:</strong> " . $e->getFile() . "</li>
                <li><strong>Línea:</strong> " . $e->getLine() . "</li>
            </ul>
            
            <a href='/nutricion-platform/public/login.php' class='back-btn'>← Volver al login</a>
        </div>
    </body>
    </html>";
    
    error_log("Error OAuth: " . $e->getMessage());
    exit;
}
?>