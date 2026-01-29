<?php
/**
 * Logout - Cerrar sesión
 * Elimina token JWT y redirige al login
 */

session_start();

// Eliminar cookie de autenticación
setcookie('auth_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Destruir sesión
session_unset();
session_destroy();

// Redirigir al login
header('Location: /nutricion-platform/public/login.php');
exit;
?>