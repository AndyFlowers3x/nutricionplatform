<?php
session_start();

require_once __DIR__ . '/../config/load_env.php';
require_once __DIR__ . '/../middleware/auth.php';

$user = AuthMiddleware::check();

if (!$user) {
    header('Location: login.php');
    exit;
}

// Verificar si completó el cuestionario
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM health_profiles WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user['user_id']]);
$profile = $stmt->fetch();

// Si NO tiene perfil completo, redirigir al cuestionario
if (!$profile || !$profile['weight'] || !$profile['height'] || !$profile['age']) {
    header('Location: questionnaire.php');
    exit;
}

// Obtener datos del usuario completos
$stmt = $conn->prepare("
    SELECT u.*, hp.*, us.weight_unit, us.height_unit
    FROM users u
    LEFT JOIN health_profiles hp ON u.id = hp.user_id
    LEFT JOIN user_settings us ON u.id = us.user_id
    WHERE u.id = :user_id
");
$stmt->execute(['user_id' => $user['user_id']]);
$userData = $stmt->fetch();

// Obtener racha actual
$stmt = $conn->prepare("SELECT * FROM user_streaks WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user['user_id']]);
$streak = $stmt->fetch();

// Si no existe racha, crear una
if (!$streak) {
    $stmt = $conn->prepare("
        INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_activity_date)
        VALUES (:user_id, 0, 0, CURDATE())
    ");
    $stmt->execute(['user_id' => $user['user_id']]);
    
    $stmt = $conn->prepare("SELECT * FROM user_streaks WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user['user_id']]);
    $streak = $stmt->fetch();
}

// Obtener calorías consumidas hoy
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(calories), 0) as total_calories,
           COALESCE(SUM(protein), 0) as total_protein,
           COALESCE(SUM(carbs), 0) as total_carbs,
           COALESCE(SUM(fats), 0) as total_fats
    FROM calories_log
    WHERE user_id = :user_id AND date = CURDATE()
");
$stmt->execute(['user_id' => $user['user_id']]);
$todayStats = $stmt->fetch();

// Obtener comidas completadas hoy
$stmt = $conn->prepare("
    SELECT COUNT(*) as completed_meals
    FROM meal_plans
    WHERE user_id = :user_id AND date = CURDATE() AND is_completed = 1
");
$stmt->execute(['user_id' => $user['user_id']]);
$mealsCompleted = $stmt->fetch()['completed_meals'];

// Calcular porcentajes
$caloriesPercentage = $userData['target_calories'] > 0 
    ? ($todayStats['total_calories'] / $userData['target_calories']) * 100 
    : 0;
$proteinPercentage = $userData['target_protein'] > 0 
    ? ($todayStats['total_protein'] / $userData['target_protein']) * 100 
    : 0;

// Próxima comida
$nextMeal = 'Desayuno';
$currentHour = (int)date('H');
if ($currentHour >= 6 && $currentHour < 12) {
    $nextMeal = 'Desayuno';
} elseif ($currentHour >= 12 && $currentHour < 18) {
    $nextMeal = 'Almuerzo';
} else {
    $nextMeal = 'Cena';
}

// Calcular IMC
$bmi = $userData['weight'] / (($userData['height']/100) ** 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weightloss Professional Nutrition</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="dashboard-page">
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 60 60" fill="none">
                    <circle cx="30" cy="30" r="30" fill="#10B981"/>
                    <path d="M30 15L40 25L30 35L20 25L30 15Z" fill="white"/>
                </svg>
                <span>Weightloss Professional Nutrition</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="javascript:void(0)" class="nav-item active" data-section="dashboard">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="javascript:void(0)" class="nav-item" data-section="calories">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7zm6 7a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1zm-3 3a1 1 0 100 2h.01a1 1 0 100-2H10zm-4 1a1 1 0 011-1h.01a1 1 0 110 2H7a1 1 0 01-1-1zm1-4a1 1 0 100 2h.01a1 1 0 100-2H7zm2 1a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1zm4-4a1 1 0 100 2h.01a1 1 0 100-2H13zM9 9a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1zM7 8a1 1 0 000 2h.01a1 1 0 000-2H7z" clip-rule="evenodd"/>
                </svg>
                <span>Calorías</span>
            </a>
            <a href="javascript:void(0)" class="nav-item" data-section="meals">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                </svg>
                <span>Plan de Comidas</span>
            </a>
            <a href="javascript:void(0)" class="nav-item" data-section="shopping">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
                <span>Lista de Compras</span>
            </a>
            <a href="javascript:void(0)" class="nav-item" data-section="assistant">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                </svg>
                <span>Asistente</span>
            </a>
            <a href="javascript:void(0)" class="nav-item" data-section="settings">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                </svg>
                <span>Ajustes</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <img src="<?php echo htmlspecialchars($userData['picture'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($userData['name']) . '&background=10B981&color=fff'); ?>" alt="Usuario" class="user-avatar">
                <div class="user-details">
                    <p class="user-name"><?php echo htmlspecialchars($userData['name']); ?></p>
                    <p class="user-email"><?php echo htmlspecialchars($userData['email']); ?></p>
                </div>
            </div>
            <a href="/nutricion-platform/api/auth/logout.php" class="btn-logout" title="Cerrar sesión">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        
        <!-- SECCIÓN: DASHBOARD -->
        <section id="section-dashboard" class="content-section active">
            <!-- Header -->
            <header class="dashboard-header">
                <div>
                    <h1>¡Hola, <?php echo htmlspecialchars(explode(' ', $userData['name'])[0]); ?>! 👋</h1>
                    <p class="header-subtitle">Aquí está tu resumen de hoy - <?php echo date('d/m/Y'); ?></p>
                </div>
                <button class="btn-refresh" onclick="location.reload()">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                    Actualizar
                </button>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #DCFCE7;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#10B981">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-label">Calorías de hoy</p>
                        <p class="stat-value"><?php echo number_format($todayStats['total_calories']); ?> <span>/ <?php echo number_format($userData['target_calories']); ?></span></p>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: <?php echo min($caloriesPercentage, 100); ?>%; background: #10B981;"></div>
                        </div>
                        <p class="stat-trend"><?php echo $caloriesPercentage < 80 ? '📊 En buen camino' : ($caloriesPercentage <= 110 ? '✅ Objetivo alcanzado' : '⚠️ Sobre el objetivo'); ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #DBEAFE;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#3B82F6">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-label">Proteína</p>
                        <p class="stat-value"><?php echo number_format($todayStats['total_protein'], 1); ?>g <span>/ <?php echo number_format($userData['target_protein'], 1); ?>g</span></p>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: <?php echo min($proteinPercentage, 100); ?>%; background: #3B82F6;"></div>
                        </div>
                        <p class="stat-trend"><?php echo round($proteinPercentage); ?>% completado</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FEF3C7;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#F59E0B">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-label">Racha actual</p>
                        <p class="stat-value"><?php echo $streak['current_streak']; ?> días</p>
                        <p class="stat-trend">
                            <?php 
                            if ($streak['current_streak'] == 0) {
                                echo '🌱 ¡Empieza hoy!';
                            } elseif ($streak['current_streak'] < 3) {
                                echo '💪 Buen inicio';
                            } elseif ($streak['current_streak'] < 7) {
                                echo '🔥 ¡Sigue así!';
                            } elseif ($streak['current_streak'] < 30) {
                                echo '🚀 ¡Increíble!';
                            } else {
                                echo '🏆 ¡Imparable!';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FCE7F3;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#EC4899">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-label">Comidas registradas</p>
                        <p class="stat-value"><?php echo $mealsCompleted; ?> <span>/ 3</span></p>
                        <p class="stat-trend">Siguiente: <?php echo $nextMeal; ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <section class="section">
                <h2 class="section-title">Acciones rápidas</h2>
                <div class="quick-actions">
                    <button class="action-btn" onclick="showSection('calories')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Registrar Comida</span>
                    </button>
                    <button class="action-btn" onclick="showSection('meals')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Ver Plan Semanal</span>
                    </button>
                    <button class="action-btn" onclick="showSection('shopping')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Lista de Compras</span>
                    </button>
                </div>
            </section>

            <!-- Info del perfil -->
            <section class="section">
                <h2 class="section-title">Tu Perfil Nutricional</h2>
                <div class="profile-info-grid">
                    <div class="profile-info-card">
                        <div class="info-icon">📊</div>
                        <div class="info-label">IMC</div>
                        <div class="info-value">
                            <?php 
                            echo number_format($bmi, 1);
                            
                            if ($bmi < 18.5) {
                                echo ' <span class="bmi-status underweight">Bajo peso</span>';
                            } elseif ($bmi < 25) {
                                echo ' <span class="bmi-status normal">Normal</span>';
                            } elseif ($bmi < 30) {
                                echo ' <span class="bmi-status overweight">Sobrepeso</span>';
                            } else {
                                echo ' <span class="bmi-status obese">Obesidad</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="profile-info-card">
                        <div class="info-icon">🎯</div>
                        <div class="info-label">Objetivo</div>
                        <div class="info-value">
                            <?php 
                            $goals = [
                                'lose_weight' => 'Perder Peso',
                                'maintain' => 'Mantener',
                                'gain_weight' => 'Subir Peso',
                                'muscle_gain' => 'Ganar Músculo'
                            ];
                            echo $goals[$userData['goal']] ?? 'No definido';
                            ?>
                        </div>
                    </div>

                    <div class="profile-info-card">
                        <div class="info-icon">🏃</div>
                        <div class="info-label">Nivel de Actividad</div>
                        <div class="info-value">
                            <?php 
                            $activities = [
                                'sedentary' => 'Sedentario',
                                'light' => 'Ligero',
                                'moderate' => 'Moderado',
                                'active' => 'Activo',
                                'very_active' => 'Muy Activo'
                            ];
                            echo $activities[$userData['activity_level']] ?? 'No definido';
                            ?>
                        </div>
                    </div>

                    <div class="profile-info-card">
                        <div class="info-icon">⚖️</div>
                        <div class="info-label">Peso Actual</div>
                        <div class="info-value">
                            <?php echo number_format($userData['weight'], 1); ?> kg
                        </div>
                    </div>
                </div>
            </section>

            <!-- Macros del día -->
            <section class="section">
                <h2 class="section-title">Macronutrientes de Hoy</h2>
                <div class="macros-grid">
                    <div class="macro-card">
                        <div class="macro-header" style="background: #DBEAFE; color: #1E40AF;">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Proteínas</span>
                        </div>
                        <div class="macro-body">
                            <div class="macro-value"><?php echo number_format($todayStats['total_protein'], 1); ?>g</div>
                            <div class="macro-target">de <?php echo number_format($userData['target_protein'], 1); ?>g</div>
                            <div class="macro-bar">
                                <div class="macro-bar-fill" style="width: <?php echo min(($todayStats['total_protein'] / $userData['target_protein']) * 100, 100); ?>%; background: #3B82F6;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="macro-card">
                        <div class="macro-header" style="background: #FEF3C7; color: #92400E;">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                            <span>Carbohidratos</span>
                        </div>
                        <div class="macro-body">
                            <div class="macro-value"><?php echo number_format($todayStats['total_carbs'], 1); ?>g</div>
                            <div class="macro-target">de <?php echo number_format($userData['target_carbs'], 1); ?>g</div>
                            <div class="macro-bar">
                                <div class="macro-bar-fill" style="width: <?php echo min(($todayStats['total_carbs'] / $userData['target_carbs']) * 100, 100); ?>%; background: #F59E0B;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="macro-card">
                        <div class="macro-header" style="background: #FEE2E2; color: #991B1B;">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                            </svg>
                            <span>Grasas</span>
                        </div>
                        <div class="macro-body">
                            <div class="macro-value"><?php echo number_format($todayStats['total_fats'], 1); ?>g</div>
                            <div class="macro-target">de <?php echo number_format($userData['target_fats'], 1); ?>g</div>
                            <div class="macro-bar">
                                <div class="macro-bar-fill" style="width: <?php echo min(($todayStats['total_fats'] / $userData['target_fats']) * 100, 100); ?>%; background: #EF4444;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <!-- SECCIÓN: CALORÍAS -->
        <section id="section-calories" class="content-section">
            <header class="dashboard-header">
                <div>
                    <h1>Registro de Calorías 🔥</h1>
                    <p class="header-subtitle">Registra tus comidas diarias</p>
                </div>
            </header>
            <div class="coming-soon-box">
                <div class="coming-soon-icon">📊</div>
                <h3>Sección en Desarrollo</h3>
                <p>Pronto podrás registrar tus comidas y ver gráficos detallados de tu progreso</p>
                <div class="coming-soon-features">
                    <div class="feature-item">✅ Búsqueda de alimentos</div>
                    <div class="feature-item">✅ Scanner de código de barras</div>
                    <div class="feature-item">✅ Gráficos de progreso</div>
                    <div class="feature-item">✅ Historial completo</div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN: PLAN DE COMIDAS -->
        <section id="section-meals" class="content-section">
            <header class="dashboard-header">
                <div>
                    <h1>Plan de Comidas 🍽️</h1>
                    <p class="header-subtitle">Tu plan personalizado de la semana</p>
                </div>
            </header>
            <div class="coming-soon-box">
                <div class="coming-soon-icon">🚀</div>
                <h3>Próximamente</h3>
                <p>Estamos trabajando en tu plan de comidas personalizado</p>
            </div>
        </section>

        <!-- SECCIÓN: LISTA DE COMPRAS -->
        <section id="section-shopping" class="content-section">
            <header class="dashboard-header">
                <div>
                    <h1>Lista de Compras 🛒</h1>
                    <p class="header-subtitle">Generada automáticamente desde tu plan</p>
                </div>
            </header>
            <div class="coming-soon-box">
                <div class="coming-soon-icon">📝</div>
                <h3>Próximamente</h3>
                <p>Tu lista de compras se generará automáticamente</p>
            </div>
        </section>

        <!-- SECCIÓN: ASISTENTE -->
        <section id="section-assistant" class="content-section">
            <header class="dashboard-header">
                <div>
                    <h1>Asistente Personal 🤖</h1>
                    <p class="header-subtitle">Tu nutricionista virtual</p>
                </div>
            </header>
            <div class="coming-soon-box">
                <div class="coming-soon-icon">💬</div>
                <h3>Próximamente</h3>
                <p>Chatea con tu asistente nutricional inteligente</p>
            </div>
        </section>

        <!-- SECCIÓN: AJUSTES -->
        <section id="section-settings" class="content-section">
            <header class="dashboard-header">
                <div>
                    <h1>Ajustes ⚙️</h1>
                    <p class="header-subtitle">Configura tu cuenta</p>
                </div>
            </header>
            <div class="coming-soon-box">
                <div class="coming-soon-icon">🔧</div>
                <h3>Próximamente</h3>
                <p>Personaliza tu experiencia en Tweight</p>
            </div>
        </section>

    </main>

    <script>
        // Navegación entre secciones
        function showSection(sectionName) {
            // Ocultar todas las secciones
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remover active de nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Mostrar sección seleccionada
            const section = document.getElementById('section-' + sectionName);
            if (section) {
                section.classList.add('active');
            }
            
            // Activar nav item correspondiente
            const navItem = document.querySelector(`[data-section="${sectionName}"]`);
            if (navItem) {
                navItem.classList.add('active');
            }
            
            // Scroll al inicio
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Event listeners para navegación
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('data-section');
                showSection(section);
            });
        });
    </script>
    <!-- Botón Menú Mobile -->
<button class="mobile-menu-btn" id="mobileMenuBtn" style="display: none;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

<script>
// Menú mobile
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const sidebar = document.querySelector('.sidebar');

if (window.innerWidth <= 1024) {
    mobileMenuBtn.style.display = 'flex';
}

window.addEventListener('resize', () => {
    if (window.innerWidth <= 1024) {
        mobileMenuBtn.style.display = 'flex';
    } else {
        mobileMenuBtn.style.display = 'none';
        sidebar.classList.remove('active');
    }
});

mobileMenuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});

// Cerrar sidebar al hacer clic en una opción (mobile)
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
        if (window.innerWidth <= 1024) {
            sidebar.classList.remove('active');
        }
    });
});

// Cerrar sidebar al hacer clic fuera (mobile)
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024 && 
        sidebar.classList.contains('active') && 
        !sidebar.contains(e.target) && 
        !mobileMenuBtn.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});
</script>
</body>
</html>