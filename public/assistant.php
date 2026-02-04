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

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['user_id']]);
$userData = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistente Virtual - Tweight</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/assistant.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="assistant-page">

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
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                </svg>
                <span>Calorías</span>
            </a>
            <a href="meals.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
                <span>Plan de Comidas</span>
            </a>
            <a href="shopping.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"/>
                </svg>
                <span>Lista de Compras</span>
            </a>
            <a href="assistant.php" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                </svg>
                <span>Asistente</span>
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

    <main class="main-content">
        
        <header class="assistant-header">
            <div>
                <h1>Asistente Virtual 🤖</h1>
                <p class="header-subtitle">Tu nutricionista personal con IA</p>
            </div>
            <button class="btn-secondary" onclick="clearChat()">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Limpiar Chat
            </button>
        </header>

        <div class="chat-container">
            <div class="chat-messages" id="chatMessages">
                <div class="welcome-message">
                    <div class="assistant-avatar">🤖</div>
                    <div class="message-content">
                        <h3>¡Hola! Soy tu asistente nutricional 👋</h3>
                        <p>Puedo ayudarte con:</p>
                        <ul>
                            <li>📊 Revisar tu progreso diario</li>
                            <li>🍽️ Sugerencias de comidas saludables</li>
                            <li>💪 Consejos nutricionales personalizados</li>
                            <li>❓ Responder tus dudas sobre nutrición</li>
                        </ul>
                        <p>¿En qué puedo ayudarte hoy?</p>
                    </div>
                </div>
            </div>

            <div class="chat-input-container">
                <div class="quick-questions">
                    <button class="quick-btn" onclick="sendQuickMessage('¿Cuántas calorías he consumido hoy?')">
                        📊 Calorías del día
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('¿Qué puedo comer?')">
                        🍽️ Qué comer
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Dame motivación')">
                        💪 Motivación
                    </button>
                </div>
                <div class="chat-input-box">
                    <input type="text" id="chatInput" placeholder="Escribe tu pregunta..." onkeypress="handleKeyPress(event)">
                    <button class="btn-send" onclick="sendMessage()">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </main>

    <div class="loader hidden" id="loader">
        <div class="spinner"></div>
    </div>

    <script src="../assets/js/assistant.js"></script>
</body>
</html>