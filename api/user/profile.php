<?php
/**
 * API: Obtener perfil del usuario
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';

// Verificar autenticación
$user = AuthMiddleware::check();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Obtener datos del usuario
    $stmt = $conn->prepare("
        SELECT u.id, u.email, u.name, u.picture, 
               hp.weight, hp.height, hp.age, hp.activity_level,
               us.language, us.weight_unit
        FROM users u
        LEFT JOIN health_profiles hp ON u.id = hp.user_id
        LEFT JOIN user_settings us ON u.id = us.user_id
        WHERE u.id = :user_id
    ");
    
    $stmt->execute(['user_id' => $user['user_id']]);
    $userData = $stmt->fetch();

    if ($userData) {
        echo json_encode([
            'success' => true,
            'user' => $userData
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos']);
}
?>