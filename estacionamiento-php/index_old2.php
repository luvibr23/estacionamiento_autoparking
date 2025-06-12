<?php
require_once "includes/flash_messages.php";
// ================================================
// PÁGINA PRINCIPAL - LOGIN (VERSIÓN CORREGIDA)
// Archivo: index_fixed.php
// ================================================

session_start();

// Verificar si el archivo config existe antes de incluirlo
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} else {
    // Definir funciones básicas si no existe config
    function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}

// Redireccionar si ya está autenticado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $dashboardUrl = ($_SESSION['rol'] === 'Administrador') ? 'views/admin_dashboard.php' : 'views/operador_dashboard.php';
    header("Location: $dashboardUrl");
    exit();
}

// Obtener mensaje flash si existe
$flashMessage = getFlashMessage();

// Obtener la URL base del proyecto
$projectPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . $projectPath;
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
    
    <!-- CSS Personalizado con ruta absoluta -->
    <?php
require_once "includes/flash_messages.php"; if (file_exists(__DIR__ . '/assets/css/login.css')): ?>
    <link href="<?php
require_once "includes/flash_messages.php"; echo $baseUrl; ?>/assets/css/login.css" rel="stylesheet">
    <?php
require_once "includes/flash_messages.php"; else: ?>
    <!-- CSS inline como fallback -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
            color: white;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        
        .loading-spinner { display: none; }
        .floating-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
        .shape { position: absolute; background: rgba(255, 255, 255, 0.1); border-radius: 50%; animation: float 6s ease-in-out infinite; }
        .shape:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; }
        .shape:nth-child(2) { width: 60px; height: 60px; top: 60%; right: 15%; animation-delay: 2s; }
        .shape:nth-child(3) { width: 100px; height: 100px; bottom: 20%; left: 20%; animation-delay: 4s; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
    </style>
    <?php
require_once "includes/flash_messages.php"; endif; ?>
</head>
<body>
    <!-- Debug info -->
    <div style="position: fixed; top: 10px; left: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 1000;">
        <strong>Debug Info:</strong><br>
        Proyecto: <?php
require_once "includes/flash_messages.php"; echo $projectPath; ?><br>
        Base URL: <?php
require_once "includes/flash_messages.php"; echo $baseUrl; ?><br>
        CSS: <?php
require_once "includes/flash_messages.php"; echo file_exists(__DIR__ . '/assets/css/login.css') ? '✅' : '❌'; ?><br>
        JS: <?php
require_once "includes/flash_messages.php"; echo file_exists(__DIR__ . '/assets/js/login.js') ? '✅' : '❌'; ?>
    </div>

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
                    </div>

                    <!-- Recordar sesión -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Recordar sesión
                        </label>
                    </div>

                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token" id="csrfToken" value="">
                    <input type="hidden" name="action" value="login">

                    <!-- Botón de login -->
                    <button type="submit" class="btn btn-primary btn-login w-100" id="loginBtn">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Iniciar Sesión
                        </span>
                        <span class="loading-spinner" aria-hidden="true">
                            <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Verificando...
                        </span>
                    </button>
                </form>

                <!-- Información de usuarios demo -->
                <div class="mt-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Usuarios de Prueba:</strong><br>
                        <small>
                            <strong>Admin:</strong> admin / admin123<br>
                            <strong>Operador:</strong> operador1 / operador123
                        </small>
                    </div>
                </div>

                <!-- Enlaces de debug -->
                <div class="text-center mt-3">
                    <small>
                        <a href="simple_login.php" class="text-muted">🔧 Login Debug</a> |
                        <a href="unblock_user.php" class="text-muted">🔓 Gestionar Usuarios</a>
                    </small>
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
    <?php
require_once "includes/flash_messages.php"; if (file_exists(__DIR__ . '/assets/js/login.js')): ?>
    <script src="<?php
require_once "includes/flash_messages.php"; echo $baseUrl; ?>/assets/js/login.js"></script>
    <?php
require_once "includes/flash_messages.php"; else: ?>
    <!-- JavaScript inline como fallback -->
    <script>
        let isLoading = false;
        let csrfToken = '';

        $(document).ready(function() {
            console.log('Login inicializado');
            
            // Cargar token CSRF
            loadCSRFToken();
            
            // Toggle de contraseña
            $('#passwordToggle').click(function() {
                const passwordField = $('#password');
                const passwordIcon = $(this).find('i');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Envío del formulario
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                handleLogin();
            });
        });

        function loadCSRFToken() {
            $.get('api/csrf_token.php')
                .done(function(data) {
                    if (data.success && data.token) {
                        csrfToken = data.token;
                        $('#csrfToken').val(csrfToken);
                        console.log('Token CSRF cargado');
                    }
                })
                .fail(function() {
                    console.warn('No se pudo cargar token CSRF');
                    csrfToken = 'fallback_' + Date.now();
                    $('#csrfToken').val(csrfToken);
                });
        }

        function handleLogin() {
            if (isLoading) return;
            
            const usuario = $('#usuario').val().trim();
            const password = $('#password').val();
            
            if (!usuario || !password) {
                Swal.fire('Error', 'Por favor complete todos los campos', 'error');
                return;
            }
            
            setLoadingState(true);
            
            $.ajax({
                url: 'controllers/AuthController.php',
                type: 'POST',
                data: {
                    usuario: usuario,
                    password: password,
                    csrf_token: csrfToken,
                    action: 'login'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Bienvenido!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    Swal.fire('Error', 'Error de conexión: ' + error, 'error');
                },
                complete: function() {
                    setLoadingState(false);
                }
            });
        }

        function setLoadingState(loading) {
            isLoading = loading;
            const loginBtn = $('#loginBtn');
            const btnText = loginBtn.find('.btn-text');
            const loadingSpinner = loginBtn.find('.loading-spinner');

            if (loading) {
                loginBtn.prop('disabled', true);
                btnText.hide();
                loadingSpinner.show();
            } else {
                loginBtn.prop('disabled', false);
                btnText.show();
                loadingSpinner.hide();
            }
        }

        // Debug
        console.log('Base URL:', '<?php
require_once "includes/flash_messages.php"; echo $baseUrl; ?>');
        console.log('CSS existe:', <?php
require_once "includes/flash_messages.php"; echo file_exists(__DIR__ . '/assets/css/login.css') ? 'true' : 'false'; ?>);
        console.log('JS existe:', <?php
require_once "includes/flash_messages.php"; echo file_exists(__DIR__ . '/assets/js/login.js') ? 'true' : 'false'; ?>);
    </script>
    <?php
require_once "includes/flash_messages.php"; endif; ?>
</body>
</html>