<?php
/**
 * DEBUG - Generar Plan de Comidas
 * 
 * Coloca este archivo en: nutricion-platform/public/debug_generate.php
 * Accede desde: http://tu-dominio.com/nutricion-platform/public/debug_generate.php
 * 
 * Este script simula el proceso de generación y muestra errores detallados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Generar Plan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1f2937;
            color: #fff;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 { margin-bottom: 2rem; color: #10b981; }
        .section {
            background: #374151;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #10b981;
        }
        .section.error { border-left-color: #ef4444; }
        .section.warning { border-left-color: #f59e0b; }
        .section h2 {
            color: #10b981;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        .section.error h2 { color: #ef4444; }
        .section.warning h2 { color: #f59e0b; }
        pre {
            background: #1f2937;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            color: #10b981;
            margin-top: 0.5rem;
        }
        .status { 
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status.ok { background: #10b981; color: white; }
        .status.error { background: #ef4444; color: white; }
        .status.warning { background: #f59e0b; color: white; }
        .test-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        .test-btn:hover { background: #059669; }
        .test-btn:disabled {
            background: #6b7280;
            cursor: not-allowed;
        }
        .code { 
            background: #1f2937; 
            padding: 0.25rem 0.5rem; 
            border-radius: 4px;
            color: #fbbf24;
            font-family: monospace;
        }
        #result {
            background: #1f2937;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
            display: none;
        }
        #result.show { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug - Generación de Plan de Comidas</h1>

        <?php
        $errors = [];
        $warnings = [];
        $info = [];
        
        // 1. VERIFICAR SESIÓN
        $info[] = "Sesión iniciada: " . (isset($_SESSION) ? "✅ SÍ" : "❌ NO");
        
        // 2. VERIFICAR ARCHIVOS
        echo '<div class="section">';
        echo '<h2>1️⃣ Verificación de Archivos</h2>';
        
        $files = [
            'config/load_env.php' => __DIR__ . '/../config/load_env.php',
            'middleware/auth.php' => __DIR__ . '/../middleware/auth.php',
            'config/database.php' => __DIR__ . '/../config/database.php',
            'api/meals/generate_plan.php' => __DIR__ . '/../api/meals/generate_plan.php',
        ];
        
        foreach ($files as $name => $path) {
            $exists = file_exists($path);
            echo '<p>';
            echo $exists ? '✅' : '❌';
            echo " <strong>$name:</strong> ";
            echo $exists ? '<span class="status ok">Existe</span>' : '<span class="status error">NO EXISTE</span>';
            if (!$exists) {
                echo '<br><span class="code">Esperado en: ' . htmlspecialchars($path) . '</span>';
                $errors[] = "Archivo no encontrado: $name";
            }
            echo '</p>';
        }
        echo '</div>';
        
        // 3. VERIFICAR AUTENTICACIÓN
        echo '<div class="section">';
        echo '<h2>2️⃣ Autenticación de Usuario</h2>';
        
        $user = null;
        if (file_exists(__DIR__ . '/../middleware/auth.php')) {
            require_once __DIR__ . '/../config/load_env.php';
            require_once __DIR__ . '/../middleware/auth.php';
            
            $user = AuthMiddleware::check();
            
            if ($user) {
                echo '<p>✅ <strong>Usuario autenticado:</strong> <span class="status ok">SÍ</span></p>';
                echo '<pre>';
                echo 'User ID: ' . htmlspecialchars($user['user_id'] ?? 'N/A') . "\n";
                echo 'Email: ' . htmlspecialchars($user['email'] ?? 'N/A') . "\n";
                echo 'Nombre: ' . htmlspecialchars($user['name'] ?? 'N/A');
                echo '</pre>';
            } else {
                echo '<p>❌ <strong>Usuario autenticado:</strong> <span class="status error">NO</span></p>';
                echo '<p style="color: #fbbf24;">⚠️ Debes iniciar sesión primero</p>';
                $errors[] = "Usuario no autenticado";
            }
        } else {
            echo '<p>❌ <strong>Archivo auth.php no encontrado</strong></p>';
            $errors[] = "No se puede verificar autenticación";
        }
        echo '</div>';
        
        // 4. VERIFICAR BASE DE DATOS Y PERFIL
        if ($user) {
            echo '<div class="section">';
            echo '<h2>3️⃣ Perfil de Usuario y Base de Datos</h2>';
            
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = new Database();
                $conn = $db->getConnection();
                
                echo '<p>✅ <strong>Conexión a BD:</strong> <span class="status ok">OK</span></p>';
                
                // Verificar tabla meal_plans
                $stmt = $conn->query("SHOW TABLES LIKE 'meal_plans'");
                $mealPlansExists = $stmt->rowCount() > 0;
                
                echo '<p>';
                echo $mealPlansExists ? '✅' : '❌';
                echo ' <strong>Tabla meal_plans:</strong> ';
                echo $mealPlansExists ? '<span class="status ok">Existe</span>' : '<span class="status error">NO EXISTE</span>';
                echo '</p>';
                
                if (!$mealPlansExists) {
                    $errors[] = "Tabla meal_plans no existe";
                    echo '<pre style="color: #ef4444;">
Ejecuta este SQL:

CREATE TABLE IF NOT EXISTS meal_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    meal_type VARCHAR(50) NOT NULL,
    meal_name VARCHAR(255) NOT NULL,
    calories DECIMAL(10,2) NOT NULL,
    protein DECIMAL(10,2) NOT NULL,
    carbs DECIMAL(10,2) NOT NULL,
    fat DECIMAL(10,2) NOT NULL,
    ingredients TEXT,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
</pre>';
                }
                
                // Verificar perfil del usuario
                $stmt = $conn->prepare("
                    SELECT u.*, hp.* 
                    FROM users u
                    LEFT JOIN health_profiles hp ON u.id = hp.user_id
                    WHERE u.id = :user_id
                ");
                $stmt->execute(['user_id' => $user['user_id']]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData) {
                    echo '<p>✅ <strong>Perfil encontrado:</strong> <span class="status ok">SÍ</span></p>';
                    
                    $hasCalories = isset($userData['target_calories']) && $userData['target_calories'] > 0;
                    $hasProtein = isset($userData['target_protein']) && $userData['target_protein'] > 0;
                    
                    echo '<pre>';
                    echo 'Calorías objetivo: ' . ($userData['target_calories'] ?? 'NO CONFIGURADO') . ' kcal';
                    if (!$hasCalories) echo ' ❌ FALTA CONFIGURAR';
                    echo "\n";
                    echo 'Proteína objetivo: ' . ($userData['target_protein'] ?? 'NO CONFIGURADO') . 'g';
                    if (!$hasProtein) echo ' ❌ FALTA CONFIGURAR';
                    echo "\n";
                    echo 'Peso: ' . ($userData['weight'] ?? 'N/A') . ' kg' . "\n";
                    echo 'Altura: ' . ($userData['height'] ?? 'N/A') . ' cm' . "\n";
                    echo 'Objetivo: ' . ($userData['goal'] ?? 'N/A');
                    echo '</pre>';
                    
                    if (!$hasCalories || !$hasProtein) {
                        $errors[] = "Perfil incompleto: falta configurar calorías o proteína objetivo";
                        echo '<p style="color: #fbbf24;">⚠️ <strong>Debes completar tu perfil de salud antes de generar el plan</strong></p>';
                    }
                } else {
                    echo '<p>❌ <strong>Perfil encontrado:</strong> <span class="status error">NO</span></p>';
                    $errors[] = "No se encontró perfil de usuario";
                }
                
            } catch (Exception $e) {
                echo '<p>❌ <strong>Error de BD:</strong> <span class="status error">' . htmlspecialchars($e->getMessage()) . '</span></p>';
                $errors[] = "Error de base de datos: " . $e->getMessage();
            }
            echo '</div>';
        }
        
        // 5. VERIFICAR RUTA DE API
        echo '<div class="section">';
        echo '<h2>4️⃣ Verificación de API</h2>';
        
        $apiPath = __DIR__ . '/../api/meals/generate_plan.php';
        $apiExists = file_exists($apiPath);
        
        echo '<p>';
        echo $apiExists ? '✅' : '❌';
        echo ' <strong>API generate_plan.php:</strong> ';
        echo $apiExists ? '<span class="status ok">Existe</span>' : '<span class="status error">NO EXISTE</span>';
        echo '</p>';
        
        if (!$apiExists) {
            echo '<p style="color: #fbbf24;">📁 Debe estar en: <span class="code">' . htmlspecialchars($apiPath) . '</span></p>';
            $errors[] = "API generate_plan.php no encontrada";
        } else {
            // Verificar permisos
            $readable = is_readable($apiPath);
            echo '<p>';
            echo $readable ? '✅' : '❌';
            echo ' <strong>Permisos de lectura:</strong> ';
            echo $readable ? '<span class="status ok">OK</span>' : '<span class="status error">Sin permisos</span>';
            echo '</p>';
            
            if (!$readable) {
                $errors[] = "API no tiene permisos de lectura";
            }
        }
        
        // URL de la API
        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        $apiUrl = str_replace('/public', '/api/meals/generate_plan.php', $baseUrl);
        
        echo '<p><strong>URL de la API:</strong></p>';
        echo '<pre>' . htmlspecialchars($apiUrl) . '</pre>';
        
        echo '</div>';
        
        // 6. RESUMEN
        $hasErrors = count($errors) > 0;
        $class = $hasErrors ? 'error' : '';
        
        echo '<div class="section ' . $class . '">';
        echo '<h2>📊 Resumen</h2>';
        
        if ($hasErrors) {
            echo '<p style="font-size: 1.2rem; margin-bottom: 1rem;">❌ <strong style="color: #ef4444;">Se encontraron ' . count($errors) . ' problema(s):</strong></p>';
            echo '<ul style="margin-left: 2rem; color: #fbbf24;">';
            foreach ($errors as $error) {
                echo '<li style="margin-bottom: 0.5rem;">' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="font-size: 1.2rem; color: #10b981;">✅ <strong>Todo parece estar configurado correctamente</strong></p>';
            echo '<p style="margin-top: 1rem;">Puedes probar la generación del plan:</p>';
        }
        
        echo '</div>';
        ?>

        <!-- BOTÓN DE PRUEBA -->
        <?php if (!$hasErrors && $user): ?>
        <div class="section">
            <h2>🧪 Prueba de Generación</h2>
            <p>Haz clic en el botón para probar la generación del plan:</p>
            <button class="test-btn" onclick="testGenerate()">🚀 Probar Generar Plan</button>
            <div id="result"></div>
        </div>
        
        <script>
        async function testGenerate() {
            const btn = event.target;
            const resultDiv = document.getElementById('result');
            
            btn.disabled = true;
            btn.textContent = '⏳ Generando...';
            resultDiv.className = '';
            resultDiv.innerHTML = '<p style="color: #fbbf24;">Generando plan de comidas...</p>';
            resultDiv.classList.add('show');
            
            try {
                const response = await fetch('../api/meals/generate_plan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const text = await response.text();
                console.log('Response text:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    resultDiv.innerHTML = `
                        <p style="color: #ef4444;"><strong>❌ Error: La respuesta no es JSON válido</strong></p>
                        <p style="color: #fbbf24;">Respuesta recibida:</p>
                        <pre style="color: #ef4444;">${text.substring(0, 500)}</pre>
                        <p style="color: #fbbf24;">Esto generalmente significa que hay un error de PHP en el archivo generate_plan.php</p>
                    `;
                    btn.disabled = false;
                    btn.textContent = '🚀 Probar Generar Plan';
                    return;
                }
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <p style="color: #10b981; font-size: 1.2rem;"><strong>✅ ¡Plan generado exitosamente!</strong></p>
                        <p style="margin-top: 1rem;">Comidas generadas: ${data.plan ? data.plan.length : 0}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                        <p style="margin-top: 1rem;">
                            <a href="meals.php" style="color: #10b981; text-decoration: underline;">
                                → Ir a ver mi plan de comidas
                            </a>
                        </p>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <p style="color: #ef4444;"><strong>❌ Error al generar plan:</strong></p>
                        <p style="color: #fbbf24;">${data.message || 'Error desconocido'}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                }
                
            } catch (error) {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <p style="color: #ef4444;"><strong>❌ Error de red o conexión:</strong></p>
                    <p style="color: #fbbf24;">${error.message}</p>
                    <p style="margin-top: 1rem;">Posibles causas:</p>
                    <ul style="margin-left: 2rem; color: #fbbf24;">
                        <li>La API no está accesible</li>
                        <li>Ruta incorrecta a la API</li>
                        <li>Error de permisos</li>
                        <li>Error de PHP en generate_plan.php</li>
                    </ul>
                `;
            }
            
            btn.disabled = false;
            btn.textContent = '🚀 Probar Generar Plan';
        }
        </script>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #4b5563;">
            <p style="text-align: center; color: #9ca3af;">
                <a href="meals.php" style="color: #10b981;">← Volver a Plan de Comidas</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a href="check_meals.php" style="color: #10b981;">Verificación Completa</a>
            </p>
        </div>
    </div>
</body>
</html>