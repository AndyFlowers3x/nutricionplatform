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

    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    $db = new Database();
    $conn = $db->getConnection();

    $sql = "SELECT * FROM foods WHERE 1=1";
    $params = [];

    if (!empty($query)) {
        $sql .= " AND (name LIKE ? OR name_en LIKE ?)";
        $params[] = '%' . $query . '%';
        $params[] = '%' . $query . '%';
    }

    if (!empty($category)) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY name ASC LIMIT ?";
    $params[] = $limit;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($foods),
        'foods' => $foods
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>