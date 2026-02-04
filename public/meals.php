<?php
session_start();

require_once __DIR__ . '/../config/load_env.php';
require_once __DIR__ . '/../middleware/auth.php';

$user = AuthMiddleware::check();

if (!$user) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Obtener perfil del usuario
$stmt = $conn->prepare("
    SELECT u.*, hp.* 
    FROM users u
    LEFT JOIN health_profiles hp ON u.id = hp.user_id
    WHERE u.id = :user_id
");
$stmt->execute(['user_id' => $user['user_id']]);
$userData = $stmt->fetch();

// Fechas de la semana
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

// Verificar si existe plan
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM meal_plans 
    WHERE user_id = :user_id 
    AND date BETWEEN :start_date AND :end_date
");
$stmt->execute([
    'user_id' => $user['user_id'],
    'start_date' => $startOfWeek,
    'end_date' => $endOfWeek
]);
$hasPlan = $stmt->fetch()['count'] > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de Comidas - Weight Professional Nutrition</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/meals.css">
    <link rel="stylesheet" href="../assets/css/toast-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="meals-page">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 60 60" fill="none">
                    <circle cx="30" cy="30" r="30" fill="#10B981"/>
                    <path d="M30 15L40 25L30 35L20 25L30 15Z" fill="white"/>
                </svg>
                <span>Weight Professional Nutrition</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="calories.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                </svg>
                <span>Calorías</span>
            </a>
            <a href="meals.php" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
                <span>Plan de Comidas</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <img src="<?php echo htmlspecialchars($userData['picture'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($userData['name'])); ?>" alt="Usuario" class="user-avatar">
                <div class="user-details">
                    <p class="user-name"><?php echo htmlspecialchars($userData['name']); ?></p>
                    <p class="user-email"><?php echo htmlspecialchars($userData['email']); ?></p>
                </div>
            </div>
            <a href="/nutricion-platform/api/auth/logout.php" class="btn-logout">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        
        <!-- Header -->
        <header class="meals-header">
            <div>
                <h1>Plan de Comidas 🍽️</h1>
                <p class="header-subtitle">Tu plan personalizado - Semana del <?php echo date('d/m', strtotime($startOfWeek)); ?> al <?php echo date('d/m/Y', strtotime($endOfWeek)); ?></p>
            </div>
            <div class="header-actions">
                <button class="btn-secondary" onclick="location.reload()">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Actualizar
                </button>
                <button class="btn-primary" onclick="generatePlan()">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                    </svg>
                    Generar Nuevo Plan
                </button>
            </div>
        </header>

        <!-- Plan Summary -->
        <div class="plan-summary">
            <div class="summary-stat">
                <div class="stat-icon">🎯</div>
                <div class="stat-info">
                    <div class="stat-label">Calorías Diarias</div>
                    <div class="stat-value"><?php echo number_format($userData['target_calories']); ?> kcal</div>
                </div>
            </div>
            <div class="summary-stat">
                <div class="stat-icon">⚡</div>
                <div class="stat-info">
                    <div class="stat-label">Proteína Diaria</div>
                    <div class="stat-value"><?php echo number_format($userData['target_protein'], 1); ?>g</div>
                </div>
            </div>
            <div class="summary-stat">
                <div class="stat-icon">🍽️</div>
                <div class="stat-info">
                    <div class="stat-label">Comidas al Día</div>
                    <div class="stat-value">3-5 comidas</div>
                </div>
            </div>
        </div>

        <!-- Week Plan -->
        <div id="weekPlanContainer">
            <?php if (!$hasPlan): ?>
                <!-- Empty State -->
                <div class="empty-plan-state">
                    <div class="empty-icon">📅</div>
                    <h2>Aún no tienes un plan de comidas</h2>
                    <p>Genera tu plan semanal personalizado basado en tus objetivos nutricionales</p>
                    <button class="btn-primary btn-large" onclick="generatePlan()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Generar Mi Plan Semanal
                    </button>
                    <div class="plan-features">
                        <div class="feature">✅ Comidas balanceadas</div>
                        <div class="feature">✅ Según tus objetivos</div>
                        <div class="feature">✅ Fácil de seguir</div>
                        <div class="feature">✅ Ajustado a tus calorías</div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Plan cargará aquí vía JavaScript -->
                <div class="loader-container">
                    <div class="spinner"></div>
                    <p>Cargando tu plan semanal...</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Modal: Detalle de Comida -->
    <div class="modal" id="mealDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="mealDetailTitle">Detalle de Comida</h2>
                <button class="btn-close" onclick="closeMealDetail()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="mealDetailBody">
                <!-- Se llenará dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div class="loader hidden" id="loader">
        <div class="spinner"></div>
        <p>Generando tu plan...</p>
    </div>

    <script src="../assets/js/meals.js"></script>
</body>
</html>