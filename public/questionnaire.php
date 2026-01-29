<?php
session_start();

require_once __DIR__ . '/../config/load_env.php';
require_once __DIR__ . '/../middleware/auth.php';

$user = AuthMiddleware::check();

if (!$user) {
    header('Location: login.php');
    exit;
}

// Verificar si ya completó el cuestionario
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM health_profiles WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user['user_id']]);
$profile = $stmt->fetch();

// Si ya tiene perfil, redirigir al dashboard
if ($profile && $profile['weight'] && $profile['height'] && $profile['age']) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuestionario de Salud - Tweight</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/questionnaire.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="questionnaire-page">
    
    <div class="questionnaire-container">
        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <p class="progress-text">Paso <span id="currentStep">1</span> de 6</p>
        </div>

        <!-- Formulario Multi-Step -->
        <form id="questionnaireForm" class="questionnaire-form">
            
            <!-- PASO 1: Información Básica -->
            <div class="form-step active" data-step="1">
                <div class="step-header">
                    <h1>👋 ¡Hola, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>!</h1>
                    <p>Cuéntanos un poco sobre ti para personalizar tu plan</p>
                </div>

                <div class="form-group">
                    <label for="weight">Peso actual</label>
                    <div class="input-group">
                        <input type="number" id="weight" name="weight" step="0.1" min="30" max="300" required>
                        <select id="weightUnit" name="weight_unit" class="unit-select">
                            <option value="kg">kg</option>
                            <option value="lb">lb</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="height">Altura</label>
                    <div class="input-group">
                        <input type="number" id="height" name="height" step="0.1" min="100" max="250" required>
                        <select id="heightUnit" name="height_unit" class="unit-select">
                            <option value="cm">cm</option>
                            <option value="ft">ft</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="age">Edad</label>
                    <input type="number" id="age" name="age" min="15" max="100" required>
                </div>

                <div class="form-group">
                    <label>Género</label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="gender" value="male" required>
                            <div class="radio-content">
                                <span class="radio-icon">👨</span>
                                <span>Masculino</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="gender" value="female" required>
                            <div class="radio-content">
                                <span class="radio-icon">👩</span>
                                <span>Femenino</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="gender" value="other" required>
                            <div class="radio-content">
                                <span class="radio-icon">⚧</span>
                                <span>Otro</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- PASO 2: Nivel de Actividad -->
            <div class="form-step" data-step="2">
                <div class="step-header">
                    <h2>🏃‍♂️ Nivel de Actividad</h2>
                    <p>¿Qué tan activo eres en tu día a día?</p>
                </div>

                <div class="activity-cards">
                    <label class="activity-card">
                        <input type="radio" name="activity_level" value="sedentary" required>
                        <div class="card-content">
                            <div class="card-icon">🛋️</div>
                            <h3>Sedentario</h3>
                            <p>Poco o ningún ejercicio</p>
                            <span class="card-detail">Trabajo de oficina, poca actividad</span>
                        </div>
                    </label>

                    <label class="activity-card">
                        <input type="radio" name="activity_level" value="light" required>
                        <div class="card-content">
                            <div class="card-icon">🚶‍♂️</div>
                            <h3>Ligero</h3>
                            <p>Ejercicio 1-3 días/semana</p>
                            <span class="card-detail">Caminatas regulares</span>
                        </div>
                    </label>

                    <label class="activity-card">
                        <input type="radio" name="activity_level" value="moderate" required>
                        <div class="card-content">
                            <div class="card-icon">🏃</div>
                            <h3>Moderado</h3>
                            <p>Ejercicio 3-5 días/semana</p>
                            <span class="card-detail">Actividad regular</span>
                        </div>
                    </label>

                    <label class="activity-card">
                        <input type="radio" name="activity_level" value="active" required>
                        <div class="card-content">
                            <div class="card-icon">💪</div>
                            <h3>Activo</h3>
                            <p>Ejercicio 6-7 días/semana</p>
                            <span class="card-detail">Entrenamiento intenso</span>
                        </div>
                    </label>

                    <label class="activity-card">
                        <input type="radio" name="activity_level" value="very_active" required>
                        <div class="card-content">
                            <div class="card-icon">🏋️</div>
                            <h3>Muy Activo</h3>
                            <p>Ejercicio intenso diario</p>
                            <span class="card-detail">Atleta o trabajo físico</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- PASO 3: Objetivo -->
            <div class="form-step" data-step="3">
                <div class="step-header">
                    <h2>🎯 Tu Objetivo</h2>
                    <p>¿Qué quieres lograr?</p>
                </div>

                <div class="goal-cards">
                    <label class="goal-card">
                        <input type="radio" name="goal" value="lose_weight" required>
                        <div class="card-content">
                            <div class="card-icon">📉</div>
                            <h3>Perder Peso</h3>
                            <p>Reducir grasa corporal</p>
                        </div>
                    </label>

                    <label class="goal-card">
                        <input type="radio" name="goal" value="maintain" required>
                        <div class="card-content">
                            <div class="card-icon">⚖️</div>
                            <h3>Mantener</h3>
                            <p>Mantener peso actual</p>
                        </div>
                    </label>

                    <label class="goal-card">
                        <input type="radio" name="goal" value="gain_weight" required>
                        <div class="card-content">
                            <div class="card-icon">📈</div>
                            <h3>Subir Peso</h3>
                            <p>Aumentar peso saludablemente</p>
                        </div>
                    </label>

                    <label class="goal-card">
                        <input type="radio" name="goal" value="muscle_gain" required>
                        <div class="card-content">
                            <div class="card-icon">💪</div>
                            <h3>Ganar Músculo</h3>
                            <p>Aumentar masa muscular</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- PASO 4: Condiciones de Salud -->
            <div class="form-step" data-step="4">
                <div class="step-header">
                    <h2>🏥 Salud y Restricciones</h2>
                    <p>Esto nos ayudará a personalizar mejor tu plan</p>
                </div>

                <div class="form-group">
                    <label>¿Tienes alguna condición de salud? (Opcional)</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="conditions[]" value="diabetes">
                            <span>Diabetes</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="conditions[]" value="hypertension">
                            <span>Hipertensión</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="conditions[]" value="cholesterol">
                            <span>Colesterol alto</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="conditions[]" value="thyroid">
                            <span>Problemas de tiroides</span>
                        </label>
                    </div>
                    <textarea name="health_conditions" placeholder="Describe otras condiciones aquí..."></textarea>
                </div>

                <div class="form-group">
                    <label>Alergias Alimentarias (Opcional)</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="allergies[]" value="lactose">
                            <span>Lactosa</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="allergies[]" value="gluten">
                            <span>Gluten</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="allergies[]" value="nuts">
                            <span>Frutos secos</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="allergies[]" value="seafood">
                            <span>Mariscos</span>
                        </label>
                    </div>
                    <textarea name="allergies_other" placeholder="Otras alergias..."></textarea>
                </div>
            </div>

            <!-- PASO 5: Preferencias Alimenticias -->
            <div class="form-step" data-step="5">
                <div class="step-header">
                    <h2>🥗 Preferencias Alimenticias</h2>
                    <p>Personaliza tu plan de comidas</p>
                </div>

                <div class="form-group">
                    <label>Tipo de Dieta</label>
                    <div class="diet-cards">
                        <label class="diet-card">
                            <input type="radio" name="diet_type" value="normal" checked>
                            <div class="card-content">
                                <span>🍽️</span>
                                <span>Normal</span>
                            </div>
                        </label>
                        <label class="diet-card">
                            <input type="radio" name="diet_type" value="vegetarian">
                            <div class="card-content">
                                <span>🥕</span>
                                <span>Vegetariano</span>
                            </div>
                        </label>
                        <label class="diet-card">
                            <input type="radio" name="diet_type" value="vegan">
                            <div class="card-content">
                                <span>🌱</span>
                                <span>Vegano</span>
                            </div>
                        </label>
                        <label class="diet-card">
                            <input type="radio" name="diet_type" value="keto">
                            <div class="card-content">
                                <span>🥑</span>
                                <span>Keto</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Comidas al día</label>
                    <select name="meals_per_day" class="select-input">
                        <option value="3">3 comidas principales</option>
                        <option value="4">3 comidas + 1 snack</option>
                        <option value="5">3 comidas + 2 snacks</option>
                        <option value="6">6 comidas pequeñas</option>
                    </select>
                </div>
            </div>

            <!-- PASO 6: Confirmación y Cálculo -->
            <div class="form-step" data-step="6">
                <div class="step-header">
                    <h2>✅ ¡Casi listo!</h2>
                    <p>Revisa tu información</p>
                </div>

                <div class="summary-card">
                    <h3>Resumen de tu Perfil</h3>
                    <div class="summary-content" id="summaryContent">
                        <!-- Se llenará dinámicamente con JS -->
                    </div>
                </div>

                <div class="calculation-preview">
                    <h3>Tu Plan Nutricional</h3>
                    <div class="calc-grid">
                        <div class="calc-item">
                            <div class="calc-label">IMC</div>
                            <div class="calc-value" id="bmiBMI">--</div>
                        </div>
                        <div class="calc-item">
                            <div class="calc-label">Calorías Diarias</div>
                            <div class="calc-value" id="calcCalories">--</div>
                        </div>
                        <div class="calc-item">
                            <div class="calc-label">Proteínas</div>
                            <div class="calc-value" id="calcProtein">--g</div>
                        </div>
                        <div class="calc-item">
                            <div class="calc-label">Carbohidratos</div>
                            <div class="calc-value" id="calcCarbs">--g</div>
                        </div>
                        <div class="calc-item">
                            <div class="calc-label">Grasas</div>
                            <div class="calc-value" id="calcFats">--g</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Navegación -->
            <div class="form-navigation">
                <button type="button" class="btn-secondary" id="prevBtn" style="display: none;">
                    ← Anterior
                </button>
                <button type="button" class="btn-primary" id="nextBtn">
                    Siguiente →
                </button>
                <button type="submit" class="btn-primary" id="submitBtn" style="display: none;">
                    🚀 Crear Mi Plan
                </button>
            </div>
        </form>
    </div>

    <!-- Loader -->
    <div id="loader" class="loader hidden">
        <div class="spinner"></div>
        <p>Creando tu plan personalizado...</p>
    </div>

    <script src="../assets/js/questionnaire.js"></script>
</body>
</html>