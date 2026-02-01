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

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user['user_id']]);
$userData = $stmt->fetch();

$startOfWeek = date('Y-m-d', strtotime('monday this week'));

// Verificar si existe lista
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM shopping_lists WHERE user_id = :user_id AND week_start = :week_start");
$stmt->execute(['user_id' => $user['user_id'], 'week_start' => $startOfWeek]);
$hasList = $stmt->fetch()['count'] > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Compras - Tweight</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/shopping.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="shopping-page">

    <!-- Sidebar (mismo que otros) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 60 60" fill="none">
                    <circle cx="30" cy="30" r="30" fill="#10B981"/>
                    <path d="M30 15L40 25L30 35L20 25L30 15Z" fill="white"/>
                </svg>
                <span>Tweight</span>
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
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6z" clip-rule="evenodd"/>
                </svg>
                <span>Calorías</span>
            </a>
            <a href="meals.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
                <span>Plan de Comidas</span>
            </a>
            <a href="shopping.php" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"/>
                </svg>
                <span>Lista de Compras</span>
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
        
        <header class="shopping-header">
            <div>
                <h1>Lista de Compras 🛒</h1>
                <p class="header-subtitle">Generada desde tu plan semanal</p>
            </div>
            <div class="header-actions">
                <button class="btn-secondary" onclick="window.print()">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2z" clip-rule="evenodd"/>
                    </svg>
                    Imprimir
                </button>
                <button class="btn-primary" onclick="generateShoppingList()">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793z"/>
                        <path fill-rule="evenodd" d="M11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" clip-rule="evenodd"/>
                    </svg>
                    Generar Lista
                </button>
            </div>
        </header>

        <div id="shoppingListContainer">
            <?php if (!$hasList): ?>
                <div class="empty-shopping-state">
                    <div class="empty-icon">🛒</div>
                    <h2>Aún no tienes una lista de compras</h2>
                    <p>Genera tu lista automáticamente desde tu plan de comidas</p>
                    <button class="btn-primary btn-large" onclick="generateShoppingList()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793z"/>
                        </svg>
                        Generar Mi Lista
                    </button>
                </div>
            <?php else: ?>
                <div class="loader-container">
                    <div class="spinner"></div>
                    <p>Cargando lista...</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <div class="loader hidden" id="loader">
        <div class="spinner"></div>
        <p>Generando lista...</p>
    </div>

    <script src="../assets/js/shopping.js"></script>
</body>
</html>