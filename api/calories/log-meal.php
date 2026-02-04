<?php
/**
<<<<<<< HEAD
 * API: Registrar comida consumida
 * Actualiza calorías del día y racha
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../config/load_env.php';
=======
 * API Endpoint: Registrar Comida (ACTUALIZADO)
 * Ruta: /nutricion-platform/api/calories/log-meal.php
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
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Verificar autenticación
$user = AuthMiddleware::check();

if (!$user) {
    http_response_code(401);
<<<<<<< HEAD
    echo json_encode(['success' => false, 'error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener datos
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['food_id']) || !isset($data['servings'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos'], JSON_UNESCAPED_UNICODE);
=======
    echo json_encode([
        'success' => false,
        'error' => 'No autorizado'
    ]);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
    exit;
}

try {
<<<<<<< HEAD
=======
    // Leer datos JSON del body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Datos inválidos');
    }

    // Validar datos requeridos
    if (!isset($data['food_id']) || !isset($data['servings'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $food_id = intval($data['food_id']);
    $servings = floatval($data['servings']);

    if ($food_id <= 0 || $servings <= 0) {
        throw new Exception('Valores inválidos');
    }

>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
    $db = new Database();
    $conn = $db->getConnection();

    // Obtener información del alimento
    $stmt = $conn->prepare("SELECT * FROM foods WHERE id = :food_id");
<<<<<<< HEAD
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

=======
    $stmt->execute(['food_id' => $food_id]);
    $food = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$food) {
        throw new Exception('Alimento no encontrado');
    }

    // Calcular valores nutricionales totales
    $total_calories = round($food['calories'] * $servings);
    $total_protein = round($food['protein'] * $servings, 2);
    $total_carbs = round($food['carbs'] * $servings, 2);
    $total_fats = round($food['fats'] * $servings, 2);

    // Crear nota descriptiva
    $notes = $servings . ' ' . $food['serving_unit'];
    if ($servings > 1) {
        // Pluralizar si es necesario
        $notes .= 's';
    }
    $notes .= ' de ' . $food['name'];

    // Insertar en la tabla calories_log
    // IMPORTANTE: Tu tabla tiene estos campos: user_id, meal_plan_id, date, calories, protein, carbs, fats, notes, created_at
    $stmt = $conn->prepare("
        INSERT INTO calories_log 
        (user_id, date, calories, protein, carbs, fats, notes, created_at) 
        VALUES 
        (:user_id, CURDATE(), :calories, :protein, :carbs, :fats, :notes, NOW())
    ");

    $stmt->execute([
        'user_id' => $user['user_id'],
        'calories' => $total_calories,
        'protein' => $total_protein,
        'carbs' => $total_carbs,
        'fats' => $total_fats,
        'notes' => $notes
    ]);

>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
    // Obtener totales del día
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(calories), 0) as total_calories,
            COALESCE(SUM(protein), 0) as total_protein,
            COALESCE(SUM(carbs), 0) as total_carbs,
<<<<<<< HEAD
            COALESCE(SUM(fats), 0) as total_fats
=======
            COALESCE(SUM(fats), 0) as total_fats,
            COUNT(*) as meals_count
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
        FROM calories_log
        WHERE user_id = :user_id AND date = CURDATE()
    ");
    $stmt->execute(['user_id' => $user['user_id']]);
<<<<<<< HEAD
    $totals = $stmt->fetch();

=======
    $today_totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Respuesta exitosa
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
    echo json_encode([
        'success' => true,
        'message' => 'Comida registrada exitosamente',
        'logged' => [
<<<<<<< HEAD
            'food' => $food['name'],
            'calories' => $calories,
            'protein' => $protein,
            'carbs' => $carbs,
            'fats' => $fats
        ],
        'today_totals' => $totals
    ], JSON_UNESCAPED_UNICODE);
=======
            'food' => $notes,
            'calories' => $total_calories,
            'protein' => number_format($total_protein, 1),
            'carbs' => number_format($total_carbs, 1),
            'fats' => number_format($total_fats, 1)
        ],
        'today_totals' => [
            'total_calories' => $today_totals['total_calories'],
            'total_protein' => number_format($today_totals['total_protein'], 1),
            'total_carbs' => number_format($today_totals['total_carbs'], 1),
            'total_fats' => number_format($today_totals['total_fats'], 1),
            'meals_count' => $today_totals['meals_count']
        ]
    ]);
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
<<<<<<< HEAD
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
=======
        'error' => $e->getMessage()
    ]);
}
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
