<?php
// ================================================
// DASHBOARD BÁSICO - POST LOGIN
// Archivo: views/dashboard.php
// ================================================

session_start();
require_once '../config/database.php';
require_once '../controllers/AuthController.php';

// Verificar autenticación
AuthController::requireAuth();

// Obtener datos del usuario
$userName = $_SESSION['nombre'] ?? 'Usuario';
$userRole = $_SESSION['rol'] ?? 'Operador';
$loginTime = $_SESSION['login_time'] ?? time();
$userId = $_SESSION['user_id'] ?? 0;

// Redirigir según rol a dashboard específico
if ($userRole === 'Administrador') {
    header('Location: admin_dashboard.php');
    exit();
} elseif ($userRole === 'Operador') {
    header('Location: operador_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Estacionamiento</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-parking me-2"></i>
                Sistema de Estacionamiento
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($userName); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Tarjeta de Bienvenida -->
        <div class="row">
            <div class="col-12">
                <div class="card welcome-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-2">
                                    <i class="fas fa-hand-wave me-2"></i>
                                    ¡Bienvenido, <?php echo htmlspecialchars($userName); ?>!
                                </h3>
                                <p class="mb-2 opacity-75">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Rol: <strong><?php echo htmlspecialchars($userRole); ?></strong>
                                </p>
                                <p class="mb-0 opacity-75">
                                    <i class="fas fa-clock me-2"></i>
                                    Última conexión: <?php echo date('d/m/Y H:i:s', $loginTime); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <i class="fas fa-user-check" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje de estado -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>¡Login exitoso!</strong> El sistema de estacionamiento está en desarrollo.
                    Pronto tendrás acceso a todas las funcionalidades.
                </div>
            </div>
        </div>

        <!-- Información del sistema -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Información del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Datos de la Sesión:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario']); ?></li>
                                    <li><strong>ID:</strong> <?php echo htmlspecialchars($userId); ?></li>
                                    <li><strong>Rol:</strong> <?php echo htmlspecialchars($userRole); ?></li>
                                    <li><strong>IP:</strong> <?php echo htmlspecialchars($_SESSION['ip_address'] ?? $_SERVER['REMOTE_ADDR']); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Próximas Funcionalidades:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-cog text-warning me-2"></i>Gestión de tickets</li>
                                    <li><i class="fas fa-cog text-warning me-2"></i>Control de espacios</li>
                                    <li><i class="fas fa-cog text-warning me-2"></i>Generación de reportes</li>
                                    <li><i class="fas fa-cog text-warning me-2"></i>Códigos QR</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary me-2" onclick="showComingSoon()">
                                <i class="fas fa-rocket me-2"></i>Explorar Funcionalidades
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    
    <script>
        /**
         * Función para cerrar sesión
         */
        function logout() {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: '¿Está seguro que desea salir del sistema?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Cerrando sesión...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Enviar petición de logout
                    $.post('../controllers/AuthController.php', {
                        action: 'logout'
                    }, function() {
                        window.location.href = '../index.php';
                    }).fail(function() {
                        // Si falla, redirigir de todos modos
                        window.location.href = '../index.php';
                    });
                }
            });
        }

        /**
         * Mostrar mensaje de funcionalidades próximamente
         */
        function showComingSoon() {
            Swal.fire({
                title: '¡Próximamente!',
                text: 'Esta funcionalidad estará disponible en la siguiente fase del desarrollo.',
                icon: 'info',
                confirmButtonColor: '#3498db',
                confirmButtonText: 'Entendido'
            });
        }

        /**
         * Verificar sesión periódicamente
         */
        setInterval(function() {
            $.get('../api/check_session.php')
                .fail(function() {
                    Swal.fire({
                        title: 'Sesión Expirada',
                        text: 'Su sesión ha expirado. Será redirigido al login.',
                        icon: 'warning',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonText: 'Ir al Login'
                    }).then(() => {
                        window.location.href = '../index.php';
                    });
                });
        }, 300000); // Verificar cada 5 minutos

        // Mostrar información de desarrollo
        console.log('Dashboard cargado correctamente');
        console.log('Usuario: <?php echo $userName; ?>');
        console.log('Rol: <?php echo $userRole; ?>');
    </script>
</body>
</html>