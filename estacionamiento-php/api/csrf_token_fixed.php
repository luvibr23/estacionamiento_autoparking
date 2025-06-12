<?php
session_start();

header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

try {
    // Generar token CSRF si no existe
    if (!isset($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    
    echo json_encode([
        "success" => true,
        "token" => $_SESSION["csrf_token"]
    ]);
    
} catch (Exception $e) {
    error_log("Error generando CSRF token: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Error generando token"
    ]);
}
?>