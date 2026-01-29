<?php
/**
 * Middleware de Autenticación
 * Protege rutas y verifica JWT
 */

require_once __DIR__ . '/../config/jwt.php';

class AuthMiddleware {
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function check() {
        $token = self::getTokenFromRequest();

        if (!$token) {
            self::unauthorized();
            return false;
        }

        $payload = JWT::verify($token);

        if (!$payload) {
            self::unauthorized();
            return false;
        }

        return $payload;
    }

    /**
     * Obtener token desde cookies o headers
     */
    private static function getTokenFromRequest() {
        // Intentar desde cookie
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        // Intentar desde header Authorization
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Respuesta no autorizado
     */
    private static function unauthorized() {
        if (self::isAjaxRequest()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        } else {
            header('Location: /public/login.php');
            exit;
        }
    }

    /**
     * Verificar si es petición AJAX
     */
    private static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
?>