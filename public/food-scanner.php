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
    <title>Escáner de Comida - Tweight</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/food-scanner.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="scanner-page">

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
            <a href="assistant.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7z" clip-rule="evenodd"/>
                </svg>
                <span>Asistente</span>
            </a>
            <a href="food-scanner.php" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                </svg>
                <span>Escáner de Comida</span>
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
        
        <header class="scanner-header">
            <div>
                <h1>Escáner de Comida 📸</h1>
                <p class="header-subtitle">Escanea tu comida y obtén información nutricional al instante</p>
            </div>
        </header>

        <div class="scanner-container">
            
            <!-- Upload Area -->
            <div class="upload-section">
                <div class="camera-preview" id="cameraPreview">
                    <div class="upload-placeholder">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        <h3>Toma una foto o sube una imagen</h3>
                        <p>La IA analizará tu comida y te dará información nutricional precisa</p>
                    </div>
                    <img id="previewImage" style="display: none; max-width: 100%; border-radius: 12px;">
                    <video id="cameraStream" autoplay playsinline style="display: none; width: 100%; border-radius: 12px;"></video>
                </div>

                <div class="upload-actions">
                    <input type="file" id="fileInput" accept="image/*" style="display: none;">
                    <button class="btn-upload" onclick="document.getElementById('fileInput').click()">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Subir Imagen
                    </button>
                    <button class="btn-camera" onclick="startCamera()">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        Usar Cámara
                    </button>
                    <button class="btn-capture" id="captureBtn" style="display: none;" onclick="capturePhoto()">
                        📸 Capturar
                    </button>
                    <button class="btn-primary" id="analyzeBtn" style="display: none;" onclick="analyzeFood()">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        Analizar Comida
                    </button>
                </div>
            </div>

            <!-- Results Area -->
            <div class="results-section" id="resultsSection" style="display: none;">
                <h2>Información Nutricional</h2>
                <div id="nutritionResults"></div>
            </div>

            <!-- History -->
            <div class="history-section">
                <h2>Historial de Escaneos</h2>
                <div class="history-grid" id="historyGrid">
                    <div class="empty-history">
                        <p>Aún no has escaneado ninguna comida</p>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <div class="loader hidden" id="loader">
        <div class="spinner"></div>
        <p>Analizando tu comida con IA...</p>
    </div>

    <script src="../assets/js/food-scanner.js"></script>
</body>
</html>