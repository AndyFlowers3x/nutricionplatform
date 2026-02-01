<?php
/**
 * API: Buscar alimentos
 * Búsqueda inteligente en base de datos
 */

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

try {
    // Verificar autenticación
    $user = AuthMiddleware::check();

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'error' => 'No autorizado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Obtener parámetros de búsqueda
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->getConnection();

    // Construir consulta SQL
    $sql = "SELECT * FROM foods WHERE 1=1";
    $params = [];

    // Filtro por texto
    if (!empty($query)) {
        $sql .= " AND (name LIKE :query OR name_en LIKE :query)";
        $params['query'] = '%' . $query . '%';
    }

    // Filtro por categoría
    if (!empty($category)) {
        $sql .= " AND category = :category";
        $params['category'] = $category;
    }

    // Ordenar y limitar
    $sql .= " ORDER BY name ASC LIMIT :limit";

    // Preparar y ejecutar
    $stmt = $conn->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Responder
    echo json_encode([
        'success' => true,
        'count' => count($foods),
        'foods' => $foods
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('Error en search.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en búsqueda',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>