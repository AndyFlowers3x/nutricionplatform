<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

ob_end_clean();

try {
    $user = AuthMiddleware::check();
    if (!$user) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'No autorizado']));
    }

    $db = new Database();
    $conn = $db->getConnection();

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    $stmt = $conn->prepare("
        SELECT message, response, created_at
        FROM assistant_conversations
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    
    $stmt->execute([$user['user_id'], $limit]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Invertir para mostrar más antiguas primero
    $conversations = array_reverse($conversations);

    echo json_encode([
        'success' => true,
        'conversations' => $conversations
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>