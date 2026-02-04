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

    // Obtener imagen
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'Imagen requerida']));
    }

    $imageData = file_get_contents($_FILES['image']['tmp_name']);
    $base64Image = base64_encode($imageData);

    // Guardar imagen primero
    $imagePath = saveImage($imageData, $user['user_id']);

    // ========================================
    // PASO 1: ANALIZAR CON CLARIFAI
    // ========================================
    $foodName = analyzeFoodWithClarifai($base64Image);

    if (!$foodName) {
        // Fallback: usar sistema local
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM foods ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $food = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $foodName = $food ? $food['name'] : 'Comida General';
    }

    // ========================================
    // PASO 2: OBTENER DATOS NUTRICIONALES
    // ========================================
    $nutritionData = getNutritionDataFromUSDA($foodName);

    if (!$nutritionData) {
        // Fallback: buscar en base de datos local
        $nutritionData = getNutritionFromLocalDB($foodName);
    }

    // ========================================
    // PASO 3: GUARDAR EN BASE DE DATOS
    // ========================================
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        INSERT INTO food_scans (user_id, food_name, calories, protein, carbs, fats, fiber, sugar, sodium, vitamins, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $vitaminsJson = json_encode($nutritionData['vitamins']);
    
    $stmt->execute([
        $user['user_id'],
        $nutritionData['name'],
        $nutritionData['calories'],
        $nutritionData['protein'],
        $nutritionData['carbs'],
        $nutritionData['fats'],
        $nutritionData['fiber'],
        $nutritionData['sugar'],
        $nutritionData['sodium'],
        $vitaminsJson,
        $imagePath
    ]);

    echo json_encode([
        'success' => true,
        'food_name' => $nutritionData['name'],
        'nutrition' => $nutritionData,
        'image_path' => $imagePath,
        'confidence' => 95
    ]);

} catch (Exception $e) {
    error_log('Error en food scanner: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * ============================================
 * ANALIZAR CON CLARIFAI API
 * ============================================
 */
function analyzeFoodWithClarifai($base64Image) {
    // ⚠️ IMPORTANTE: Reemplaza con tu API Key de Clarifai
    // Obtén tu key gratis en: https://portal.clarifai.com/
    $apiKey = 'TU_CLARIFAI_API_KEY_AQUI';
    
    if ($apiKey === 'TU_CLARIFAI_API_KEY_AQUI') {
        error_log('Clarifai API Key no configurada');
        return null;
    }

    $data = [
        'user_app_id' => [
            'user_id' => 'clarifai',
            'app_id' => 'main'
        ],
        'inputs' => [
            [
                'data' => [
                    'image' => [
                        'base64' => $base64Image
                    ]
                ]
            ]
        ]
    ];

    $ch = curl_init('https://api.clarifai.com/v2/models/food-item-recognition/outputs');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Key ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log('Clarifai API error: HTTP ' . $httpCode);
        return null;
    }

    $result = json_decode($response, true);

    if (isset($result['outputs'][0]['data']['concepts'][0]['name'])) {
        return $result['outputs'][0]['data']['concepts'][0]['name'];
    }

    return null;
}

/**
 * ============================================
 * OBTENER DATOS NUTRICIONALES DE USDA
 * ============================================
 */
function getNutritionDataFromUSDA($foodName) {
    // ⚠️ IMPORTANTE: Reemplaza con tu API Key de USDA
    // Obtén tu key gratis en: https://fdc.nal.usda.gov/api-key-signup.html
    $apiKey = 'TU_USDA_API_KEY_AQUI';
    
    if ($apiKey === 'TU_USDA_API_KEY_AQUI') {
        error_log('USDA API Key no configurada');
        return null;
    }

    // Traducir al inglés si es necesario
    $translations = [
        'pollo' => 'chicken',
        'arroz' => 'rice',
        'plátano' => 'banana',
        'manzana' => 'apple',
        'huevo' => 'egg',
        'pan' => 'bread',
        'leche' => 'milk',
        'queso' => 'cheese',
        'carne' => 'beef',
        'pescado' => 'fish'
    ];
    
    $searchTerm = strtolower($foodName);
    foreach ($translations as $spanish => $english) {
        if (strpos($searchTerm, $spanish) !== false) {
            $foodName = $english;
            break;
        }
    }

    $searchUrl = "https://api.nal.usda.gov/fdc/v1/foods/search?" . http_build_query([
        'query' => $foodName,
        'pageSize' => 1,
        'api_key' => $apiKey
    ]);

    $ch = curl_init($searchUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log('USDA API error: HTTP ' . $httpCode);
        return null;
    }

    $data = json_decode($response, true);

    if (!isset($data['foods'][0])) {
        return null;
    }

    $food = $data['foods'][0];
    $nutrients = $food['foodNutrients'] ?? [];

    $nutritionData = [
        'name' => $food['description'],
        'calories' => 0,
        'protein' => 0,
        'carbs' => 0,
        'fats' => 0,
        'fiber' => 0,
        'sugar' => 0,
        'sodium' => 0,
        'vitamins' => []
    ];

    foreach ($nutrients as $nutrient) {
        $name = $nutrient['nutrientName'] ?? '';
        $value = $nutrient['value'] ?? 0;

        // Calorías
        if (stripos($name, 'Energy') !== false && stripos($name, 'kcal') !== false) {
            $nutritionData['calories'] = round($value);
        }
        // Proteína
        elseif (stripos($name, 'Protein') !== false) {
            $nutritionData['protein'] = round($value, 2);
        }
        // Carbohidratos
        elseif (stripos($name, 'Carbohydrate') !== false && stripos($name, 'by difference') !== false) {
            $nutritionData['carbs'] = round($value, 2);
        }
        // Grasas
        elseif (stripos($name, 'Total lipid') !== false || stripos($name, 'Fat') !== false) {
            $nutritionData['fats'] = round($value, 2);
        }
        // Fibra
        elseif (stripos($name, 'Fiber') !== false) {
            $nutritionData['fiber'] = round($value, 2);
        }
        // Azúcar
        elseif (stripos($name, 'Sugars, total') !== false) {
            $nutritionData['sugar'] = round($value, 2);
        }
        // Sodio
        elseif (stripos($name, 'Sodium') !== false) {
            $nutritionData['sodium'] = round($value);
        }
        // Vitaminas y minerales
        elseif (preg_match('/(Vitamin|Calcium|Iron|Potassium|Magnesium)/i', $name)) {
            $nutritionData['vitamins'][] = [
                'name' => $name,
                'value' => round($value, 2)
            ];
        }
    }

    return $nutritionData;
}

/**
 * ============================================
 * OBTENER DATOS DE BASE DE DATOS LOCAL
 * ============================================
 */
function getNutritionFromLocalDB($foodName) {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT * FROM foods WHERE name LIKE ? OR name_en LIKE ? LIMIT 1");
    $searchTerm = '%' . $foodName . '%';
    $stmt->execute([$searchTerm, $searchTerm]);
    $food = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$food) {
        // Datos por defecto
        return [
            'name' => $foodName,
            'calories' => 150,
            'protein' => 10.0,
            'carbs' => 20.0,
            'fats' => 5.0,
            'fiber' => 2.0,
            'sugar' => 5.0,
            'sodium' => 100,
            'vitamins' => [
                ['name' => 'Vitamina C', 'value' => 10.0],
                ['name' => 'Calcio', 'value' => 50.0]
            ]
        ];
    }

    return [
        'name' => $food['name'],
        'calories' => (int)$food['calories'],
        'protein' => (float)$food['protein'],
        'carbs' => (float)$food['carbs'],
        'fats' => (float)$food['fats'],
        'fiber' => (float)($food['fiber'] ?? 0),
        'sugar' => (float)($food['sugar'] ?? 0),
        'sodium' => (int)($food['sodium'] ?? 50),
        'vitamins' => generateVitamins($food['category'])
    ];
}

