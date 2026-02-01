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
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));

    $stmt = $conn->prepare("SELECT * FROM shopping_lists WHERE user_id = ? AND week_start = ? ORDER BY category ASC, item_name ASC");
    $stmt->execute([$user['user_id'], $startOfWeek]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($items as $item) {
        $category = $item['category'];
        if (!isset($grouped[$category])) {
            $grouped[$category] = [];
        }
        $grouped[$category][] = $item;
    }

    echo json_encode([
        'success' => true,
        'items' => $items,
        'grouped' => $grouped,
        'has_list' => count($items) > 0,
        'week_start' => $startOfWeek
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>