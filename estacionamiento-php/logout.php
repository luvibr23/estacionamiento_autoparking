<?php
session_start();

// Log de la acción de logout
error_log("Logout iniciado para usuario: " . ($_SESSION["username"] ?? "desconocido"));

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Regenerar ID de sesión por seguridad
session_start();
session_regenerate_id(true);
session_destroy();

// Limpiar cache del navegador
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirigir al login con mensaje
header("Location: login_final.php?message=logout_success");
exit;
?>