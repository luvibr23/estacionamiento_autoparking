<?php
// ================================================
// CONTROLADOR DE AUTENTICACIÓN
// Archivo: controllers/AuthController.php
// ================================================

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Procesar login
     */
    public function login() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        // Verificar CSRF token
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido']);
            return;
        }
        
        // Validar datos de entrada
        $usuario = cleanInput($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($usuario) || empty($password)) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
            return;
        }
        
        // Intentar autenticación
        $result = $this->userModel->authenticate($usuario, $password);
        
        if ($result['success']) {
            // Crear sesión
            $this->createSession($result['user']);
            
            // Respuesta exitosa
            $this->jsonResponse([
                'success' => true,
                'message' => 'Login exitoso',
                'redirect' => $this->getDashboardUrl($result['user']['rol'])
            ]);
        } else {
            // Login fallido
            $this->jsonResponse(['success' => false, 'message' => $result['message']]);
        }
    }
    
    /**
     * Crear sesión de usuario
     */
    private function createSession($userData) {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
        // Guardar datos en sesión
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['usuario'] = $userData['usuario'];
        $_SESSION['nombre'] = $userData['nombre'];
        $_SESSION['rol'] = $userData['rol'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // Guardar en tabla de sesiones
        $this->saveSessionToDatabase();
    }
    
    /**
     * Guardar sesión en base de datos
     */
    private function saveSessionToDatabase() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $query = "INSERT INTO sesiones (id, usuario_id, ip_address, user_agent, payload) 
                     VALUES (:session_id, :usuario_id, :ip_address, :user_agent, :payload)
                     ON DUPLICATE KEY UPDATE 
                     ip_address = VALUES(ip_address),
                     user_agent = VALUES(user_agent),
                     payload = VALUES(payload),
                     ultimo_activity = CURRENT_TIMESTAMP";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':session_id', session_id());
            $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
            $stmt->bindParam(':ip_address', $_SESSION['ip_address']);
            $stmt->bindParam(':user_agent', $_SESSION['user_agent']);
            $stmt->bindParam(':payload', serialize($_SESSION));
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error guardando sesión: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener URL del dashboard según rol
     */
    private function getDashboardUrl($rol) {
        switch ($rol) {
            case 'Administrador':
                return 'views/admin_dashboard.php';
            case 'Operador':
                return 'views/operador_dashboard.php';
            default:
                return 'views/dashboard.php';
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Eliminar sesión de base de datos
            $this->deleteSessionFromDatabase();
            
            // Limpiar sesión
            session_unset();
            session_destroy();
            
            // Regenerar ID de sesión
            session_start();
            session_regenerate_id(true);
            
            setFlashMessage('success', 'Sesión cerrada correctamente');
        }
        
        redirect('index.php');
    }
    
    /**
     * Eliminar sesión de base de datos
     */
    private function deleteSessionFromDatabase() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $query = "DELETE FROM sesiones WHERE id = :session_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':session_id', session_id());
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error eliminando sesión: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Verificar rol de usuario
     */
    public static function hasRole($rol) {
        return self::isAuthenticated() && $_SESSION['rol'] === $rol;
    }
    
    /**
     * Middleware de autenticación
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            setFlashMessage('error', 'Debe iniciar sesión para acceder');
            redirect('index.php');
        }
        
        // Verificar timeout de sesión (1 hora)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            session_unset();
            session_destroy();
            setFlashMessage('warning', 'Sesión expirada, inicie sesión nuevamente');
            redirect('index.php');
        }
        
        // Actualizar última actividad
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Middleware de rol
     */
    public static function requireRole($rol) {
        self::requireAuth();
        
        if (!self::hasRole($rol)) {
            setFlashMessage('error', 'No tiene permisos para acceder a esta sección');
            redirect('views/dashboard.php');
        }
    }
    
    /**
     * Respuesta JSON
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}

// ================================================
// PROCESAMIENTO DE REQUESTS
// ================================================

// Procesar requests AJAX
if (isset($_POST['action'])) {
    $auth = new AuthController();
    
    switch ($_POST['action']) {
        case 'login':
            $auth->login();
            break;
        case 'logout':
            $auth->logout();
            break;
        default:
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}
?>