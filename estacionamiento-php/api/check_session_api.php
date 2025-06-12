<?php
session_start();

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

try {
    // Verificar si existe sesión activa
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Sesión no válida'
        ]);
        exit;
    }
    
    // Verificar tiempo de última actividad (opcional)
    $tiempoMaximoInactividad = 3600; // 1 hora en segundos
    if (isset($_SESSION['ultima_actividad'])) {
        $tiempoInactivo = time() - $_SESSION['ultima_actividad'];
        if ($tiempoInactivo > $tiempoMaximoInactividad) {
            // Destruir sesión por inactividad
            session_destroy();
            echo json_encode([
                'success' => false,
                'message' => 'Sesión expirada por inactividad'
            ]);
            exit;
        }
    }
    
    // Actualizar última actividad
    $_SESSION['ultima_actividad'] = time();
    
    // Regenerar ID de sesión periódicamente por seguridad
    if (!isset($_SESSION['regenerado']) || (time() - $_SESSION['regenerado']) > 300) {
        session_regenerate_id(true);
        $_SESSION['regenerado'] = time();
    }
    
    echo json_encode([
        'success' => true,
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'] ?? 'operator',
        'ultima_actividad' => $_SESSION['ultima_actividad']
    ]);
    
} catch (Exception $e) {
    error_log("Error en check_session: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}