
<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../login_final.php');
    exit;
}

// Obtener información del usuario
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'operator';
$nombre = $_SESSION['nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Estacionamiento</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        /* Estilos adicionales inline como fallback */
        .content-section { display: none; }
        .content-section:first-child { display: block; }
        .parking-space { 
            min-height: 60px; 
            border: 2px solid #ddd; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            transition: all 0.3s ease; 
            cursor: pointer; 
        }
        .parking-space.available { background-color: #d4edda; border-color: #28a745; color: #155724; }
        .parking-space.occupied { background-color: #f8d7da; border-color: #dc3545; color: #721c24; }
        .parking-space:hover { transform: scale(1.05); }
        .search-results { max-height: 200px; overflow-y: auto; }
    </style>
<link rel="stylesheet" href="assets/css/dashboard.css">
<link rel="stylesheet" href="assets/css/login.css">
<link rel="stylesheet" href="assets/css/login.css.css">
</head>
<body class="<?= $role !== 'admin' ? 'user-operator' : '' ?>">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-parking me-2"></i>Sistema de Estacionamiento</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                            <a class="nav-link" href="#" onclick="irMenuPrincipal()">
                                <i class="fas fa-home me-1"></i>Menú Principal
                            </a>
                        </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <span id="user-name"><?= htmlspecialchars($nombre) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" onclick="showSection('dashboard')">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showSection('vehiculos')">
                                <i class="fas fa-car me-2"></i>Vehículos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showSection('tarifas')">
                                <i class="fas fa-dollar-sign me-2"></i>Tarifas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showSection('tiempo-real')">
                                <i class="fas fa-clock me-2"></i>Tiempo Real
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showSection('reportes')">
                                <i class="fas fa-chart-bar me-2"></i>Reportes
                            </a>
                        </li>
                        <li class="nav-item admin-only">
                            <a class="nav-link" href="#" onclick="showSection('usuarios')">
                                <i class="fas fa-users me-2"></i>Usuarios
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Dashboard</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateDashboardData()">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Espacios Ocupados</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="espacios-ocupados">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-car fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Espacios Disponibles</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="espacios-disponibles">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-parking fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ingresos Hoy</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="ingresos-hoy">S/ 0.00</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Vehículos</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-vehiculos">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="tabla-actividad">
                                            <thead>
                                                <tr>
                                                    <th>Placa</th>
                                                    <th>Acción</th>
                                                    <th>Hora</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="4" class="text-center">Cargando...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Estado del Sistema</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="small mb-1">Ocupación del Estacionamiento</div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%" id="progress-ocupacion"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small mb-1">Ingresos del Día</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progress-ingresos"></div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="status-indicator online"></div>
                                        <small>Sistema en línea</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehículos Section -->
                <div id="vehiculos-section" class="content-section" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Gestión de Vehículos</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button class="btn btn-primary" onclick="mostrarModalVehiculo()">
                                    <i class="fas fa-plus me-2"></i>Registrar Vehículo
                                </button>
                                <button class="btn btn-success" onclick="mostrarModalCliente()">
                                    <i class="fas fa-user-plus me-2"></i>Nuevo Cliente
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Vehículos Registrados</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabla-vehiculos">
                                    <thead>
                                        <tr>
                                            <th>Placa</th>
                                            <th>Tipo</th>
                                            <th>Hora Entrada</th>
                                            <th>Tiempo Transcurrido</th>
                                            <th>Tarifa</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center">Cargando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarifas Section -->
                <div id="tarifas-section" class="content-section" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Gestión de Tarifas</h1>
                        <button class="btn btn-primary" onclick="mostrarModalTarifa()">
                            <i class="fas fa-plus me-2"></i>Nueva Tarifa
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Tarifas por Tipo de Vehículo</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th>Tarifa/Hora</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Auto</td>
                                                    <td>S/ 3.00</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Moto</td>
                                                    <td>S/ 2.00</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Camioneta</td>
                                                    <td>S/ 4.00</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Tarifas Especiales</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label>Tarifa Nocturna (8PM - 6AM)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">S/</span>
                                            <input type="number" class="form-control" value="2.50" step="0.50">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Tarifa Fin de Semana</label>
                                        <div class="input-group">
                                            <span class="input-group-text">S/</span>
                                            <input type="number" class="form-control" value="3.50" step="0.50">
                                        </div>
                                    </div>
                                    <button class="btn btn-success">Actualizar Tarifas</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tiempo Real Section -->
                <div id="tiempo-real-section" class="content-section" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Monitoreo en Tiempo Real</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button class="btn btn-success me-2" onclick="actualizarTiempoReal()">
                                <i class="fas fa-sync-alt me-2"></i>Actualizar
                            </button>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">Espacios Totales</h5>
                                    <h2 class="display-4" id="total-espacios">50</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title text-success">Disponibles</h5>
                                    <h2 class="display-4" id="espacios-libres">50</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title text-danger">Ocupados</h5>
                                    <h2 class="display-4" id="espacios-ocupados-rt">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title text-warning">% Ocupación</h5>
                                    <h2 class="display-4" id="porcentaje-ocupacion">0%</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Mapa de Espacios de Estacionamiento</h6>
                        </div>
                        <div class="card-body">
                            <div id="mapa-espacios" class="parking-map">
                                <!-- Mapa visual del estacionamiento se genera por JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reportes Section -->
                <div id="reportes-section" class="content-section" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Reportes</h1>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Filtros de Reporte</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Fecha Inicio</label>
                                            <input type="date" class="form-control" id="fecha-inicio">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Fecha Fin</label>
                                            <input type="date" class="form-control" id="fecha-fin">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Tipo de Reporte</label>
                                            <select class="form-control" id="tipo-reporte">
                                                <option value="ingresos">Ingresos</option>
                                                <option value="ocupacion">Ocupación</option>
                                                <option value="vehiculos">Vehículos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button class="btn btn-primary w-100" onclick="generarReporte()">
                                                <i class="fas fa-chart-line me-2"></i>Generar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Gráfico de Reportes</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="grafico-ingresos"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Resumen</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <h4>Total Ingresos</h4>
                                            <h2 class="text-success">S/ 450.00</h2>
                                        </div>
                                        <div class="mb-3">
                                            <h4>Vehículos Atendidos</h4>
                                            <h2 class="text-info">89</h2>
                                        </div>
                                        <div class="mb-3">
                                            <h4>Tiempo Promedio</h4>
                                            <h2 class="text-warning">2.5h</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usuarios Section (Solo Admin) -->
                <div id="usuarios-section" class="content-section admin-only" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Gestión de Usuarios</h1>
                        <button class="btn btn-primary" onclick="mostrarModalUsuario()">
                            <i class="fas fa-plus me-2"></i>Nuevo Usuario
                        </button>
                    </div>

                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Nombre</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>admin</td>
                                            <td>Administrador</td>
                                            <td><span class="badge bg-danger">Admin</span></td>
                                            <td><span class="badge bg-success">Activo</span></td>
                                            <td>2024-01-01</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-lock"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>operador1</td>
                                            <td>Operador 1</td>
                                            <td><span class="badge bg-info">Operador</span></td>
                                            <td><span class="badge bg-success">Activo</span></td>
                                            <td>2024-01-15</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-lock"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            
<!-- Sección: Vehículos -->
<section id="vehiculos-section" class="content-section" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gestión de Vehículos</h2>
        <button class="btn btn-primary" onclick="mostrarModalVehiculo()">Registrar Vehículo</button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped" id="tablaVehiculos">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Color</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Datos se insertan dinámicamente -->
            </tbody>
        </table>
    </div>
</section>

<!-- Modal de Registro de Vehículo -->
<div class="modal fade" id="modalVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formVehiculo" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Placa</label>
                    <input type="text" class="form-control" id="placa" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="modelo" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Color</label>
                    <input type="text" class="form-control" id="color" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Vehículo</label>
                    <select class="form-select" id="tipo-vehiculo" required>
                        <option value="">Seleccione...</option>
                        <option value="auto">Auto</option>
                        <option value="moto">Moto</option>
                        <option value="camioneta">Camioneta</option>
                        <option value="bus">Bus</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Código de Cliente</label>
                    <input type="text" class="form-control" id="codigo-cliente">
                </div>
                <div id="resultados-clientes"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Registrar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

</main>
        </div>
    </div>

    <!-- Modal Registrar Vehículo -->
    <div class="modal fade" id="modalVehiculo" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nuevo Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formVehiculo">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Placa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="placa" placeholder="ABC-123" maxlength="8" required>
                                    <div class="form-text">Formato: ABC123 o ABC-123</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Vehículo <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tipo-vehiculo" required>
                                        <option value="">Seleccionar</option>
                                        <option value="auto">Auto</option>
                                        <option value="moto">Moto</option>
                                        <option value="camioneta">Camioneta</option>
                                        <option value="bus">Bus</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Modelo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="modelo" placeholder="Toyota Corolla" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Color <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="color" placeholder="Blanco" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cliente (Opcional)</label>
                            <input type="text" class="form-control" id="buscar-cliente" placeholder="Buscar cliente por nombre o código...">
                            <input type="hidden" id="codigo-cliente">
                            <div id="resultados-clientes" class="search-results mt-2"></div>
                            <div class="form-text">
                                <small>
                                    Busque un cliente existente o 
                                    <a href="#" onclick="mostrarModalCliente()" class="text-decoration-none">crear uno nuevo</a>
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="registrarVehiculo()">
                        <i class="fas fa-save me-2"></i>Registrar Vehículo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear Cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCliente">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre-cliente" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellido-cliente" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono-cliente" placeholder="987654321">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email-cliente" placeholder="cliente@email.com">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion-cliente" rows="2" placeholder="Dirección completa"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="crearCliente()">
                        <i class="fas fa-user-plus me-2"></i>Crear Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUsuario">
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-control" id="rol" required>
                                <option value="">Seleccionar</option>
                                <option value="admin">Administrador</option>
                                <option value="operator">Operador</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="crearUsuario()">Crear</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.js"></script>
    <!-- Dashboard JS -->
    <script src="../assets/js/dashboard_fixed.js"></script>
    <script>
// Test de verificación de rutas
console.log('=== DEBUG DE RUTAS ===');
console.log('Ubicación actual:', window.location.href);
console.log('Pathname:', window.location.pathname);
console.log('Intentando acceder a controlador...');

fetch('../controllers/VehiculoController.php?accion=listar_vehiculos')
    .then(response => {
        console.log('Status de controlador:', response.status);
        if (response.status === 404) {
            console.log('❌ Controlador NO encontrado en ../controllers/');
        } else {
            console.log('✅ Controlador encontrado');
        }
    })
    .catch(error => {
        console.log('❌ Error al acceder al controlador:', error);
    });
</script>
    <script>
        // Script de inicialización inmediata
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard cargado para:', '<?= htmlspecialchars($username) ?>');
            console.log('Rol:', '<?= htmlspecialchars($role) ?>');
        });
<script>
// Test de verificación de rutas
console.log('=== DEBUG DE RUTAS ===');
console.log('Ubicación actual:', window.location.href);
console.log('Pathname:', window.location.pathname);
console.log('Intentando acceder a controlador...');

fetch('../controllers/VehiculoController.php?accion=listar_vehiculos')
    .then(response => {
        console.log('Status de controlador:', response.status);
        if (response.status === 404) {
            console.log('❌ Controlador NO encontrado en ../controllers/');
        } else {
            console.log('✅ Controlador encontrado');
        }
    })
    .catch(error => {
        console.log('❌ Error al acceder al controlador:', error);
    });
</script>
    </script>
    <script src="../assets/js/logout-functions.js"></script>
    <script src="../assets/js/vehiculos-functions.js"></script>

<script src="assets/js/vehiculos-funcitons.js"></script>
<script src="assets/js/dashboard.js"></script>

</body>
</html>