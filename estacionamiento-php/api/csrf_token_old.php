<?php
// ================================================
// API PARA GENERAR TOKEN CSRF
// Archivo: api/csrf_token.php
// ================================================

// NO incluir config/database.php aquí para evitar warnings de sesión
// Iniciar sesión SIEMPRE al principio
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar headers para JSON ANTES de cualquier output
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Función para generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

try {
    // Generar token CSRF
    $csrfToken = generateCSRFToken();
    
    // Respuesta exitosa
    $response = [
        'success' => true,
        'token' => $csrfToken,
        'timestamp' => time(),
        'session_id' => session_id()
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Error al generar token
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error generando token de seguridad: ' . $e->getMessage(),
        'timestamp' => time()
    ]);
} catch (Throwable $e) {
    // Capturar cualquier tipo de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error crítico del servidor',
        'timestamp' => time(),
        'debug' => $e->getMessage()
    ]);
}

// Finalizar output buffer si existe
if (ob_get_level()) {
    ob_end_flush();
}
?>