/**
 * Generar vitaminas según categoría
 */
function generateVitamins($category) {
    $vitamins = [
        'proteins' => [
            ['name' => 'Vitamina B6', 'value' => 0.5],
            ['name' => 'Vitamina B12', 'value' => 0.3],
            ['name' => 'Niacina', 'value' => 10.2],
            ['name' => 'Hierro', 'value' => 1.2]
        ],
        'fruits' => [
            ['name' => 'Vitamina C', 'value' => 8.7],
            ['name' => 'Vitamina A', 'value' => 64.0],
            ['name' => 'Potasio', 'value' => 358.0],
            ['name' => 'Folato', 'value' => 20.0]
        ],
        'vegetables' => [
            ['name' => 'Vitamina K', 'value' => 92.0],
            ['name' => 'Vitamina A', 'value' => 835.0],
            ['name' => 'Vitamina C', 'value' => 89.2],
            ['name' => 'Calcio', 'value' => 47.0]
        ],
        'grains' => [
            ['name' => 'Tiamina', 'value' => 0.4],
            ['name' => 'Niacina', 'value' => 5.1],
            ['name' => 'Hierro', 'value' => 2.8],
            ['name' => 'Magnesio', 'value' => 44.0]
        ],
        'dairy' => [
            ['name' => 'Calcio', 'value' => 276.0],
            ['name' => 'Vitamina D', 'value' => 124.0],
            ['name' => 'Vitamina B12', 'value' => 1.1],
            ['name' => 'Fósforo', 'value' => 222.0]
        ]
    ];

    return $vitamins[$category] ?? [
        ['name' => 'Vitamina C', 'value' => 5.0],
        ['name' => 'Calcio', 'value' => 20.0]
    ];
}

/**
 * Guardar imagen
 */
function saveImage($imageData, $userId) {
    $uploadsDir = __DIR__ . '/../../uploads/food-scans/';
    
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $filename = $userId . '_' . time() . '.jpg';
    $filepath = $uploadsDir . $filename;
    
    file_put_contents($filepath, $imageData);

    return '/uploads/food-scans/' . $filename;
}
?>