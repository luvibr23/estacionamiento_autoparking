<?php
// ================================================
// CONTROLADOR DE AUTENTICACIÓN CORREGIDO
// Archivo: controllers/AuthController_fixed.php
// ================================================

// Suprimir cualquier output previo y errores

session_start();


error_reporting(E_ERROR | E_PARSE); // Solo errores críticos


ini_set('display_errors', 0);

// Limpiar cualquier output buffer previo
if (ob_get_level()) {
    ob_clean();
}

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar headers JSON ANTES de cualquier output
//header('Content-Type: application/json; charset=utf-8');
//header('Cache-Control: no-cache, must-revalidate');
//header('Pragma: no-cache');

// Función para respuesta JSON limpia
function jsonResponse($data) {
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Función para conectar a base de datos
function getDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=estacionamiento_db;charset=utf8mb4", "root", "123456");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        jsonResponse([
            'success' => false,
            'message' => 'Error de conexión a la base de datos'
        ]);
    }
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}

// Verificar acción
$action = $_POST['action'] ?? '';

if ($action === 'login') {
    // Procesar login
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validar datos de entrada
    if (empty($usuario) || empty($password)) {
        jsonResponse([
            'success' => false,
            'message' => 'Usuario y contraseña son requeridos'
        ]);
    }
    
    try {
        // Conectar a base de datos
        $pdo = getDatabaseConnection();
        
        // Buscar usuario
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND activo = 1");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonResponse([
                'success' => false,
                'message' => 'Usuario no encontrado o inactivo'
            ]);
        }
        
        // Verificar si está bloqueado
        $now = new DateTime();
        if ($user['bloqueado_hasta'] && new DateTime($user['bloqueado_hasta']) > $now) {
            $bloqueadoHasta = new DateTime($user['bloqueado_hasta']);
            $minutosRestantes = max(0, ceil(($bloqueadoHasta->getTimestamp() - $now->getTimestamp()) / 60));
            
            jsonResponse([
                'success' => false,
                'message' => "Usuario bloqueado temporalmente. Tiempo restante: {$minutosRestantes} minutos."
            ]);
        }
        
        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            // Login exitoso
            
            // Resetear intentos fallidos
            $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL, ultimo_acceso = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Crear sesión
           session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['usuario'] = $user['usuario'];
$_SESSION['username'] = $user['usuario']; // ← ESTA LÍNEA AGREGA LO QUE FALTABA
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['rol'] = $user['rol'];
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            
            // Determinar dashboard según rol
            $dashboardUrl = ($user['rol'] === 'Administrador') 
                ? 'views/admin_dashboard.php' 
                : 'views/operador_dashboard.php';
            
            jsonResponse([
                'success' => true,
                'message' => 'Login exitoso',
                'redirect' => $dashboardUrl,
                'user' => [
                    'id' => $user['id'],
                    'usuario' => $user['usuario'],
                    'nombre' => $user['nombre'],
                    'rol' => $user['rol']
                ]
            ]);
            
        } else {
            // Contraseña incorrecta - incrementar intentos fallidos
            $nuevosIntentos = $user['intentos_fallidos'] + 1;
            $bloqueadoHasta = null;
            
            if ($nuevosIntentos >= 5) {
                $bloqueadoHasta = date('Y-m-d H:i:s', time() + 900); // 15 minutos
            }
            
            $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?");
            $stmt->execute([$nuevosIntentos, $bloqueadoHasta, $user['id']]);
            
            $intentosRestantes = max(0, 5 - $nuevosIntentos);
            
            if ($nuevosIntentos >= 5) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Usuario bloqueado por exceso de intentos fallidos. Intente en 15 minutos.',
                    'blocked' => true
                ]);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => "Contraseña incorrecta. Intentos restantes: {$intentosRestantes}",
                    'attempts_remaining' => $intentosRestantes
                ]);
            }
        }
        
    } catch (Exception $e) {
        // Log del error (sin mostrarlo al usuario)
        error_log("Error en autenticación: " . $e->getMessage());
        
        jsonResponse([
            'success' => false,
            'message' => 'Error interno del servidor'
        ]);
    }
    
} elseif ($action === 'logout') {
    // Procesar logout
    
    // Limpiar sesión
    session_unset();
    session_destroy();
    
    // Regenerar ID de sesión
    session_start();
    session_regenerate_id(true);
    
    jsonResponse([
        'success' => true,
        'message' => 'Sesión cerrada correctamente'
    ]);
    
} else {
    // Acción no válida
    jsonResponse([
        'success' => false,
        'message' => 'Acción no válida'
    ]);
}
?>