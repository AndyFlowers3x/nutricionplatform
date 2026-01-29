<?php
/**
 * Manejo de JSON Web Tokens (JWT)
 * Para autenticación y sesiones
 */

class JWT {
    private static $secret;
    private static $expiration;

    public static function init() {
        self::$secret = $_ENV['JWT_SECRET'] ?? 'default_secret_change_in_production';
        self::$expiration = $_ENV['JWT_EXPIRATION'] ?? 86400; // 24 horas
    }

    /**
     * Crear token JWT
     */
    public static function create($user_id, $email, $name) {
        self::init();

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode([
            'user_id' => $user_id,
            'email' => $email,
            'name' => $name,
            'iat' => time(),
            'exp' => time() + self::$expiration
        ]);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            self::$secret,
            true
        );

        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Verificar y decodificar token
     */
    public static function verify($token) {
        self::init();

        if (empty($token)) {
            return false;
        }

        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $tokenParts;

        $validSignature = hash_hmac(
            'sha256',
            $header . "." . $payload,
            self::$secret,
            true
        );

        $base64UrlSignature = self::base64UrlEncode($validSignature);

        if ($base64UrlSignature !== $signature) {
            return false;
        }

        $payloadData = json_decode(self::base64UrlDecode($payload), true);

        if ($payloadData['exp'] < time()) {
            return false; // Token expirado
        }

        return $payloadData;
    }

    /**
     * Codificar en Base64 URL
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificar desde Base64 URL
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>