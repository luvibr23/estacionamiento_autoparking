<?php
// ================================================
// LOGIN DEFINITIVO - VERSIÓN QUE FUNCIONA
// Archivo: login_final.php
// ================================================

session_start();
// Manejar mensaje de logout
$logout_message = "";
if (isset($_GET["message"]) && $_GET["message"] === "logout_success") {
    $logout_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        Sesión cerrada exitosamente. Puede iniciar sesión nuevamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

// Redireccionar si ya está autenticado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $dashboardUrl = ($_SESSION['rol'] === 'Administrador') ? 'views/admin_dashboard.php' : 'views/operador_dashboard.php';
    header("Location: $dashboardUrl");
    exit();
}

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
            position: relative;
            z-index: 2;
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
        
        .debug-info {
            position: fixed;
            top: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 11px;
            z-index: 1000;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; }
        .shape:nth-child(2) { width: 60px; height: 60px; top: 60%; right: 15%; animation-delay: 2s; }
        .shape:nth-child(3) { width: 100px; height: 100px; bottom: 20%; left: 20%; animation-delay: 4s; }
        
        @keyframes float { 
            0%, 100% { transform: translateY(0px); } 
            50% { transform: translateY(-20px); } 
        }
    </style>
</head>
<body>
    <!-- Debug info -->
    <div class="debug-info">
        <strong>Sistema de Login v2.0</strong><br>
        Base URL: <?php echo $baseUrl; ?><br>
        Archivos Fixed: <span id="filesStatus">🔄 Verificando...</span>
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
                <!-- Contenedor para mensajes dinámicos -->
                <div id="flashMessages"></div>

                <!-- Formulario de login -->
                <form id="loginForm" novalidate>
                    <!-- Campo Usuario -->
                    <div class="form-floating">
                        <input type="text" class="form-control" id="usuario" name="usuario" 
                               placeholder="Usuario" required autocomplete="username" value="admin">
                        <label for="usuario">
                            <i class="fas fa-user me-2"></i>Usuario
                        </label>
                        <div class="invalid-feedback">
                            Por favor ingrese su usuario
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Contraseña" required autocomplete="current-password" value="admin123">
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Contraseña
                        </label>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="invalid-feedback">
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
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </span>
                        <span class="loading-spinner">
                            <i class="fas fa-spinner fa-spin me-2"></i>Verificando...
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
                        <a href="simple_login.php" class="text-muted">🔧 Login Simple</a> |
                        <a href="test_login_direct.php" class="text-muted">🧪 Test Login</a> |
                        <a href="unblock_user.php" class="text-muted">🔓 Desbloquear</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    
    <script>
        let isLoading = false;
        let csrfToken = '';

        $(document).ready(function() {
            console.log('🚀 Login Final v2.0 iniciado');
            
            // Verificar archivos fixed
            checkFixedFiles();
            
            // Cargar token CSRF
            loadCSRFToken();
            
            // Configurar eventos
            setupEvents();
        });

        function checkFixedFiles() {
            Promise.all([
                fetch('controllers/AuthController_fixed.php').then(r => r.ok),
                fetch('api/csrf_token_fixed.php').then(r => r.ok)
            ]).then(results => {
                const authExists = results[0];
                const csrfExists = results[1];
                
                let status = '';
                if (authExists && csrfExists) {
                    status = '✅ OK';
                    $('#filesStatus').html(status).css('color', '#28a745');
                } else {
                    status = '❌ Faltan archivos';
                    $('#filesStatus').html(status).css('color', '#dc3545');
                    showAlert('warning', 'Algunos archivos corregidos no están disponibles. Creando automáticamente...');
                    
                    // Redirigir a crear archivos
                    setTimeout(() => {
                        window.location.href = 'create_fixed_files.php';
                    }, 3000);
                }
            });
        }

        function setupEvents() {
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

            // Enter en campos
            $('#usuario, #password').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#loginForm').submit();
                }
            });
        }

        function loadCSRFToken() {
            console.log('🔐 Cargando token CSRF...');
            
            $.ajax({
                url: 'api/csrf_token_fixed.php',
                type: 'GET',
                dataType: 'json',
                timeout: 10000,
                success: function(data) {
                    if (data.success && data.token) {
                        csrfToken = data.token;
                        $('#csrfToken').val(csrfToken);
                        console.log('✅ Token CSRF cargado');
                    } else {
                        console.warn('⚠️ Respuesta CSRF inválida');
                        generateFallbackToken();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error cargando CSRF:', status, error);
                    
                    // Intentar con API original
                    $.get('api/csrf_token.php')
                        .done(function(data) {
                            if (data.success && data.token) {
                                csrfToken = data.token;
                                $('#csrfToken').val(csrfToken);
                                console.log('✅ Token CSRF cargado (API original)');
                            }
                        })
                        .fail(function() {
                            generateFallbackToken();
                        });
                }
            });
        }

        function generateFallbackToken() {
            csrfToken = 'fallback_' + Date.now();
            $('#csrfToken').val(csrfToken);
            console.log('🔄 Usando token de respaldo');
        }

        function handleLogin() {
            if (isLoading) return;
            
            const usuario = $('#usuario').val().trim();
            const password = $('#password').val();
            
            if (!usuario || !password) {
                Swal.fire('Error', 'Por favor complete todos los campos', 'error');
                return;
            }
            
            console.log('🔑 Intentando login con:', usuario);
            setLoadingState(true);
            
            const formData = {
                usuario: usuario,
                password: password,
                csrf_token: csrfToken,
                action: 'login'
            };

            // Intentar con controlador corregido primero
            $.ajax({
                url: 'controllers/AuthController_fixed.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 15000,
                success: function(response) {
                    console.log('📧 Respuesta recibida:', response);
                    handleLoginResponse(response);
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error con controlador fixed:', xhr.responseText);
                    
                    // Intentar con controlador original
                    $.ajax({
                        url: 'controllers/AuthController.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            console.log('📧 Respuesta (original):', response);
                            handleLoginResponse(response);
                        },
                        error: function(xhr2, status2, error2) {
                            console.error('❌ Error con ambos controladores');
                            handleLoginError(xhr2, status2, error2);
                        },
                        complete: function() {
                            setLoadingState(false);
                        }
                    });
                },
                complete: function() {
                    if (!isLoading) return; // Ya se completó con el fallback
                    setLoadingState(false);
                }
            });
        }

        function handleLoginResponse(response) {
            if (response.success) {
                console.log('✅ Login exitoso');
                Swal.fire({
                    icon: 'success',
                    title: '¡Bienvenido!',
                    text: response.message || 'Login exitoso',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    console.log('🏠 Redirigiendo a:', response.redirect);
                    window.location.href = response.redirect;
                });
            } else {
                console.log('❌ Login fallido:', response.message);
                Swal.fire('Error', response.message, 'error');
                $('#password').val('').focus();
            }
        }

        function handleLoginError(xhr, status, error) {
            console.error('💥 Error completo:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText.substring(0, 500)
            });
            
            let message = 'Error de conexión. ';
            
            if (status === 'timeout') {
                message = 'Tiempo de espera agotado. ';
            } else if (xhr.status === 500) {
                message = 'Error interno del servidor. ';
            }
            
            message += 'Intente con el login simple.';
            
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: message,
                footer: '<a href="simple_login.php">Usar Login Simple</a>'
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

        function showAlert(type, message) {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger', 
                'warning': 'alert-warning',
                'info': 'alert-info'
            };

            const iconClass = {
                'success': 'fa-check-circle',
                'error': 'fa-times-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };

            const alertHtml = `
                <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass[type]} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            $('#flashMessages').html(alertHtml);
        }

        // Debug adicional
        console.log('🎯 Base URL:', '<?php echo $baseUrl; ?>');
    </script>
</body>
</html>