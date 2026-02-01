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
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    $stmt = $conn->prepare("SELECT m.ingredients FROM meal_plans mp JOIN meals m ON mp.meal_id = m.id WHERE mp.user_id = ? AND mp.date BETWEEN ? AND ?");
    $stmt->execute([$user['user_id'], $startOfWeek, $endOfWeek]);
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($meals)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'No hay plan de comidas']));
    }

    $allIngredients = [];
    foreach ($meals as $meal) {
        if (!empty($meal['ingredients'])) {
            $ingredients = explode(',', $meal['ingredients']);
            foreach ($ingredients as $ingredient) {
                $ingredient = trim($ingredient);
                if (!empty($ingredient)) {
                    $allIngredients[] = $ingredient;
                }
            }
        }
    }

    // Borrar lista anterior
    $stmt = $conn->prepare("DELETE FROM shopping_lists WHERE user_id = ? AND week_start = ?");
    $stmt->execute([$user['user_id'], $startOfWeek]);

    // Categorizar ingredientes
    $categories = [
        'Frutas y Verduras' => ['plátano', 'manzana', 'lechuga', 'tomate', 'brócoli', 'zanahoria', 'espinaca', 'aguacate'],
        'Proteínas' => ['pollo', 'carne', 'pescado', 'salmón', 'atún', 'huevo', 'tofu'],
        'Granos' => ['arroz', 'pasta', 'pan', 'tortilla', 'avena', 'quinoa'],
        'Lácteos' => ['leche', 'yogurt', 'queso'],
        'Despensa' => ['aceite', 'sal', 'miel', 'salsa'],
        'Otros' => []
    ];

    $itemCounts = [];
    foreach ($allIngredients as $ingredient) {
        $ingredient = strtolower($ingredient);
        $found = false;

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($ingredient, $keyword) !== false) {
                    if (!isset($itemCounts[$ingredient])) {
                        $itemCounts[$ingredient] = ['name' => ucfirst($ingredient), 'quantity' => 1, 'category' => $category];
                    } else {
                        $itemCounts[$ingredient]['quantity']++;
                    }
                    $found = true;
                    break 2;
                }
            }
        }

        if (!$found) {
            if (!isset($itemCounts[$ingredient])) {
                $itemCounts[$ingredient] = ['name' => ucfirst($ingredient), 'quantity' => 1, 'category' => 'Otros'];
            } else {
                $itemCounts[$ingredient]['quantity']++;
            }
        }
    }

    $itemsAdded = 0;
    $ins = $conn->prepare("INSERT INTO shopping_lists (user_id, item_name, category, quantity, week_start) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($itemCounts as $item) {
        $ins->execute([$user['user_id'], $item['name'], $item['category'], $item['quantity'], $startOfWeek]);
        $itemsAdded++;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Lista generada',
        'items_added' => $itemsAdded,
        'week_start' => $startOfWeek
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>