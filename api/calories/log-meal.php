<?php
/**
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

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit;
}

try {
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

    $db = new Database();
    $conn = $db->getConnection();

    // Obtener información del alimento
    $stmt = $conn->prepare("SELECT * FROM foods WHERE id = :food_id");
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

    // Obtener totales del día
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(calories), 0) as total_calories,
            COALESCE(SUM(protein), 0) as total_protein,
            COALESCE(SUM(carbs), 0) as total_carbs,
            COALESCE(SUM(fats), 0) as total_fats,
            COUNT(*) as meals_count
        FROM calories_log
        WHERE user_id = :user_id AND date = CURDATE()
    ");
    $stmt->execute(['user_id' => $user['user_id']]);
    $today_totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Comida registrada exitosamente',
        'logged' => [
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

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}