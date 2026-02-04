<?php
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

echo "<h1>Debug: Generar Plan de Comidas</h1>";

try {
    $user = AuthMiddleware::check();

    if (!$user) {
        die("❌ Error: No autorizado");
    }

    echo "✅ Usuario autenticado: " . $user['user_id'] . "<br><br>";

    $db = new Database();
    $conn = $db->getConnection();

    // Verificar perfil
    $stmt = $conn->prepare("SELECT * FROM health_profiles WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        die("❌ Error: Perfil no encontrado");
    }

    echo "✅ Perfil encontrado<br>";
    echo "Calorías objetivo: " . $profile['target_calories'] . "<br><br>";

    // Verificar comidas disponibles
    $stmt = $conn->query("SELECT meal_type, COUNT(*) as total FROM meals GROUP BY meal_type");
    $mealCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Comidas disponibles en BD:</h2>";
    foreach ($mealCounts as $count) {
        echo $count['meal_type'] . ": " . $count['total'] . " comidas<br>";
    }

    if (empty($mealCounts)) {
        die("<br>❌ ERROR: No hay comidas en la base de datos. Ejecuta el SQL de INSERT.");
    }

    echo "<br><h2>Generando plan...</h2>";

    $targetCalories = (int)$profile['target_calories'];
    $breakfastCals = round($targetCalories * 0.30);
    $lunchCals = round($targetCalories * 0.40);
    $dinnerCals = round($targetCalories * 0.30);

    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    echo "Semana: $startOfWeek al $endOfWeek<br>";
    echo "Calorías por comida: Desayuno=$breakfastCals, Almuerzo=$lunchCals, Cena=$dinnerCals<br><br>";

    // Borrar plan existente
    $stmt = $conn->prepare("DELETE FROM meal_plans WHERE user_id = :user_id AND date BETWEEN :start_date AND :end_date");
    $stmt->execute(['user_id' => $user['user_id'], 'start_date' => $startOfWeek, 'end_date' => $endOfWeek]);
    echo "✅ Plan anterior eliminado<br><br>";

    $mealsAdded = 0;
    
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("$startOfWeek +$i days"));
        echo "<strong>Día " . ($i + 1) . " ($date):</strong><br>";

        // DESAYUNO
        $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_type = 'breakfast' ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $breakfast = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($breakfast) {
            $stmt2 = $conn->prepare("INSERT INTO meal_plans (user_id, meal_id, date, meal_type, scheduled_time) VALUES (:user_id, :meal_id, :date, :meal_type, :time)");
            $stmt2->execute([
                'user_id' => $user['user_id'],
                'meal_id' => $breakfast['id'],
                'date' => $date,
                'meal_type' => 'breakfast',
                'time' => '08:00:00'
            ]);
            echo "  ✅ Desayuno: " . $breakfast['name'] . "<br>";
            $mealsAdded++;
        } else {
            echo "  ❌ No se encontró desayuno<br>";
        }

        // ALMUERZO
        $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_type = 'lunch' ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $lunch = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lunch) {
            $stmt2 = $conn->prepare("INSERT INTO meal_plans (user_id, meal_id, date, meal_type, scheduled_time) VALUES (:user_id, :meal_id, :date, :meal_type, :time)");
            $stmt2->execute([
                'user_id' => $user['user_id'],
                'meal_id' => $lunch['id'],
                'date' => $date,
                'meal_type' => 'lunch',
                'time' => '13:00:00'
            ]);
            echo "  ✅ Almuerzo: " . $lunch['name'] . "<br>";
            $mealsAdded++;
        } else {
            echo "  ❌ No se encontró almuerzo<br>";
        }

        // CENA
        $stmt = $conn->prepare("SELECT * FROM meals WHERE meal_type = 'dinner' ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $dinner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dinner) {
            $stmt2 = $conn->prepare("INSERT INTO meal_plans (user_id, meal_id, date, meal_type, scheduled_time) VALUES (:user_id, :meal_id, :date, :meal_type, :time)");
            $stmt2->execute([
                'user_id' => $user['user_id'],
                'meal_id' => $dinner['id'],
                'date' => $date,
                'meal_type' => 'dinner',
                'time' => '19:00:00'
            ]);
            echo "  ✅ Cena: " . $dinner['name'] . "<br>";
            $mealsAdded++;
        } else {
            echo "  ❌ No se encontró cena<br>";
        }

        echo "<br>";
    }

    echo "<h2>✅ COMPLETADO</h2>";
    echo "Total de comidas agregadas: $mealsAdded<br>";
    echo "<br><a href='../../public/meals.php'>Ver Plan de Comidas</a>";

} catch (Exception $e) {
    echo "<h2>❌ ERROR</h2>";
    echo $e->getMessage();
}
?>