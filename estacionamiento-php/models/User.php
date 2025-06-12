<?php
// ================================================
// MODELO DE USUARIO
// Archivo: models/User.php
// ================================================

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table = 'usuarios';
    
    // Propiedades
    public $id;
    public $usuario;
    public $password;
    public $nombre;
    public $rol;
    public $activo;
    public $fecha_creacion;
    public $fecha_actualizacion;
    public $ultimo_acceso;
    public $intentos_fallidos;
    public $bloqueado_hasta;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Autenticar usuario
     */
    public function authenticate($usuario, $password) {
        try {
            // Verificar si el usuario existe y está activo
            $query = "SELECT * FROM {$this->table} 
                     WHERE usuario = :usuario AND activo = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Usuario no encontrado o inactivo'];
            }
            
            // Verificar si está bloqueado
            $blockStatus = $this->isBlocked($user['id']);
            if ($blockStatus['blocked']) {
                $minutesLeft = max(0, ceil((strtotime($blockStatus['until']) - time()) / 60));
                
                if ($blockStatus['reason'] === 'time') {
                    return [
                        'success' => false, 
                        'message' => "Usuario bloqueado temporalmente. Tiempo restante: {$minutesLeft} minutos.",
                        'blocked_until' => $blockStatus['until'],
                        'attempts' => $blockStatus['attempts']
                    ];
                } else {
                    return [
                        'success' => false, 
                        'message' => "Usuario bloqueado por exceso de intentos fallidos. Intente en {$minutesLeft} minutos.",
                        'blocked_until' => $blockStatus['until'],
                        'attempts' => $blockStatus['attempts']
                    ];
                }
            }
            
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                // Login exitoso
                $this->resetFailedAttempts($user['id']);
                $this->updateLastAccess($user['id']);
                
                // Cargar datos del usuario
                $this->loadUserData($user);
                
                return ['success' => true, 'message' => 'Login exitoso', 'user' => $user];
            } else {
                // Contraseña incorrecta
                $newAttempts = $this->incrementFailedAttempts($user['id']);
                
                $remainingAttempts = max(0, 5 - $newAttempts);
                
                if ($newAttempts >= 5) {
                    return [
                        'success' => false, 
                        'message' => 'Usuario bloqueado por exceso de intentos fallidos. Intente en 15 minutos.',
                        'attempts' => $newAttempts,
                        'blocked' => true
                    ];
                } else {
                    return [
                        'success' => false, 
                        'message' => "Contraseña incorrecta. Intentos restantes: {$remainingAttempts}",
                        'attempts' => $newAttempts,
                        'remaining' => $remainingAttempts
                    ];
                }
            }
            
        } catch (Exception $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Verificar si el usuario está bloqueado
     */
    private function isBlocked($userId) {
        $query = "SELECT bloqueado_hasta, intentos_fallidos FROM {$this->table} 
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if ($result) {
            // Verificar si está bloqueado por tiempo
            if ($result['bloqueado_hasta'] && strtotime($result['bloqueado_hasta']) > time()) {
                return [
                    'blocked' => true,
                    'reason' => 'time',
                    'until' => $result['bloqueado_hasta'],
                    'attempts' => $result['intentos_fallidos']
                ];
            }
            
            // Verificar si ha excedido el límite de intentos (auto-reset si ha pasado el tiempo)
            if ($result['intentos_fallidos'] >= 5) {
                // Si no hay tiempo de bloqueo establecido, establecerlo ahora
                if (!$result['bloqueado_hasta'] || strtotime($result['bloqueado_hasta']) <= time()) {
                    $this->setBlockTime($userId);
                    return [
                        'blocked' => true,
                        'reason' => 'attempts',
                        'until' => date('Y-m-d H:i:s', time() + 900), // 15 minutos
                        'attempts' => $result['intentos_fallidos']
                    ];
                }
            }
        }
        
        return ['blocked' => false];
    }
    
    /**
     * Establecer tiempo de bloqueo
     */
    private function setBlockTime($userId) {
        $query = "UPDATE {$this->table} 
                 SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }
    
    /**
     * Incrementar intentos fallidos
     */
    private function incrementFailedAttempts($userId) {
        $query = "UPDATE {$this->table} 
                 SET intentos_fallidos = intentos_fallidos + 1,
                     bloqueado_hasta = CASE 
                         WHEN intentos_fallidos >= 4 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                         ELSE bloqueado_hasta
                     END
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        // Obtener el número actual de intentos
        $checkQuery = "SELECT intentos_fallidos FROM {$this->table} WHERE id = :id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $userId);
        $checkStmt->execute();
        $result = $checkStmt->fetch();
        
        return $result ? $result['intentos_fallidos'] : 0;
    }
    
    /**
     * Resetear intentos fallidos
     */
    private function resetFailedAttempts($userId) {
        $query = "UPDATE {$this->table} 
                 SET intentos_fallidos = 0, bloqueado_hasta = NULL 
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }
    
    /**
     * Actualizar último acceso
     */
    private function updateLastAccess($userId) {
        $query = "UPDATE {$this->table} 
                 SET ultimo_acceso = NOW() 
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }
    
    /**
     * Cargar datos del usuario
     */
    private function loadUserData($userData) {
        $this->id = $userData['id'];
        $this->usuario = $userData['usuario'];
        $this->nombre = $userData['nombre'];
        $this->rol = $userData['rol'];
        $this->activo = $userData['activo'];
        $this->fecha_creacion = $userData['fecha_creacion'];
        $this->ultimo_acceso = $userData['ultimo_acceso'];
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getUserById($id) {
        $query = "SELECT id, usuario, nombre, rol, activo, fecha_creacion, ultimo_acceso 
                 FROM {$this->table} WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Obtener usuario por username
     */
    public function getUserByUsername($usuario) {
        $query = "SELECT id, usuario, nombre, rol, activo, fecha_creacion, ultimo_acceso 
                 FROM {$this->table} WHERE usuario = :usuario";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Verificar si el usuario existe
     */
    public function userExists($usuario) {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE usuario = :usuario";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE {$this->table} 
                 SET password = :password 
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile($userId, $nombre) {
        $query = "UPDATE {$this->table} 
                 SET nombre = :nombre 
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas de usuario
     */
    public function getUserStats($userId) {
        $query = "SELECT 
                    COUNT(*) as total_accesos,
                    MAX(ultimo_acceso) as ultimo_acceso,
                    intentos_fallidos
                 FROM {$this->table} 
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>