<?php
require_once "includes/flash_messages.php";
session_start();

// Verificar si el usuario ya está logueado
if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
    // Redirigir según el rol
    $role = $_SESSION["role"] ?? "operator";
    
    if ($role === "admin") {
        header("Location: views/admin_dashboard.php");
    } else {
        header("Location: views/operador_dashboard.php");
    }
    exit;
}

// Si no está logueado, redirigir al login
header("Location: login_final.php");
exit;
?>