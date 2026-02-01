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

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT * FROM health_profiles WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'Perfil no encontrado']));
    }

    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    // Borrar plan anterior
    $stmt = $conn->prepare("DELETE FROM meal_plans WHERE user_id = ? AND date BETWEEN ? AND ?");
    $stmt->execute([$user['user_id'], $startOfWeek, $endOfWeek]);

    $mealsAdded = 0;
    
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("$startOfWeek +$i days"));

        // Desayuno
        $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_type = 'breakfast' ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $breakfast = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($breakfast) {
            $ins = $conn->prepare("INSERT INTO meal_plans (user_id, meal_id, date, meal_type, scheduled_time) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$user['user_id'], $breakfast['id'], $date, 'breakfast', '08:00:00']);
            $mealsAdded++;
        }

        // Almuerzo
        $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_type = 'lunch' ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $lunch = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($lunch) {
            $ins = $conn->prepare("INSERT INTO meal_plans (user_id, meal_id, date, meal_type, scheduled_time) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$user['user_id'], $lunch['id'], $date, 'lunch', '13:00:00']);
            $mealsAdded++;
        }

        // Cena
        $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_type = 'dinner' ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $dinner = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dinner) {
            $ins = $conn->prepare("INSERT INTO meal_plans (user_id, meal_id, date, meal_type, scheduled_time) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$user['user_id'], $dinner['id'], $date, 'dinner', '19:00:00']);
            $mealsAdded++;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Plan generado exitosamente',
        'meals_added' => $mealsAdded,
        'week_start' => $startOfWeek,
        'week_end' => $endOfWeek
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>