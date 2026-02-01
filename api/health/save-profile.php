<?php
/**
 * API: Guardar perfil de salud del usuario
 * Calcula y guarda datos nutricionales
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start(); //Blindar salida Ali


// Habilitar errores para debug -- Cambio ali
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
//Cambio Ali

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

// Obtener datos del POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

// Validar datos requeridos
$required = ['weight_kg', 'height_cm', 'age', 'gender', 'activity_level', 'goal'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Campo requerido faltante: $field"]);
        exit;
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Preparar datos de condiciones de salud
    $healthConditions = '';
    if (isset($data['conditions']) && is_array($data['conditions'])) {
        $healthConditions = implode(', ', $data['conditions']);
    }
    if (isset($data['health_conditions']) && !empty($data['health_conditions'])) {
        if (!empty($healthConditions)) $healthConditions .= '; ';
        $healthConditions .= $data['health_conditions'];
    }

    // Preparar datos de alergias
    $allergies = '';
    if (isset($data['allergies']) && is_array($data['allergies'])) {
        $allergies = implode(', ', $data['allergies']);
    }
    if (isset($data['allergies_other']) && !empty($data['allergies_other'])) {
        if (!empty($allergies)) $allergies .= '; ';
        $allergies .= $data['allergies_other'];
    }

    // Preparar preferencias dietéticas
    $dietaryPreferences = '';
    if (isset($data['diet_type'])) {
        $dietaryPreferences = 'Tipo: ' . $data['diet_type'];
    }
    if (isset($data['meals_per_day'])) {
        if (!empty($dietaryPreferences)) $dietaryPreferences .= '; ';
        $dietaryPreferences .= 'Comidas/día: ' . $data['meals_per_day'];
    }

    // Guardar o actualizar perfil de salud
    $stmt = $conn->prepare("
        INSERT INTO health_profiles (
            user_id, weight, height, age, gender, activity_level, 
            health_conditions, dietary_preferences, allergies, goal,
            target_calories, target_protein, target_carbs, target_fats
        ) VALUES (
            :user_id, :weight, :height, :age, :gender, :activity_level,
            :health_conditions, :dietary_preferences, :allergies, :goal,
            :target_calories, :target_protein, :target_carbs, :target_fats
        )
        ON DUPLICATE KEY UPDATE
            weight = VALUES(weight),
            height = VALUES(height),
            age = VALUES(age),
            gender = VALUES(gender),
            activity_level = VALUES(activity_level),
            health_conditions = VALUES(health_conditions),
            dietary_preferences = VALUES(dietary_preferences),
            allergies = VALUES(allergies),
            goal = VALUES(goal),
            target_calories = VALUES(target_calories),
            target_protein = VALUES(target_protein),
            target_carbs = VALUES(target_carbs),
            target_fats = VALUES(target_fats),
            updated_at = CURRENT_TIMESTAMP
    ");

    $result = $stmt->execute([
        'user_id' => $user['user_id'],
        'weight' => $data['weight_kg'],
        'height' => $data['height_cm'],
        'age' => $data['age'],
        'gender' => $data['gender'],
        'activity_level' => $data['activity_level'],
        'health_conditions' => $healthConditions ?: NULL,
        'dietary_preferences' => $dietaryPreferences ?: NULL,
        'allergies' => $allergies ?: NULL,
        'goal' => $data['goal'],
        'target_calories' => $data['target_calories'],
        'target_protein' => $data['target_protein'],
        'target_carbs' => $data['target_carbs'],
        'target_fats' => $data['target_fats']
    ]);

    if (!$result) {
        throw new Exception('Error al guardar perfil de salud');
    }

    // Actualizar o crear configuración del usuario
    $stmt = $conn->prepare("
        INSERT INTO user_settings (user_id, weight_unit, height_unit, language)
        VALUES (:user_id, :weight_unit, :height_unit, 'es')
        ON DUPLICATE KEY UPDATE
            weight_unit = VALUES(weight_unit),
            height_unit = VALUES(height_unit)
    ");

    $stmt->execute([
        'user_id' => $user['user_id'],
        'weight_unit' => $data['weight_unit'] ?? 'kg',
        'height_unit' => $data['height_unit'] ?? 'cm'
    ]);

    // Inicializar racha del usuario
    initializeUserStreak($conn, $user['user_id']);
ob_clean();//Ali

    echo json_encode([
        'success' => true,
        'message' => 'Perfil guardado exitosamente',
        'data' => [
            'bmi' => $data['bmi'] ?? 0,
            'target_calories' => $data['target_calories'],
            'target_protein' => $data['target_protein'],
            'target_carbs' => $data['target_carbs'],
            'target_fats' => $data['target_fats']
        ]
    ]);
exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al guardar perfil',
        'details' => $e->getMessage()
    ]);
}
exit;
/**
 * Inicializar racha del usuario
 */
function initializeUserStreak($conn, $userId) {
    // Crear tabla de rachas si no existe
    $conn->exec("
        CREATE TABLE IF NOT EXISTS user_streaks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            current_streak INT DEFAULT 0,
            longest_streak INT DEFAULT 0,
            last_activity_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Insertar racha inicial
    $stmt = $conn->prepare("
        INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_activity_date)
        VALUES (:user_id, 1, 1, CURDATE())
        ON DUPLICATE KEY UPDATE
            current_streak = GREATEST(current_streak, 1),
            longest_streak = GREATEST(longest_streak, 1),
            last_activity_date = CURDATE()
    ");

    $stmt->execute(['user_id' => $userId]);
}
?>