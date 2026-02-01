<?php
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

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'foods' => $foods,
        'count' => count($foods)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al buscar alimentos: ' . $e->getMessage()
    ]);
}