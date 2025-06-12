<?php
// ================================================
// API PARA GENERAR TOKEN CSRF - VERSIÓN LIMPIA
// Archivo: api/csrf_token_fixed.php
// ================================================

// Suprimir warnings y errores menores
error_reporting(E_ERROR | E_PARSE);
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
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
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
    
    // Limpiar cualquier output previo antes de enviar JSON
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Respuesta exitosa
    $response = [
        'success' => true,
        'token' => $csrfToken,
        'timestamp' => time(),
        'session_id' => session_id()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Limpiar output antes de error
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Error al generar token
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error generando token de seguridad',
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    // Capturar cualquier tipo de error
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error crítico del servidor',
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
}

// Finalizar limpiamente
exit();
?>