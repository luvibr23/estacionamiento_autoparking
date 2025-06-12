<?php
// Funciones para manejar mensajes flash

function setFlashMessage($message, $type = "info") {
    session_start();
    $_SESSION["flash_message"] = [
        "message" => $message,
        "type" => $type
    ];
}

function getFlashMessage() {
    session_start();
    if (isset($_SESSION["flash_message"])) {
        $flash = $_SESSION["flash_message"];
        unset($_SESSION["flash_message"]);
        return $flash;
    }
    return null;
}

function hasFlashMessage() {
    session_start();
    return isset($_SESSION["flash_message"]);
}

function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = "";
        switch ($flash["type"]) {
            case "success":
                $alertClass = "alert-success";
                break;
            case "error":
            case "danger":
                $alertClass = "alert-danger";
                break;
            case "warning":
                $alertClass = "alert-warning";
                break;
            case "info":
            default:
                $alertClass = "alert-info";
                break;
        }
        
        return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($flash["message"]) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }
    return "";
}
?>