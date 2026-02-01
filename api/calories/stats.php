<?php
/**
 * API: Obtener estadísticas de calorías
 * Datos diarios, semanales y mensuales
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Verificar autenticación
$user = AuthMiddleware::check();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stats = [];

    // Estadísticas de hoy
    if ($period === 'today' || $period === 'all') {
        $stmt = $conn->prepare("
            SELECT 
                COALESCE(SUM(calories), 0) as total_calories,
                COALESCE(SUM(protein), 0) as total_protein,
                COALESCE(SUM(carbs), 0) as total_carbs,
                COALESCE(SUM(fats), 0) as total_fats,
                COUNT(*) as meals_logged
            FROM calories_log
            WHERE user_id = :user_id AND date = CURDATE()
        ");
        $stmt->execute(['user_id' => $user['user_id']]);
        $stats['today'] = $stmt->fetch();
    }

    // Estadísticas de la semana
    if ($period === 'week' || $period === 'all') {
        $stmt = $conn->prepare("
            SELECT 
                date,
                SUM(calories) as calories,
                SUM(protein) as protein,
                SUM(carbs) as carbs,
                SUM(fats) as fats
            FROM calories_log
            WHERE user_id = :user_id 
            AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY date
            ORDER BY date ASC
        ");
        $stmt->execute(['user_id' => $user['user_id']]);
        $stats['week'] = $stmt->fetchAll();
    }

    // Estadísticas del mes
    if ($period === 'month' || $period === 'all') {
        $stmt = $conn->prepare("
            SELECT 
                AVG(daily_calories) as avg_calories,
                MAX(daily_calories) as max_calories,
                MIN(daily_calories) as min_calories
            FROM (
                SELECT date, SUM(calories) as daily_calories
                FROM calories_log
                WHERE user_id = :user_id 
                AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY date
            ) as daily_totals
        ");
        $stmt->execute(['user_id' => $user['user_id']]);
        $stats['month'] = $stmt->fetch();
    }

    // Racha actual
    $stmt = $conn->prepare("SELECT * FROM user_streaks WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user['user_id']]);
    $stats['streak'] = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al obtener estadísticas',
        'details' => $e->getMessage()
    ]);
}
?>