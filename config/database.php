<?php
/**
 * Configuración de Base de Datos
<<<<<<< HEAD
 * Manejo de conexión PDO con MySQL
 */

=======
 * Archivo: config/database.php
 * 
 * Este archivo usa las variables del archivo .env
 */

// Cargar variables de entorno
require_once __DIR__ . '/load_env.php';

>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $conn;
<<<<<<< HEAD

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? '';
        $this->username = $_ENV['DB_USER'] ?? '';
        $this->password = $_ENV['DB_PASS'] ?? '';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    }

    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos");
        }

        return $this->conn;
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>
```

=======
    
    public function __construct() {
        // Obtener configuración desde .env
        $this->host = env('DB_HOST', 'localhost');
        $this->db_name = env('DB_NAME', 'nutricion_platform');
        $this->username = env('DB_USER', 'root');
        $this->password = env('DB_PASS', '');
        $this->charset = env('DB_CHARSET', 'utf8mb4');
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . 
                   ";dbname=" . $this->db_name . 
                   ";charset=" . $this->charset;
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configurar PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch(PDOException $e) {
            // Log del error
            error_log("Error de conexión a BD: " . $e->getMessage());
            
            // En desarrollo, mostrar el error
            if (env('APP_ENV') === 'development') {
                throw new Exception("Error de conexión a BD: " . $e->getMessage());
            } else {
                throw new Exception("Error al conectar con la base de datos");
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Método para verificar la conexión
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa',
                    'host' => $this->host,
                    'database' => $this->db_name,
                    'user' => $this->username
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'host' => $this->host,
                'database' => $this->db_name,
                'user' => $this->username
            ];
        }
    }
}
?>
>>>>>>> c54ba6597d1462ca55653a83f10c8f0d24e55f7b
