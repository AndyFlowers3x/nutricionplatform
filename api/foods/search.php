<?php
<<<<<<< HEAD
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
=======
/**
 * API Endpoint: Búsqueda de Alimentos (ACTUALIZADO)
 * Ruta: /nutricion-platform/api/foods/search.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Incluir dependencias
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Verificar autenticación
$user = AuthMiddleware::check();

if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'No autorizado'
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Obtener parámetros de búsqueda
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';

    // Construir consulta SQL
    $sql = "SELECT 
                id,
                name,
                category,
                serving_size,
                serving_unit,
                calories,
                protein,
                carbs,
                fats,
                fiber,
                sodium
            FROM foods 
            WHERE 1=1";
    
    $params = [];

    if (!empty($query)) {
        $sql .= " AND (name LIKE :query OR name_en LIKE :query)";
        $params['query'] = '%' . $query . '%';
    }

    if (!empty($category)) {
        $sql .= " AND category = :category";
        $params['category'] = $category;
    }

    $sql .= " ORDER BY name ASC LIMIT 50";
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
<<<<<<< HEAD
        'count' => count($foods),
        'foods' => $foods
=======
        'foods' => $foods,
        'count' => count($foods)
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
    ]);

} catch (Exception $e) {
    http_response_code(500);
<<<<<<< HEAD
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
=======
    echo json_encode([
        'success' => false,
        'error' => 'Error al buscar alimentos: ' . $e->getMessage()
    ]);
}
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
