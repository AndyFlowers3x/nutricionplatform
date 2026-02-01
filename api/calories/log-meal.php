<?php
/**
 * API: Registrar comida consumida
 * Actualiza calorías del día y racha
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Verificar autenticación
$user = AuthMiddleware::check();

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener datos
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['food_id']) || !isset($data['servings'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Obtener información del alimento
    $stmt = $conn->prepare("SELECT * FROM foods WHERE id = :food_id");
    $stmt->execute(['food_id' => $data['food_id']]);
    $food = $stmt->fetch();

    if (!$food) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Alimento no encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $servings = floatval($data['servings']);
    
    // Calcular valores nutricionales
    $calories = round($food['calories'] * $servings);
    $protein = round($food['protein'] * $servings, 2);
    $carbs = round($food['carbs'] * $servings, 2);
    $fats = round($food['fats'] * $servings, 2);

    // Registrar en log de calorías
    $stmt = $conn->prepare("
        INSERT INTO calories_log (user_id, date, calories, protein, carbs, fats, notes)
        VALUES (:user_id, CURDATE(), :calories, :protein, :carbs, :fats, :notes)
    ");

    $notes = $food['name'] . ' (' . $servings . ' ' . $food['serving_unit'] . ')';

    $stmt->execute([
        'user_id' => $user['user_id'],
        'calories' => $calories,
        'protein' => $protein,
        'carbs' => $carbs,
        'fats' => $fats,
        'notes' => $notes
    ]);

    // Actualizar racha
    updateStreak($conn, $user['user_id']);

    // Obtener totales del día
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(calories), 0) as total_calories,
            COALESCE(SUM(protein), 0) as total_protein,
            COALESCE(SUM(carbs), 0) as total_carbs,
            COALESCE(SUM(fats), 0) as total_fats
        FROM calories_log
        WHERE user_id = :user_id AND date = CURDATE()
    ");
    $stmt->execute(['user_id' => $user['user_id']]);
    $totals = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Comida registrada exitosamente',
        'logged' => [
            'food' => $food['name'],
            'calories' => $calories,
            'protein' => $protein,
            'carbs' => $carbs,
            'fats' => $fats
        ],
        'today_totals' => $totals
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al registrar comida',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Actualizar racha del usuario
 */
function updateStreak($conn, $userId) {
    // Obtener última actividad
    $stmt = $conn->prepare("
        SELECT last_activity_date, current_streak 
        FROM user_streaks 
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
    $streak = $stmt->fetch();

    $today = date('Y-m-d');
    $currentStreak = 1;

    if ($streak && $streak['last_activity_date']) {
        $lastDate = new DateTime($streak['last_activity_date']);
        $todayDate = new DateTime($today);
        $diff = $lastDate->diff($todayDate)->days;

        if ($diff == 0) {
            // Mismo día, no incrementar
            return;
        } elseif ($diff == 1) {
            // Día consecutivo, incrementar
            $currentStreak = $streak['current_streak'] + 1;
        } else {
            // Rompió la racha
            $currentStreak = 1;
        }
    }

    // Actualizar racha
    $stmt = $conn->prepare("
        INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_activity_date)
        VALUES (:user_id, :current_streak, :current_streak, :today)
        ON DUPLICATE KEY UPDATE
            current_streak = :current_streak,
            longest_streak = GREATEST(longest_streak, :current_streak),
            last_activity_date = :today
    ");

    $stmt->execute([
        'user_id' => $userId,
        'current_streak' => $currentStreak,
        'today' => $today
    ]);
}
?>