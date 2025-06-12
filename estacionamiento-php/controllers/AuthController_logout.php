<?php
session_start();

header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Log para debugging
error_log("AuthController: " . $_SERVER["REQUEST_METHOD"] . " - " . file_get_contents("php://input"));

// Verificar método
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

// Obtener datos de entrada
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

$action = $input["action"] ?? "";

try {
    switch ($action) {
        case "logout":
            // Verificar que hay una sesión activa
            if (!isset($_SESSION["user_id"])) {
                echo json_encode(["success" => false, "message" => "No hay sesión activa"]);
                break;
            }
            
            $username = $_SESSION["username"] ?? "desconocido";
            error_log("Logout AJAX para usuario: " . $username);
            
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
            
            echo json_encode([
                "success" => true,
                "message" => "Sesión cerrada exitosamente",
                "redirect" => "login_final.php"
            ]);
            break;
            
        case "check_session":
            if (isset($_SESSION["user_id"])) {
                echo json_encode([
                    "success" => true,
                    "user_id" => $_SESSION["user_id"],
                    "username" => $_SESSION["username"],
                    "role" => $_SESSION["role"] ?? "operator"
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Sesión no válida"]);
            }
            break;
            
        default:
            echo json_encode(["success" => false, "message" => "Acción no válida"]);
    }
    
} catch (Exception $e) {
    error_log("Error en AuthController: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error interno del servidor"]);
}
?>