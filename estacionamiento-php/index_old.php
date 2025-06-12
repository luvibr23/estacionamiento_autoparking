<?php
require_once "includes/flash_messages.php";
// ================================================
// PÁGINA PRINCIPAL - LOGIN
// Archivo: index.php
// ================================================

session_start();
require_once 'config/database.php';

// Redireccionar si ya está autenticado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $dashboardUrl = ($_SESSION['rol'] === 'Administrador') ? 'views/admin_dashboard.php' : 'views/operador_dashboard.php';
    header("Location: $dashboardUrl");
    exit();
}

// Obtener mensaje flash si existe
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Estacionamiento</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>
    <!-- Formas flotantes de fondo -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header del login -->
            <div class="login-header">
                <i class="fas fa-parking"></i>
                <h4>Sistema de Estacionamiento</h4>
                <p class="mb-0 mt-2" style="opacity: 0.8;">Iniciar Sesión</p>
            </div>

            <!-- Cuerpo del login -->
            <div class="login-body">
                <!-- Mostrar mensajes flash desde PHP -->
                <?php
require_once "includes/flash_messages.php"; if ($flashMessage): ?>
                <div class="alert alert-<?php
require_once "includes/flash_messages.php"; echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php
require_once "includes/flash_messages.php"; echo $flashMessage['type'] === 'error' ? 'times-circle' : ($flashMessage['type'] === 'success' ? 'check-circle' : 'info-circle'); ?> me-2"></i>
                    <?php
require_once "includes/flash_messages.php"; echo htmlspecialchars($flashMessage['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php
require_once "includes/flash_messages.php"; endif; ?>

                <!-- Contenedor para mensajes dinámicos -->
                <div id="flashMessages"></div>

                <!-- Formulario de login -->
                <form id="loginForm" novalidate role="form" aria-label="Formulario de inicio de sesión">
                    <!-- Campo Usuario -->
                    <div class="form-floating">
                        <input type="text" class="form-control" id="usuario" name="usuario" 
                               placeholder="Usuario" required autocomplete="username"
                               aria-describedby="usuario-help">
                        <label for="usuario">
                            <i class="fas fa-user me-2" aria-hidden="true"></i>Usuario
                        </label>
                        <div class="invalid-feedback" id="usuario-error" role="alert">
                            Por favor ingrese su usuario
                        </div>
                        <div class="sr-only" id="usuario-help">
                            Ingrese su nombre de usuario para acceder al sistema
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Contraseña" required autocomplete="current-password"
                               aria-describedby="password-help">
                        <label for="password">
                            <i class="fas fa-lock me-2" aria-hidden="true"></i>Contraseña
                        </label>
                        <button type="button" class="password-toggle" id="passwordToggle"
                                aria-label="Mostrar u ocultar contraseña" 
                                title="Mostrar contraseña">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                        <div class="invalid-feedback" id="password-error" role="alert">
                            Por favor ingrese su contraseña
                        </div>
                        <div class="sr-only" id="password-help">
                            Ingrese su contraseña. Use el botón del ojo para mostrar u ocultar la contraseña
                        </div>
                    </div>

                    <!-- Recordar sesión -->
                    <div class="remember-me">
                        <input type="checkbox" id="rememberMe" name="rememberMe" 
                               aria-describedby="remember-help">
                        <label for="rememberMe" class="form-check-label">
                            Recordar sesión
                        </label>
                        <div class="sr-only" id="remember-help">
                            Marque esta casilla para mantener su sesión activa en este dispositivo
                        </div>
                    </div>

                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token" id="csrfToken" value="">
                    <input type="hidden" name="action" value="login">

                    <!-- Botón de login -->
                    <button type="submit" class="btn btn-primary btn-login w-100" id="loginBtn"
                            aria-describedby="login-help">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Iniciar Sesión
                        </span>
                        <span class="loading-spinner" aria-hidden="true">
                            <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Verificando...
                        </span>
                        <span class="sr-only" id="login-help">
                            Presione para iniciar sesión en el sistema
                        </span>
                    </button>
                </form>

                <!-- Enlaces del footer -->
                <div class="footer-links">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        ¿Olvidó su contraseña?
                    </a>
                </div>

                <!-- Información de usuarios demo -->
                <div class="demo-info mt-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Usuarios de Prueba:</strong><br>
                        <small>
                            <strong>Admin:</strong> admin / admin123<br>
                            <strong>Operador:</strong> operador1 / operador123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para recuperar contraseña -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>Recuperar Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Contacte al administrador del sistema para recuperar su contraseña.
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Administrador:</strong> admin<br>
                        <strong>Email:</strong> admin@estacionamiento.com
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <!-- JavaScript personalizado -->
    <script src="assets/js/login.js"></script>
</body>
</html>