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

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['message']) || empty(trim($data['message']))) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'Mensaje requerido']));
    }

    $db = new Database();
    $conn = $db->getConnection();

    // Obtener perfil del usuario para contexto
    $stmt = $conn->prepare("
        SELECT u.name, hp.target_calories, hp.goal, hp.dietary_preferences, hp.allergies
        FROM users u
        LEFT JOIN health_profiles hp ON u.id = hp.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user['user_id']]);
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener estadísticas del día
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(calories), 0) as total_calories,
            COALESCE(SUM(protein), 0) as total_protein
        FROM calories_log
        WHERE user_id = ? AND date = CURDATE()
    ");
    $stmt->execute([$user['user_id']]);
    $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Generar respuesta del asistente
    $message = trim($data['message']);
    $response = generateAssistantResponse($message, $userProfile, $todayStats);

    // Guardar conversación
    $stmt = $conn->prepare("
        INSERT INTO assistant_conversations (user_id, message, response, context)
        VALUES (?, ?, ?, ?)
    ");
    
    $context = json_encode([
        'calories_today' => $todayStats['total_calories'],
        'target_calories' => $userProfile['target_calories']
    ]);
    
    $stmt->execute([$user['user_id'], $message, $response, $context]);

    echo json_encode([
        'success' => true,
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Generar respuesta del asistente
 */
function generateAssistantResponse($message, $profile, $stats) {
    $message = strtolower($message);
    
    // Saludos
    if (preg_match('/\b(hola|buenos días|buenas tardes|hey|hi)\b/i', $message)) {
        return "¡Hola " . $profile['name'] . "! 👋 Soy tu asistente nutricional. Hoy has consumido " . 
               number_format($stats['total_calories']) . " calorías de tu objetivo de " . 
               number_format($profile['target_calories']) . " kcal. ¿En qué puedo ayudarte?";
    }
    
    // Calorías del día
    if (preg_match('/\b(calorías|consumido|cuánto|comido)\b/i', $message)) {
        $remaining = $profile['target_calories'] - $stats['total_calories'];
        $percentage = ($stats['total_calories'] / $profile['target_calories']) * 100;
        
        if ($percentage < 50) {
            return "Has consumido " . number_format($stats['total_calories']) . " kcal (" . 
                   round($percentage) . "% de tu objetivo). Te quedan " . number_format($remaining) . 
                   " kcal para hoy. ¡Vas muy bien! 💪";
        } elseif ($percentage < 90) {
            return "Llevas " . number_format($stats['total_calories']) . " kcal consumidas (" . 
                   round($percentage) . "%). Te faltan " . number_format($remaining) . 
                   " kcal. ¡Excelente progreso! 🎯";
        } elseif ($percentage <= 110) {
            return "Has consumido " . number_format($stats['total_calories']) . " kcal. " .
                   "Estás muy cerca de tu objetivo de " . number_format($profile['target_calories']) . 
                   " kcal. ¡Perfecto! ✅";
        } else {
            return "Has consumido " . number_format($stats['total_calories']) . " kcal, " .
                   "un poco más de tu objetivo de " . number_format($profile['target_calories']) . 
                   " kcal. No te preocupes, mañana es un nuevo día. 😊";
        }
    }
    
    // Recomendaciones de comida
    if (preg_match('/\b(qué|puedo|comer|recomiendas|sugiere|ideas)\b/i', $message)) {
        $goal = $profile['goal'];
        $suggestions = [
            'lose_weight' => "Para perder peso, te recomiendo: ensalada de pollo a la parrilla, salmón con brócoli al vapor, o un bowl de quinoa con vegetales. ¿Te interesa alguna? 🥗",
            'maintain' => "Para mantener tu peso, prueba: pasta integral con vegetales, arroz con pollo y ensalada, o tacos de pescado. ¿Cuál prefieres? 🍽️",
            'gain_weight' => "Para ganar peso saludablemente: smoothie de proteína con avena, sándwich de pavo con aguacate, o pasta con carne magra. ¿Te animas? 💪",
            'muscle_gain' => "Para ganar músculo: pechuga de pollo con arroz integral, salmón con batata, o huevos con aguacate y pan integral. ¿Cuál eliges? 🏋️"
        ];
        
        return $suggestions[$goal] ?? "Te sugiero comidas balanceadas como pollo con vegetales, pescado al horno, o ensaladas con proteína. ¿Qué tipo de comida prefieres? 🍴";
    }
    
    // Proteína
    if (preg_match('/\b(proteína|músculo|gym)\b/i', $message)) {
        return "Hoy has consumido " . number_format($stats['total_protein'], 1) . "g de proteína. " .
               "Alimentos ricos en proteína: pollo, pescado, huevos, legumbres, yogurt griego. " .
               "¿Necesitas ideas de recetas? 💪";
    }
    
    // Motivación
    if (preg_match('/\b(motivación|ánimo|difícil|no puedo)\b/i', $message)) {
        return "¡Tú puedes! 💪 Cada día es una oportunidad para mejorar. Recuerda que los pequeños " .
               "cambios consistentes generan grandes resultados. Ya has dado el primer paso al usar " .
               "esta app. ¡Sigue adelante! 🌟";
    }
    
    // Agua
    if (preg_match('/\b(agua|hidratar|tomar)\b/i', $message)) {
        return "¡Excelente pregunta! 💧 Se recomienda tomar 2-3 litros de agua al día. Beneficios: " .
               "mejora el metabolismo, ayuda a la digestión, reduce el hambre. ¿Quieres que te " .
               "recuerde beber agua durante el día?";
    }
    
    // Plan de comidas
    if (preg_match('/\b(plan|semana|menú)\b/i', $message)) {
        return "Puedes generar tu plan de comidas semanal en la sección 'Plan de Comidas'. " .
               "Se creará automáticamente según tus objetivos nutricionales. ¿Te ayudo con algo más? 📅";
    }
    
    // Respuesta por defecto
    return "Puedo ayudarte con:\n\n" .
           "📊 Revisar tus calorías del día\n" .
           "🍽️ Sugerir comidas saludables\n" .
           "💪 Consejos de nutrición\n" .
           "📅 Información sobre tu plan\n\n" .
           "¿Qué te gustaría saber?";
}
?>