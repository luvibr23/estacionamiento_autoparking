<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../login_final.php');
    exit;
}

// Verificar rol de operador
$role = $_SESSION['role'] ?? 'operator';
if ($role === 'admin') {
    // Redirigir a dashboard de admin si es administrador
    header('Location: admin_dashboard.php');
    exit;
}

// Obtener información del usuario
$username = $_SESSION['username'];
$nombre = $_SESSION['nombre'] ?? 'Operador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operador - Sistema de Estacionamiento</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="user-operator">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-info fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-parking me-2"></i>Sistema de Estacionamiento - Operador</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <span id="user-name"><?= htmlspecialchars($nombre) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
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
                            <a class="nav-link" href="#" onclick="showSection('tiempo-real')">
                                <i class="fas fa-clock me-2"></i>Tiempo Real
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showSection('reportes')">
                                <i class="fas fa-chart-bar me-2"></i>Reportes Básicos
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
                        <h1 class="h2">Dashboard de Operador</h1>
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
                        <div class="col-xl-4 col-md-6 mb-4">
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

                        <div class="col-xl-4 col-md-6 mb-4">
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

                        <div class="col-xl-4 col-md-6 mb-4">
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

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3 mb-3">
                                            <button class="btn btn-primary btn-lg w-100" onclick="mostrarModalVehiculo()">
                                                <i class="fas fa-plus fa-2x mb-2"></i>
                                                <br>Registrar Vehículo
                                            </button>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <button class="btn btn-success btn-lg w-100" onclick="showSection('tiempo-real')">
                                                <i class="fas fa-eye fa-2x mb-2"></i>
                                                <br>Ver Espacios
                                            </button>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <button class="btn btn-warning btn-lg w-100" onclick="buscarVehiculoSalida()">
                                                <i class="fas fa-sign-out-alt fa-2x mb-2"></i>
                                                <br>Procesar Salida
                                            </button>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <button class="btn btn-info btn-lg w-100" onclick="mostrarModalCliente()">
                                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                                <br>Nuevo Cliente
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-lg-12">
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
                                                    <th>Tiempo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" class="text-center">Cargando...</td>
                                                </tr>
                                            </tbody>
                                        </table>
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

                    <!-- Búsqueda Rápida -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="buscar-placa" placeholder="Buscar por placa...">
                                <button class="btn btn-outline-secondary" type="button" onclick="buscarVehiculoPorPlaca()">Buscar</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group w-100">
                                <button class="btn btn-outline-primary" onclick="filtrarVehiculos('todos')">Todos</button>
                                <button class="btn btn-outline-success" onclick="filtrarVehiculos('activos')">En Estacionamiento</button>
                                <button class="btn btn-outline-secondary" onclick="filtrarVehiculos('registrados')">Solo Registrados</button>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Vehículos</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabla-vehiculos">
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
                                        <tr>
                                            <td colspan="6" class="text-center">Cargando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tiempo Real Section -->
                <div id="tiempo-real-section" class="content-section" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Estado en Tiempo Real</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button class="btn btn-success me-2" onclick="actualizarTiempoReal()">
                                <i class="fas fa-sync-alt me-2"></i>Actualizar
                            </button>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center border-primary">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">Total</h5>
                                    <h2 class="display-4" id="total-espacios">50</h2>
                                    <small class="text-muted">Espacios</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-success">
                                <div class="card-body">
                                    <h5 class="card-title text-success">Disponibles</h5>
                                    <h2 class="display-4 text-success" id="espacios-libres">50</h2>
                                    <small class="text-muted">Libres</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-danger">
                                <div class="card-body">
                                    <h5 class="card-title text-danger">Ocupados</h5>
                                    <h2 class="display-4 text-danger" id="espacios-ocupados-rt">0</h2>
                                    <small class="text-muted">En uso</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center border-warning">
                                <div class="card-body">
                                    <h5 class="card-title text-warning">Ocupación</h5>
                                    <h2 class="display-4 text-warning" id="porcentaje-ocupacion">0%</h2>
                                    <small class="text-muted">Porcentaje</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Mapa de Espacios</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-center gap-4">
                                        <div><span class="parking-space available me-2" style="width: 20px; height: 20px; display: inline-block;"></span>Disponible</div>
                                        <div><span class="parking-space occupied me-2" style="width: 20px; height: 20px; display: inline-block;"></span>Ocupado</div>
                                        <div><span class="parking-space reserved me-2" style="width: 20px; height: 20px; display: inline-block;"></span>Reservado</div>
                                    </div>
                                </div>
                            </div>
                            <div id="mapa-espacios" class="parking-map">
                                <!-- Mapa visual se genera por JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reportes Section -->
                <div id="reportes-section" class="content-section" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Reportes Básicos</h1>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card shadow text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Vehículos Hoy</h5>
                                    <h2 class="text-primary" id="vehiculos-hoy">0</h2>
                                    <small class="text-muted">Registros del día</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Tiempo Promedio</h5>
                                    <h2 class="text-info" id="tiempo-promedio">0h</h2>
                                    <small class="text-muted">Estancia promedio</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Ocupación Máxima</h5>
                                    <h2 class="text-warning" id="ocupacion-maxima">0%</h2>
                                    <small class="text-muted">Pico del día</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Resumen de Actividad Diaria</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Hora</th>
                                            <th>Entradas</th>
                                            <th>Salidas</th>
                                            <th>Ocupación</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-resumen-horario">
                                        <tr>
                                            <td colspan="4" class="text-center">Cargando datos...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modales (reutilizando los mismos del admin pero sin funciones restringidas) -->
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

    <!-- Modal Buscar Vehículo para Salida -->
    <div class="modal fade" id="modalBuscarSalida" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buscar Vehículo para Salida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Buscar por Placa</label>
                        <input type="text" class="form-control" id="buscar-placa-salida" placeholder="Ingrese la placa del vehículo">
                    </div>
                    <div id="resultados-busqueda-salida"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="buscarVehiculoActivo()">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.3/sweetalert2.min.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    
    <script>
        // Funciones específicas para operador
        function buscarVehiculoSalida() {
            const modal = new bootstrap.Modal(document.getElementById('modalBuscarSalida'));
            modal.show();
        }
        
        function buscarVehiculoActivo() {
            const placa = document.getElementById('buscar-placa-salida').value.trim();
            if (!placa) {
                showNotification('Ingrese una placa para buscar', 'error');
                return;
            }
            
            // Implementar búsqueda de vehículo activo
            makeRequest('listar_vehiculos', { placa: placa, en_estacionamiento: true })
                .then(data => {
                    if (data.success && data.vehiculos.length > 0) {
                        const vehiculo = data.vehiculos[0];
                        if (vehiculo.registro_id) {
                            if (confirm(`¿Procesar salida del vehículo ${vehiculo.placa}?`)) {
                                procesarSalida(vehiculo.registro_id);
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalBuscarSalida'));
                                modal.hide();
                            }
                        } else {
                            showNotification('El vehículo no está actualmente en el estacionamiento', 'warning');
                        }
                    } else {
                        showNotification('No se encontró el vehículo o no está en el estacionamiento', 'warning');
                    }
                })
                .catch(error => {
                    showNotification('Error al buscar vehículo', 'error');
                });
        }
        
        function filtrarVehiculos(filtro) {
            const params = {};
            if (filtro === 'activos') {
                params.en_estacionamiento = true;
            }
            
            makeRequest('listar_vehiculos', params)
                .then(data => {
                    if (data.success) {
                        vehiculosData = data.vehiculos;
                        updateVehiculosTable();
                    }
                });
        }
        
        function buscarVehiculoPorPlaca() {
            const placa = document.getElementById('buscar-placa').value.trim();
            if (placa.length >= 3) {
                makeRequest('listar_vehiculos', { placa: placa })
                    .then(data => {
                        if (data.success) {
                            vehiculosData = data.vehiculos;
                            updateVehiculosTable();
                        }
                    });
            } else {
                loadVehiculos();
            }
        }
        
        // Inicialización específica para operador
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard de Operador cargado para:', '<?= htmlspecialchars($username) ?>');
            
            // Auto-buscar cuando se escribe en el campo de búsqueda
            const buscarPlaca = document.getElementById('buscar-placa');
            if (buscarPlaca) {
                let timeoutId;
                buscarPlaca.addEventListener('input', function() {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => {
                        if (this.value.length >= 3 || this.value.length === 0) {
                            buscarVehiculoPorPlaca();
                        }
                    }, 300);
                });
            }
        });
    </script>
    <script src="../assets/js/logout-functions.js"></script>
    <script src="../assets/js/vehiculos-functions.js"></script>
</body>
</html>