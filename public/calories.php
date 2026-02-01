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
    SELECT u.*, hp.*, us.weight_unit 
    FROM users u
    LEFT JOIN health_profiles hp ON u.id = hp.user_id
    LEFT JOIN user_settings us ON u.id = us.user_id
    WHERE u.id = :user_id
");
$stmt->execute(['user_id' => $user['user_id']]);
$userData = $stmt->fetch();

// Obtener estadísticas del día
$stmt = $conn->prepare("
    SELECT 
        COALESCE(SUM(calories), 0) as total_calories,
        COALESCE(SUM(protein), 0) as total_protein,
        COALESCE(SUM(carbs), 0) as total_carbs,
        COALESCE(SUM(fats), 0) as total_fats,
        COUNT(*) as meals_count
    FROM calories_log
    WHERE user_id = :user_id AND date = CURDATE()
");
$stmt->execute(['user_id' => $user['user_id']]);
$todayStats = $stmt->fetch();

// Obtener historial de hoy
$stmt = $conn->prepare("
    SELECT * FROM calories_log
    WHERE user_id = :user_id AND date = CURDATE()
    ORDER BY created_at DESC
");
$stmt->execute(['user_id' => $user['user_id']]);
$todayLog = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Calorías - Weight Proffessional Nutrition 
    </title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/calories.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="calories-page">

    <!-- Sidebar (mismo del dashboard) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 60 60" fill="none">
                    <circle cx="30" cy="30" r="30" fill="#10B981"/>
                    <path d="M30 15L40 25L30 35L20 25L30 15Z" fill="white"/>
                </svg>
                <span>Weight Proffessional Nutrition </span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="calories.php" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                </svg>
                <span>Calorías</span>
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
        <header class="calories-header">
            <div>
                <h1>Registro de Calorías 🔥</h1>
                <p class="header-subtitle">Registra lo que comes hoy - <?php echo date('d/m/Y'); ?></p>
            </div>
            <button class="btn-primary" onclick="openAddFoodModal()">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Agregar Comida
            </button>
        </header>

        <!-- Stats Summary -->
        <div class="calories-summary">
            <div class="summary-card main-calories">
                <div class="summary-icon">🔥</div>
                <div class="summary-content">
                    <div class="summary-label">Calorías de Hoy</div>
                    <div class="summary-value" id="totalCalories"><?php echo number_format($todayStats['total_calories']); ?></div>
                    <div class="summary-target">de <?php echo number_format($userData['target_calories']); ?> kcal</div>
                    <div class="summary-progress">
                        <div class="summary-progress-bar" id="caloriesProgressBar" style="width: <?php echo min(($todayStats['total_calories'] / $userData['target_calories']) * 100, 100); ?>%;"></div>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon" style="background: #DBEAFE; color: #3B82F6;">⚡</div>
                <div class="summary-content">
                    <div class="summary-label">Proteína</div>
                    <div class="summary-value" id="totalProtein"><?php echo number_format($todayStats['total_protein'], 1); ?>g</div>
                    <div class="summary-target">de <?php echo number_format($userData['target_protein'], 1); ?>g</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon" style="background: #FEF3C7; color: #F59E0B;">🌾</div>
                <div class="summary-content">
                    <div class="summary-label">Carbohidratos</div>
                    <div class="summary-value" id="totalCarbs"><?php echo number_format($todayStats['total_carbs'], 1); ?>g</div>
                    <div class="summary-target">de <?php echo number_format($userData['target_carbs'], 1); ?>g</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon" style="background: #FEE2E2; color: #EF4444;">🥑</div>
                <div class="summary-content">
                    <div class="summary-label">Grasas</div>
                    <div class="summary-value" id="totalFats"><?php echo number_format($todayStats['total_fats'], 1); ?>g</div>
                    <div class="summary-target">de <?php echo number_format($userData['target_fats'], 1); ?>g</div>
                </div>
            </div>
        </div>

        <!-- Today's Log -->
        <section class="calories-log-section">
            <h2 class="section-title">Comidas de Hoy (<?php echo $todayStats['meals_count']; ?>)</h2>
            
            <div class="log-list" id="logList">
                <?php if (empty($todayLog)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🍽️</div>
                        <h3>Aún no has registrado comidas hoy</h3>
                        <p>Haz clic en "Agregar Comida" para empezar a registrar</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todayLog as $entry): ?>
                        <div class="log-item">
                            <div class="log-icon">🍴</div>
                            <div class="log-details">
                                <div class="log-name"><?php echo htmlspecialchars($entry['notes']); ?></div>
                                <div class="log-time"><?php echo date('H:i', strtotime($entry['created_at'])); ?></div>
                            </div>
                            <div class="log-macros">
                                <span class="macro-badge calories"><?php echo number_format($entry['calories']); ?> kcal</span>
                                <span class="macro-badge protein"><?php echo number_format($entry['protein'], 1); ?>g P</span>
                                <span class="macro-badge carbs"><?php echo number_format($entry['carbs'], 1); ?>g C</span>
                                <span class="macro-badge fats"><?php echo number_format($entry['fats'], 1); ?>g G</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- Modal: Agregar Comida -->
    <div class="modal" id="addFoodModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Agregar Comida</h2>
                <button class="btn-close" onclick="closeAddFoodModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <!-- Buscador -->
                <div class="search-box">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" id="foodSearchInput" placeholder="Buscar alimento... (ej: pollo, arroz, manzana)" autocomplete="off">
                </div>

                <!-- Filtro de categorías -->
                <div class="category-filters">
                    <button class="category-btn active" data-category="">Todos</button>
                    <button class="category-btn" data-category="fruits">🍎 Frutas</button>
                    <button class="category-btn" data-category="vegetables">🥦 Vegetales</button>
                    <button class="category-btn" data-category="proteins">🍗 Proteínas</button>
                    <button class="category-btn" data-category="grains">🌾 Granos</button>
                    <button class="category-btn" data-category="dairy">🥛 Lácteos</button>
                </div>

                <!-- Resultados de búsqueda -->
                <div class="search-results" id="searchResults">
                    <div class="search-hint">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <p>Busca un alimento para ver los resultados</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Porción -->
    <div class="modal" id="servingModal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2>Cantidad Consumida</h2>
                <button class="btn-close" onclick="closeServingModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div id="selectedFoodInfo"></div>
                
                <div class="serving-input-group">
                    <label for="servingsInput">Porciones</label>
                    <div class="serving-controls">
                        <button class="btn-serving" onclick="decreaseServing()">-</button>
                        <input type="number" id="servingsInput" value="1" min="0.5" max="10" step="0.5">
                        <button class="btn-serving" onclick="increaseServing()">+</button>
                    </div>
                </div>

                <div class="serving-preview" id="servingPreview">
                    <!-- Se llenará dinámicamente -->
                </div>

                <button class="btn-primary btn-block" onclick="confirmLogMeal()">
                    Registrar Comida
                </button>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div class="loader hidden" id="loader">
        <div class="spinner"></div>
        <p>Guardando...</p>
    </div>

    <script src="../assets/js/calories.js"></script>
</body>
</html>