<?php
/**
 * API: Obtener plan de comidas del usuario
 * Retorna plan de la semana actual
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

$user = AuthMiddleware::check();

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Obtener fecha de inicio y fin de la semana
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    // Obtener plan de comidas con detalles
    $stmt = $conn->prepare("
        SELECT 
            mp.*,
            m.name as meal_name,
            m.description,
            m.calories,
            m.protein,
            m.carbs,
            m.fats,
            m.preparation_time,
            m.image_url
        FROM meal_plans mp
        JOIN meals m ON mp.meal_id = m.id
        WHERE mp.user_id = :user_id 
        AND mp.date BETWEEN :start_date AND :end_date
        ORDER BY mp.date ASC, 
                 FIELD(mp.meal_type, 'breakfast', 'lunch', 'dinner', 'snack')
    ");

    $stmt->execute([
        'user_id' => $user['user_id'],
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek
    ]);

    $plan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar por día
    $weekPlan = [];
    foreach ($plan as $meal) {
        $date = $meal['date'];
        if (!isset($weekPlan[$date])) {
            $weekPlan[$date] = [
                'date' => $date,
                'day_name' => strftime('%A', strtotime($date)),
                'meals' => []
            ];
        }
        $weekPlan[$date]['meals'][] = $meal;
    }

    echo json_encode([
        'success' => true,
        'week_start' => $startOfWeek,
        'week_end' => $endOfWeek,
        'plan' => array_values($weekPlan),
        'has_plan' => count($plan) > 0
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener plan',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>