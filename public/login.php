<?php
session_start();

// Si ya está autenticado, redirigir al dashboard
if (isset($_COOKIE['auth_token'])) {
    require_once __DIR__ . '/../config/load_env.php';
    require_once __DIR__ . '/../config/jwt.php';
    
    $payload = JWT::verify($_COOKIE['auth_token']);
    if ($payload) {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Tweight Nutrition</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="login-logo">
                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="30" cy="30" r="30" fill="#10B981"/>
                    <path d="M30 15L40 25L30 35L20 25L30 15Z" fill="white"/>
                    <path d="M30 35L40 45L30 55L20 45L30 35Z" fill="white" opacity="0.6"/>
                </svg>
            </div>

            <!-- Título -->
            <h1 class="login-title">Bienvenido a Tweight</h1>
            <p class="login-subtitle">Tu plataforma de nutrición personalizada</p>

            <!-- Botón Google -->
            <a href="/nutricion-platform/api/auth/google.php" class="btn-google">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span>Continuar con Google</span>
            </a>

            <!-- Divider -->
            <div class="login-divider">
                <span>o</span>
            </div>

            <!-- Formulario Email (Opcional - próximamente) -->
            <form class="login-form" id="emailLoginForm" style="display: none;">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary" disabled>
                    Disponible próximamente
                </button>
            </form>

            <!-- Footer -->
            <p class="login-footer">
                Al continuar, aceptas nuestros 
                <a href="#">Términos de Servicio</a> y 
                <a href="#">Política de Privacidad</a>
            </p>
        </div>
    </div>

    <!-- Loader -->
    <div id="loader" class="loader hidden">
        <div class="spinner"></div>
        <p>Iniciando sesión...</p>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>