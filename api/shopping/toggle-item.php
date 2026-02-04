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

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['item_id'])) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'ID requerido']));
    }

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("UPDATE shopping_lists SET is_checked = NOT is_checked WHERE id = ? AND user_id = ?");
    $stmt->execute([$data['item_id'], $user['user_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>